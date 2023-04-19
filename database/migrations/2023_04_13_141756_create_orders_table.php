<?php

use App\Enums\{Resources, Statuses};
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Consumer::class)->constrained()->cascadeOnDelete();
            $table->tinyInteger('resource')->default(Resources::ENERGY->value);
            $table->decimal('resource_amount', 22, 4)->default(0);
            $table->string('status')->default(Statuses::new->value);
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
