@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">GE Coordinator Overview 👨‍💼</h2>
            <p class="text-muted mb-0">Monitor General Education program performance and faculty management</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('gecoordinator.instructors') }}" class="btn btn-success rounded-pill px-3 shadow-sm">
                <i class="bi bi-person-plus"></i> Manage GE Instructors
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-4">
        @php
            $cards = [
                [
                    'label' => 'Total GE Instructors',
                    'icon' => 'bi bi-person-video3',
                    'value' => $countInstructors,
                    'color' => 'primary',
                    'trend' => 'GE faculty members'
                ],
                [
                    'label' => 'Total Students Enrolled',
                    'icon' => 'bi bi-mortarboard-fill',
                    'value' => $countStudents,
                    'color' => 'success',
                    'trend' => 'In GE subjects this semester'
                ],
                [
                    'label' => 'Active GE Courses',
                    'icon' => 'bi bi-journal-text',
                    'value' => $countCourses,
                    'color' => 'info',
                    'trend' => 'Current offerings'
                ]
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-3 p-2 bg-{{ $card['color'] }}-subtle me-3">
                                <i class="{{ $card['icon'] }} text-{{ $card['color'] }} fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">{{ $card['label'] }}</h6>
                                <h3 class="fw-bold text-{{ $card['color'] }} mb-0">{{ $card['value'] }}</h3>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-arrow-right"></i> {{ $card['trend'] }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mt-4">
        {{-- Faculty Status Overview --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    {{-- Header Section --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 p-2 bg-primary-subtle me-3">
                                <i class="bi bi-person-video3 text-primary fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-semibold mb-1">Faculty Status Overview</h5>
                                <p class="text-muted small mb-0">
                                    Managing <span class="fw-bold">{{ $countInstructors }}</span> GE Faculty Members
                                </p>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-pill px-3 hover-lift" type="button" id="helpDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-info-circle me-1"></i> Help
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-3 shadow-sm" aria-labelledby="helpDropdown" style="min-width: 280px;">
                                <li class="small">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                        <h6 class="mb-0">Status Guide</h6>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item border-0 px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-mortarboard-fill text-success me-2"></i>
                                                <strong>Active:</strong>
                                                <span class="ms-2 text-muted small">Currently teaching</span>
                                            </div>
                                        </div>
                                        <div class="list-group-item border-0 px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-x-fill text-danger me-2"></i>
                                                <strong>Inactive:</strong>
                                                <span class="ms-2 text-muted small">On leave/deactivated</span>
                                            </div>
                                        </div>
                                        <div class="list-group-item border-0 px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-plus text-warning me-2"></i>
                                                <strong>Pending:</strong>
                                                <span class="ms-2 text-muted small">Awaiting verification</span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Overall Progress Bar --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-semibold mb-0">
                                <i class="bi bi-graph-up text-primary me-2"></i>
                                Faculty Distribution
                            </h6>
                            <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                                Total: {{ $countInstructors }} Members
                            </div>
                        </div>
                        <div class="progress rounded-pill" style="height: 12px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                style="width: {{ $countInstructors > 0 ? ($countActiveInstructors / $countInstructors) * 100 : 0 }}%" 
                                data-bs-toggle="tooltip" 
                                title="Active: {{ $countActiveInstructors }} faculty members">
                            </div>
                            <div class="progress-bar bg-danger" role="progressbar" 
                                style="width: {{ $countInstructors > 0 ? ($countInactiveInstructors / $countInstructors) * 100 : 0 }}%" 
                                data-bs-toggle="tooltip" 
                                title="Inactive: {{ $countInactiveInstructors }} faculty members">
                            </div>
                            <div class="progress-bar bg-warning" role="progressbar" 
                                style="width: {{ $countInstructors > 0 ? ($countPendingInstructors / $countInstructors) * 100 : 0 }}%" 
                                data-bs-toggle="tooltip" 
                                title="Pending: {{ $countPendingInstructors }} faculty members">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <div class="small">
                                <i class="bi bi-circle-fill text-success me-1"></i> Active
                            </div>
                            <div class="small">
                                <i class="bi bi-circle-fill text-danger me-1"></i> Inactive
                            </div>
                            <div class="small">
                                <i class="bi bi-circle-fill text-warning me-1"></i> Pending
                            </div>
                        </div>
                    </div>

                    {{-- Status Cards --}}
                    <div class="row g-4">
                        {{-- Active Faculty Card --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 bg-success-subtle hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 p-2 bg-success text-white me-3">
                                            <i class="bi bi-mortarboard-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-success small fw-semibold">Active Faculty</div>
                                            <h3 class="fw-bold text-success mb-0">{{ $countActiveInstructors }}</h3>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">Currently Teaching</span>
                                        <span class="badge bg-success text-white px-2 py-1">
                                            {{ $countInstructors > 0 ? number_format(($countActiveInstructors / $countInstructors) * 100, 1) : '0.0' }}%
                                        </span>
                                    </div>
                                    @if($countActiveInstructors > 0)
                                        <a href="{{ route('gecoordinator.instructors') }}#active" 
                                           class="stretched-link" 
                                           data-bs-toggle="tooltip" 
                                           title="View active faculty members">
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Inactive Faculty Card --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 bg-danger-subtle hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 p-2 bg-danger text-white me-3">
                                            <i class="bi bi-person-x-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-danger small fw-semibold">Inactive Faculty</div>
                                            <h3 class="fw-bold text-danger mb-0">{{ $countInactiveInstructors }}</h3>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">On Leave/Deactivated</span>
                                        <span class="badge bg-danger text-white px-2 py-1">
                                            {{ $countInstructors > 0 ? number_format(($countInactiveInstructors / $countInstructors) * 100, 1) : '0.0' }}%
                                        </span>
                                    </div>
                                    @if($countInactiveInstructors > 0)
                                        <a href="{{ route('gecoordinator.instructors') }}#inactive" 
                                           class="stretched-link" 
                                           data-bs-toggle="tooltip" 
                                           title="View inactive faculty members">
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Pending Verification Card --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 bg-warning-subtle hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 p-2 bg-warning text-white me-3">
                                            <i class="bi bi-person-plus fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-warning small fw-semibold">Pending Verification</div>
                                            <h3 class="fw-bold text-warning mb-0">{{ $countPendingInstructors }}</h3>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">Awaiting Approval</span>
                                        <span class="badge bg-warning text-dark px-2 py-1">
                                            {{ ($countInstructors + $countPendingInstructors) > 0 ? number_format(($countPendingInstructors / ($countInstructors + $countPendingInstructors)) * 100, 1) : '0.0' }}%
                                        </span>
                                    </div>
                                    @if($countPendingInstructors > 0)
                                        <a href="{{ route('gecoordinator.instructors') }}#pending" 
                                           class="stretched-link" 
                                           data-bs-toggle="tooltip" 
                                           title="View pending faculty members">
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <h5 class="fw-semibold mb-4">
                        <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Actions
                    </h5>
                    
                    <div class="d-flex flex-column gap-3 flex-grow-1">
                        <a href="{{ route('gecoordinator.assign-subjects') }}" 
                           class="btn btn-light text-start rounded-3 p-3 hover-lift position-relative"
                           data-bs-toggle="tooltip"
                           data-bs-placement="right"
                           title="Assign subjects to GE faculty members">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="bi bi-journal-plus fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Assign Subjects</h6>
                                    <p class="small text-muted mb-0">Manage faculty course loads</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('gecoordinator.manage-schedule') }}" 
                           class="btn btn-light text-start rounded-3 p-3 hover-lift position-relative"
                           data-bs-toggle="tooltip"
                           data-bs-placement="right"
                           title="View and manage class schedules">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 bg-success bg-opacity-10 text-success me-3">
                                    <i class="bi bi-calendar3 fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Class Schedules</h6>
                                    <p class="small text-muted mb-0">View and manage schedules</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('gecoordinator.reports') }}" 
                           class="btn btn-light text-start rounded-3 p-3 hover-lift position-relative"
                           data-bs-toggle="tooltip"
                           data-bs-placement="right"
                           title="Generate and view reports">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 bg-info bg-opacity-10 text-info me-3">
                                    <i class="bi bi-file-earmark-bar-graph fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Reports</h6>
                                    <p class="small text-muted mb-0">Generate and view reports</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover'
        });
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
</script>
@endpush

@push('styles')
<style>
    .hover-lift {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.075) !important;
    }
    .progress {
        overflow: visible;
    }
    .progress-bar {
        position: relative;
        overflow: visible;
    }
    .progress-bar::after {
        content: '';
        position: absolute;
        right: -1px;
        top: -3px;
        height: 18px;
        width: 1px;
        background-color: rgba(0, 0, 0, 0.1);
    }
    .progress-bar:last-child::after {
        display: none;
    }
    .card {
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
</style>
@endpush
@endsection
