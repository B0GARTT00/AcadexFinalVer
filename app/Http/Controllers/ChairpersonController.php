<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Models\Department;
use App\Models\Course;
use App\Models\UnverifiedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChairpersonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ============================
    // Instructor Management
    // ============================

    public function manageInstructors()
    {
        Gate::authorize('chairperson');

        $instructors = User::where('role', 0)
            ->where('department_id', Auth::user()->department_id)
            ->orderBy('last_name')
            ->get();

        $pendingAccounts = UnverifiedUser::with('department', 'course')
            ->where('department_id', Auth::user()->department_id)
            ->get();

        return view('chairperson.manage-instructors', compact('instructors', 'pendingAccounts'));
    }

    public function storeInstructor(Request $request)
    {
        Gate::authorize('chairperson');

        $request->validate([
            'first_name'    => 'required|string|max:255',
            'middle_name'   => 'nullable|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|string|regex:/^[^@]+$/|unique:unverified_users,email|max:255',
            'password'      => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->letters()->numbers()->symbols(),
            ],
            'department_id' => 'required|exists:departments,id',
            'course_id'     => 'required|exists:courses,id',
        ]);

        $fullEmail = strtolower(trim($request->email)) . '@brokenshire.edu.ph';

        UnverifiedUser::create([
            'first_name'    => $request->first_name,
            'middle_name'   => $request->middle_name,
            'last_name'     => $request->last_name,
            'email'         => $fullEmail,
            'password'      => Hash::make($request->password),
            'department_id' => $request->department_id,
            'course_id'     => $request->course_id,
        ]);

        return redirect()->back()->with('status', 'Instructor account submitted for approval.');
    }

    public function deactivateInstructor($id)
    {
        Gate::authorize('chairperson');

        $instructor = User::where('id', $id)
            ->where('role', 0)
            ->where('department_id', Auth::user()->department_id)
            ->firstOrFail();

        $instructor->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Instructor deactivated successfully.');
    }

    public function activateInstructor($id)
    {
        Gate::authorize('chairperson');

        $instructor = User::where('id', $id)
            ->where('role', 0)
            ->where('department_id', Auth::user()->department_id)
            ->firstOrFail();

        $instructor->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Instructor activated successfully.');
    }

    // ============================
    // Subject Assignment
    // ============================

    public function assignSubjects()
    {
        Gate::authorize('chairperson');

        $academicPeriodId = session('active_academic_period_id');

        $subjects = Subject::where('department_id', Auth::user()->department_id)
            ->where('is_deleted', false)
            ->where('academic_period_id', $academicPeriodId)
            ->orderBy('subject_code')
            ->get();

        $instructors = User::where('role', 0)
            ->where('department_id', Auth::user()->department_id)
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();

        return view('chairperson.assign-subjects', compact('subjects', 'instructors'));
    }

    public function storeAssignedSubject(Request $request)
    {
        Gate::authorize('chairperson');

        $academicPeriodId = session('active_academic_period_id');

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'instructor_id' => 'required|exists:users,id',
        ]);

        $subject = Subject::where('id', $request->subject_id)
            ->where('department_id', Auth::user()->department_id)
            ->where('academic_period_id', $academicPeriodId)
            ->firstOrFail();

        $instructor = User::where('id', $request->instructor_id)
            ->where('role', 0)
            ->where('department_id', Auth::user()->department_id)
            ->where('is_active', true)
            ->firstOrFail();

        $subject->update([
            'instructor_id' => $instructor->id,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('chairperson.assignSubjects')->with('success', 'Subject assigned successfully.');
    }
    public function toggleAssignedSubject(Request $request)
    {
        Gate::authorize('chairperson');
        
        $academicPeriodId = session('active_academic_period_id');
        
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'instructor_id' => 'nullable|exists:users,id', // instructor_id is nullable for unassign
        ]);
    
        $subject = Subject::where('id', $request->subject_id)
            ->where('department_id', Auth::user()->department_id)
            ->where('academic_period_id', $academicPeriodId)
            ->firstOrFail();
    
        // Check if there are any enrolled students in the subject
        $enrolledStudents = $subject->students()->count(); // Assuming the 'students' relationship is defined
    
        // If there are enrolled students and we are trying to unassign the subject, prevent the action
        if ($enrolledStudents > 0 && !$request->instructor_id) {
            return redirect()->route('chairperson.assignSubjects')->with('error', 'Cannot unassign subject as it has enrolled students.');
        }
    
        // If an instructor is selected, assign them, otherwise unassign the instructor
        if ($request->instructor_id) {
            $instructor = User::where('id', $request->instructor_id)
                ->where('role', 0) // Ensure the user is an instructor
                ->where('department_id', Auth::user()->department_id)
                ->where('is_active', true)
                ->firstOrFail();
    
            $subject->update([
                'instructor_id' => $instructor->id,
                'updated_by' => Auth::id(),
            ]);
    
            return redirect()->route('chairperson.assignSubjects')->with('success', 'Instructor assigned successfully.');
        } else {
            // Unassign the instructor only if no students are enrolled
            $subject->update([
                'instructor_id' => null,
                'updated_by' => Auth::id(),
            ]);
    
            return redirect()->route('chairperson.assignSubjects')->with('success', 'Instructor unassigned successfully.');
        }
    }
    
    
    
    // ============================
    // View Grades
    // ============================

    public function viewGrades(Request $request)
    {
        Gate::authorize('chairperson');

        $selectedYear = $request->input('year_level');
        $selectedPeriodId = $request->input('academic_period_id');

        $students = Student::with(['subjects', 'termGrades', 'course'])
            ->where('department_id', Auth::user()->department_id)
            ->when(Auth::user()->course_id, fn($q) => $q->where('course_id', Auth::user()->course_id))
            ->when($selectedYear, fn($q) => $q->where('year_level', $selectedYear))
            ->when($selectedPeriodId, fn($q) => $q->where('academic_period_id', $selectedPeriodId))
            ->where('is_deleted', false)
            ->orderBy('last_name')
            ->get();

        $academicPeriods = \App\Models\AcademicPeriod::where('is_deleted', false)
            ->orderByDesc('academic_year')
            ->orderByRaw("FIELD(semester, '1st', '2nd', 'Summer')")
            ->get();

        return view('chairperson.view-grades', [
            'students' => $students,
            'selectedYear' => $selectedYear,
            'academicPeriods' => $academicPeriods,
        ]);
    }

    // ============================
    // Students by Year Level
    // ============================

    public function viewStudentsPerYear()
    {
        Gate::authorize('chairperson');

        $students = Student::with('course')
            ->where('department_id', Auth::user()->department_id)
            ->when(Auth::user()->course_id, fn($q) => $q->where('course_id', Auth::user()->course_id))
            ->where('is_deleted', false)
            ->orderBy('year_level')
            ->orderBy('last_name')
            ->get();

        return view('chairperson.students-by-year', compact('students'));
    }
}