@extends('layouts.master')

@section('page_title', 'Central Recycle Bin')

@push('css')
    <style>
        div img {
            max-width: 7em;
        }

        .card:not(:first-child) .card-body {
            overflow-x: auto;
        }
    </style>
@endpush

@section('content')
    @php
        // Central permissions
        $canRestoreUser = auth()->user()->can($central_permissions['central_soft_deletes']['user_restore']['permission']);
        $canForceDelUser = auth()->user()->can($central_permissions['central_soft_deletes']['user_force_delete']['permission']);
        $hasUserActions = $canRestoreUser || $canForceDelUser;

        $canRestoreTenant = auth()->user()->can($central_permissions['central_soft_deletes']['tenant_restore']['permission']);
        $canForceDelTenant = auth()->user()->can($central_permissions['central_soft_deletes']['tenant_force_delete']['permission']);
        $hasTenantActions = $canRestoreTenant || $canForceDelTenant;

        $isSuperAdmin = auth()->user()->hasRole(config('roles.root_roles.central.root'));
    @endphp

    @if ($users->isEmpty() && $tenants->isEmpty())
        <div class="text-center">
            <img src="{{ asset('global_assets/icons/bin-empty-2.gif') }}" alt="Bin empty" />
            <h6 class="text-muted mt-2">The bin is empty.</h6>
        </div>
    @else
        <div class="alert alert-warning alert-dismissible has-do-not-show-again-button border-0" id="bin-info">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <span
                >Deleting a record will permanently remove it along with all its associated data. This action cannot be
                undone. However, you can choose to restore any item if needed. Please proceed with caution.</span>
        </div>

        {{-- Users --}}
        @if ($users->isNotEmpty())
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title">Users</h6>
                    {!! Qs::getPanelOptionsHtml() !!}
                </div>
                <div class="card-body">
                    <table class="datatable-button-html5-image table">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Contact</th>
                                <th>Deleted At</th>
                                @if ($hasUserActions)
                                    <th class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <img
                                            class="rounded-circle"
                                            style="height: 40px; width: 40px"
                                            src="{{ Usr::getTenancyAwarePhotoUrl($user->photo_path) }}"
                                        />
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                                    <td>{{ $user->email ?? $user->primary_mobile ?? '—' }}</td>
                                    <td>{{ Qs::formatDate($user->deleted_at) }}</td>
                                    @if ($hasUserActions)
                                        <td class="text-center">
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a
                                                        class="material-symbols-rounded"
                                                        href="javascript:;"
                                                        data-toggle="dropdown"
                                                    >lists</a>
                                                    <div class="dropdown-menu dropdown-menu-left">
                                                        @if ($canRestoreUser)
                                                            <a
                                                                id="{{ $user->id }}"
                                                                onclick="$('form#user-restore-' + this.id).submit()"
                                                                href="javascript:;"
                                                                class="dropdown-item"
                                                            >
                                                                <i class="material-symbols-rounded">undo</i> Restore
                                                            </a>
                                                            <form
                                                                class="d-none"
                                                                method="post"
                                                                id="user-restore-{{ $user->id }}"
                                                                action="{{ route('central.user.restore', $user->id) }}"
                                                            >
                                                                @csrf
                                                            </form>
                                                        @endif
                                                        @if ($canForceDelUser)
                                                            <a
                                                                id="{{ $user->id }}"
                                                                onclick="confirmForceDelete(this.id, 'user')"
                                                                href="javascript:;"
                                                                class="dropdown-item text-danger"
                                                            >
                                                                <i class="material-symbols-rounded">delete</i> Delete
                                                            </a>
                                                            <form
                                                                class="d-none"
                                                                method="post"
                                                                id="user-force-delete-{{ $user->id }}"
                                                                action="{{ route('central.user.force_delete', $user->id) }}"
                                                            >
                                                                @csrf
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
        @endif

        {{-- Tenants --}}
        @if ($tenants->isNotEmpty())
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title">Tenants</h6>
                    {!! Qs::getPanelOptionsHtml() !!}
                </div>
                <div class="card-body">
                    <table class="datatable-button-html5-image table">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Identification</th>
                                <th>Domain / Entry</th>
                                <th>Deleted At</th>
                                @if ($hasTenantActions)
                                    <th class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tenants as $tenant)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $tenant->name }}</td>
                                    <td>
                                        {{ $tenant->identification_mode === 'request_data' ? 'Request Data' : 'Domain' }}
                                    </td>
                                    <td>
                                        @if ($tenant->identification_mode === 'domain')
                                            {{ Qs::formatDomain($tenant->domain->domain) ?? '—' }}
                                        @else
                                            {{ $tenant->tenant_slug ?? '—' }}
                                        @endif
                                    </td>
                                    <td>{{ Qs::formatDate($tenant->deleted_at) }}</td>
                                    @if ($hasTenantActions)
                                        <td class="text-center">
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a
                                                        class="material-symbols-rounded"
                                                        href="javascript:;"
                                                        data-toggle="dropdown"
                                                    >lists</a>
                                                    <div class="dropdown-menu dropdown-menu-left">
                                                        @if ($canRestoreTenant)
                                                            <a
                                                                id="{{ $tenant->id }}"
                                                                onclick="$('form#tenant-restore-' + this.id).submit()"
                                                                href="javascript:;"
                                                                class="dropdown-item"
                                                            >
                                                                <i class="material-symbols-rounded">undo</i> Restore
                                                            </a>
                                                            <form
                                                                class="d-none"
                                                                method="post"
                                                                id="tenant-restore-{{ $tenant->id }}"
                                                                action="{{ route('central.tenant.restore', $tenant->id) }}"
                                                            >
                                                                @csrf
                                                            </form>
                                                        @endif
                                                        @if ($canForceDelTenant)
                                                            <a
                                                                id="{{ $tenant->id }}"
                                                                onclick="confirmForceDelete(this.id, 'tenant')"
                                                                href="javascript:;"
                                                                class="dropdown-item text-danger"
                                                            >
                                                                <i class="material-symbols-rounded">delete</i> Delete
                                                            </a>
                                                            <form
                                                                class="d-none"
                                                                method="post"
                                                                id="tenant-force-delete-{{ $tenant->id }}"
                                                                action="{{ route('central.tenant.force_delete', $tenant->id) }}"
                                                            >
                                                                @csrf
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
        @endif

        {{-- Empty Bin --}}
        @if ($isSuperAdmin)
            <div class="card">
                <div class="card-body d-flex flex-wrap">
                    <label class="font-weight-bold w-100">Empty Bin</label>
                    @foreach (Usr::getCanSoftDeleteModelNames($model_names_to_remove, false) as $model_name)
                        <div class="pr-2">
                            <button
                                id="{{ $model_name }}"
                                type="button"
                                onclick="confirmPermanentDeleteTwice(this.id)"
                                class="btn btn-danger btn-sm"
                            >
                                <i class="material-symbols-rounded">delete</i> Empty {{ ucfirst($model_name) }}
                            </button>
                            <form
                                method="post"
                                id="item-delete-{{ $model_name }}"
                                action="{{ route('model.empty_soft_deleted', $model_name) }}"
                                class="hidden"
                            >
                                @csrf
                                @method('delete')
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @endif
@endsection
