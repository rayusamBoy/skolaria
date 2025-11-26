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

@can('view_assessment_sheet')
@foreach($assessments as $as)
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="font-weight-bold">
            Assessments - {{ $as->exam->name . ' - ' . $as->exam->year }}
        </h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body collapse">
        {{-- Sheet Table --}}
        @includeWhen(auth()->user()->can('view_assessment_sheet'), 'pages.assessments.show.sheet')

        {{-- Print Button --}}
        @can('print_assessment')
        <div class="text-center mt-3">
            <div class="list-icons">
                <div class="dropdown">
                    @if(Qs::getSetting('allow_assessmentsheet_print') || auth()->user()->hasRole('super_admin'))
                    @if(auth()->user()->can('print_assessment_variants'))
                    <a href="#" class="btn btn-secondary btn-lg" data-toggle="dropdown">
                        Print Assessment <i class="material-symbols-rounded ml-2">print</i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-left">
                        <a target="_blank" href="{{ route('assessments.print_cover', Qs::hash($student_id)) }}" class="dropdown-item">
                            Cover
                        </a>
                        <a target="_blank" href="{{ route('assessments.print_minimal', [Qs::hash($student_id), $as->exam->id, $year]) }}" class="dropdown-item">
                            Minimal
                        </a>
                        <a target="_blank" href="{{ route('assessments.print_detailed', [Qs::hash($student_id), $as->exam->id, $year]) }}" class="dropdown-item">
                            Detailed
                        </a>
                    </div>
                    @else
                    <a target="_blank" href="{{ route('assessments.print_detailed', [Qs::hash($student_id), $as->exam->id, $year]) }}" class="btn btn-secondary btn-lg">
                        Print Assessment <i class="material-symbols-rounded ml-2">print</i>
                    </a>
                    @endif
                    @endif
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>

{{-- Assessment Comments --}}
@can('comment_assessment')
@include('pages.assessments.show.comments')
@endcan

{{-- Skill Rating --}}
@can('rate_skills')
@include('pages.assessments.show.skills')
@endcan
@endforeach
@endcan

@endsection
