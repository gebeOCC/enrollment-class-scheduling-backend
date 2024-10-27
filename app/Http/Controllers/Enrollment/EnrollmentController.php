<?php

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolYear;
use App\Models\StudentType;
use App\Models\YearSection;
use App\Models\YearSectionSubjects;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    public function getYearLevelSectionSectionSubjects($courseid, $yearLevelNumber, $section)
    {

        $course = Course::select('id')
            ->where(DB::raw('MD5(id)'), '=', $courseid)
            ->first();


        $today = Carbon::now();
        $twoWeeksLater = Carbon::now()->addWeeks(2);

        // Attempt to find the current school year
        $currentSchoolYearenrollment = SchoolYear::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($currentSchoolYearenrollment) {
            $schoolYearId = $currentSchoolYearenrollment->id;
        } else {
            // If no current school year is found, check for one starting within the next two weeks
            $upcomingSchoolYear = SchoolYear::where('start_date', '<=', $twoWeeksLater)
                ->orderBy('start_date', 'asc') // Optional: to get the earliest upcoming year
                ->first();

            $schoolYearId = $upcomingSchoolYear ? $upcomingSchoolYear->id : null;
        }

        $yearSectionId = YearSection::where('course_id', '=', $course->id)
            ->where('year_level_id', '=', $yearLevelNumber)
            ->where('school_year_id', '=', $schoolYearId)
            ->where('section', '=', $section)->first()->id;

        $classes = YearSectionSubjects::select(
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
        )
            ->join('subjects', 'subjects.id', '=', 'subject_id')
            ->where('year_section_id', '=', $yearSectionId)
            ->get();



        $studentType = StudentType::select('id', 'student_type_name')
            ->get();

        return response([
            'message' => 'success',
            'classes' => $classes,
            'studentType' => $studentType
        ]);
    }
}
