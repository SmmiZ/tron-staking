<?php

use App\Enums\Statuses;
use App\Models\Stake;
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
        Schema::whenTableHasColumn('stakes', 'status', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dateTime('available_at')->nullable()->after('trx_amount');
        });

        Schema::whenTableHasColumn('stakes', 'available_at', function (Blueprint $table) {
            Stake::query()->whereNull('available_at')->update(['available_at' => now()]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::whenTableDoesntHaveColumn('stakes', 'status', function (Blueprint $table) {
            $table->dropColumn('available_at');
            $table->tinyInteger('status')->default(Statuses::new->value)->after('trx_amount');
        });
    }
};
