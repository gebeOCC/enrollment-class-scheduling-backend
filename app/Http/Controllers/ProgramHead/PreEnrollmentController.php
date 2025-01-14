<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use App\Models\EnrolledStudent;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentPreEnollmentList;
use App\Models\StudentPreEnollmentListSubject;
use App\Models\StudentSubject;
use App\Models\StudentType;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserInformation;
use App\Models\YearLevel;
use App\Models\YearSection;
use App\Models\YearSectionSubjects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PreEnrollmentController extends Controller
{
    public function getCourseYearLevelSujects($hashedCourseId, $yearLevelId)
    {
        $courseId = DB::table('course')
            ->select('id')
            ->where(DB::raw('MD5(id)'), '=', $hashedCourseId)
            ->first()->id;

        $schoolYearId = SchoolYear::where('enrollment_status', '=', 'ongoing')->first()->id;

        $subjects = Subject::select(
            'subjects.id',
            'subject_code',
            'descriptive_title',
            'credit_units',
            'lecture_hours',
            'laboratory_hours',
            'course_id',
            'school_year_id',
            'year_level_id',
        )
            ->join('year_section_subjects', 'subjects.id', '=', 'year_section_subjects.subject_id')
            ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
            ->where('course_id', '=', $courseId)
            ->where('year_level_id', '=', $yearLevelId)
            ->where('school_year_id', '=', $schoolYearId)
            ->get();

        return response(['subjects' => $subjects, 'message' => 'success']);
    }

    public function getStudentInfoStudentIdNumber($studentId)
    {
        $studentId = User::select('id')
            ->orWhere('user_id_no', 'like', '%' . $studentId)
            ->first();

        if (!$studentId) {
            return response(['message' => 'no user found']);
        }

        $student = UserInformation::select('user_id_no', 'user_id', 'first_name', 'middle_name', 'last_name')
        ->join('users', 'users.id', '=', 'user_information.user_id')
            ->where('user_id', '=', $studentId->id)
            ->first();

        return response(['message' => 'success', 'student' => $student, 'studentId' => $studentId->id]);
    }

    public function getYearLevelSectionSections($courseId, $yearLevelId)
    {
        $subjects = YearSection::select('year_section.id', 'section', 'course_id', 'year_level_id', 'max_students')
            ->selectRaw('COUNT(enrolled_students.id) as student_count')
            ->leftJoin('enrolled_students', 'year_section.id', '=', 'enrolled_students.year_section_id')
            ->where('course_id', '=', $courseId)
            ->where('year_level_id', '=', $yearLevelId)
            ->groupBy('year_section.id', 'section', 'course_id', 'year_level_id', 'max_students')
            ->get();

        return response(['message' => 'success', 'subjects' => $subjects]);
    }

    public function getYearLevelSectionSectionSubjects($id)
    {
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
            ->where('year_section_id', '=', $id)
            ->get();

        return response(['message' => 'success', 'classes' => $classes]);
    }

    public function submitStudentClasses($preEnrollmentId, $studentId, $yearSectionId, $studentTypeId, Request $request)
    {

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
                'year_section_subjects_id' => $classSubject['id'],
            ]);
        }

        StudentPreEnollmentList::where('id', $preEnrollmentId)
            ->update([
                'pre_enrollment_status' => 'enrolled'
            ]);

        return response(['message' => 'success']);
    }
}
