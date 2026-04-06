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
        @includeWhen(auth()->user()->can($tenant_permissions['assessments']['show']['permission']), 'pages.assessments.show.sheet')

        {{-- Print Button --}}
        @if(auth()->user()->canAny([$tenant_permissions['assessments']['print_cover']['permission'], $tenant_permissions['assessments']['print_minimal']['permission'], $tenant_permissions['assessments']['print_detailed']['permission']]))
        <div class="text-center mt-3">
            <div class="list-icons">
                <div class="dropdown">
                    <a href="#" class="btn btn-secondary btn-lg" data-toggle="dropdown">
                        Print Assessment <i class="material-symbols-rounded ml-2">print</i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-left">
                        @can($tenant_permissions['assessments']['print_cover']['permission'])
                        <a target="_blank" href="{{ route('assessments.print_cover', Qs::hash($student_id)) }}" class="dropdown-item">
                            Cover
                        </a>
                        @endcan
                        @can($tenant_permissions['assessments']['print_minimal']['permission'])
                        <a target="_blank" href="{{ route('assessments.print_minimal', [Qs::hash($student_id), $as->exam->id, $year]) }}" class="dropdown-item">
                            Minimal
                        </a>
                        @endcan
                        @can($tenant_permissions['assessments']['print_detailed']['permission'])
                        <a target="_blank" href="{{ route('assessments.print_detailed', [Qs::hash($student_id), $as->exam->id, $year]) }}" class="dropdown-item">
                            Detailed
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Assessment Comments --}}
@includeWhen(auth()->user()->can($tenant_permissions['assessments']['comments_update']['permission']), 'pages.assessments.show.comments')
{{-- Skill Rating --}}
@includeWhen(auth()->user()->can($tenant_permissions['assessments']['skills_update']['permission']), 'pages.assessments.show.skills')

@endforeach

@endsection
