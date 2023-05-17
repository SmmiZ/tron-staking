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
        'line_percents',
        'reward',
    ];

    protected $casts = [
        'level' => 'integer',
        'reward' => 'integer',
        'conditions' => 'object',
        'alt_conditions' => 'object',
        'line_percents' => 'array',
    ];
}
