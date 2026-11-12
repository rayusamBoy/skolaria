@extends('layouts.master')

@section('page_title', 'Student Assessmentsheet')

@section('content')
    <div class="card">
        <div class="card-header text-center">
            <h4 class="card-title font-weight-bold">
                Student Assessmentsheet for => {{ $sr->user->name . ' (' . $sr->my_class->name . ' ' . $sr->section->name . ')' }}
            </h4>
        </div>
    </div>

    @foreach ($assessments as $as)
        <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="font-weight-bold">Assessments - {{ $as->exam->name . ' - ' . $as->exam->year }}</h6>
                <div class="header-elements pt-0">
                    <div class="list-icons">
                        @if (auth()->user()->canAny(['assessments.print_cover', 'assessments.print_minimal', 'assessments.print_detailed']))
                            <a
                                class="list-icons-item"
                                target="_blank"
                                href=""
                                class="btn btn-secondary btn-lg"
                                data-toggle="dropdown"
                                data-action="print"
                            ></a>
                            <div class="dropdown-menu">
                                @can('assessments.print_cover')
                                    <a
                                        target="_blank"
                                        href="{{ route('assessments.print_cover', Qs::hash($student_id)) }}"
                                        class="dropdown-item"
                                    >
                                        Print Cover
                                    </a>
                                @endcan
                                @can('assessments.print_minimal')
                                    <a
                                        target="_blank"
                                        href="{{ route('assessments.print_minimal', [Qs::hash($student_id), $as->id, $year]) }}"
                                        class="dropdown-item"
                                    >
                                        Print Minimal
                                    </a>
                                @endcan
                                @can('assessments.print_detailed')
                                    <a
                                        target="_blank"
                                        href="{{ route('assessments.print_detailed', [Qs::hash($student_id), $as->id, $year]) }}"
                                        class="dropdown-item"
                                    >
                                        Print Detailed
                                    </a>
                                @endcan
                            </div>
                        @endcan
                        <a class="list-icons-item disabled" data-action="fullscreen"></a>
                        <a class="list-icons-item" data-action="collapse"></a>
                        <a class="list-icons-item" data-action="remove"></a>
                    </div>
                </div>
            </div>

            <div class="card-body collapse">
                {{-- Sheet Table --}}
                @includeWhen(auth()->user()->can('assessments.show'), 'pages.assessments.show.sheet')
            </div>
        </div>

        {{-- Assessment Comments --}}
        @includeWhen(auth()->user()->can('assessments.comment_update'), 'pages.assessments.show.comments')
        {{-- Skill Rating --}}
        @includeWhen(auth()->user()->can('assessments.skills_update'), 'pages.assessments.show.skills')

    @endforeach

@endsection
