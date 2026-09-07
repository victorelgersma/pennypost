<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['enclosure_url', 'enclosure_label']);
            $table->json('enclosures')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('enclosures');
            $table->string('enclosure_url')->nullable()->after('body');
            $table->string('enclosure_label')->nullable()->after('enclosure_url');
        });
    }
};

