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
        Schema::whenTableHasColumn('users', 'sort', function (Blueprint $table) {
            $table->dropColumn('sort');
            $table->string('lang', 5)->after('email')->default('ru');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::whenTableHasColumn('users', 'lang', function (Blueprint $table) {
            $table->dropColumn('lang');
            $table->integer('sort')->after('email')->default(50);
        });
    }
};
