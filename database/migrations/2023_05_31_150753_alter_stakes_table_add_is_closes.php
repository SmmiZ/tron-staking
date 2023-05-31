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
        Schema::whenTableDoesntHaveColumn('stakes', 'is_closes', function (Blueprint $table) {
            $table->boolean('is_closes')->default(false)->after('trx_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::whenTableHasColumn('stakes', 'is_closes', function (Blueprint $table) {
            $table->dropColumn('is_closes');
        });
    }
};
