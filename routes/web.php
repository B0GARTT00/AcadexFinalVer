<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChairpersonController;
use App\Http\Controllers\Chairperson\AccountApprovalController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\DeanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AcademicPeriodController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\FinalGradeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\StudentImportController;
use App\Http\Middleware\EnsureAcademicPeriodSet;

// Welcome Page
Route::get('/', fn () => view('auth.login'));

// Profile Management
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Academic Period Selection
Route::middleware('auth')->group(function () {
    Route::get('/select-academic-period', function () {
        $periods = \App\Models\AcademicPeriod::where('is_deleted', false)
            ->orderByDesc('academic_year')
            ->orderByRaw("FIELD(semester, '1st', '2nd', 'Summer')")
            ->get();

        return view('instructor.select-academic-period', compact('periods'));
    })->name('select.academicPeriod');

    Route::post('/set-academic-period', function (Request $request) {
        $request->validate([
            'academic_period_id' => 'required|exists:academic_periods,id',
        ]);
        session(['active_academic_period_id' => $request->academic_period_id]);
        return redirect()->intended('/dashboard');
    })->name('set.academicPeriod');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'academic.period.set'])
    ->name('dashboard');

// Chairperson Routes
Route::prefix('chairperson')
    ->middleware(['auth', 'academic.period.set'])
    ->name('chairperson.')
    ->group(function () {
        Route::get('/instructors', [ChairpersonController::class, 'manageInstructors'])->name('instructors');
        Route::get('/instructors/create', [ChairpersonController::class, 'createInstructor'])->name('createInstructor');
        Route::post('/instructors/store', [ChairpersonController::class, 'storeInstructor'])->name('storeInstructor');
        Route::post('/instructors/{id}/deactivate', [ChairpersonController::class, 'deactivateInstructor'])->name('deactivateInstructor');

        Route::get('/assign-subjects', [ChairpersonController::class, 'assignSubjects'])->name('assignSubjects');
        Route::post('/assign-subjects/store', [ChairpersonController::class, 'storeAssignedSubject'])->name('storeAssignedSubject');
        
        // Add this route for toggling assigned subjects
        Route::post('/assign-subjects/toggle', [ChairpersonController::class, 'toggleAssignedSubject'])->name('toggleAssignedSubject');

        Route::get('/grades', [ChairpersonController::class, 'viewGrades'])->name('viewGrades');
        Route::get('/students-by-year', [ChairpersonController::class, 'viewStudentsPerYear'])->name('studentsByYear');

        Route::get('/approvals', [AccountApprovalController::class, 'index'])->name('accounts.index');
        Route::post('/approvals/{id}/approve', [AccountApprovalController::class, 'approve'])->name('accounts.approve');
        Route::post('/approvals/{id}/reject', [AccountApprovalController::class, 'reject'])->name('accounts.reject');
    });

// Curriculum Routes
Route::middleware(['auth', 'academic.period.set'])->group(function () {
    Route::get('/curriculum/select-subjects', [CurriculumController::class, 'selectSubjects'])->name('curriculum.selectSubjects');
    Route::post('/curriculum/confirm-subjects', [CurriculumController::class, 'confirmSubjects'])->name('curriculum.confirmSubjects');
    Route::get('/curriculum/{curriculum}/fetch-subjects', [CurriculumController::class, 'fetchSubjects'])->name('curriculum.fetchSubjects');
});

// Instructor Routes
Route::prefix('instructor')
    ->middleware(['auth', EnsureAcademicPeriodSet::class])
    ->name('instructor.')
    ->group(function () {
        Route::get('/dashboard', [InstructorController::class, 'dashboard'])->name('dashboard');

        // Student Management
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/enroll', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::delete('/students/{student}/drop', [StudentController::class, 'drop'])->name('students.drop');

        // ✅ Student Import Routes
        Route::get('/students/import', [StudentImportController::class, 'showUploadForm'])->name('students.import');
        Route::post('/students/import', [StudentImportController::class, 'upload'])->name('students.import.upload');
        Route::post('/students/import/confirm', [StudentImportController::class, 'confirmImport'])->name('students.import.confirm');

        // Grades
        Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
        Route::get('/grades/partial', [GradeController::class, 'partial'])->name('grades.partial');
        Route::post('/grades/save', [GradeController::class, 'store'])->name('grades.store');
        Route::post('/grades/ajax-save-score', [GradeController::class, 'ajaxSaveScore'])->name('grades.ajaxSaveScore');

        // Final Grades
        Route::get('/final-grades', [FinalGradeController::class, 'index'])->name('final-grades.index');
        Route::post('/final-grades/generate', [FinalGradeController::class, 'generate'])->name('final-grades.generate');

        // Activities
        Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
        Route::get('/activities/create', [ActivityController::class, 'create'])->name('activities.create');
        Route::post('/activities/store', [ActivityController::class, 'store'])->name('activities.store');
        Route::put('/activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
        Route::delete('/activities/{id}', [ActivityController::class, 'delete'])->name('activities.delete');
    });

// Dean Routes
Route::prefix('dean')->middleware('auth')->name('dean.')->group(function () {
    Route::get('/instructors', [DeanController::class, 'viewInstructors'])->name('instructors');
    Route::get('/students', [DeanController::class, 'viewStudents'])->name('students');
    Route::get('/grades', [DeanController::class, 'viewGrades'])->name('grades');
    Route::get('/instructor/grades/partial', [GradeController::class, 'partial'])->name('instructor.grades.partial');
    Route::get('/dean/students', [DeanController::class, 'viewStudents'])->name('dean.students');
});

// Admin Routes
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/departments', [AdminController::class, 'departments'])->name('departments');
    Route::get('/departments/create', [AdminController::class, 'createDepartment'])->name('createDepartment');
    Route::post('/departments/store', [AdminController::class, 'storeDepartment'])->name('storeDepartment');

    Route::get('/courses', [AdminController::class, 'courses'])->name('courses');
    Route::get('/courses/create', [AdminController::class, 'createCourse'])->name('createCourse');
    Route::post('/courses/store', [AdminController::class, 'storeCourse'])->name('storeCourse');

    Route::get('/subjects', [AdminController::class, 'subjects'])->name('subjects');
    Route::get('/subjects/create', [AdminController::class, 'createSubject'])->name('createSubject');
    Route::post('/subjects/store', [AdminController::class, 'storeSubject'])->name('storeSubject');

    Route::get('/academic-periods', [AcademicPeriodController::class, 'index'])->name('academicPeriods');
    Route::post('/academic-periods/generate', [AcademicPeriodController::class, 'generate'])->name('academicPeriods.generate');
    Route::get('/user-logs', [AdminController::class, 'viewUserLogs'])->name('userLogs');
    Route::get('/admin/user-logs/filter', [AdminController::class, 'filterUserLogs'])->name('user_logs.filter');

    Route::get('/users', [AdminController::class, 'viewUsers'])->name('users');
    Route::post('/users/confirm-password', [AdminController::class, 'adminConfirmUserCreationWithPassword'])->name('confirmUserCreationWithPassword');
    Route::post('/users/store-verified-user', [AdminController::class, 'storeUser'])->name('storeVerifiedUser');
});

// Auth Routes
require __DIR__.'/auth.php';
