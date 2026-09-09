@extends('layouts.master')

@section('page_title', 'Manage Divisions')

@section('content')
    @php
        $canEdit = auth()->user()->can($tenant_permissions['divisions']['edit']['permission']);
        $canDelete = auth()->user()->can($tenant_permissions['divisions']['destroy']['permission']);
        $hasActions = $canEdit || $canDelete;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Divisions</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item">
                    <a href="#all-divisions" class="nav-link active" data-toggle="tab">Manage Divisions</a>
                </li>

                @can($tenant_permissions['divisions']['store']['permission'])
                    <li class="nav-item">
                        <a href="#new-division" class="nav-link" data-toggle="tab">Add Division</a>
                    </li>
                @endcan
            </ul>

            <div class="tab-content">
                {{-- Manage Divisions tab --}}
                <div class="tab-pane fade show active" id="all-divisions">
                    <table class="datatable-button-html5-columns table" id="manage-divisions-table">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Class Type</th>
                                <th>Range</th>
                                <th>Remark</th>
                                @if ($hasActions)
                                    <th class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($divisions as $dv)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $dv->name }}</td>
                                    <td>
                                        {{
                                            $dv->class_type_id
                                            ? $class_types->firstWhere('id', $dv->class_type_id)->name
                                            : 'Not Applicable'
                                        }}
                                    </td>
                                    <td>{{ "{$dv->points_from} - {$dv->points_to}" }}</td>
                                    <td>{{ $dv->remarks }}</td>
                                    @if ($hasActions)
                                        <td class="text-center">
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a
                                                        href="javascript:;"
                                                        class="material-symbols-rounded"
                                                        data-toggle="dropdown"
                                                    >lists</a>
                                                    <div class="dropdown-menu dropdown-menu-left">
                                                        @if ($canEdit)
                                                            <a
                                                                href="{{ route('divisions.edit', $dv->id) }}"
                                                                class="dropdown-item"
                                                            >
                                                                <i class="material-symbols-rounded">edit</i> Edit
                                                            </a>
                                                        @endif
                                                        @if ($canDelete)
                                                            <a
                                                                id="division-delete-{{ $dv->id }}"
                                                                onclick="confirmDelete(this.id)"
                                                                href="javascript:;"
                                                                class="dropdown-item text-danger"
                                                            >
                                                                <i class="material-symbols-rounded">delete</i> Delete
                                                            </a>
                                                            <form
                                                                method="post"
                                                                id="item-delete-division-delete-{{ $dv->id }}"
                                                                action="{{ route('divisions.destroy', $dv->id) }}"
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

                {{-- Create Divisions tab --}}
                @can($tenant_permissions['divisions']['store']['permission'])
                    <div class="tab-pane fade" id="new-division">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info alert-dismissible border-0">
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                    <span>
                                        If the division applies to all class types select
                                        <strong>Not Applicable</strong>; otherwise select the specific Class Type.
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <form
                                    method="post"
                                    action="{{ route('divisions.store') }}"
                                    class="ajax-store"
                                    data-reload="#manage-divisions-table"
                                >
                                    @csrf
                                    {{-- Name --}}
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label font-weight-semibold">
                                            Name <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-lg-9">
                                            <input
                                                name="name"
                                                value="{{ old('name') }}"
                                                required
                                                type="text"
                                                class="form-control text-uppercase"
                                                placeholder="E.g. I"
                                            />
                                        </div>
                                    </div>
                                    {{-- Class Type --}}
                                    <div class="form-group row">
                                        <label for="class_type_id" class="col-lg-3 col-form-label font-weight-semibold">
                                            Class Type
                                        </label>
                                        <div class="col-lg-9">
                                            <select name="class_type_id" id="class_type_id" class="form-control select">
                                                <option value="">Not Applicable</option>
                                                @foreach ($class_types as $ct)
                                                    <option
                                                        value="{{ $ct->id }}"
                                                        @selected(old('class_type_id') == $ct->id)
                                                    >
                                                        {{ $ct->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Points From & Points To --}}
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label font-weight-semibold">
                                            Points From <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-lg-3">
                                            <input
                                                name="points_from"
                                                type="number"
                                                min="0"
                                                max="100"
                                                value="{{ old('points_from') }}"
                                                required
                                                class="form-control"
                                                placeholder="0"
                                            />
                                        </div>
                                        <label class="col-lg-3 col-form-label font-weight-semibold">
                                            Points To <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-lg-3">
                                            <input
                                                name="points_to"
                                                type="number"
                                                min="0"
                                                max="100"
                                                value="{{ old('points_to') }}"
                                                required
                                                class="form-control"
                                                placeholder="0"
                                            />
                                        </div>
                                    </div>
                                    {{-- Remarks --}}
                                    <div class="form-group row">
                                        <label for="remarks" class="col-lg-3 col-form-label font-weight-semibold"
                                            >Remarks</label>
                                        <div class="col-lg-9">
                                            <select
                                                name="remarks"
                                                id="remarks"
                                                class="form-control select"
                                                data-placeholder="Select Remark"
                                            >
                                                <option value="">Select Remarks</option>
                                                @foreach (Mk::getRemarks() as $rem)
                                                    <option value="{{ $rem }}" @selected(old('remarks') == $rem)>
                                                        {{ $rem }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Submit Button --}}
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">
                                            Submit form <i class="material-symbols-rounded ml-2">send</i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>

@endsection
