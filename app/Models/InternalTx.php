<?php

namespace App\Models;

use App\Enums\InternalTxTypes;
use App\Events\ProfitReceivedEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
