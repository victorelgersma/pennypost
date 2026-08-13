<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;

class DeliverMessages extends Command
{
    protected $signature = 'messages:deliver';

    protected $description = 'Deliver all messages whose scheduled batch time has arrived';

    public function handle(): int
    {
        $due = Message::query()->due()->get();

        if ($due->isEmpty()) {
            $this->info('No messages are due for delivery.');
            return self::SUCCESS;
        }

        Message::query()->due()->update(['delivered_at' => now()]);

        $this->info("Delivered {$due->count()} message(s).");

        return self::SUCCESS;
    }
}
