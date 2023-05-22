<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'line',
        'ids',
    ];

    protected $casts = [
        'ids' => 'array',
    ];
}
