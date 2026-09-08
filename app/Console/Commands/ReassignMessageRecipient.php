<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\User;
use Illuminate\Console\Command;

class ReassignMessageRecipient extends Command
{
    protected $signature = 'messages:reassign-recipient
        {from : Email or numeric ID of the current recipient}
        {to : Email or numeric ID of the new recipient}
        {--force : Actually perform the reassignment — omit this for a dry run that only lists what would change}';

    protected $description = 'List, and optionally reassign, every message addressed to one user so it points at another user instead';

    public function handle(): int
    {
        $from = $this->resolveUser($this->argument('from'));
        $to = $this->resolveUser($this->argument('to'));

        if (! $from) {
            $this->error("Could not find a user matching [{$this->argument('from')}] (checked trashed accounts too).");

            return self::FAILURE;
        }

        if (! $to) {
            $this->error("Could not find a user matching [{$this->argument('to')}] (checked trashed accounts too).");

            return self::FAILURE;
        }

        if ($from->id === $to->id) {
            $this->error('The "from" and "to" users are the same account.');

            return self::FAILURE;
        }

        $messages = Message::where('recipient_id', $from->id)
            ->with('sender')
            ->orderBy('sent_at')
            ->get();

        if ($messages->isEmpty()) {
            $this->info("No messages are addressed to {$from->name} ({$from->email}).");

            return self::SUCCESS;
        }

        $this->info("{$messages->count()} message(s) addressed to {$from->name} ({$from->email}):");
        $this->table(
            ['ID', 'From', 'Sent at', 'Delivered?', 'Draft?', 'Body (preview)'],
            $messages->map(fn (Message $m) => [
                $m->id,
                $m->sender->name ?? 'Deleted Account',
                $m->sent_at?->format('Y-m-d') ?? '—',
                $m->isDelivered() ? 'yes' : 'no',
                $m->is_draft ? 'yes' : 'no',
                str($m->body)->limit(60),
            ])
        );

        if (! $this->option('force')) {
            $this->newLine();
            $this->comment("Dry run — nothing was changed. Re-run with --force to reassign these to {$to->name} ({$to->email}).");

            return self::SUCCESS;
        }

        Message::where('recipient_id', $from->id)->update(['recipient_id' => $to->id]);

        $this->info("Reassigned {$messages->count()} message(s) to {$to->name} ({$to->email}).");

        return self::SUCCESS;
    }

    protected function resolveUser(string $identifier): ?User
    {
        $query = User::withTrashed();

        return is_numeric($identifier)
            ? $query->find((int) $identifier)
            : $query->where('email', $identifier)->first();
    }
}
