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
        $latestSchoolYear = SchoolYear::latest()->first();

        $classes = YearSectionSubjects::select(
            'year_section_subjects.id',
            'subject_id',
            'day',
            'start_time',
            'end_time',
            'room_id',
            'descriptive_title',
            'room_name',
            'section',
            'year_level',
            'course_name_abbreviation',
            'year_section.year_level_id',
            'year_section.course_id',
            'school_year_id',
        )
            ->selectRaw(
                "MD5(year_section_subjects.id) as hashed_year_section_subject_id"
            )
            ->join('subjects', 'subjects.id', '=', 'year_section_subjects.subject_id')
            ->join('rooms', 'rooms.id', '=', 'year_section_subjects.room_id')
            ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
            ->join('course', 'course.id', '=', 'year_section.course_id')
            ->join('year_level', 'year_level.id', '=', 'year_section.year_level_id')
            ->where('faculty_id', '=', $facultyId)
            ->where('school_year_id', '=', $latestSchoolYear->id)
            ->get();

        return $classes;
    }

    public function getClassStudents($hashedclassId)
    {
        $classId = YearSectionSubjects::where(DB::raw('MD5(id)'), '=', $hashedclassId)
            ->first()->id;

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
            'subject_code'
        )
            ->join('rooms', 'rooms.id', '=', 'room_id')
            ->join('subjects', 'subjects.id', '=', 'year_section_subjects.subject_id')
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
