<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Artisan, Schema};

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leader_levels', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('level');
            $table->string('name_ru');
            $table->string('name_en');
            $table->json('conditions')->nullable();
            $table->json('alt_conditions')->nullable();
            $table->json('line_percents')->nullable();
            $table->unsignedInteger('reward');
            $table->timestamps();
        });

        Artisan::call('db:seed --class=LeaderLevelsSeeder');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leader_levels');
    }
};
