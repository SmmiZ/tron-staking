<?php

namespace App\Models;

use App\Enums\ReactorTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reactor extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
    ];

    protected $casts = [
        'type' => ReactorTypes::class,
        'active_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
