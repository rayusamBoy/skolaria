@extends('layouts.master')

@section('page_title', 'Manage TimeTables')

@section('content')
    @php
        $canView = auth()->user()->can('time_tables.show_record');
        $canEdit = auth()->user()->can('time_tables.edit_record');
        $canManage = auth()->user()->can('time_tables.manage_record');
        $canDelete = auth()->user()->can('time_tables.delete_record');
        $hasActions = $canView || $canEdit || $canManage || $canDelete;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage TimeTables</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item dropdown">
                    <a href="javascript:;" class="nav-link active dropdown-toggle" data-toggle="dropdown"
                        >Show TimeTables</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach ($my_classes as $mc)
                            <a href="#ttr{{ $mc->id }}" class="dropdown-item" data-toggle="tab">{{ $mc->name }}</a>
                        @endforeach
                    </div>
                </li>
                @can('time_tables.store')
                    <li class="nav-item">
                        <a href="#add-tt" class="nav-link" data-toggle="tab">Create Timetable</a>
                    </li>
                @endcan
            </ul>

            <div class="tab-content">
                @can('time_tables.store')
                    <div class="tab-pane fade" id="add-tt">
                        <div class="col-md-8">
                            <form class="ajax-store" method="post" action="{{ route('ttr.store') }}">
                                @csrf
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input
                                            name="name"
                                            value="{{ old('name') }}"
                                            required
                                            type="text"
                                            class="form-control"
                                            placeholder="Name of TimeTable"
                                        />
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="exam_id" class="col-lg-3 col-form-label font-weight-semibold"
                                        >Type (Class or Exam)</label>
                                    <div class="col-lg-9">
                                        <select
                                            class="select form-control"
                                            onchange="hideShowSection(this.value, '#section_id')"
                                            name="exam_id"
                                            id="exam_id"
                                        >
                                            <option value="class">Class Timetable</option>
                                            @foreach ($exams as $ex)
                                                <option @selected(old('exam_id') == $ex->id) value="{{ $ex->id }}">
                                                    {{ $ex->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="my_class_id" class="col-lg-3 col-form-label font-weight-semibold"
                                        >Class <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <select
                                            required
                                            data-placeholder="Select Class"
                                            onchange="getClassSections(this.value)"
                                            class="form-control select"
                                            name="my_class_id"
                                            id="my_class_id"
                                        >
                                            <option value=""></option>
                                            @foreach ($my_classes as $mc)
                                                <option @selected(old('my_class_id') == $mc->id) value="{{ $mc->id }}">
                                                    {{ $mc->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="section_id" class="col-lg-3 col-form-label font-weight-semibold"
                                        >Section <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <select
                                            required
                                            data-placeholder="Select Class First"
                                            class="form-control select"
                                            name="section_id"
                                            id="section_id"
                                        ></select>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button id="ajax-btn" type="submit" class="btn btn-primary">
                                        Submit form <i class="material-symbols-rounded ml-2">send</i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endcan

                @foreach ($my_classes as $mc)
                    <div class="tab-pane fade show" id="ttr{{ $mc->id }}">
                        <table class="datatable-button-html5-columns table">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Type</th>
                                    <th>Year</th>
                                    @if ($hasActions)
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tt_records->where('my_class_id', $mc->id) as $ttr)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $ttr->name }}</td>
                                        <td>{{ $ttr->my_class->name }}</td>
                                        <td>{{ $ttr->section->name ?? 'All' }}</td>
                                        <td>{{ $ttr->exam_id ? $ttr->exam->name : 'Class Timetable' }}</td>
                                        <td>{{ $ttr->year }}</td>
                                        @if ($hasActions)
                                            <td class="text-center">
                                                <div class="list-icons">
                                                    <div class="dropdown">
                                                        <a
                                                            class="material-symbols-rounded"
                                                            href="#"
                                                            data-toggle="dropdown"
                                                        >lists</a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            @if ($canView)
                                                                <a
                                                                    href="{{ route('ttr.show', $ttr->id) }}"
                                                                    class="dropdown-item"
                                                                >
                                                                    <i class="material-symbols-rounded">visibility</i
                                                                    >View
                                                                </a>
                                                            @endif
                                                            @if ($canEdit)
                                                                <a
                                                                    href="{{ route('ttr.edit', $ttr->id) }}"
                                                                    class="dropdown-item"
                                                                >
                                                                    <i class="material-symbols-rounded">edit</i>Edit
                                                                </a>
                                                            @endif
                                                            @if ($canManage)
                                                                <a
                                                                    href="{{ route('ttr.manage', $ttr->id) }}"
                                                                    class="dropdown-item"
                                                                >
                                                                    <i class="material-symbols-rounded">manage_search</i
                                                                    >Manage
                                                                </a>
                                                            @endif
                                                            @if ($canDelete)
                                                                <a
                                                                    id="{{ $ttr->id }}"
                                                                    onclick="confirmPermanentDelete(this.id)"
                                                                    href="javascript:;"
                                                                    class="dropdown-item text-danger"
                                                                >
                                                                    <i class="material-symbols-rounded">delete</i>Delete
                                                                </a>
                                                                <form
                                                                    method="post"
                                                                    id="item-delete-{{ $ttr->id }}"
                                                                    action="{{ route('ttr.destroy', $ttr->id) }}"
                                                                    class="hidden"
                                                                >
                                                                    @csrf
                                                                    @method('delete')
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- TimeTable Ends --}}
@endsection
