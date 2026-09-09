@extends('layouts.master')

@section('page_title', 'Manage Classes')

@section('content')
    @php
        $canEdit = auth()->user()->can($tenant_permissions['my_classes']['edit']['permission']);
        $canDelete = auth()->user()->can($tenant_permissions['my_classes']['destroy']['permission']);
        $hasActions = $canEdit || $canDelete;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Classes</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item">
                    <a href="#all-classes" class="nav-link active" data-toggle="tab">Manage Classes</a>
                </li>

                @can($tenant_permissions['my_classes']['store']['permission'])
                    <li class="nav-item">
                        <a href="#new-class" class="nav-link" data-toggle="tab">Create New Class</a>
                    </li>
                @endcan
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="all-classes">
                    <table class="datatable-button-html5-columns table" id="manage-classes-table">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Class Type</th>
                                @if ($hasActions)
                                    <th class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($my_classes as $c)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $c->name }}</td>
                                    <td>{{ $c->class_type->name }}</td>
                                    @if ($hasActions)
                                        <td class="text-center">
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a
                                                        class="material-symbols-rounded"
                                                        href="javascript:;"
                                                        data-toggle="dropdown"
                                                    >lists</a>
                                                    <div class="dropdown-menu dropdown-menu-left">
                                                        @if ($canEdit)
                                                            <a
                                                                href="{{ route('classes.edit', $c->id) }}"
                                                                class="dropdown-item"
                                                            >
                                                                <i class="material-symbols-rounded">edit</i> Edit
                                                            </a>
                                                        @endif
                                                        @if ($canDelete)
                                                            <a
                                                                id="class-delete-{{ $c->id }}"
                                                                onclick="confirmDelete(this.id)"
                                                                href="javascript:;"
                                                                class="dropdown-item text-danger"
                                                            >
                                                                <i class="material-symbols-rounded">delete</i> Delete
                                                            </a>
                                                            <form
                                                                method="post"
                                                                id="item-delete-class-delete-{{ $c->id }}"
                                                                action="{{ route('classes.destroy', $c->id) }}"
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

                @can($tenant_permissions['my_classes']['store']['permission'])
                    <div class="tab-pane fade" id="new-class">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info alert-dismissible border-0">
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                    <span>
                                        When a class is created, a Section will be automatically created for the class.
                                        You can edit it or add more sections at
                                        <a href="{{ route('sections.index') }}">Manage Sections</a>.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <form
                                    class="ajax-store"
                                    method="post"
                                    action="{{ route('classes.store') }}"
                                    data-reload="#manage-classes-table"
                                >
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
                                                placeholder="Name of Class"
                                            />
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="class_type_id" class="col-lg-3 col-form-label font-weight-semibold"
                                            >Class Type</label>
                                        <div class="col-lg-9">
                                            <select
                                                required
                                                data-placeholder="Select Class Type"
                                                class="form-control select"
                                                name="class_type_id"
                                                id="class_type_id"
                                            >
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

                                    <div class="text-right">
                                        <button id="ajax-btn" type="submit" class="btn btn-primary">
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

    {{-- Class List Ends --}}

@endsection
