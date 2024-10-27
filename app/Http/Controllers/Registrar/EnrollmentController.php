<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\YearSectionSubjects;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function getSubjectClasses($subjectId)
    {
        $schoolYearId = SchoolYear::where('enrollment_status', '=', 'ongoing')->first()->id;

        return YearSectionSubjects::select(
            'class_code',
            'day',
            'end_time',
            'faculty_id',
            'year_section_subjects.id',
            'room_id',
            'start_time',
            'subject_id',
            'year_section_id',
            'subject_code',
            'descriptive_title',
            'credit_units',
            'section',
            'course_id',
            'course_name_abbreviation',
            'year_level_id',
            'year_level',
        )
            ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
            ->join('subjects', 'subjects.id', '=', 'subject_id')
            ->join('course', 'course.id', '=', 'year_section.course_id')
            ->join('year_level', 'year_level.id', '=', 'year_section.year_level_id')
            ->where('subject_id', '=', $subjectId)
            ->where('school_year_id', '=', $schoolYearId)
            ->get();
    }
}
