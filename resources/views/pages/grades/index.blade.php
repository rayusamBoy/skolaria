@extends('layouts.master')

@section('page_title', 'Manage Grades')

@section('content')

@php
$canEdit = auth()->user()->can($tenant_permissions['grades']['edit']['permission']);
$canDelete = auth()->user()->can($tenant_permissions['grades']['destroy']['permission']);
$hasActions = $canEdit || $canDelete;
@endphp

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Grades</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item"><a href="#all-grades" class="nav-link active" data-toggle="tab">Manage Grades</a></li>
            @can($tenant_permissions['grades']['store']['permission'])
            <li class="nav-item"><a href="#new-grade" class="nav-link" data-toggle="tab">Add Grade</a></li>
            @endcan
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="all-grades">
                <table class="table datatable-button-html5-columns" id="manage-grades-table">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Name</th>
                            <th>Class Type</th>
                            <th>Range</th>
                            <th>Point</th>
                            <th>Remark</th>
                            @if($hasActions)
                            <th class="text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grades as $gr)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $gr->name }}</td>
                            <td>{{ $gr->class_type_id ? $class_types->where('id', $gr->class_type_id)->first()->name : 'Not Applicable'}}</td>
                            <td>{{ "{$gr->mark_from} - {$gr->mark_to}" }}</td>
                            <td>{{ $gr->point }}</td>
                            <td>{{ $gr->remarks }}</td>
                            @if($hasActions)
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="javascript:;" class="material-symbols-rounded" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            @if($canEdit)
                                            <a href="{{ route('grades.edit', $gr->id) }}" class="dropdown-item"><i class="material-symbols-rounded">edit</i> Edit</a>
                                            @endif
                                            @if($canDelete)
                                            <a id="{{ $gr->id }}" onclick="confirmDelete(this.id)" href="javascript:;" class="dropdown-item text-danger"><i class="material-symbols-rounded">delete</i> Delete</a>
                                            <form method="post" id="item-delete-{{ $gr->id }}" action="{{ route('grades.destroy', $gr->id) }}" class="hidden">@csrf @method('delete')</form>
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

            @can($tenant_permissions['grades']['store']['permission'])
            <div class="tab-pane fade" id="new-grade">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info border-0 alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                            <span>If the grade you are creating applies to all class types select <strong>Not Applicable.</strong> Otherwise select the Class Type that the grade applies to.</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10">
                        <form method="post" action="{{ route('grades.store') }}" class="ajax-store" data-reload="#manage-grades-table">
                            @csrf
                            {{-- Name --}}
                            <div class="form-group row">
                                <label for="name" class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                                <div class="col-lg-9">
                                    <input name="name" id="name" value="{{ old('name') }}" required type="text" class="form-control text-uppercase" placeholder="E.g., A">
                                </div>
                            </div>
                            {{-- Class Type --}}
                            <div class="form-group row">
                                <label for="class_type_id" class="col-lg-3 col-form-label font-weight-semibold">Class Type</label>
                                <div class="col-lg-9">
                                    <select class="form-control select" name="class_type_id" id="class_type_id">
                                        <option value="">Not Applicable</option>
                                        @foreach($class_types as $ct)
                                        <option @selected(old('class_type_id')==$ct->id) value="{{ $ct->id }}">{{ $ct->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- Mark From --}}
                            <div class="form-group row">
                                <label for="mark_from" class="col-lg-3 col-form-label font-weight-semibold">Mark From <span class="text-danger">*</span></label>
                                <div class="col-lg-3">
                                    <input min="0" max="100" id="mark_from" name="mark_from" value="{{ old('mark_from') }}" required type="number" class="form-control" placeholder="E.g., 0">
                                </div>
                            </div>
                            {{-- Mark To --}}
                            <div class="form-group row">
                                <label for="mark_to" class="col-lg-3 col-form-label font-weight-semibold">Mark To <span class="text-danger">*</span></label>
                                <div class="col-lg-3">
                                    <input min="0" max="100" id="mark_to" name="mark_to" value="{{ old('mark_to') }}" required type="number" class="form-control" placeholder="E.g., 35">
                                </div>
                            </div>
                            {{-- Point --}}
                            <div class="form-group row">
                                <label for="point" class="col-lg-3 col-form-label font-weight-semibold">Point <span class="text-danger">*</span>
                                    <i class="material-symbols-rounded float-right text-info" data-toggle="tooltip" title="Sample points: 1 → A, 2 → B, 3 → C, 4 → D, 5 → F">info</i>
                                </label>
                                <div class="col-lg-3">
                                    <input name="point" id="point" min="0" required type="number" class="form-control" placeholder="E.g., 1">
                                </div>
                                <div class="col-lg-6 m-lg-auto"><span class="font-weight-bold font-italic text-info-800">{{ __('msg.point_info') }}</span></div>
                            </div>
                            {{-- Remarks --}}
                            <div class="form-group row">
                                <label for="remarks" class="col-lg-3 col-form-label font-weight-semibold">Remarks</label>
                                <div class="col-lg-9">
                                    <select class="form-control select" data-placeholder="Select Remarks" name="remarks" id="remarks">
                                        <option value="">Select Remark</option>
                                        @foreach(Mk::getRemarks() as $rem)
                                        <option @selected(old('remarks')==$rem) value="{{ $rem }}">{{ $rem }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- Submit Button --}}
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Submit form <i class="material-symbols-rounded ml-2">send</i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>

{{--Grades List Ends--}}

@endsection
