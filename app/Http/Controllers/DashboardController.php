<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\
{
    Student, 
    Subject, 
    TermGrade, 
    FinalGrade,
    User,
    UserLog,
    Course,
};

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (Gate::allows('instructor')) {
            if (!session()->has('active_academic_period_id')) {
                return redirect()->route('select.academicPeriod');
            }

            $academicPeriodId = session('active_academic_period_id');
            $instructorId = Auth::id();

            $subjects = Subject::where('instructor_id', $instructorId)
                ->where('academic_period_id', $academicPeriodId)
                ->with('students')
                ->get();

            $instructorStudents = $subjects->flatMap->students
                ->where('is_deleted', false)
                ->unique('id')
                ->count();

            $enrolledSubjectsCount = $subjects->count();

            $subjectIds = $subjects->pluck('id');
            $finalGrades = FinalGrade::whereIn('subject_id', $subjectIds)
                ->where('academic_period_id', $academicPeriodId)
                ->get();

            $totalPassedStudents = $finalGrades->where('remarks', 'Passed')->count();
            $totalFailedStudents = $finalGrades->where('remarks', 'Failed')->count();

            $terms = ['prelim', 'midterm', 'prefinal', 'final'];
            $termCompletions = [];

            foreach ($terms as $term) {
                $termId = $this->getTermId($term);
                $total = 0;
                $graded = 0;

                foreach ($subjects as $subject) {
                    $studentCount = $subject->students->where('is_deleted', false)->count();
                    $gradedCount = TermGrade::where('subject_id', $subject->id)
                        ->where('term_id', $termId)
                        ->distinct('student_id')
                        ->count('student_id');

                    $total += $studentCount;
                    $graded += $gradedCount;
                }

                $termCompletions[$term] = [
                    'graded' => $graded,
                    'total' => $total,
                ];
            }

            $subjectCharts = [];
            foreach ($subjects as $subject) {
                $termsData = [];
                $termPercentages = [];

                foreach ($terms as $term) {
                    $termId = $this->getTermId($term);
                    $studentCount = $subject->students->where('is_deleted', false)->count();
                    $gradedCount = TermGrade::where('subject_id', $subject->id)
                        ->where('term_id', $termId)
                        ->distinct('student_id')
                        ->count('student_id');

                    $percentage = $studentCount > 0 ? round(($gradedCount / $studentCount) * 100, 2) : 0;

                    $termsData[$term] = [
                        'graded' => $gradedCount,
                        'total' => $studentCount,
                        'percentage' => $percentage,
                    ];

                    $termPercentages[] = $percentage;
                }

                $subjectCharts[] = [
                    'code' => $subject->subject_code,
                    'description' => $subject->subject_description,
                    'terms' => $termsData,
                    'termPercentages' => $termPercentages,
                ];
            }

            return view('dashboard.instructor', compact(
                'instructorStudents',
                'enrolledSubjectsCount',
                'totalPassedStudents',
                'totalFailedStudents',
                'termCompletions',
                'subjectCharts'
            ));
        }

        if (Gate::allows('chairperson')) {
            if (!session()->has('active_academic_period_id')) {
                return redirect()->route('select.academicPeriod');
            }
            
            $countInstructors = User::where("is_active", 1)
                                ->where("role", 0)
                                ->count();
                            
            $countStudents = Student::count();
            $countCourses = Course::count();

            return view('dashboard.chairperson', 
            compact(
                "countInstructors", 
                "countStudents",
                "countCourses",
            ));
        }

        if (Gate::allows('admin')) {

            // Get the selected date or default to today
            $selectedDate = $request->query('date', Carbon::today()->toDateString());

        $totalUsers = User::count();
        $hours = range(0, 23);

        // Get the login count for today or the selected date
        $loginCount = UserLog::where('event_type', 'login')
            ->whereDate('created_at', $selectedDate)
            ->count();

        // Get successful logins for the selected date, grouped by hour
        $successfulLogins = UserLog::selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->where('event_type', 'login')
            ->whereDate('created_at', $selectedDate)
            ->groupByRaw('HOUR(created_at)')
            ->pluck('total', 'hour');

        // Get failed logins for the selected date, grouped by hour
        $failedLogins = UserLog::selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->where('event_type', 'failed_login')
            ->whereDate('created_at', $selectedDate)
            ->groupByRaw('HOUR(created_at)')
            ->pluck('total', 'hour');

        // Get the count of failed logins for today or the selected date
        $failedLoginCount = UserLog::where('event_type', 'failed_login')
            ->whereDate('created_at', $selectedDate)
            ->count();

        $successfulData = [];
        $failedData = [];

        // Populate the data arrays with hourly counts, defaulting to 0 if no data is available
        foreach ($hours as $hour) {
            $successfulData[] = $successfulLogins[$hour] ?? 0;
            $failedData[] = $failedLogins[$hour] ?? 0;
        }

        // Pass data to the view
        return view('dashboard.admin', compact(
            'totalUsers',
            'loginCount',
            'failedLoginCount',
            'successfulData',
            'failedData',
            'selectedDate' // Pass selected date to the view for the form
        ));
        }

        if (Gate::allows('dean')) {
            return view('dashboard.dean');
        }

        abort(403, 'Unauthorized access.');
    }

    private function getTermId($term)
    {
        return [
            'prelim' => 1,
            'midterm' => 2,
            'prefinal' => 3,
            'final' => 4,
        ][$term] ?? null;
    }
}
