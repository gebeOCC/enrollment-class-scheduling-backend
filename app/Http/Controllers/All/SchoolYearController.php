<?php

namespace App\Http\Controllers\All;

use App\Http\Controllers\Controller;
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
                 END as enrollment_ongoing")
        )
            ->join('semesters', 'school_years.semester_id', '=', 'semesters.id')
            ->orderBy('school_years.id', 'desc')
            ->get();

        $semesters = Semester::select('id', 'semester_name')->get();

        return response(['school_years' => $schoolYear, 'semesters' => $semesters]);
    }

    public function getSchoolYearDetails($schoolYear, $semester)
    {

        $today = Carbon::now();

        list($startYear, $endYear) = explode('-', $schoolYear);

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
                 END as enrollment_ongoing")
        )
            ->join('semesters', 'semesters.id', '=', 'school_years.semester_id')
            // Check if the school year falls between start_year and end_year
            ->where('school_years.start_year', '=', $startYear)
            ->where('school_years.end_year', '=', $endYear)
            ->where('semesters.semester_name', '=', $semester)
            ->first();

        return response([
            "message" => "success",
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
