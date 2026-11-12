@extends('layouts.master')

@section('page_title', 'My Dashboard')

@section('content')
    @if (! Qs::currentSessionMatchesCurrentYear() && auth()->user()->can('system.view_warnings'))
        @php session()->flash('pop_warning_confirm', __('msg.session_and_year_not_matched')) @endphp
    @endif

    <div class="card-columns">
        {{-- Active Students --}}
        @can('dashboard_statistics.active_students')
            <div class="card card-body bg-teal-300">
                <div class="media">
                    <div class="media-body">
                        <div class="row align-items-center">
                            <div class="col-5">
                                <h3 class="counter mb-0">{{ $active_students_count }}</h3>
                                <span class="text-capitalize font-size-xs">Active Students</span>
                            </div>
                            <div class="col-7">
                                <div class="small mt-1 text-white">
                                    M: {{ $active_students_male_count }} &nbsp; F: {{ $active_students_female_count }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @can('dashboard_statistics.student_breakdown_modal')
                        <div
                            class="align-self-center d-md-block ml-3 cursor-pointer"
                            data-toggle="modal"
                            data-target="#students-number"
                        >
                            <em class="btn btn-sm">VIEW BREAKDOWN</em>
                        </div>
                    @endcan
                </div>
            </div>
        @endcan

        {{-- Graduated Students --}}
        @can('dashboard_statistics.graduated_students')
            <div class="card card-body bg-slate-800">
                <div class="media">
                    <div class="media-body">
                        <div class="row align-items-center">
                            <div class="col-5">
                                <h3 class="counter mb-0">{{ $grad_students_count }}</h3>
                                <span class="text-capitalize font-size-xs">Graduated Students</span>
                            </div>
                            <div class="col-7">
                                <div class="small mt-1 text-white">
                                    M: {{ $grad_students_male_count }} &nbsp; F: {{ $grad_students_female_count }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Overall Students --}}
        @can('dashboard_statistics.overall_students')
            <div class="card card-body bg-slate-600">
                <div class="media">
                    <div class="media-body">
                        <div class="row align-items-center">
                            <div class="col-5">
                                <h3 class="counter mb-0">{{ $students_count }}</h3>
                                <span class="text-capitalize font-size-xs">Overall Students</span>
                            </div>
                            <div class="col-7">
                                <canvas
                                    id="spark-overall-students"
                                    style="max-height: 40px"
                                    width="160"
                                    height="40"
                                    aria-label="Overall students trend"
                                    role="img"
                                ></canvas>
                                <div class="small mt-1 text-white">
                                    M: {{ $gender_counts['male'] }} &nbsp; F: {{ $gender_counts['female'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Total Administrators --}}
        @can('dashboard_statistics.administrators')
            <div class="card card-body bg-danger-600 has-bg-image">
                <div class="media">
                    <div class="media-body">
                        <div class="row align-items-center">
                            <div class="col-5">
                                <h3 class="counter mb-0">{{ $staff_breakdown['admins_count'] }}</h3>
                                <span class="text-capitalize font-size-xs">Total Administrators</span>
                            </div>
                            <div class="col-7">
                                <div class="small mt-1 text-white">
                                    M: {{ $staff_breakdown['admins_male_count'] }} &nbsp; F: {{ $staff_breakdown['admins_female_count'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="align-self-center ml-3">
                        <i class="material-symbols-rounded symbol-3x opacity-75">group</i>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Total Teachers --}}
        @can('dashboard_statistics.teachers')
            <div class="card card-body bg-danger-400 has-bg-image">
                <div class="media">
                    <div class="media-body ml-2">
                        <div class="row">
                            <div class="col-12 text-left">
                                <h3 class="counter mb-0">{{ $staff_breakdown['teachers'] }}</h3>
                                <span class="text-capitalize font-size-xs">Total Teachers</span>
                            </div>
                        </div>
                    </div>
                    <div class="align-self-center ml-3">
                        <i class="material-symbols-rounded symbol-3x opacity-75">group</i>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Accountants & Librarians --}}
        @can('dashboard_statistics.accountants_librarians')
            <div class="card card-body bg-danger-400 has-bg-image">
                <div class="media">
                    <div class="media-body ml-2">
                        <div class="row">
                            <div class="col-6 text-left">
                                <h3 class="counter mb-0">{{ $staff_breakdown['accountants'] }}</h3>
                                <span class="text-capitalize font-size-xs">Total Accountants</span>
                            </div>
                            <div class="col-6 text-left">
                                <h3 class="counter mb-0">{{ $staff_breakdown['librarians'] }}</h3>
                                <span class="text-capitalize font-size-xs">Total Librarians</span>
                            </div>
                        </div>
                    </div>
                    <div class="align-self-center ml-3">
                        <i class="material-symbols-rounded symbol-3x opacity-75">library_books</i>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Assessments Declared --}}
        @can('dashboard_statistics.assessments_declared')
            <div class="card card-body bg-success-400 has-bg-image">
                <div class="media">
                    <div class="media-body">
                        <h3 class="counter mb-0">{{ $assessments_count }}</h3>
                        <span class="text-capitalize font-size-xs">Assessments Declared</span>
                    </div>
                    <div class="align-self-center ml-3">
                        <i class="material-symbols-rounded symbol-3x opacity-75">folder_open</i>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Total Parents --}}
        @can('dashboard_statistics.parents')
            <div class="card card-body has-bg-image bg-indigo-400">
                <div class="media">
                    <div class="media-body">
                        <div class="row align-items-center">
                            <div class="col-5">
                                <h3 class="counter mb-0">{{ $parents_count }}</h3>
                                <span class="text-capitalize font-size-xs">Total Parents</span>
                            </div>
                            <div class="col-7">
                                <div class="small mt-1 text-white">
                                    M: {{ $parents_male_count }} &nbsp; F: {{ $parents_female_count }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Total Subjects --}}
        @can('dashboard_statistics.subjects')
            <div class="card card-body bg-brown-400 has-bg-image">
                <div class="media">
                    <div class="media-body">
                        <h3 class="counter mb-0">{{ $subjects->count() }}</h3>
                        <span class="text-capitalize font-size-xs">Total Subjects</span>
                    </div>
                    <div class="align-self-center ml-3">
                        <i class="material-symbols-rounded symbol-3x opacity-75">subject</i>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Total Classes --}}
        @can('dashboard_statistics.classes')
            <div class="card card-body has-bg-image bg-orange-400">
                <div class="media">
                    <div class="media-body">
                        <h3 class="counter mb-0">{{ $my_classes->count() }}</h3>
                        <span class="text-capitalize font-size-xs">Total Classes</span>
                    </div>
                    <div class="align-self-center ml-3">
                        <i class="material-symbols-rounded symbol-3x opacity-75">class</i>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Results Declared --}}
        @can('dashboard_statistics.results_declared')
            <div class="card card-body bg-success-400 has-bg-image">
                <div class="media">
                    <div class="media-body">
                        <h3 class="counter mb-0">{{ $exams_count }}</h3>
                        <span class="text-capitalize font-size-xs">Results Declared</span>
                    </div>
                    <div class="align-self-center ml-3">
                        <i class="material-symbols-rounded symbol-3x opacity-75">file_open</i>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Total Payments Done --}}
        @can('dashboard_statistics.payments_done')
            <div class="card card-body has-bg-image bg-pink-400">
                <div class="media">
                    <div class="media-body">
                        <h3 class="counter mb-0">{{ $total_pay_records }}</h3>
                        <span class="text-capitalize font-size-xs">Total Payments Done</span>
                    </div>
                    <div class="align-self-center ml-3">
                        <i class="material-symbols-rounded symbol-3x opacity-75">paid</i>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Total Books --}}
        @can('dashboard_statistics.books')
            <div class="card card-body has-bg-image bg-violet-800">
                <div class="media">
                    <div class="media-body">
                        <h3 class="counter mb-0">{{ $books_count }}</h3>
                        <span class="text-capitalize font-size-xs">Total Books</span>
                    </div>
                    <div class="align-self-center ml-3">
                        <i class="material-symbols-rounded symbol-3x opacity-75">library_books</i>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Total Book Requests --}}
        @can('dashboard_statistics.book_requests')
            <div class="card card-body has-bg-image bg-violet-400">
                <div class="media">
                    <div class="media-body ml-2">
                        <div class="row">
                            <div class="col-6 text-left">
                                {{ $book_requests_returned_count }}<br />
                                <span class="text-capitalize font-size-xs">Returned</span>
                            </div>
                            <div class="col-6 text-left">
                                {{ $book_requests_count - $book_requests_returned_count }}<br />
                                <span class="text-capitalize font-size-xs">Out</span>
                            </div>
                        </div>
                    </div>
                    <div class="media-body">
                        <h3 class="counter mb-0">{{ $book_requests_count }}</h3>
                        <span class="text-capitalize font-size-xs">Total Book Requests</span>
                    </div>
                </div>
            </div>
        @endcan
    </div>

    {{-- Reports section (larger charts) --}}
    <div class="row">
        @can('dashboard_statistics.gender_chart')
            <div class="col-md-6">
                <div class="card card-collapsed print-this">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title">Gender Distribution (Students)</h6>
                        <div class="header-elements print-none pt-0">
                            <div class="list-icons">
                                <a class="list-icons-item print-this-btn" href="javascript:;" data-action="print"></a>
                                <a class="list-icons-item disabled" data-action="fullscreen"></a>
                                <a class="list-icons-item" data-action="collapse"></a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas
                            id="chart-gender"
                            width="400"
                            height="240"
                            aria-label="Gender distribution"
                            role="img"
                        ></canvas>
                    </div>
                </div>
            </div>
        @endcan
        @can('dashboard_statistics.payments_chart')
            <div class="col-md-6">
                <div class="card card-collapsed print-this">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title">Payments (Last 12 months)</h6>
                        <div class="header-elements print-none pt-0">
                            <div class="list-icons">
                                <a class="list-icons-item print-this-btn" href="javascript:;" data-action="print"></a>
                                <a class="list-icons-item disabled" data-action="fullscreen"></a>
                                <a class="list-icons-item" data-action="collapse"></a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas
                            id="chart-payments"
                            width="400"
                            height="240"
                            aria-label="Payments trend"
                            role="img"
                        ></canvas>
                    </div>
                </div>
            </div>
        @endcan
    </div>

    @can('dashboard_statistics.students_per_class_chart')
        <div class="row">
            <div class="col-12">
                <div class="card card-collapsed print-this">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title">Students per Class</h6>
                        <div class="header-elements print-none pt-0">
                            <div class="list-icons">
                                <a class="list-icons-item print-this-btn" href="javascript:;" data-action="print"></a>
                                <a class="list-icons-item disabled" data-action="fullscreen"></a>
                                <a class="list-icons-item" data-action="collapse"></a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas
                            id="chart-students-per-class"
                            width="800"
                            height="240"
                            aria-label="Students per class"
                            role="img"
                        ></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- Notices --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-collapsed">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title">
                        Notices<sup class="badge badge-default notices-badge">{{ $unviewed_count }}</sup>
                    </h6>
                    <div class="header-elements">
                        <div class="list-icons">
                            @can('notices.view')
                                <a class="btn btn-link list-icons-item" href="{{ route('notices.index') }}">Manage</a>
                            @endcan
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

    {{-- Student breakdown modal --}}
    @includeWhen(auth()->user()->can('dashboard_statistics.student_breakdown_modal') && auth()->user()->can('notices.view'), 'pages.modals.students_number_breakdown')

@endsection

@pushOnce('scripts')
    <script>
        // Data injected from the controller
        const months = @json($chart_months ?? []);
        const studentsMonthly = @json($students_monthly ?? array_fill(0, 12, 0));
        const paymentsMonthly = @json($payments_monthly ?? array_fill(0, 12, 0));
        const genderCounts = @json($gender_counts ?? ['male' => 0, 'female' => 0]);
        const studentsPerClassLabels = @json($students_per_class_labels ?? []);
        const studentsPerClassCounts = @json($students_per_class_counts ?? []);
    </script>
@endPushOnce
