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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('address');
            $table->string('subscribe_address')->nullable()->comment('Chain gateway api');
            $table->integer('subscribe_id')->nullable()->comment('Chain gateway api');
            $table->decimal('balance', 18, 4)->default(0)->comment('TRX');
            $table->json('token_balance')->nullable();
            $table->timestamp('last_transaction_time')->nullable();
            $table->timestamp('stake_timestamp')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
