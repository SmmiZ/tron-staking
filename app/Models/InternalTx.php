<?php

namespace App\Models;

use App\Enums\InternalTxTypes;
use App\Events\ProfitReceivedEvent;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class InternalTx extends Model
{
    use HasFactory;

    protected $table = 'internal_txs';

    protected static function booted(): void
    {
        static::created(function (InternalTx $tx) {
            if ($tx->type === InternalTxTypes::stakeProfit) {
                event(new ProfitReceivedEvent($tx));
            }
        });
    }

    /**
     * amount - Начислено
     * received - Фактически получено
     */
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'received',
    ];

    protected $casts = [
        'type' => InternalTxTypes::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Формирует текущий баланс для клиента.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeBalance(Builder $query): Builder
    {
        return $query
            ->select(DB::raw('SUM(CASE WHEN type < 200 THEN received ELSE received*-1 END) as received'))
            ->groupBy('user_id');
    }
}
