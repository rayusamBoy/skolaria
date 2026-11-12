@extends('layouts.master')

@section('page_title', 'Batch Marks')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div
                class="alert alert-info alert-dismissible has-do-not-show-again-button alert-styled-left"
                id="note-info"
            >
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <span>
                    <strong>Note:</strong>
                    You can create assessment records of students either here through downloading
                    <strong>batch template</strong> or in
                    <a href="{{ route('marks.manage') }}"> Manage Exam Marks</a> section only for the current session
                    and current students class. Once the student has been promoted to the next session you will not be
                    able to create the records for the current session anymore.
                </span>
            </div>
        </div>
    </div>

    @includeWhen(auth()->user()->can('marks.initialize_marks'), 'pages/marks/batch/initialize')
    @includeWhen(auth()->user()->can('marks.batch_template'), 'pages/marks/batch/template')
    @includeWhen(auth()->user()->can('marks.batch_upload'), 'pages/marks/batch/upload')
    @includeWhen(auth()->user()->can('marks.batch_update'), 'pages/marks/batch/update')
    @includeWhen(auth()->user()->can('marks.batch_delete'), 'pages/marks/batch/delete')

@endsection
