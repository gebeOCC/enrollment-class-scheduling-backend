<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\EnrolledStudent;
use App\Models\Faculty;
use App\Models\SchoolYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getCourseEnrolledStudents()
    {
        $userId = Auth::user()->id;

        $department = Faculty::select('department_id', 'faculty_id', 'department_name')
            ->where('faculty_id', '=', $userId)
            ->join('department', 'department.id', '=', 'faculty.department_id')
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

        $totalStudents = Course::where('department_id', '=', $department->department_id)
            ->leftJoin('year_section', 'course.id', '=', 'year_section.course_id')
            ->leftJoin('enrolled_students', function ($join) use ($schoolYearId) {
                $join->on('year_section.id', '=', 'enrolled_students.year_section_id')
                    ->where('year_section.school_year_id', '=', $schoolYearId);
            })
            ->select('course.id', 'course.course_name', 'course.course_name_abbreviation', DB::raw('COUNT(enrolled_students.id) as total_students'))
            ->groupBy('course.id', 'course.course_name', 'course.course_name_abbreviation',)
            ->get();

        $dateEnrolled = EnrolledStudent::select('date_enrolled', 'enrolled_students.year_section_id', 'course_id', 'course_name_abbreviation', DB::raw('COUNT(enrolled_students.id) as total_students'))
            ->groupBy('date_enrolled', 'enrolled_students.year_section_id', 'course_id', 'course_name_abbreviation')
            ->join('year_section', 'year_section.id', '=', 'enrolled_students.year_section_id')
            ->join('course', 'course.id', '=', 'year_section.course_id')
            ->get();

        return response(['message' => 'success', 'totalStudents' => $totalStudents, 'department' => $department, 'dateEnrolled' => $dateEnrolled]);
    }

    public function getEnrollmentDashboardData(Request $request)
    {
        $user = $request->user();

        $department = Faculty::select('department_id', 'faculty_id', 'department_name')
            ->where('faculty_id', '=', $user->id)
            ->join('department', 'department.id', '=', 'faculty.department_id')
            ->first();

        $today = Carbon::now();
        $twoWeeksLater = Carbon::now()->addWeeks(2);

        $enrollmentOngoing = SchoolYear::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->exists();

        $enrollmentPreparation = SchoolYear::whereDate('start_date', '<=', $twoWeeksLater)->exists();

        $schoolYearDetails = [];

        if ($enrollmentOngoing) {
            $schoolYearDetails = SchoolYear::where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->with('Semester')
                ->first();
        } elseif ($enrollmentPreparation) {
            $schoolYearDetails = SchoolYear::whereDate('start_date', '<=', $twoWeeksLater)
            ->with('Semester')
            ->first();
        }

        $coursesReports =  [];

        if ($user->user_role == "program_head" || $user->user_role == "evaluator") {
            $coursesReports =
                Faculty::where('faculty_id', $user->id)
                ->with([
                    'Department.Course' => function ($query) use ($schoolYearDetails) {
                        $query->withCount([
                            // Total Male Students in All First-Year
                            'YearSection as first_year_male_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                                    ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                                    ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                                    ->where('year_level_id', '=', 1)
                                    ->where('gender', 'Male');
                            },
                            // Total Female Students in All First-Year
                            'YearSection as first_year_female_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                                    ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                                    ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                                    ->where('year_level_id', '=', 1)
                                    ->where('gender', 'Female');
                            },
                            // Total Male Students in All Second-Year
                            'YearSection as second_year_male_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                                    ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                                    ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                                    ->where('year_level_id', '=', 2)
                                    ->where('gender', 'Male');
                            },
                            // Total Female Students in All Second-Year
                            'YearSection as second_year_female_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                                    ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                                    ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                                    ->where('year_level_id', '=', 2)
                                    ->where('gender', 'Female');
                            },
                            // Total Male Students in All Second-Year
                            'YearSection as third_year_male_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                                    ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                                    ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                                    ->where('year_level_id', '=', 3)
                                    ->where('gender', 'Male');
                            },
                            // Total Female Students in All Second-Year
                            'YearSection as third_year_female_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                                    ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                                    ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                                    ->where('year_level_id', '=', 3)
                                    ->where('gender', 'Female');
                            },
                            // Total Male Students in All Second-Year
                            'YearSection as fourth_year_male_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                                    ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                                    ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                                    ->where('year_level_id', '=', 4)
                                    ->where('gender', 'Male');
                            },
                            // Total Female Students in All Second-Year
                            'YearSection as fourth_year_female_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                                    ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                                    ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                                    ->where('year_level_id', '=', 4)
                                    ->where('gender', 'Female');
                            },
                            // Total Freshman
                            'YearSection as freshman_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                                    ->where('student_type_id', '=', 1);
                            },
                            // Total Transferee
                            'YearSection as transferee_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                                    ->where('student_type_id', '=', 2);
                            },
                            // Total Old
                            'YearSection as old_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                                    ->where('student_type_id', '=', 3);
                            },
                            // Total Returnee
                            'YearSection as returnee_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                                    ->where('student_type_id', '=', 4);
                            },
                            // Total students enrolled
                            'YearSection as enrolled_student_count' => function ($sectionQuery) use ($schoolYearDetails) {
                                $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                                    ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id');
                            },
                        ])->with(['YearSection' => function ($query) {
                            $query->join('enrolled_students', 'year_section.id', '=', 'enrolled_students.year_section_id')
                                ->join('course', 'course.id', '=', 'year_section.course_id')
                                ->select(
                                    'enrolled_students.date_enrolled',  // Only select the enrollment date
                                    'course_id',
                                    'course_name_abbreviation',
                                    DB::raw('COUNT(enrolled_students.id) as total_students')
                                )
                                ->groupBy('enrolled_students.date_enrolled', 'course_id', 'course_name_abbreviation'); // Group by date and course
                        }]);
                    },
                ])
                ->first();
        } else if ($user->user_role == "registrar") {
            $coursesReports =
                Course::withCount([
                    // Total Male Students in All First-Year
                    'YearSection as first_year_male_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                            ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                            ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                            ->where('year_level_id', '=', 1)
                            ->where('gender', 'Male');
                    },
                    // Total Female Students in All First-Year
                    'YearSection as first_year_female_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                            ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                            ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                            ->where('year_level_id', '=', 1)
                            ->where('gender', 'Female');
                    },
                    // Total Male Students in All Second-Year
                    'YearSection as second_year_male_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                            ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                            ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                            ->where('year_level_id', '=', 2)
                            ->where('gender', 'Male');
                    },
                    // Total Female Students in All Second-Year
                    'YearSection as second_year_female_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                            ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                            ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                            ->where('year_level_id', '=', 2)
                            ->where('gender', 'Female');
                    },
                    // Total Male Students in All Second-Year
                    'YearSection as third_year_male_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                            ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                            ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                            ->where('year_level_id', '=', 3)
                            ->where('gender', 'Male');
                    },
                    // Total Female Students in All Second-Year
                    'YearSection as third_year_female_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                            ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                            ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                            ->where('year_level_id', '=', 3)
                            ->where('gender', 'Female');
                    },
                    // Total Male Students in All Second-Year
                    'YearSection as fourth_year_male_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                            ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                            ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                            ->where('year_level_id', '=', 4)
                            ->where('gender', 'Male');
                    },
                    // Total Female Students in All Second-Year
                    'YearSection as fourth_year_female_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                            ->join('users', 'users.id',  '=',  'enrolled_students.student_id')
                            ->join('user_information', 'users.id',  '=',  'user_information.user_id')
                            ->where('year_level_id', '=', 4)
                            ->where('gender', 'Female');
                    },
                    // Total Freshman
                    'YearSection as freshman_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                            ->where('student_type_id', '=', 1);
                    },
                    // Total Transferee
                    'YearSection as transferee_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                            ->where('student_type_id', '=', 2);
                    },
                    // Total Old
                    'YearSection as old_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                            ->where('student_type_id', '=', 3);
                    },
                    // Total Returnee
                    'YearSection as returnee_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id')
                            ->where('student_type_id', '=', 4);
                    },
                    // Total students enrolled
                    'YearSection as enrolled_student_count' => function ($sectionQuery) use ($schoolYearDetails) {
                        $sectionQuery->where('school_year_id', $schoolYearDetails->id)
                            ->join('enrolled_students', 'year_section.id',  '=',  'enrolled_students.year_section_id');
                    },
                ])
                ->with(['YearSection' => function ($query) {
                    $query->join('enrolled_students', 'year_section.id', '=', 'enrolled_students.year_section_id')
                        ->join('course', 'course.id', '=', 'year_section.course_id')
                        ->select(
                            'enrolled_students.date_enrolled',  // Only select the enrollment date
                            'course_id',
                            'course_name_abbreviation',
                            DB::raw('COUNT(enrolled_students.id) as total_students')
                        )
                        ->groupBy('enrolled_students.date_enrolled', 'course_id', 'course_name_abbreviation'); // Group by date and course
                }])
                ->get();
        }

        return response([
            "message" => "success",
            "coursesReports" => $coursesReports,
            'department' => $department,
            'schoolYearDetails' => $schoolYearDetails,
        ]);
    }
}
