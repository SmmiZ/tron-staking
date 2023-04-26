<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaderLevel extends Model
{
    protected $fillable = [
        'name_ru',
        'name_en',
        'conditions',
        'alt_conditions',
        'reward',
    ];

    protected $casts = [
        'conditions' => 'object',
        'alt_conditions' => 'object',
    ];
}
