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
        Schema::whenTableDoesntHaveColumn('users', 'is_banned', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('leader_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::whenTableHasColumn('users', 'is_banned', function (Blueprint $table) {
            $table->dropColumn('is_banned');
        });
    }
};
