@extends('layouts.master')

@section('page_title', 'Student Marksheet')

@section('content')

<div class="card">
    <div class="card-header text-center">
        <h4 class="card-title font-weight-bold">Student Marksheet for &rArr; {{ $sr->user->name.' (' . $sr->my_class->name . ' ' . $sr->section->name . ')' }} </h4>
    </div>
</div>

@foreach($exams as $ex)
@foreach($exam_records->where('exam_id', $ex->id) as $exr)

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="font-weight-bold">{{ "{$ex->name} - {$ex->year}" }}</h6>
        <div class="header-elements pt-0">
            <div class="list-icons">
                @if(auth()->user()->can($tenant_permissions['marks']['print_view']['permission']))
                <a class="list-icons-item" target="_blank" href="{{ route('marks.print', [Qs::hash($student_id), $ex->id, $year]) }}" data-action="print"></a>
                @endcan
                <a class="list-icons-item disabled" data-action="fullscreen"></a>
                <a class="list-icons-item" data-action="collapse"></a>
                <a class="list-icons-item" data-action="remove"></a>
            </div>
        </div>
    </div>

    <div class="card-body collapse">
        {{--Sheet Table--}}
        @include('pages.marks.show.sheet')
    </div>
</div>

{{-- EXAM COMMENTS   --}}
@includeWhen(auth()->user()->can($tenant_permissions['marks']['comment_update']['permission']), 'pages.marks.show.comments')

{{-- SKILL RATING --}}
@includeWhen(auth()->user()->can($tenant_permissions['marks']['skills_update']['permission']), 'pages.marks.show.skills')

@endforeach
@endforeach

@endsection
