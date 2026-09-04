<?php

namespace App\Console\Commands;

use App\Mail\DatabaseBackupMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use ZipArchive;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--to=victor@vjbe.net : Email address to send the backup to}';

    protected $description = 'Snapshot the SQLite database, zip it, and email it as a backup';

    public function handle(): int
    {
        $databasePath = config('database.connections.sqlite.database');

        if (! is_string($databasePath) || $databasePath === ':memory:' || ! file_exists($databasePath)) {
            $this->error("No usable SQLite database file found at [{$databasePath}].");

            return self::FAILURE;
        }

        $timestamp = now()->format('Y-m-d');
        $workDir = storage_path('app/backups');

        if (! is_dir($workDir)) {
            mkdir($workDir, 0755, true);
        }

        $snapshotPath = "{$workDir}/pennypost-{$timestamp}.sqlite";
        $zipPath = "{$workDir}/pennypost-backup-{$timestamp}.zip";

        // Clean up any stale files from a previous failed run before we start.
        @unlink($snapshotPath);
        @unlink($zipPath);

        // VACUUM INTO takes a clean, consistent snapshot of the live database
        // without holding a lock that would block the app — safer than
        // copying the .sqlite file directly, which risks grabbing it
        // mid-write and producing a corrupt backup.
        DB::statement('VACUUM INTO ?', [$snapshotPath]);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            $this->error('Could not create the backup zip file.');
            @unlink($snapshotPath);

            return self::FAILURE;
        }

        $zip->addFile($snapshotPath, "pennypost-{$timestamp}.sqlite");
        $zip->close();

        // The loose .sqlite snapshot is no longer needed once it's zipped —
        // only the zip gets emailed and kept around briefly.
        @unlink($snapshotPath);

        $recipient = $this->option('to');

        Mail::to($recipient)->send(new DatabaseBackupMail($zipPath, basename($zipPath)));

        @unlink($zipPath);

        $this->info("Backup created and emailed to {$recipient}.");

        return self::SUCCESS;
    }
}
