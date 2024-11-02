<?php

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
use App\Http\Controllers\Faculty\ClassController;
use App\Http\Controllers\ProgramHead\PreEnrollmentController;
use App\Http\Controllers\Student\StudentClassController;
use App\Http\Controllers\ProgramHead\DashboardController;
use App\Http\Controllers\ProgramHead\PhFacultyController;
use App\Http\Controllers\Enrollment\EnrollmentController;
use App\Http\Controllers\UserController;

Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // DEPARTMENT
    Route::post('add-department', [DepartmentController::class, 'addDepartment']);
    Route::get('get-departments-courses', [DepartmentController::class, 'getDepartmentsCourses']);
    Route::get('get-departments', [DepartmentController::class, 'getDepartments']);
    Route::post('assign-program-head', [DepartmentController::class, 'assignProgramHead']);
    Route::post('assign-new-program-head', [DepartmentController::class, 'assignNewProgramHead']);
    Route::get('get-department-faculties/{id}', [DepartmentController::class, 'getDepartmentFaculties']);

    // COURSES
    Route::get('get-department-courses', [CourseController::class, 'getDepartmentCourses']);
    Route::get('get-course-curriculums/{courseid}', [CourseController::class, 'getCourseCurriculums']);
    Route::get('get-course-name/{courseid}', [CourseController::class, 'getCourseName']);
    Route::post('add-course-curriculum/{courseid}', [CourseController::class, 'addCourseCurriculum']);
    Route::post('add-course', [DepartmentController::class, 'addCourse']);

    // CURRICULUM
    Route::get('get-year-levels', [YearLevelController::class, 'getYearLevels']);
    Route::get('get-curriculum-terms-subjects/{courseid}/{schoolyear}', [CurriculumController::class, 'getCurriculumTermsSubjects']);
    Route::post('add-curriculum-term', [CurriculumController::class, 'addCurriculumTerm']);
    Route::get('get-subjects', [CurriculumController::class, 'getSubjects']);
    Route::post('add-curr-term-subject/{id}', [CurriculumController::class, 'addCurrTermSubject']);

    // ROOM
    Route::post('add-room', [RoomController::class, 'addRoom']);
    Route::get('get-rooms', [RoomController::class, 'getRooms']);
    Route::post('assign-room', [RoomController::class, 'assignRoom']);
    Route::post('unassign-room/{id}', [RoomController::class, 'unassignRoom']);

    // USER
    Route::post('change-password', [UserController::class, 'changePassword']);

    // FACULTY
    Route::get('get-faculty-list', [FacultyController::class, 'getFacultyList']);
    Route::post('add-faculty', [FacultyController::class, 'addFaculty']);
    Route::get('ph-get-faculty-list', [PhFacultyController::class, 'getFacultyList']);
    Route::post('set-faculty-inactive/{id}', [PhFacultyController::class, 'setInactive']);
    Route::post('set-faculty-active/{id}', [PhFacultyController::class, 'setActive']);
    Route::post('set-faculty-evaluator/{id}', [PhFacultyController::class, 'setFacultyEvaluator']);
    Route::post('set-faculty-faculty/{id}', [PhFacultyController::class, 'setFacultyFaculty']);
    Route::get('get-faculty-details/{id}', [FacultyController::class, 'getFacultyDetails']);
    Route::post('set-faculty-department', [FacultyController::class, 'setFacultyDepartment']);

    // STUDENT
    Route::get('get-student-list', [StudentController::class, 'getStudentList']);
    Route::post('add-new-student', [StudentController::class, 'addNewStudent']);
    Route::post('import-students', [StudentController::class, 'importStudents']);
    Route::get('get-student-details/{id}', [StudentController::class, 'getStudentDetails']);

    // SCHOOL YEAR & SEMESTER
    Route::get('get-semesters', [SemesterController::class, 'getSemesters']);
    Route::get('get-school-years', [SchoolYearController::class, 'getSchoolYears']);
    Route::get('get-school-year-details/{schoolYear}/{semester}', [SchoolYearController::class, 'getSchoolYearDetails']);
    Route::post('set-sy-default/{schoolYearid}', [SchoolYearController::class, 'setSyDefault']);
    Route::post('add-school-year', [SchoolYearController::class, 'addSchoolYear']);

    // ENROLLMENT
    Route::get('enrollment/{courseid}', [EnrollmentCourseController::class, 'getYearLevelSections']);
    Route::post('add-new-section/{courseid}', [EnrollmentCourseController::class, 'addNewSection']);
    Route::get('get-department-rooms', [EnrollmentCourseController::class, 'getDepartmentRooms']);
    Route::get('get-instructors', [EnrollmentCourseController::class, 'getInstructors']);
    Route::get('get-classes/{course_id}/{year_level_name}/{section}', [EnrollmentCourseController::class, 'getClasses']);
    Route::get('get-room-time/{id}/{day}', [EnrollmentCourseController::class, 'getRoomTime']);
    Route::get('get-instructor-time/{id}/{day}', [EnrollmentCourseController::class, 'getInstructorTime']);
    Route::post('add-class/{yearSectionId}', [EnrollmentCourseController::class, 'addClass']);
    Route::get('get-year-section-id', [EnrollmentCourseController::class, 'getYearSectionId']);

    // CLASSES
    Route::get('get-faculty-classes', [ClassController::class, 'getFacultyClasses']);
    Route::get('get-student-classes', [StudentClassController::class, 'getStudentClasses']);
    Route::get('get-class-students/{classId}', [ClassController::class, 'getClassStudents']);
    Route::get('get-enrollment-record', [StudentClassController::class, 'getEnrollmentRecord']);

    // PRE_ENROLLMENT
    Route::get('get-course-year-level-sujects/{courseId}/{yearLevelId}', [PreEnrollmentController::class, 'getCourseYearLevelSujects']);
    Route::get('get-student-info-student-id-number/{studentId}', [PreEnrollmentController::class, 'getStudentInfoStudentIdNumber']);

    // ENROLL STUDENT
    Route::get('get-year-level-section-sections/{courseId}/{yearLevelId}', [PreEnrollmentController::class, 'getYearLevelSectionSections']);
    Route::get('get-year-level-section-section-subjects/{courseid}/{yearLevelNumber}/{section}', [EnrollmentController::class, 'getYearLevelSectionSectionSubjects']);
    Route::get('get-classes/{subjectCode}', [EnrollmentController::class, 'getClasses']);
    Route::post('enroll-student/{studentId}/{studentTypeId}/{yearSectionId}', [EnrollmentController::class, 'enrollStudent']);
    Route::post('submit-student-classes/{preEnrollmentId}/{studentId}/{yearSectionId}/{studentTypeId}', [PreEnrollmentController::class, 'submitStudentClasses']);

    Route::get('get-course-enrolled-students', [DashboardController::class, 'getCourseEnrolledStudents']);

    Route::get('get-subject-classes/{subjectId}', [EnrollmentController::class, 'getSubjectClasses']);
});
