<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Subject;
use App\Models\FinalGrade;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DeanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ============================
    // View Instructors under Dean
    // ============================

    public function viewInstructors()
    {
        Gate::authorize('dean');

        $instructors = User::where('role', 0) // Instructor role
            ->where('department_id', Auth::user()->department_id)
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();

        return view('dean.instructors', compact('instructors'));
    }

    // ============================
    // View Students under Dean
    // ============================

    public function viewStudents()
    {
        Gate::authorize('dean');

        $students = Student::with('course')
            ->where('department_id', Auth::user()->department_id)
            ->where('is_deleted', false)
            ->orderBy('last_name')
            ->get();

        return view('dean.students', compact('students'));
    }

    // ============================
    // View Final Grades by Course
    // ============================

    public function viewGrades(Request $request)
    {
        Gate::authorize('dean');
    
        $departmentId = Auth::user()->department_id;
        $academicPeriodId = session('active_academic_period_id'); // Assuming academic period is stored in session
    
        // List of courses in the dean's department
        $courses = Course::where('department_id', $departmentId)
            ->where('is_deleted', false)
            ->orderBy('course_code')
            ->get();
    
        // Initialize collections
        $students = collect();
        $finalGrades = collect();
        $instructors = collect();
        $subjects = collect();
    
        // Step 1: Filter by selected course
        if ($request->filled('course_id')) {
            $courseId = $request->input('course_id');
    
            // Step 2: Get instructors for the selected course
            $instructors = User::where('role', 0) // role 0 = instructor
                ->where('department_id', $departmentId)
                ->where('is_active', true)
                ->whereHas('subjects', function ($query) use ($courseId, $academicPeriodId) {
                    $query->where('course_id', $courseId)
                        ->where('academic_period_id', $academicPeriodId)
                        ->where('is_deleted', false);
                })
                ->orderBy('last_name')
                ->get();
    
            // Step 3: Get subjects for the selected course and instructor
            if ($request->filled('instructor_id')) {
                $instructorId = $request->input('instructor_id');
    
                $subjects = Subject::where([
                        ['instructor_id', $instructorId],
                        ['department_id', $departmentId],
                        ['academic_period_id', $academicPeriodId],
                        ['course_id', $courseId],
                        ['is_deleted', false],
                    ])
                    ->orderBy('subject_code')
                    ->get();
    
                // Step 4: Get students for the selected subject and course
                if ($request->filled('subject_id')) {
                    $subjectId = $request->input('subject_id');
    
                    // Get students enrolled in the selected subject
                    $subject = Subject::with('students')->findOrFail($subjectId);
    
                    $students = $subject->students()
                        ->where('students.department_id', $departmentId)
                        ->where('students.course_id', $courseId)
                        ->where('students.is_deleted', false)
                        ->wherePivot('is_deleted', false)
                        ->orderBy('students.last_name')
                        ->get();
    
                    // Get final grades for the students in the selected subject
                    $finalGrades = FinalGrade::where('subject_id', $subjectId)
                        ->whereIn('student_id', $students->pluck('id'))
                        ->get()
                        ->keyBy('student_id');
                }
            }
        }
    
        return view('dean.grades', compact(
            'courses',
            'students',
            'finalGrades',
            'instructors',
            'subjects'
        ));
    }
    
}
