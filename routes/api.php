<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Registrar\DepartmentController;
use App\Http\Controllers\Registrar\RoomController;
use App\Http\Controllers\Registrar\FacultyController;
use App\Http\Controllers\Registrar\StudentController;
use App\Http\Controllers\All\SemesterController;
use App\Http\Controllers\All\SchoolYearController;
use App\Http\Controllers\ProgramHead\CourseController;
use App\Http\Controllers\All\YearLevelController;
use App\Http\Controllers\ProgramHead\CurriculumController;
use App\Http\Controllers\ProgramHead\EnrollmentCourseController;

Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('add-department', [DepartmentController::class, 'addDepartment']);
    Route::get('get-departments-courses', [DepartmentController::class, 'getDepartmentsCourses']);
    Route::get('get-departments', [DepartmentController::class, 'getDepartments']);
    Route::post('assign-program-head', [DepartmentController::class, 'assignProgramHead']);
    Route::post('assign-new-program-head', [DepartmentController::class, 'assignNewProgramHead']);
    Route::get('get-department-faculties/{id}', [DepartmentController::class, 'getDepartmentFaculties']);

    Route::get('get-department-courses', [CourseController::class, 'getDepartmentCourses']);
    Route::get('get-course-curriculums/{courseid}', [CourseController::class, 'getCourseCurriculums']);
    Route::get('get-course-name/{courseid}', [CourseController::class, 'getCourseName']);
    Route::post('add-course-curriculum/{courseid}', [CourseController::class, 'addCourseCurriculum']);

    Route::get('get-year-levels', [YearLevelController::class, 'getYearLevels']);
    Route::get('get-curriculum-terms-subjects/{courseid}/{schoolyear}', [CurriculumController::class, 'getCurriculumTermsSubjects']);
    Route::post('add-curriculum-term', [CurriculumController::class, 'addCurriculumTerm']);
    Route::get('get-subjects', [CurriculumController::class, 'getSubjects']);
    Route::post('add-curr-term-subject', [CurriculumController::class, 'addCurrTermSubject']);
    
    Route::post('add-course', [DepartmentController::class, 'addCourse']);

    Route::post('add-room', [RoomController::class, 'addRoom']);
    Route::get('get-rooms', [RoomController::class, 'getRooms']);
    Route::post('assign-room', [RoomController::class, 'assignRoom']);
    Route::post('unassign-room/{id}', [RoomController::class, 'unassignRoom']);

    Route::get('get-faculty-list', [FacultyController::class, 'getFacultyList']);
    Route::post('add-faculty', [FacultyController::class, 'addFaculty']);

    Route::get('get-student-list', [StudentController::class, 'getStudentList']);
    Route::post('add-student', [StudentController::class, 'addStudent']);

    Route::get('get-semesters', [SemesterController::class, 'getSemesters']);
    Route::get('get-school-years', [SchoolYearController::class, 'getSchoolYears']);
    Route::get('get-school-year-details/{schoolYear}/{semester}', [SchoolYearController::class, 'getSchoolYearDetails']);
    Route::post('add-school-year', [SchoolYearController::class, 'addSchoolYear']);
    Route::post('stop-enrollment/{id}', [SchoolYearController::class, 'stopEnrollment']);
    Route::post('start-enrollment/{id}', [SchoolYearController::class, 'startEnrollment']);
    Route::post('resume-enrollment/{id}', [SchoolYearController::class, 'resumeEnrollment']);

    Route::get('enrollment/{courseid}', [EnrollmentCourseController::class, 'getYearLevelSections']);
    Route::post('add-new-section', [EnrollmentCourseController::class, 'addNewSection']);
});
