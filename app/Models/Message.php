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
        'is_draft',
        'scheduled_for',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'is_draft' => 'boolean',
            'scheduled_for' => 'immutable_datetime',
            'sent_at' => 'immutable_datetime',
            'delivered_at' => 'immutable_datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id')->withTrashed();
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id')->withTrashed();
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

    public function scopeDrafts(Builder $query): Builder
    {
        return $query->where('is_draft', true);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('is_draft', false);
    }

    public function scopeDue(Builder $query, ?CarbonInterface $asOf = null): Builder
    {
        return $query->sent()->undelivered()->where('scheduled_for', '<=', $asOf ?? now());
    }

    /**
     * Work out which weekly batch (Friday 12:00 UTC) a message sent "now"
     * will go out in. The cutoff to catch a given Friday's batch is the
     * preceding Monday at 12:00 UTC.
     */
    public static function nextBatchFor(?CarbonInterface $sentAt = null): CarbonImmutable
    {
        $sentAt = CarbonImmutable::instance($sentAt ?? now())->setTimezone('UTC');
        $probe = $sentAt->startOfDay();

        for ($i = 0; $i < 14; $i++) {
            if ($probe->isFriday()) {
                $friday = $probe->setTime(12, 0);
                $cutoff = $friday->subDays(config('pennypost.cutoff_days_before_batch'));

                if ($sentAt->lessThanOrEqualTo($cutoff)) {
                    return $friday;
                }
            }

            $probe = $probe->addDay();
        }

        return $sentAt->next(CarbonImmutable::FRIDAY)->setTime(12, 0);
    }


    public static function humanDayLabel(CarbonInterface $date): string
    {
        $date = CarbonImmutable::instance($date)->startOfDay();
        $today = CarbonImmutable::now('UTC')->startOfDay();

        return match (true) {
            $date->equalTo($today) => __('today'),
            $date->equalTo($today->addDay()) => __('tomorrow'),
            default => $date->format('D, j M'),
        };
    }

    /**
     * The next delivery event, full stop — regardless of whether a new
     * letter could still catch it. Distinct from nextBatchFor(): if it's
     * currently Friday before noon, that means today's batch (built from
     * letters sealed before this week's cutoff) hasn't gone out yet, so
     * "next delivery" is today, not next week.
     */
    public static function nextDeliveryAt(?CarbonInterface $asOf = null): CarbonImmutable
    {
        $asOf = CarbonImmutable::instance($asOf ?? now())->setTimezone('UTC');
        $today = $asOf->startOfDay();

        if ($today->isFriday()) {
            $todayNoon = $today->setTime(12, 0);

            if ($asOf->lessThanOrEqualTo($todayNoon)) {
                return $todayNoon;
            }
        }

        return $asOf->next(CarbonImmutable::FRIDAY)->setTime(12, 0);
    }
}
