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
            <li class="nav-item"><a href="#manage-roles" class="nav-link active" data-toggle="tab">Manage Roles</a></li>
            @can($tenant_permissions['roles']['store']['permission'])
            <li class="nav-item"><a href="#create-role" class="nav-link" data-toggle="tab">Create Role</a></li>
            @endcan
        </ul>

        <div class="tab-content">
            {{-- Create Role Tab --}}
            @can($tenant_permissions['roles']['store']['permission'])
            <div class="tab-pane fade" id="create-role">
                <form method="POST" action="{{ route('roles.store') }}" class="ajax-store">
                    @csrf
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label font-weight-semibold" for="name">Role Name <span class="text-danger">*</span></label>
                        <div class="col-lg-5">
                            <input type="text" name="name" class="form-control text-capitalize" id="name" placeholder="e.g., Teacher, Super Admin" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-lg-4 m-auto">
                            <span class="font-weight-bold font-italic text-info-800">capitalize words separated by single spaces</span>
                        </div>
                    </div>

                    {{-- Grouped permissions --}}
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label font-weight-semibold">Assign Permissions <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <div class="accordion" id="permissionsAccordion">
                                @foreach($groupedPermissions as $group => $perms)
                                <div class="card mb-2">
                                    <div class="card-header p-0" id="heading-{{ Str::slug($group) }}">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-left d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapse-{{ Str::slug($group) }}" aria-expanded="true" aria-controls="collapse-{{ Str::slug($group) }}">
                                                <span class="font-weight-bold">{{ ucfirst(str_replace('_', ' ', $group)) }}</span>
                                                <span class="badge badge-primary badge-pill">{{ count($perms) }}</span>
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapse-{{ Str::slug($group) }}" class="collapse show" aria-labelledby="heading-{{ Str::slug($group) }}" data-parent="#permissionsAccordion">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary select-all-group" data-group="{{ Str::slug($group) }}">Select All</a>
                                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary deselect-all-group" data-group="{{ Str::slug($group) }}">Deselect All</a>
                                            </div>
                                            <div class="row">
                                                @foreach($perms as $perm)
                                                <div class="col-md-6 col-lg-4 mb-2">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="custom-control-input" id="perm-{{ $perm->id }}" data-group="{{ Str::slug($group) }}">
                                                        <label class="custom-control-label" for="perm-{{ $perm->id }}">
                                                            {{ $perm->label ?? $perm->name }}
                                                            @if($perm->description)
                                                            <i class="material-symbols-rounded text-muted cursor-help" data-toggle="tooltip" title="{{ $perm->description }}">help</i>
                                                            @endif
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Submit <i class="material-symbols-rounded ml-2">send</i></button>
                    </div>
                </form>
            </div>
            @endcan

            {{-- Manage Roles Tab --}}
            <div class="tab-pane fade show active" id="manage-roles">
                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th># Permissions</th>
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
                            <td>{{ in_array($role->name, Usr::getTenancyAwareRootRoles(), true) ? $permissions->count() . " (All)" : $permissions->count() }}</td>
                            <td>{{ $role->created_at->diffForHumans() }}</td>
                            <td>{{ $role->updated_at->diffForHumans() }}</td>
                            @if(!\in_array($role->name, Usr::getTenancyAwareRootRoles(), true))
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="material-symbols-rounded" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            {{-- Edit --}}
                                            @can($tenant_permissions['roles']['edit']['permission'])
                                            <a href="{{ route('roles.edit', $role->id) }}" class="dropdown-item"><i class="material-symbols-rounded">edit</i> Edit</a>
                                            @endcan
                                            {{-- Delete --}}
                                            @if(!\in_array($role->name, Usr::getTenancyAwareDefaultRoles(), true) && auth()->user()->can($tenant_permissions['roles']['delete']['permission']))
                                            <a href="javascript:;" id="{{ $role->id }}" class="dropdown-item text-danger" onclick="confirmDelete(this.id)">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form method="POST" action="{{ route('roles.destroy', $role->id) }}" id="item-delete-{{ $role->id }}" class="d-none">
                                                @csrf @method('DELETE')
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
