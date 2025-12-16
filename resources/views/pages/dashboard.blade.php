@extends('layouts.master')

@section('page_title', 'My Dashboard')

@section('content')

@if(!Qs::currentSessionMatchesCurrentYear() && auth()->user()->can('view_system_warnings'))
@php session()->flash('pop_warning_confirm', __('msg.session_and_year_not_matched')) @endphp
@endif

<div class="card-columns">

    <!-- Active Students -->
    <div class="card card-body bg-info-300 has-bg-image">
        <div class="media cursor-pointer" data-toggle="modal" data-target="#students-number">
            <div class="media-body">
                <div class="row align-items-center">
                    <div class="col-5">
                        <h3 class="mb-0 counter">{{ $active_students_count }}</h3>
                        <span class="text-capitalize font-size-xs">Active Students</span>
                    </div>
                    <div class="col-7">
                        <div class="small text-white mt-1">
                            M: {{ $active_students_male_count }}
                            &nbsp; F: {{ $active_students_female_count }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graduated Students -->
    <div class="card card-body bg-purple-300 has-bg-image">
        <div class="media">
            <div class="media-body">
                <div class="row align-items-center">
                    <div class="col-5">
                        <h3 class="mb-0 counter">{{ $grad_students_count }}</h3>
                        <span class="text-capitalize font-size-xs">Graduated Students</span>
                    </div>
                    <div class="col-7">
                        <div class="small text-white mt-1">
                            M: {{ $grad_students_male_count }}
                            &nbsp; F: {{ $grad_students_female_count }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Students -->
    <div class="card card-body bg-purple-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <div class="row align-items-center">
                    <div class="col-5">
                        <h3 class="mb-0 counter">{{ $students_count }}</h3>
                        <span class="text-capitalize font-size-xs">Overall Students</span>
                    </div>
                    <div class="col-7">
                        <canvas id="spark-overall-students" style="max-height: 40px" width="160" height="40" aria-label="Overall students trend" role="img"></canvas>
                        <div class="small text-white mt-1">
                            M: {{ $gender_counts['male'] }}
                            &nbsp; F: {{ $gender_counts['female'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Total Administrators -->
    <div class="card card-body bg-danger-600 has-bg-image">
        <div class="media">
            <div class="media-body">
                <div class="row align-items-center">
                    <div class="col-5">
                        <h3 class="mb-0 counter">{{ $staff_breakdown['admins_count'] }}</h3>
                        <span class="text-capitalize font-size-xs">Total Administrators</span>
                    </div>
                    <div class="col-7">
                        <div class="small text-white mt-1">
                            M: {{ $staff_breakdown['admins_male_count'] }}
                            &nbsp; F: {{ $staff_breakdown['admins_female_count'] }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="ml-3 align-self-center d-none d-md-block">
                <i class="material-symbols-rounded symbol-3x opacity-75">group</i>
            </div>
        </div>
    </div>

    <!-- Support Team (Teachers) -->
    <div class="card card-body bg-danger-400 has-bg-image">
        <div class="media">
            <div class="media-body ml-2">
                <div class="row">
                    <div class="col-12 text-left">
                        {{ $staff_breakdown['teachers'] }}
                        <br>
                        <span class="text-capitalize font-size-xs">Total Teachers</span>
                    </div>
                </div>
            </div>
            <div class="ml-3 align-self-center d-none d-md-block">
                <i class="material-symbols-rounded symbol-3x opacity-75">group</i>
            </div>
        </div>
    </div>

    <!-- Accountants & Librarians -->
    <div class="card card-body bg-danger-400 has-bg-image">
        <div class="media">
            <div class="media-body ml-2">
                <div class="row">
                    <div class="col-6 text-left">
                        {{ $staff_breakdown['accountants'] }}
                        <br>
                        <span class="text-capitalize font-size-xs">Total Accountants</span>
                    </div>
                    <div class="col-6 text-left">
                        {{ $staff_breakdown['librarians'] }}
                        <br>
                        <span class="text-capitalize font-size-xs">Total Librarians</span>
                    </div>
                </div>
            </div>
            <div class="ml-3 align-self-center d-none d-md-block">
                <i class="material-symbols-rounded symbol-3x opacity-75">library_books</i>
            </div>
        </div>
    </div>
 
    <!-- Assessments Declared -->
    <div class="card card-body bg-success-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $assessments_count }}</h3>
                <span class="text-capitalize font-size-xs">Assessments Declared</span>
            </div>
            <div class="ml-3 align-self-center">
                <i class="material-symbols-rounded symbol-3x opacity-75">folder_open</i>
            </div>
        </div>
    </div>

    <!-- Total Parents -->
    <div class="card card-body bg-indigo-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <div class="row align-items-center">
                    <div class="col-5">
                        <h3 class="mb-0 counter">{{ $parents_count }}</h3>
                        <span class="text-capitalize font-size-xs">Total Parents</span>
                    </div>
                    <div class="col-7">
                        <div class="small text-white mt-1">
                            M: {{ $parents_male_count }}
                            &nbsp; F: {{ $parents_female_count }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Subjects -->
    <div class="card card-body bg-brown-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $subjects->count() }}</h3>
                <span class="text-capitalize font-size-xs">Total Subjects</span>
            </div>
            <div class="ml-3 align-self-center">
                <i class="material-symbols-rounded symbol-3x opacity-75">subject</i>
            </div>
        </div>
    </div>
  
    <!-- Total Classes -->
    <div class="card card-body bg-orange-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $classes_count }}</h3>
                <span class="text-capitalize font-size-xs">Total Classes</span>
            </div>
            <div class="ml-3 align-self-center">
                <i class="material-symbols-rounded symbol-3x opacity-75">domain</i>
            </div>
        </div>
    </div>

    <!-- Results Declared -->
    <div class="card card-body bg-primary-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $exams_count }}</h3>
                <span class="text-capitalize font-size-xs">Results Declared</span>
            </div>
            <div class="ml-3 align-self-center">
                <i class="material-symbols-rounded symbol-3x opacity-75">file_open</i>
            </div>
        </div>
    </div>
  
    <!-- Total Payments Done -->
    <div class="card card-body bg-pink-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $total_pay_records }}</h3>
                <span class="text-capitalize font-size-xs">Total Payments Done</span>
            </div>
            <div class="ml-3 align-self-center">
                <i class="material-symbols-rounded symbol-3x opacity-75">paid</i>
            </div>
        </div>
    </div>

    <!-- Total Books -->
    <div class="card card-body bg-violet-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $books_count }}</h3>
                <span class="text-capitalize font-size-xs">Total Books</span>
            </div>
            <div class="ml-3 align-self-center">
                <i class="material-symbols-rounded symbol-3x opacity-75">library_books</i>
            </div>
        </div>
    </div>

    <!-- Total Book Requests -->
    <div class="card card-body bg-warning-400 has-bg-image">
        <div class="media">
            <div class="media-body ml-2">
                <div class="row">
                    <div class="col-6 text-left">
                        {{ $book_requests_returned_count }}<br>
                        <span class="text-capitalize font-size-xs">Returned</span>
                    </div>
                    <div class="col-6 text-left">
                        {{ $book_requests_count - $book_requests_returned_count }}<br>
                        <span class="text-capitalize font-size-xs">Out</span>
                    </div>
                </div>
            </div>
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $book_requests_count }}</h3>
                <span class="text-capitalize font-size-xs">Total Book Requests</span>
            </div>
        </div>
    </div>

</div>

<!-- Reports section (larger charts) -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>Gender Distribution (Students)</h5>
                <canvas id="chart-gender" width="400" height="240" aria-label="Gender distribution" role="img"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>Payments (Last 12 months)</h5>
                <canvas id="chart-payments" width="400" height="240" aria-label="Payments trend" role="img"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5>Students per Class</h5>
                <canvas id="chart-students-per-class" width="800" height="240" aria-label="Students per class" role="img"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Notices -->
<div class="row">
    <div class="col-12">
        <div class="card card-collapsed">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Notices<sup class="badge badge-default notices-badge">{{ $unviewed_count }}</sup></h6>
                <div class="header-elements">
                    <div class="list-icons">
                        <a class="btn btn-link list-icons-item" href="{{ route('notices.index') }}">Manage</a>
                        <a class="list-icons-item" data-action="collapse"></a>
                        <a class="list-icons-item" data-action="remove"></a>
                    </div>
                </div>
            </div>
            <div class="notices">
                @include('pages.notices.show')
            </div>
        </div>
    </div>
</div>

@include('pages.modals.students_number')

@endsection

@section('scripts')
<script>
    // Data injected from the controller
    const months = @json($chart_months ?? []);
    const studentsMonthly = @json($students_monthly ?? array_fill(0, 12, 0));
    const paymentsMonthly = @json($payments_monthly ?? array_fill(0, 12, 0));
    const genderCounts = @json($gender_counts ?? ['male' => 0, 'female' => 0]);
    const studentsPerClassLabels = @json($students_per_class_labels ?? []);
    const studentsPerClassCounts = @json($students_per_class_counts ?? []);

</script>
@endsection
