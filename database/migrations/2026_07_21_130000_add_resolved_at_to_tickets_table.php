<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('tickets', 'resolved_at')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->timestamp('resolved_at')->nullable()->after('status');
            });
        }

        // Backfill resolved_at for existing resolved tickets
        DB::table('tickets')
            ->where('status', 'resolved')
            ->whereNull('resolved_at')
            ->update(['resolved_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tickets', 'resolved_at')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('resolved_at');
            });
        }
    }
};
