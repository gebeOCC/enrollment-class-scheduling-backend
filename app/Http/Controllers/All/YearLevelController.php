<?php

namespace App\Http\Controllers\All;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\YearLevel;

class YearLevelController extends Controller
{
    public function getYearLevels() {
        return YearLevel::select('id', 'year_level_name')
        ->get();
    }
}
