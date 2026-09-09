@extends('layouts.master')

@section('page_title', "Edit Role - $role->name")

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Edit Role</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('roles.update', $role->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold" for="name"
                        >Role Name <span class="text-danger">*</span></label>
                    <div class="col-lg-5">
                        <input
                            @readonly(\in_array($role->name, Usr::getTenancyAwareDefaultRoles(), true))
                            type="text"
                            name="name"
                            class="form-control text-capitalize"
                            id="name"
                            value="{{ old('name', $role->name) }}"
                            required
                        />
                    </div>
                    <div class="col-lg-4 m-auto">
                        <span class="font-weight-bold font-italic text-info-800">capitalize words separated by single spaces</span>
                    </div>
                </div>

                {{-- Grouped permissions --}}
                @php
                    $isRootRole = in_array($role->name, Usr::getTenancyAwareRootRoles(), true);
                @endphp
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">Permissions</label>
                    <div class="col-lg-9">
                        <div class="accordion" id="permissionsAccordion">
                            @foreach ($grouped_permissions as $group => $perms)
                                <div class="card mb-2">
                                    <div class="card-header p-0" id="heading-{{ Str::slug($group) }}">
                                        <h2 class="mb-0">
                                            <button
                                                class="btn btn-link btn-block d-flex justify-content-between align-items-center text-left"
                                                type="button"
                                                data-toggle="collapse"
                                                data-target="#collapse-{{ Str::slug($group) }}"
                                                aria-expanded="true"
                                                aria-controls="collapse-{{ Str::slug($group) }}"
                                            >
                                                <span class="font-weight-bold">{{ ucfirst(str_replace('_', ' ', $group)) }}</span>
                                                <span class="badge badge-primary badge-pill badge-default">{{ count($perms) }}</span>
                                            </button>
                                        </h2>
                                    </div>
                                    <div
                                        id="collapse-{{ Str::slug($group) }}"
                                        class="show collapse"
                                        aria-labelledby="heading-{{ Str::slug($group) }}"
                                        data-parent="#permissionsAccordion"
                                    >
                                        <div class="card-body">
                                            @if (! $isRootRole)
                                                <div class="mb-2">
                                                    <a
                                                        href="javascript:void(0)"
                                                        class="btn btn-sm btn-outline-secondary select-all-group"
                                                        data-group="{{ Str::slug($group) }}"
                                                    >Select All</a>
                                                    <a
                                                        href="javascript:void(0)"
                                                        class="btn btn-sm btn-outline-secondary deselect-all-group"
                                                        data-group="{{ Str::slug($group) }}"
                                                    >Deselect All</a>
                                                </div>
                                            @endif
                                            <div class="row">
                                                @foreach ($perms as $perm)
                                                    <div class="col-md-6 col-lg-4 mb-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input
                                                                type="checkbox"
                                                                name="permissions[]"
                                                                value="{{ $perm->name }}"
                                                                class="custom-control-input"
                                                                id="perm-{{ $perm->id }}"
                                                                data-group="{{ Str::slug($group) }}"
                                                                @disabled($isRootRole)
                                                                @checked($role->permissions->contains('name', $perm->name))
                                                            />
                                                            <label
                                                                class="custom-control-label"
                                                                for="perm-{{ $perm->id }}"
                                                            >
                                                                {{ $perm->label ?? $perm->name }}
                                                                @if ($perm->description)
                                                                    <i
                                                                        class="material-symbols-rounded text-muted cursor-help"
                                                                        data-toggle="tooltip"
                                                                        title="{{ $perm->description }}"
                                                                    >help</i>
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
                        @if ($isRootRole)
                            <small class="form-text text-muted">Permissions cannot be modified for root roles.</small>
                        @endif
                    </div>
                </div>

                <div class="text-right">
                    <button @disabled($isRootRole) type="submit" class="btn btn-primary">
                        Update <i class="material-symbols-rounded ml-2">save</i>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
