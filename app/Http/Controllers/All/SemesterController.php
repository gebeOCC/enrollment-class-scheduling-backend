<?php

namespace App\Http\Controllers\All;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Semester;

class SemesterController extends Controller
{
    public function getSemesters(){
        return Semester::select('id', 'semester_name')->get();
    }
}
