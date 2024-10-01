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
                'pre_enrollment_id' => $studentPreEnrollmentList->id,
                'subject_id' => $subject['id'],
            ]);
        }
        return response(['message' => 'success'], 200);
    }

    public function getPreEnrollmentList()
    {
        $schoolYearId = SchoolYear::where('enrollment_status', '=', 'ongoing')->first()->id;

        $pendingList = StudentPreEnollmentList::select(
            'student_pre_enrollment_list.id',
            'student_pre_enrollment_list.student_id',
            'school_year_id',
            'student_type_id',
            'course_id',
            'year_level_id',
            'pre_enrollment_status',
            'first_name',
            'middle_name',
            'last_name',
            'application_no',
            'user_id_no',
            'year_level_name',
            'course_name_abbreviation',
            'student_type_name'
        )
            ->join('user_information', 'student_pre_enrollment_list.student_id', '=', 'user_information.user_id')
            ->leftJoin('student', 'student_pre_enrollment_list.student_id', '=', 'student.student_id')
            ->leftJoin('users', 'users.id', '=', 'student_pre_enrollment_list.student_id')
            ->join('year_level', 'year_level.id', '=', 'student_pre_enrollment_list.year_level_id')
            ->join('course', 'course.id', '=', 'student_pre_enrollment_list.course_id')
            ->join('student_type', 'student_type.id', '=', 'student_pre_enrollment_list.student_type_id')
            ->where('pre_enrollment_status', '=', 'pending')
            ->where('school_year_id', '=', $schoolYearId)
            ->orderBy('student_pre_enrollment_list.updated_at', 'desc')
            ->get();

        // $enrolledList = StudentPreEnollmentList::select(
        //     'student_pre_enrollment_list.student_id',
        //     'school_year_id',
        //     'student_type_id',
        //     'course_id',
        //     'year_level_id',
        //     'pre_enrollment_status',
        //     'first_name',
        //     'middle_name',
        //     'last_name',
        //     'application_no',
        //     'user_id_no',
        //     'year_level_name',
        //     'course_name_abbreviation',
        //     'student_type_name'
        // )
        //     ->join('user_information', 'student_pre_enrollment_list.student_id', '=', 'user_information.user_id')
        //     ->leftJoin('student', 'student_pre_enrollment_list.student_id', '=', 'student.student_id')
        //     ->leftJoin('users', 'users.id', '=', 'student_pre_enrollment_list.student_id')
        //     ->join('year_level', 'year_level.id', '=', 'student_pre_enrollment_list.year_level_id')
        //     ->join('course', 'course.id', '=', 'student_pre_enrollment_list.course_id')
        //     ->join('student_type', 'student_type.id', '=', 'student_pre_enrollment_list.student_type_id')
        //     ->where('pre_enrollment_status', '=', 'enrolled')
        //     ->where('school_year_id', '=', $schoolYearId)
        //     ->orderBy('student_pre_enrollment_list.updated_at', 'desc')
        //     ->get();

        return response([
            'message' => 'success',
            'pending' => $pendingList,
            // 'enrolled' => $enrolledList
        ]);
    }

    public function getLatestStudents()
    {
        return User::select('users.id', 'user_id_no', 'first_name', 'last_name')
            ->join('user_information', 'users.id', '=', 'user_information.user_id')
            ->where('user_role', '=', 'student')
            ->whereNotNull('user_id_no') // Exclude users without a user_id_no
            ->orderBy('users.id', 'desc')
            ->take(5)
            ->get();
    }

    public function createUserId($id, Request $request)
    {

        $studentIdExist = User::where('user_id_no', '=', $request->user_id_no)
            ->whereNotNull('user_id_no')
            ->first();

        if ($studentIdExist) {
            return response(['message' => 'Student number exist']);
        }

        User::where('id', $id)->update([
            'user_id_no' => $request->user_id_no,
        ]);

        return response(['message' => 'success']);
    }

    public function getStudentPreEnrollmentSubjects($id)
    {
        $schoolYearId = SchoolYear::where('enrollment_status', '=', 'ongoing')->first()->id;

        $subjects = StudentPreEnollmentList::select('student_pre_enrollment_list.id', 'subject_id', 'subject_code', 'descriptive_title')
            ->join('student_pre_enrollment_list_subjects', 'student_pre_enrollment_list.id', '=', 'student_pre_enrollment_list_subjects.pre_enrollment_id')
            ->join('subjects', 'subjects.id', '=', 'student_pre_enrollment_list_subjects.subject_id')
            ->where('student_id', '=', $id)
            ->where('school_year_id', '=', $schoolYearId)
            ->get();

        return response(['subjects' => $subjects, 'message' => 'success']);
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
