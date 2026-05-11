@extends('layouts.master')

@section('page_title', 'Tabulation Sheet')

@push('styles')
<style>
    .tabulation-table tbody tr {
        content-visibility: auto;
        contain-intrinsic-size: 0 40px;
    }

</style>
@endpush

@section('content')

{{-- Selector Form --}}
<div class="card no-print">
    <div class="card-header header-elements-inline">
        <h6 class="card-title"><i class="material-symbols-rounded mr-2">table</i> Tabulation Sheet</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <form method="post" class="page-block" action="{{ route('marks.tabulation_select') }}">
            @csrf
            <div class="row">
                {{-- Select Year --}}
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="year" class="col-form-label font-weight-bold">Select Year:</label>
                        <select onchange="getYearExams(this.value)" data-placeholder="Select Year" required id="year" name="year" class="form-control select">
                            <option value="">Select Year</option>
                            @foreach($years as $yr)
                            <option @selected($selected && $yr->year == $year) value="{{ $yr->year }}">{{ $yr->year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- Exam --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="exam_id" class="col-form-label font-weight-bold">Exam:</label>
                        <select required id="exam_id" name="exam_id" class="form-control select-search" data-placeholder="Select Year First">
                            <option value="">Select Year First</option>
                            @if($selected)
                            @foreach ($exams as $exam)
                            <option @selected($selected && $exam_id==$exam->id) value="{{ $exam->id }}">{{ $exam->name }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                {{-- Class --}}
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="my_class_id" class="col-form-label font-weight-bold">Class:</label>
                        <select onchange="getClassSections(this.value, '#section_id_add_all')" required id="my_class_id" name="my_class_id" class="form-control select" data-placeholder="Select Class">
                            <option value="">Select Class</option>
                            @foreach ($my_classes as $c)
                            <option @selected($selected && $my_class->id == $c->id) value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- Section --}}
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="section_id_add_all" class="col-form-label font-weight-bold">Section:</label>
                        <select required id="section_id_add_all" name="section_id" data-placeholder="Select Class First" class="form-control select">
                            @if($selected)
                            @foreach ($sections->where('my_class_id', $my_class->id) as $s)
                            <option @selected($section_id==$s->id) value="{{ $s->id }}">
                                {{ $s->name }}
                            </option>
                            @endforeach
                            <option @selected($section_id=='all' ) value="all" title="All Sections">All</option>
                            @endif
                        </select>
                    </div>
                </div>
                {{-- Submit Button --}}
                <div class="col-md-2 mt-4">
                    <div class="text-right mt-1">
                        <button type="submit" class="btn btn-primary">View Sheet <i class="material-symbols-rounded ml-2">send</i></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selected)
<div class="card tabulation w-fit wmin-100-pcnt">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="card-title">
            Tabulation: {{ $my_class->name }} @if(isset($section)) {{ $section->name }} @endif - {{ $ex->name }} ({{ $year }})
        </h6>
        <div class="header-elements pt-0">
            <div class="list-icons">
                @can($tenant_permissions['marks']['print_tabulation']['permission'])
                <a class="list-icons-item" target="_blank" href="{{ route('marks.print_tabulation', [$exam_id, $my_class->id, $section_id, $year]) }}" data-action="print"></a>
                @endcan
                <a class="list-icons-item" data-action="fullscreen"></a>
                <a class="list-icons-item" data-action="collapse"></a>
                <a class="list-icons-item" data-action="remove"></a>
            </div>
        </div>
    </div>

    <div class="card-body">
        @include('pages.marks.tabulation.partials.summaries')
        @include('pages.marks.tabulation.partials.table')
        @include('pages.marks.tabulation.partials.performance_summary')
        <p class="text-center mt-4" style="font-size: 12px;">
            {{ date('Y') }}. {{ ucwords(strtolower($settings->firstWhere('key', 'system_name')->value)) }}. All rights reserved.
        </p>
    </div>
</div>
@endif

@endsection
