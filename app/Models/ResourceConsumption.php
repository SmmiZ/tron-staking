<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceConsumption extends Model
{
    use HasFactory;

    protected $table = 'resource_consumption';

    protected $fillable = [
        'consumer_id',
        'day',
        'energy_amount',
        'bandwidth_amount',
        'created_at',
        'updated_at',
    ];
}
