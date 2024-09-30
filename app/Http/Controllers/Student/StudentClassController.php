<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EnrolledStudent;
use App\Models\SchoolYear;
use App\Models\YearSectionSubjects;
use Illuminate\Http\Request;

class StudentClassController extends Controller
{
    public function getStudentClasses(Request $request)
    {
        $studentId = $request->user()->id;
        $latestSchoolYear = SchoolYear::latest()->first();

        return EnrolledStudent::select(
            'enrolled_students.id',
            'enrolled_students_id',
            'year_section_subjects_id',
            'subject_id',
            'year_section_subjects.faculty_id',
            'enrolled_students.year_section_id',
            'year_section.school_year_id',
            'room_id',
            'first_name',
            'middle_name',
            'last_name',
            'room_name',
            'day',
            'start_time',
            'end_time',
            'descriptive_title',
            'credit_units',
            'class_code',
            'subject_code',
        )
            ->join('student_subjects', 'enrolled_students.id', 'student_subjects.enrolled_students_id')
            ->join('year_section', 'year_section.id', 'enrolled_students.year_section_id')
            ->join('year_section_subjects', 'year_section_subjects.id', 'student_subjects.year_section_subjects_id')
            ->join('rooms', 'rooms.id', 'year_section_subjects.room_id')
            ->join('subjects', 'subjects.id', 'year_section_subjects.subject_id')
            ->join('users', 'users.id', 'year_section_subjects.faculty_id')
            ->join('user_information', 'users.id', 'user_information.user_id')
            ->where('student_id', '=', $studentId)
            ->where('year_section.school_year_id', '=', $latestSchoolYear->id)
            ->get();
    }
}
