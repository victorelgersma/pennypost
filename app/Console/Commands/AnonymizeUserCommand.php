<?php

namespace App\Console\Commands;

use App\Actions\AnonymizeUser;
use App\Models\User;
use Illuminate\Console\Command;

class AnonymizeUserCommand extends Command
{
    protected $signature = 'users:anonymize {user : Email or numeric ID of the user to anonymize}';

    protected $description = "Anonymize a user account to 'Deleted Account' the same way self-service deletion does — for accounts that were soft-deleted directly in the database and never went through that flow";

    public function handle(AnonymizeUser $anonymizeUser): int
    {
        $identifier = $this->argument('user');

        $user = is_numeric($identifier)
            ? User::withTrashed()->find((int) $identifier)
            : User::withTrashed()->where('email', $identifier)->first();

        if (! $user) {
            $this->error("Could not find a user matching [{$identifier}] (checked trashed accounts too).");

            return self::FAILURE;
        }

        if (str_ends_with($user->email, '@deleted.pennypost.invalid')) {
            $this->info('That user already looks anonymized — nothing to do.');

            return self::SUCCESS;
        }

        if (! $this->confirm("Anonymize {$user->name} ({$user->email})? This deletes their drafts and cannot be undone.")) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $anonymizeUser($user);

        $this->info("Done — {$identifier} is now anonymized as 'Deleted Account'.");

        return self::SUCCESS;
    }
}
