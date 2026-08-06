<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('tickets', 'email_message_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('email_message_id')->nullable()->index()->after('assigned_to');
                $table->text('raw_email')->nullable()->after('email_message_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tickets', 'email_message_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn(['email_message_id', 'raw_email']);
            });
        }
    }
};
