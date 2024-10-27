<?php

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\EnrolledStudent;
use App\Models\SchoolYear;
use App\Models\StudentSubject;
use App\Models\StudentType;
use App\Models\Subject;
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
            'year_section_subjects.id',
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
            'studentType' => $studentType,
            'yearSectionId' => $yearSectionId
        ]);
    }

    public function getClasses($subjectCode)
    {

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

        $subject = Subject::where('subject_code', '=', $subjectCode)->first();

        if (!$subject) {
            return response([
                'message' => 'subject not found',
            ]);
        }

        $classes = YearSectionSubjects::select(
            'year_section_subjects.id',
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
            'school_year_id',
        )
            ->join('subjects', 'subjects.id', '=', 'subject_id')
            ->join('year_section', 'year_section.id', '=', 'year_section_id')
            ->where('subject_id', '=', $subject->id)
            ->where('school_year_id', '=', $schoolYearId)
            ->get();

        return response([
            'message' => 'success',
            'classes' => $classes,
        ]);
    }

    public function enrollStudent($studentId, $studentTypeId, $yearSectionId, Request $request)
    {
        $today = Carbon::now();
        $twoWeeksLater = Carbon::now()->addWeeks(2);

        $currentSchoolYearenrollment = SchoolYear::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($currentSchoolYearenrollment) {
            $schoolYearId = $currentSchoolYearenrollment->id;
        } else {
            $upcomingSchoolYear = SchoolYear::where('start_date', '<=', $twoWeeksLater)
                ->orderBy('start_date', 'asc')
                ->first();
            $schoolYearId = $upcomingSchoolYear ? $upcomingSchoolYear->id : null;
        }

        if (EnrolledStudent::where('student_id', '=', $studentId)->first()) {
            return response(['message' => 'student already enrolled']);
        }

        $enrolledStudent = EnrolledStudent::create([
            'student_id' => $studentId,
            'year_section_id' => $yearSectionId,
            'student_type_id' => $studentTypeId,
            'enroll_type' => 'on-time',
            'date_enrolled' => now(),
        ]);

        $classes = $request->input('classes');

        foreach ($classes as $classSubject) {
            StudentSubject::create([
                'enrolled_students_id' => $enrolledStudent->id,
                'year_section_subjects_id' => $classSubject['subject_id'],
            ]);
        }

        return response(['message' => 'success']);
    }
}
