<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'body',
        'scheduled_for',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'immutable_datetime',
            'delivered_at' => 'immutable_datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function isDelivered(): bool
    {
        return $this->delivered_at !== null;
    }

    public function scopeDelivered(Builder $query): Builder
    {
        return $query->whereNotNull('delivered_at');
    }

    public function scopeUndelivered(Builder $query): Builder
    {
        return $query->whereNull('delivered_at');
    }

    public function scopeDue(Builder $query, ?CarbonInterface $asOf = null): Builder
    {
        return $query->undelivered()->where('scheduled_for', '<=', $asOf ?? now());
    }

    /**
     * Work out which weekly batch (Sunday 12:00 UTC) a message sent "now"
     * will go out in. The cutoff to catch a given Sunday's batch is the
     * preceding Friday at 12:00 UTC.
     */
    public static function nextBatchFor(?CarbonInterface $sentAt = null): CarbonImmutable
    {
        $sentAt = CarbonImmutable::instance($sentAt ?? now())->setTimezone('UTC');
        $probe = $sentAt->startOfDay();

        for ($i = 0; $i < 14; $i++) {
            if ($probe->isSunday()) {
                $sunday = $probe->setTime(12, 0);
                $cutoff = $sunday->subDays(2); // Friday 12:00 UTC

                if ($sentAt->lessThanOrEqualTo($cutoff)) {
                    return $sunday;
                }
            }

            $probe = $probe->addDay();
        }

        // Unreachable in practice — keeps the return type honest.
        return $sentAt->next(CarbonImmutable::SUNDAY)->setTime(12, 0);
    }
}