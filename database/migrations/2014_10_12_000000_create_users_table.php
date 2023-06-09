<?php

use App\Models\User;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->integer('sort')->default(50);
            $table->string('the_code', 8);
            $table->string('invitation_code', 8)->nullable();
            $table->string('linear_path')->nullable();
            $table->tinyInteger('leader_level')->default(0);
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        User::query()->create([
            'name' => 'System',
            'email' => 'no-reply@orlna7sd6fags8df67a.ru',
            'the_code' => '00000000',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
