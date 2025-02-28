<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\EnrolledStudent;
use Illuminate\Http\Request;
use App\Models\YearSectionSubjects;
use App\Models\SchoolYear;
use App\Models\StudentAttendance;
use App\Models\StudentSubject;
use App\Models\User;
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
            ->leftjoin('rooms', 'rooms.id', '=', 'year_section_subjects.room_id')
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

    public function getClassId($hashedclassId)
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
            ->leftjoin('rooms', 'rooms.id', '=', 'room_id')
            ->join('subjects', 'subjects.id', '=', 'year_section_subjects.subject_id')
            ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
            ->join('course', 'course.id', '=', 'year_section.course_id')
            ->where('year_section_subjects.id', '=', $classId)
            ->first();

        return response(['classInfo' => $class]);
    }

    public function getStudentAttendance($classId, $formattedDate)
    {
        return YearSectionSubjects::select(
            'first_name',
            'last_name',
            'middle_name',
            'attendance_status',
            'student_attendance.id',
            'attendance_status',
            'enrolled_students.student_id',
            'attendance_date'
        )
            ->where('year_section_subjects.id', '=', $classId)
            ->join('student_subjects', 'year_section_subjects.id', '=', 'student_subjects.year_section_subjects_id')
            ->join('enrolled_students', 'enrolled_students.id', '=', 'student_subjects.enrolled_students_id')
            ->join('users', 'users.id', '=', 'enrolled_students.student_id')
            ->join('user_information', 'users.id', '=', 'user_information.user_id')
            ->leftJoin('student_attendance', function ($join) use ($formattedDate) {
                $join->on('users.id', '=', 'student_attendance.student_id')
                    ->where('attendance_date', $formattedDate);
            })
            ->orderBy('last_name', 'asc')
            ->get();
    }

    public function updateStudentAttendanceStatus($classId, $status, $student_id, $formattedDate, $id)
    {
        StudentAttendance::where('student_id', '=', $student_id)
            ->where('year_section_subjects_id', '=', $classId)
            ->where('attendance_date', '=', $formattedDate)
            ->where('id', '=', $id)
            ->update([
                'attendance_status' => $status,
            ]);

        return response(['message' => 'success']);
    }

    public function createStudentAttendanceStatus($classId, $status, $student_id, $formattedDate)
    {
        $studentAttendance = StudentAttendance::where('student_id', '=', $student_id)
            ->where('year_section_subjects_id', '=', $classId)
            ->where('attendance_date', '=', $formattedDate)
            ->first();

        if ($studentAttendance) {
            $studentAttendance->update([
                'attendance_status' => $status,
            ]);
        } else {
            StudentAttendance::create([
                'year_section_subjects_id' => $classId,
                'student_id' => $student_id,
                'attendance_date' => $formattedDate,
                'attendance_status' => $status,
            ]);
        }
    }

    public function getClassAttendanceStatusCount($classId)
    {
        return StudentAttendance::where('year_section_subjects_id', $classId)
            ->select(
                'attendance_date',
                DB::raw('COUNT(CASE WHEN attendance_status = "Present" THEN 1 END) as present_count'),
                DB::raw('COUNT(CASE WHEN attendance_status = "Absent" THEN 1 END) as absent_count'),
                DB::raw('COUNT(CASE WHEN attendance_status = "Late" THEN 1 END) as late_count'),
                DB::raw('COUNT(CASE WHEN attendance_status = "Excused" THEN 1 END) as excused_count')
            )
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();
    }

    public function markAllStatus($id, $date, $status)
    {
        $students = StudentSubject::where('year_section_subjects_id', '=', $id)
            ->join('enrolled_students', 'enrolled_students.id', '=', 'student_subjects.enrolled_students_id')
            ->get();

        foreach ($students as $student) {
            $studentAttendance = StudentAttendance::where('student_id', '=', $student->student_id)
                ->where('year_section_subjects_id', '=', $id)
                ->where('attendance_date', '=', $date)
                ->first();

            if ($studentAttendance) {
                $studentAttendance->update([
                    'attendance_status' => $status,
                ]);
            } else {
                StudentAttendance::create([
                    'year_section_subjects_id' => $id,
                    'student_id' => $student->student_id,
                    'attendance_date' => $date,
                    'attendance_status' => $status,
                ]);
            }
        }

        return response(['message' => 'success']);
    }

    public function deleteAttendance($id, $date)
    {
        StudentAttendance::where('year_section_subjects_id', '=', $id)
            ->where('attendance_date', '=', $date)
            ->delete();
    }

    public function getStudentAttendanceInfo($id)
    {

        $dates = StudentAttendance::select('attendance_date')
            ->where('year_section_subjects_id', '=', $id)
            ->orderBy('attendance_date', 'ASC')
            ->get();

        $students = User::select('users.id', 'user_information.first_name', 'user_information.last_name', 'user_information.middle_name')
            ->with('StudentAttendance')
            ->join('enrolled_students', 'users.id', '=', 'enrolled_students.student_id')
            ->join('student_subjects', 'enrolled_students.id', '=', 'student_subjects.enrolled_students_id')
            ->join('user_information', 'users.id', '=', 'user_information.user_id')
            ->where('student_subjects.year_section_subjects_id', '=', $id)
            ->orderBy('user_information.last_name', 'ASC')
            ->get();

        //  StudentSubject::select('user_information.first_name', 'user_information.last_name', 'user_information.middle_name')
        //     ->where('student_subjects.year_section_subjects_id', '=', $id)
        //     ->join('enrolled_students', 'enrolled_students.id', '=', 'student_subjects.enrolled_students_id')
        //     ->join('users', 'users.id', '=', 'enrolled_students.student_id')
        //     ->join('user_information', 'users.id', '=', 'user_information.user_id')
        //     ->with(['StudentAttendance' => function ($query) {
        //         $query->where('student_id', 'users.id');
        //     }])
        //     ->get();

        return response(['message' => 'success', 'dates' => $dates, 'students' => $students]);
    }
}
