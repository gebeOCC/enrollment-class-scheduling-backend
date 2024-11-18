<?php

namespace App\Http\Controllers\All;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\EnrolledStudent;
use App\Models\Faculty;
use Illuminate\Http\Request;
use App\Models\SchoolYear;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SchoolYearController extends Controller
{
    public function addSchoolYear(Request $request)
    {
        // Check if there's a conflicting enrollment period
        $conflict = SchoolYear::where(function ($query) use ($request) {
            $query->where('start_date', '<=', $request->end_date)
                ->where('end_date', '>=', $request->start_date);
        })
            ->exists();

        if ($conflict) {
            return response()->json(["message" => "There's a conflict with an existing enrollment period."]);
        }

        // Create the new school year if no conflict is found
        SchoolYear::create([
            'semester_id' => $request->semester_id,
            'start_year' => $request->start_year,
            'end_year' => $request->end_year,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json(["message" => "Success"]);
    }

    public function getSchoolYears()
    {
        $today = Carbon::now();
        $twoWeeksLater = Carbon::now()->addWeeks(2);

        $schoolYear = SchoolYear::select(
            'semester_id',
            'start_year',
            'end_year',
            'start_date',
            'end_date',
            'is_current',
            'semester_name',
            DB::raw("CASE 
                    WHEN '$today' BETWEEN start_date AND end_date 
                    THEN true 
                    ELSE false 
                 END as enrollment_ongoing"),
            DB::raw("CASE 
                    WHEN '$twoWeeksLater' >= start_date
                    THEN true 
                    ELSE false 
                 END as preparation"),
        )
            ->join('semesters', 'school_years.semester_id', '=', 'semesters.id')
            ->orderBy('school_years.id', 'desc')
            ->get();

        $semesters = Semester::select('id', 'semester_name')->get();

        return response(['school_years' => $schoolYear, 'semesters' => $semesters]);
    }

    public function getSchoolYearDetails($schoolYear, $semester, Request $request)
    {

        $today = Carbon::now();

        list($startYear, $endYear) = explode('-', $schoolYear);

        $twoWeeksLater = Carbon::now()->addWeeks(2);

        $schoolYearDetails = SchoolYear::select(
            'school_years.id',
            'semester_id',
            'start_year',
            'end_year',
            'start_date',
            'end_date',
            'is_current',
            'semester_name',
            DB::raw("CASE 
                    WHEN '$today' BETWEEN start_date AND end_date 
                    THEN true 
                    ELSE false 
                 END as enrollment_ongoing"),
            DB::raw("CASE 
                    WHEN '$twoWeeksLater' >= start_date
                    THEN true 
                    ELSE false 
                 END as preparation"),
        )
            ->join('semesters', 'semesters.id', '=', 'school_years.semester_id')
            ->where('school_years.start_year', '=', $startYear)
            ->where('school_years.end_year', '=', $endYear)
            ->where('semesters.semester_name', '=', $semester)
            ->first();

        $user = $request->user();

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
            "schoolYearDetails" => $schoolYearDetails
        ]);
    }

    public function setSyDefault($schoolYearid)
    {
        SchoolYear::where('is_current', '=', 1)->update([
            'is_current' => 0,
        ]);

        SchoolYear::where('id', '=', $schoolYearid)->update([
            'is_current' => 1,
        ]);

        return response(["message" => "success"]);
    }
}
