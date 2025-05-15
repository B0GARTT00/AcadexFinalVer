<header class="px-6 py-4 shadow-md d-flex justify-content-between align-items-center" style="background-color: #023336; color: white;">
    <!-- Left: Current Academic Period -->
    <h1 class="fs-5 fw-semibold mb-0">
        @php
            $activePeriod = \App\Models\AcademicPeriod::find(session('active_academic_period_id'));
        @endphp
        @if($activePeriod)
            @php
                $semesterLabel = '';
                $academicYear = $activePeriod->academic_year;
    
                switch ($activePeriod->semester) {
                    case '1st':
                        $semesterLabel = 'First Semester';
                        break;
                    case '2nd':
                        $semesterLabel = 'Second Semester';
                        break;
                    case 'Summer':
                        $semesterLabel = 'Summer';
                        break;
                    default:
                        $semesterLabel = 'Unknown Semester';
                        break;
                }
    
                // Only split the academic year if it's not Summer
                if ($activePeriod->semester != 'Summer') {
                    list($startYear, $endYear) = explode('-', $academicYear);
                }
            @endphp
            
            @if($activePeriod->semester != 'Summer')
                {{ $semesterLabel }} - AY {{ $startYear }} - {{ $endYear }}
            @else
                {{ $semesterLabel }} - AY {{ $academicYear }}
            @endif
    
        @else
            Dashboard
        @endif
    </h1>    

    <!-- Right: Profile Dropdown -->
    @php
        $nameParts = explode(' ', Auth::user()->name);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[count($nameParts) - 1] ?? '';
        $displayName = $firstName . ' ' . $lastName;
    @endphp
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($displayName) }}"
                 alt="avatar"
                 class="rounded-circle me-2"
                 width="32"
                 height="32">
            <span class="fw-medium">{{ $displayName }}</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
            <li>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="dropdown-item" type="submit">Logout</button>
                </form>
            </li>
        </ul>
    </div>
</header>
