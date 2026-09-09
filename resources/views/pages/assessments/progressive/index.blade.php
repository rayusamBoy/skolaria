@extends('layouts.master')

@section('page_title', 'Progressive Sheet')

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="material-symbols-rounded mr-2">folder_copy</i> Progressive Sheet</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('assessments.progressive_select') }}">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exam_id" class="col-form-label font-weight-bold">Assessments for:</label>
                            <select
                                required
                                id="exam_id"
                                name="exam_id"
                                class="form-control select"
                                data-placeholder="Select Exam"
                            >
                                @foreach ($assessments as $as)
                                    <option @selected($selected && $exam_id == $as->id) value="{{ $as->id }}">
                                        {{ $as->exam->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @php
                        $my_classes = $user_is_class_section_teacher ? $my_classes->whereIn('id', $teacher_class_ids) : $my_classes;
                    @endphp
                    {{-- Class --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="my_class_id" class="col-form-label font-weight-bold">Class:</label>
                            <select
                                onchange="{{ $user_is_class_section_teacher ? "getTeacherClassSections(this.value, '#section_id')" : "getClassSections(this.value, '#section_id')" }}"
                                required
                                id="my_class_id"
                                name="my_class_id"
                                class="form-control select"
                                data-placeholder="Select Class"
                            >
                                <option value=""></option>
                                @foreach ($my_classes as $c)
                                    <option @selected($selected && $my_class_id == $c->id) value="{{ $c->id }}">
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- Section --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="section_id" class="col-form-label font-weight-bold">Section:</label>
                            <select
                                required
                                id="section_id"
                                name="section_id"
                                data-placeholder="Select Class First"
                                class="form-control select"
                            >
                                @if ($selected)
                                    @foreach ($sections->where('my_class_id', $my_class_id) as $s)
                                        <option @selected($section_id == $s->id) value="{{ $s->id }}">
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    {{-- Submit --}}
                    <div class="col-md-2 mt-4">
                        <div class="mt-1 text-right">
                            <button type="submit" class="btn btn-primary">
                                View Sheet <i class="material-symbols-rounded ml-2">send</i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($selected)
        <div class="card wmin-100-pcnt w-fit">
            <div class="card-header header-elements-inline">
                <h6 class="font-weight-bold">
                    Progressive Sheet for {{ $my_class->name }}
                    @if (isset($section)) {{ $section->name }} @endif
                    - {{ $ex->name }} ({{ Number::spellOrdinal($asr->first()->exam->term) }} term, {{ $year }})
                </h6>
                <div class="header-elements pt-0">
                    <div class="list-icons">
                        @if (auth()->user()->can($tenant_permissions['assessments']['print_progressive']['permission']))
                            <a
                                class="list-icons-item"
                                target="_blank"
                                href="{{ route('assessments.print_progressive', [$assessment->id, $my_class_id, $section_id]) }}"
                                data-action="print"
                            ></a>
                        @endcan
                        <a class="list-icons-item" data-action="fullscreen"></a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <table class="fls-scrolls-hoverable table-styled float-head" data-fl-scrolls id="results-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="font-weight-bold text-center">#</th>
                            <th class="font-weight-bold pl-2">STUDENT NAME</th>
                            <th class="font-weight-bold text-vertical text-center">Gender</th>
                            @foreach ($subjects as $sub)
                                <th
                                    class="font-weight-bold text-vertical text-center"
                                    title="{{ $sub->name }}"
                                    rowspan="2"
                                >
                                    {{ strtoupper($sub->slug) }}
                                </th>
                                <th class="font-weight-bold text-info-800 text-vertical text-center" title="Grade">
                                    Grade
                                </th>
                                <th class="font-weight-bold text-vertical text-center" title="Position">Pos</th>
                            @endforeach
                            <th class="font-weight-bold text-vertical text-center text-red-600">Total</th>
                            <th class="font-weight-bold text-vertical text-center text-blue-800">Average</th>
                            <th
                                class="font-weight-bold text-vertical text-center text-orange-800"
                                title="Average Grade"
                            >
                                Avg Grade
                            </th>
                            <th class="font-weight-bold text-vertical text-center text-green-800">Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @include('pages.assessments.progressive.partials.table_body')
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection
