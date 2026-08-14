<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_draft')->default(true)->after('body');
            $table->timestamp('sent_at')->nullable()->after('scheduled_for');
        });

        // Recipient and scheduled_for are only required once a letter is
        // sealed and sent — drafts can exist without either.
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('recipient_id')->nullable()->change();
            $table->timestamp('scheduled_for')->nullable()->change();
        });

        // Every row that already existed was created under the old
        // "submit = send immediately" flow, so none of them are drafts.
        DB::table('messages')->update([
            'is_draft' => false,
            'sent_at' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['is_draft', 'sent_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('recipient_id')->nullable(false)->change();
            $table->timestamp('scheduled_for')->nullable(false)->change();
        });
    }
};
