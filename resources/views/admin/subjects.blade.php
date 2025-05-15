@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 text-dark fw-bold mb-0">📚 Subjects</h1>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#subjectModal">+ Add Subject</button>
    </div>

    {{-- Subjects Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Units</th>
                        <th>Year Level</th>
                        <th>Department</th>
                        <th>Course</th>
                        <th>Academic Period</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        <tr>
                            <td>{{ $subject->id }}</td>
                            <td>{{ $subject->subject_code }}</td>
                            <td>{{ $subject->subject_description ?? '-' }}</td>
                            <td>{{ $subject->units }}</td>
                            <td>{{ $subject->year_level ?? '-' }}</td>
                            <td>{{ $subject->department->department_description ?? '-' }}</td>
                            <td>{{ $subject->course->course_description ?? '-' }}</td>
                            <td>{{ $subject->academicPeriod->academic_year ?? '-' }} {{ $subject->academicPeriod->semester ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted fst-italic py-3">No subjects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Subject Modal --}}
<div class="modal fade" id="subjectModal" tabindex="-1" aria-labelledby="subjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="subjectModalLabel">Add New Subject</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.storeSubject') }}">
                @csrf
                <div class="modal-body">
                    {{-- Academic Period --}}
                    <div class="mb-3">
                        <label class="form-label">Academic Period</label>
                        <select name="academic_period_id" class="form-select" required>
                            <option value="">-- Select Academic Period --</option>
                            @foreach($academicPeriods as $period)
                                <option value="{{ $period->id }}">
                                    {{ $period->academic_year }} - {{ ucfirst($period->semester) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department --}}
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select" required id="department-select">
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->department_description }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Course --}}
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select" required id="course-select">
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" data-department="{{ $course->department_id }}">
                                    {{ $course->course_description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject Code</label>
                        <input type="text" name="subject_code" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject Description</label>
                        <input type="text" name="subject_description" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Units</label>
                        <input type="number" name="units" class="form-control" required min="1" max="6">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Year Level</label>
                        <select name="year_level" class="form-select" required>
                            <option value="">-- Select Year Level --</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                            <option value="5">5th Year</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@push('scripts')
<script>
    $(document).ready(function() {
        // Filter courses based on selected department
        $('#department-select').change(function() {
            const departmentId = $(this).val();
            const courseSelect = $('#course-select');
            
            // Reset course selection
            courseSelect.val('');
            
            // Show/hide courses based on department
            courseSelect.find('option').each(function() {
                if (!departmentId || $(this).val() === '' || $(this).data('department') == departmentId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
</script>
@endpush
@endsection
