<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentPreEnollmentList;
use App\Models\StudentPreEnollmentListSubject;
use App\Models\StudentType;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserInformation;
use App\Models\YearLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PreEnrollmentController extends Controller
{
    public function getYearLevelAndStudentType()
    {
        $yearLevel = YearLevel::select('id', 'year_level_name')
            ->get();

        $studentType = StudentType::select('id', 'student_type_name')
            ->get();

        $subjects = Subject::select(
            'subjects.id',
            'subject_code',
            'descriptive_title',
            'credit_units',
            'lecture_hours',
            'laboratory_hours'
        )
            ->get();

        return response(['yearLevel' => $yearLevel, 'studentType' => $studentType, 'subjects' => $subjects]);
    }

    public function addNewStudent(Request $request)
    {
        $user = User::create([
            'password' => Hash::make($request->password),
            'user_role' => $request->user_role,
        ]);

        UserInformation::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'gender' => $request->gender,
            'birthday' => $request->birthday,
            'contact_number' => $request->contact_number,
            'email_address' => $request->email_address,
            'present_address' => $request->present_address,
            'zip_code' => $request->zip_code,
        ]);

        Student::create([
            'student_id' => $user->id,
            'application_no' => $request->application_no,
        ]);

        return response(['message' => 'success', 'studentId' => $user->id]);
    }

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
            'school_year_id'
        )
            ->join('year_section_subjects', 'subjects.id', '=', 'year_section_subjects.subject_id')
            ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
            ->where('course_id', '=', $courseId)
            ->where('year_section_id', '=', $yearLevelId)
            ->where('school_year_id', '=', $schoolYearId)
            ->get();

        return response(['subjects' => $subjects, 'message' => 'success']);
    }

    public function getStudentInfoApplicaiotnId($studentId)
    {
        $studentId = Student::select('student_id')
            ->where('application_no', '=', $studentId)
            ->first();

        if (!$studentId) {
            return response(['message' => 'no user found']);
        }

        $student = UserInformation::select('user_id', 'first_name', 'middle_name', 'last_name')
            ->where('user_id', '=', $studentId->student_id)
            ->first();

        return response(['message' => 'success', 'student' => $student, 'studentId' => $studentId->student_id]);
    }

    public function getStudentInfoStudentIdNumber($studentId)
    {
        $studentId = User::select('id')
            ->where('user_id_no', '=', $studentId)
            ->first();

        if (!$studentId) {
            return response(['message' => 'no user found']);
        }

        $student = UserInformation::select('user_id', 'first_name', 'middle_name', 'last_name')
            ->where('user_id', '=', $studentId->id)
            ->first();

        return response(['message' => 'success', 'student' => $student, 'studentId' => $studentId->id]);
    }

    public function createStudentPreEnrollment($studentId, $student_type_id, $hashedCourseId, $year_level_id, Request $request)
    {
        $courseId = DB::table('course')
            ->select('id')
            ->where(DB::raw('MD5(id)'), '=', $hashedCourseId)
            ->first()->id;

        $schoolYearId = SchoolYear::where('enrollment_status', '=', 'ongoing')->first()->id;

        $studentPreEnrollmentList = StudentPreEnollmentList::create([
            'student_id' => $studentId,
            'school_year_id' => $schoolYearId,
            'student_type_id' => $student_type_id,
            'course_id' => $courseId,
            'year_level_id' => $year_level_id,
            'pre_enrollment_status' => 'pending',
        ]);

        $subjects = $request->input('subjects');

        foreach ($subjects as $subject) {
            StudentPreEnollmentListSubject::create([
                'pre_enrollment_id' =>  $studentPreEnrollmentList->id,
                'subject_id' => $subject['id'],
            ]);
        }
        return response(['message' => 'success'], 200);
    }
}
