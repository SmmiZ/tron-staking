<?php

namespace App\Models;

use App\Enums\Statuses;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'trx_amount',
        'status',
    ];

    protected $casts = [
        'status' => Statuses::class,
    ];

    /**
     * Атрибут. CSS - класс для вывода статуса
     *
     * @return Attribute
     */
    public function statusClass(): Attribute
    {
        return Attribute::get(fn() => match (true) {
            $this->status === Statuses::completed => 'label label-success',
            $this->status === Statuses::declined => 'label label-error',
            default => 'label',
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
