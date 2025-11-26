@extends('layouts.master')

@section('page_title', 'Manage Roles')

@section('content')
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Roles</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item"><a href="#create-role" class="nav-link active" data-toggle="tab">Create Role</a></li>
            <li class="nav-item"><a href="#manage-roles" class="nav-link" data-toggle="tab">Manage Roles</a></li>
        </ul>

        <div class="tab-content">
            {{-- Create Role Tab --}}
            <div class="tab-pane fade show active" id="create-role">
                <form method="POST" action="{{ route('roles.store') }}" class="ajax-store">
                    @csrf
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label font-weight-semibold" for="name">Role Name <span class="text-danger">*</span></label>
                        <div class="col-lg-5">
                            <input type="text" name="name" class="form-control text-capitalize" id="name" placeholder="e.g., Teacher, Admin" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-lg-4 m-auto">
                            <span class="font-weight-bold font-italic text-info-800">capitalize words separated by single spaces</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label font-weight-semibold" for="permissions">Assign Permissions <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <select name="permissions[]" id="permissions" class="form-control select" multiple data-placeholder="Select permissions">
                                @foreach ($permissions as $permission)
                                <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Submit <i class="material-symbols-rounded ml-2">send</i></button>
                    </div>
                </form>
            </div>

            {{-- Manage Roles Tab --}}
            <div class="tab-pane fade" id="manage-roles">
                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Permissions</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                        <tr>
                            @php
                            $permissions = $role->permissions->pluck('name');
                            @endphp
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $role->name }}</td>
                            <td>{{ $permissions->join(', ') }}</td>
                            <td>{{ $role->created_at->diffForHumans() }}</td>
                            <td>{{ $role->updated_at->diffForHumans() }}</td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="material-symbols-rounded" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            <a href="{{ route('roles.edit', $role->id) }}" class="dropdown-item"><i class="material-symbols-rounded">edit</i> Edit</a>

                                            <a href="javascript:;" id="{{ $role->id }}" class="dropdown-item text-danger" onclick="confirmDelete(this.id)">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form method="POST" action="{{ route('roles.destroy', $role->id) }}" id="item-delete-{{ $role->id }}" class="d-none">
                                                @csrf @method('DELETE')
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
