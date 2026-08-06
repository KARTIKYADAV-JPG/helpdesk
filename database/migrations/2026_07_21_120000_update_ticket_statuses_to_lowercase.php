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
        // Standardize status values in existing database rows to lowercase
        DB::table('tickets')->where('status', 'Open')->update(['status' => 'open']);
        DB::table('tickets')->where('status', 'In Progress')->update(['status' => 'in_progress']);
        DB::table('tickets')->where('status', 'Resolved')->update(['status' => 'resolved']);
        DB::table('tickets')->where('status', 'Closed')->update(['status' => 'closed']);

        // Update default column value for status to 'new'
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('status')->default('new')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('status')->default('open')->change();
        });
    }
};
