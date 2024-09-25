<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use App\Models\StudentType;
use App\Models\YearLevel;
use Illuminate\Http\Request;

class PreEnrollmentController extends Controller
{
    public function getYearLevelAndStudentType()
    {
        $yearLevel = YearLevel::select('id', 'year_level_name')
            ->get();

        $studentType = StudentType::select('id', 'student_type_name')
            ->get();

        return response(['yearLevel' => $yearLevel, 'studentType' => $studentType]);
    }
}
