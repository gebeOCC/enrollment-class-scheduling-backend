<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\YearSectionSubjects;
use App\Models\SchoolYear;
use App\Models\StudentSubject;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    public function getFacultyClasses(Request $request)
    {
        $facultyId = $request->user()->id;

        $currentSchoolYear = SchoolYear::select('school_years.id',  'start_year', 'end_year', 'semester_id', 'semester_name')
            ->where('is_current', '=', 1)
            ->join('semesters', 'semesters.id', '=', 'school_years.semester_id')
            ->first();

        if (!$currentSchoolYear) {
            return response([
                'message' => 'no school year',
            ]);
        }

        $classes = YearSectionSubjects::select(
            'year_section_id',
            'faculty_id',
            'room_id',
            'subject_id',
            'day',
            'start_time',
            'end_time',
            'end_time',
            'descriptive_title',
            'room_name',
            'section',
            'year_level_id',
            'year_level',
            'course_id',
            'course_name_abbreviation',
            'school_year_id',
        )
            ->selectRaw(
                "SHA2(year_section_subjects.id, 256) as hashed_year_section_subject_id"
            )
            ->join('subjects', 'subjects.id', '=', 'year_section_subjects.subject_id')
            ->join('rooms', 'rooms.id', '=', 'year_section_subjects.room_id')
            ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
            ->join('year_level', 'year_level.id', '=', 'year_section.year_level_id')
            ->join('course', 'course.id', '=', 'year_section.course_id')
            ->where('faculty_id', '=', $facultyId)
            ->where('school_year_id', '=', $currentSchoolYear->id)
            ->get();

        return response([
            'message' => 'success',
            'classes' => $classes,
            'schoolYear' => $currentSchoolYear,
        ]);
    }

    public function getClassStudents($hashedclassId)
    {
        $yearSectionSubject = YearSectionSubjects::where(DB::raw('SHA2(id, 256)'), '=', $hashedclassId)
            ->first();

        $classId = $yearSectionSubject ? $yearSectionSubject->id : null;

        $class = YearSectionSubjects::select(
            'year_section_subjects.id',
            'start_time',
            'end_time',
            'day',
            'room_id',
            'subject_id',
            'year_section_id',
            'room_name',
            'descriptive_title',
            'subject_code',
            'section',
            'course_id',
            'course_name_abbreviation',
        )
            ->join('rooms', 'rooms.id', '=', 'room_id')
            ->join('subjects', 'subjects.id', '=', 'year_section_subjects.subject_id')
            ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
            ->join('course', 'course.id', '=', 'year_section.course_id')
            ->where('year_section_subjects.id', '=', $classId)
            ->first();

        $students = StudentSubject::select(
            'student_subjects.id',
            'enrolled_students_id',
            'year_section_subjects_id',
            'student_id',
            'user_id_no',
            'first_name',
            'middle_name',
            'last_name',
            'email_address',
            'year_section_id',
            'section',
            'year_level_id',
            'year_level',
            'course_id',
            'course_name_abbreviation',
        )
            ->join('enrolled_students', 'enrolled_students.id', '=', 'student_subjects.enrolled_students_id')
            ->join('user_information', 'user_information.user_id', '=', 'enrolled_students.student_id')
            ->join('users', 'users.id', '=', 'enrolled_students.student_id')
            ->join('year_section', 'year_section.id', '=', 'enrolled_students.year_section_id')
            ->join('year_level', 'year_level.id', '=', 'year_section.year_level_id')
            ->join('course', 'course.id', '=', 'year_section.course_id')
            ->where('year_section_subjects_id', '=', $classId)
            ->get();

        return response(['classInfo' => $class, 'students' => $students]);
    }
}
