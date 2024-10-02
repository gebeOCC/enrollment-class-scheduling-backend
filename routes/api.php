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
use App\Http\Controllers\Faculty\ClassController;
use App\Http\Controllers\ProgramHead\PreEnrollmentController;
use App\Http\Controllers\Student\StudentClassController;
use App\Http\Controllers\ProgramHead\DashboardController;
use App\Http\Controllers\Registrar\EnrollmentController;

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
    Route::post('add-curr-term-subject/{id}', [CurriculumController::class, 'addCurrTermSubject']);

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
    Route::post('add-new-section/{courseid}', [EnrollmentCourseController::class, 'addNewSection']);
    Route::get('get-department-rooms', [EnrollmentCourseController::class, 'getDepartmentRooms']);
    Route::get('get-instructors', [EnrollmentCourseController::class, 'getInstructors']);
    Route::get('get-classes/{course_id}/{year_level_name}/{section}', [EnrollmentCourseController::class, 'getClasses']);
    Route::get('get-room-time/{id}/{day}', [EnrollmentCourseController::class, 'getRoomTime']);
    Route::get('get-instructor-time/{id}/{day}', [EnrollmentCourseController::class, 'getInstructorTime']);
    Route::post('add-class/{yearSectionId}', [EnrollmentCourseController::class, 'addClass']);

    Route::get('get-faculty-classes', [ClassController::class, 'getFacultyClasses']);
    Route::get('get-student-classes', [StudentClassController::class, 'getStudentClasses']);
    Route::get('get-class-students/{classId}', [ClassController::class, 'getClassStudents']);

    Route::get('get-year-section-id', [EnrollmentCourseController::class, 'getYearSectionId']);

    Route::get('pre-enrollment-startup', [PreEnrollmentController::class, 'getYearLevelAndStudentType']);
    Route::post('add-new-student', [PreEnrollmentController::class, 'addNewStudent']);
    Route::get('get-course-year-level-sujects/{courseId}/{yearLevelId}', [PreEnrollmentController::class, 'getCourseYearLevelSujects']);
    Route::get('get-student-info-application-id/{studentId}', [PreEnrollmentController::class, 'getStudentInfoApplicaiotnId']);
    Route::get('get-student-info-student-id-number/{studentId}', [PreEnrollmentController::class, 'getStudentInfoStudentIdNumber']);
    Route::post('create-student-pre-enrollment/{student_id}/{student_type_id}/{course_id}/{year_level_id}', [PreEnrollmentController::class, 'createStudentPreEnrollment']);

    Route::get('get-pre-enrollment-list', [PreEnrollmentController::class, 'getPreEnrollmentList']);
    Route::get('get-latest-students', [PreEnrollmentController::class, 'getLatestStudents']);
    Route::post('create-user-id/{id}', [PreEnrollmentController::class, 'createUserId']);
    Route::get('student-pre-enrollment-subjects/{id}', [PreEnrollmentController::class, 'getStudentPreEnrollmentSubjects']);
    Route::get('get-year-level-section-sections/{courseId}/{yearLevelId}', [PreEnrollmentController::class, 'getYearLevelSectionSections']);
    Route::get('get-year-level-section-section-subjects/{id}', [PreEnrollmentController::class, 'getYearLevelSectionSectionSubjects']);
    Route::post('submit-student-classes/{preEnrollmentId}/{studentId}/{yearSectionId}/{studentTypeId}', [PreEnrollmentController::class, 'submitStudentClasses']);

    Route::get('get-course-enrolled-students', [DashboardController::class, 'getCourseEnrolledStudents']);

    Route::get('get-subject-classes/{subjectId}', [EnrollmentController::class, 'getSubjectClasses']);
});
