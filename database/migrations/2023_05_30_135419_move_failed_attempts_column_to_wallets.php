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
        Schema::whenTableHasColumn('stakes', 'failed_attempts', function (Blueprint $table) {
            $table->dropColumn('failed_attempts');
        });
        Schema::whenTableDoesntHaveColumn('wallets', 'failed_attempts', function (Blueprint $table) {
            $table->integer('failed_attempts')->default(0)->after('address')->comment('Кол-во неудачных попыток');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::whenTableHasColumn('wallets', 'failed_attempts', function (Blueprint $table) {
            $table->dropColumn('failed_attempts');
        });
        Schema::whenTableDoesntHaveColumn('stakes', 'failed_attempts', function (Blueprint $table) {
            $table->integer('failed_attempts')->default(0)->after('updated_at')->comment('Кол-во неудачных попыток');
        });
    }
};
