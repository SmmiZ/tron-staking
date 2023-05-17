<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Structure\LevelCollection;
use App\Models\LeaderLevel;

class StructureController extends Controller
{
    public function levels(): LevelCollection
    {
        return new LevelCollection(LeaderLevel::all());
    }
}
