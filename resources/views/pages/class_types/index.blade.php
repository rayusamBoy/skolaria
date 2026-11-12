@extends('layouts.master')

@section('page_title', 'Manage Class Types')

@section('content')
    @php
        $canEdit = auth()->user()->can('class_types.edit');
        $canDelete = auth()->user()->can('class_types.destroy');
        $hasActions = $canEdit || $canDelete;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Class Types</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item">
                    <a href="#manage-class-types" class="nav-link active" data-toggle="tab"> Manage Class Types </a>
                </li>
                @can('class_types.store')
                    <li class="nav-item">
                        <a href="#new-class-type" class="nav-link" data-toggle="tab">Create New Class Type</a>
                    </li>
                @endcan
            </ul>

            <div class="tab-content">
                {{-- Create New Class Type --}}
                @can('class_types.store')
                    <div class="tab-pane fade" id="new-class-type">
                        <div class="row">
                            <div class="col-md-6">
                                <form
                                    class="ajax-store"
                                    method="post"
                                    action="{{ route('class_types.store') }}"
                                    data-reload="#manage-class-types-table"
                                >
                                    @csrf
                                    {{-- Name --}}
                                    <div class="form-group row">
                                        <label class="col-lg-6 col-form-label font-weight-semibold">
                                            Name <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-lg-6">
                                            <input
                                                name="name"
                                                value="{{ old('name') }}"
                                                required
                                                type="text"
                                                class="form-control"
                                                placeholder="Name of Class Type"
                                            />
                                        </div>
                                    </div>
                                    {{-- Code --}}
                                    <div class="form-group row">
                                        <label class="col-lg-6 col-form-label font-weight-semibold">
                                            Code <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-lg-6">
                                            <input
                                                name="code"
                                                value="{{ old('code') }}"
                                                required
                                                type="text"
                                                class="form-control"
                                                placeholder="Code of Class Type"
                                            />
                                        </div>
                                    </div>
                                    {{-- Minimum Subjects Required --}}
                                    <div class="alert alert-warning border-0">
                                        <span
                                            >The average, GPA and division are computed based on the
                                            <strong>mimimum subjects required value</strong>. Only the highest marks, up
                                            to the specified limit, are considered in the calculation. If a student has
                                            <em>fewer subjects than required</em>, their
                                            <strong>average and GPA will be set to 0</strong>, and they will be assigned
                                            the <strong>lowest division</strong>.</span>
                                        <br />
                                        <strong>Set the value to 0 to include all subjects in the calculation.</strong>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-6 col-form-label font-weight-semibold">
                                            Minimum Subjects Required <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-lg-6">
                                            <input
                                                type="number"
                                                name="minimum_subjects_required"
                                                min="0"
                                                value="{{ old('minimum_subjects_required') ?? 0 }}"
                                                required
                                                class="form-control"
                                            />
                                        </div>
                                    </div>

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

                {{-- Manage existing Class Types --}}
                <div class="tab-pane fade show active" id="manage-class-types">
                    <table class="datatable-button-html5-columns table" id="manage-class-types-table">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Minimum Subjects Required</th>
                                @if ($hasActions)
                                    <th class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($class_types as $ct)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $ct->name }}</td>
                                    <td>{{ $ct->code }}</td>
                                    <td>{{ $ct->minimum_subjects_required }}</td>
                                    @if ($hasActions)
                                        <td class="text-center">
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a class="material-symbols-rounded" href="#" data-toggle="dropdown"
                                                        >lists</a>
                                                    <div class="dropdown-menu dropdown-menu-left">
                                                        @if ($canEdit)
                                                            <a
                                                                href="{{ route('class_types.edit', $ct->id) }}"
                                                                class="dropdown-item"
                                                            >
                                                                <i class="material-symbols-rounded">edit</i> Edit
                                                            </a>
                                                        @endif
                                                        @if ($canDelete)
                                                            <a
                                                                id="{{ $ct->id }}"
                                                                onclick="confirmDelete(this.id)"
                                                                href="javascript:;"
                                                                class="dropdown-item text-danger"
                                                            >
                                                                <i class="material-symbols-rounded">delete</i> Delete
                                                            </a>
                                                            <form
                                                                method="post"
                                                                id="item-delete-{{ $ct->id }}"
                                                                action="{{ route('class_types.destroy', $ct->id) }}"
                                                                class="d-none"
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
            </div>
        </div>
    </div>

@endsection
