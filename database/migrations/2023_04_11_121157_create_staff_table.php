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
        Schema::create('staff', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email',64)->unique();
            $table->string('name',64)->nullable();
            $table->string('password',64);
            $table->unsignedInteger('pin')->nullable();
            $table->rememberToken();
            $table->integer('access_level')->default('50');
            $table->boolean('is_enable')->default(1);
            $table->timestamps();
        });

        if (!app()->isProduction() && config('app.debug')) {
            Artisan::call('db:seed --class=StaffSeeder');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
