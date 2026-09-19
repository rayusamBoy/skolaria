@extends('layouts.master')

@section('page_title', 'Continous Assessments List')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info alert-dismissible border-0">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <span
                    >You are viewing Continous Assessments (CA) list for <strong>all years.</strong> For actions about,
                    consider navigating to <a href="{{ route('exams.index') }}">Exams List</a> instead.</span>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">List of Continous Assessments</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active">
                    <table class="datatable-button-html5-columns table">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Term</th>
                                <th>Session</th>
                                <td>Category</td>
                                <td>Is Locked?</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assessments as $as)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $as->exam->name }} Assessment</td>
                                    <td>{{ ucfirst(NUmber::spellOrdinal($as->exam->term)) . 'Term ' }}</td>
                                    <td>{{ $as->exam->year }}</td>
                                    <td>{{ $as->exam->category->name }}</td>
                                    <td>{{ $as->exam->is_locked ? "Yes" : "No" }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{--Class List Ends--}}

@endsection
