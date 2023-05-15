<?php

use App\Models\Consumer;
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
        Schema::create('resource_consumption', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Consumer::class)->constrained()->cascadeOnDelete();
            $table->date('day');
            $table->unsignedBigInteger('energy_amount');
            $table->unsignedBigInteger('bandwidth_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_consumption');
    }
};
