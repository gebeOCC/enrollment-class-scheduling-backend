<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EnrolledStudent;
use App\Models\SchoolYear;
use App\Models\StudentAttendance;
use App\Models\YearSectionSubjects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentClassController extends Controller
{
    public function getStudentClasses(Request $request)
    {
        $studentId = $request->user()->id;

        $currentSchoolYear = SchoolYear::select('school_years.id', 'start_year', 'end_year', 'semester_id', 'semester_name')
            ->where('is_current', '=', 1)
            ->join('semesters', 'semesters.id', '=', 'school_years.semester_id')
            ->first();

        if (!$currentSchoolYear) {
            return response(['message' => 'no current school year']);
        }

        $studentClasses = EnrolledStudent::whereHas('YearSection', function ($query) use ($currentSchoolYear) {
            $query->where('school_year_id', '=', $currentSchoolYear->id);
        })
            ->whereHas('student', function ($query) use ($studentId) {
                $query->where('id', '=', $studentId);
            })
            ->with([
                'YearSection' => function ($query) use ($currentSchoolYear) {
                    $query->where('school_year_id', '=', $currentSchoolYear->id);
                },
                'Evaluator.EvaluatorInformation',
                'StudentType',
                'YearSection.Course',
                'YearSection.YearLevel',
                'YearSection.SchoolYear.Semester',
                'StudentSubject.YearSectionSubjects.Subject',
                'StudentSubject.YearSectionSubjects.Instructor.InstructorInformation',
                'StudentSubject.YearSectionSubjects.Room',
                'StudentSubject.YearSectionSubjects.SubjectSecondarySchedule.Room',
                'Student.StudentInformation'
            ])
            ->first();

        if (!$studentClasses || !$studentClasses->YearSection) {
            return response([
                'message' => 'not enrolled',
                'schoolYear' => $currentSchoolYear,
            ]);
        }

        return response([
            'message' => 'success',
            'studentClasses' => $studentClasses,
            'schoolYear' => $currentSchoolYear,
        ]);
    }

    public function
    getEnrollmentRecord(Request $request)
    {
        $studentId = $request->user()->id;

        $studentClasses = EnrolledStudent::where('student_id', '=', $studentId)
            ->with(
                'Evaluator.EvaluatorInformation',
                'StudentType',
                'YearSection.Course',
                'YearSection.YearLevel',
                'YearSection.SchoolYear.Semester',
                'StudentSubject.YearSectionSubjects.Subject',
                'StudentSubject.YearSectionSubjects.Instructor.InstructorInformation',
                'StudentSubject.YearSectionSubjects.Room',
                'StudentSubject.YearSectionSubjects.SubjectSecondarySchedule.Room',
                'Student.StudentInformation'
            )
            ->get();

        if ($studentClasses->isEmpty()) {
            return response(['message' => 'no data']);
        }

        return response(['message' => 'success', 'studentClasses' => $studentClasses]);
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
}
