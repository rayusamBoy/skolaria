@extends('layouts.master')

@section('page_title', 'Manage Tenants')

@section('content')
    @php
        $canEdit = auth()->user()->can($central_permissions['tenants']['edit']['permission']);
        $canShow = auth()->user()->can($central_permissions['tenants']['show']['permission']);
        $canDelete = auth()->user()->can($central_permissions['tenants']['destroy']['permission']);
        $canRunCommand = auth()->user()->can($central_permissions['tenants']['run_command']['permission']);
        $hasActions = $canEdit || $canShow || $canDelete || $canRunCommand;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Tenants</h6>
            <div class="header-elements pt-0">
                <div class="list-icons">
                    @if (auth()->user()->can($central_permissions['master_data_syncs']['sync']['permission']) && config('tenancy.active_identification_mode') === 'request_data')
                        <a
                            class="list-icons-item"
                            href="{{ route('master_data_sync.index') }}"
                            data-action="data-sync"
                        ></a>
                    @endcan
                    <a class="list-icons-item" data-action="collapse"></a>
                    <a class="list-icons-item" data-action="remove"></a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item">
                    <a href="#manage-tenants" class="nav-link active" data-toggle="tab">Manage Tenants</a>
                </li>
                @can($central_permissions['tenants']['store']['permission'])
                    <li class="nav-item"><a href="#new-tenant" class="nav-link" data-toggle="tab">Create Tenant</a></li>
                @endcan
            </ul>

            <div class="tab-content">
                @can($central_permissions['tenants']['store']['permission'])
                    <div class="tab-pane fade" id="new-tenant">
                        <form
                            class="ajax-store"
                            data-reload="#manage-tenants-table"
                            method="post"
                            action="{{ route('tenants.store') }}"
                        >
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="identification_mode">Identification Mode <span class="text-danger">*</span></label>
                                        <select
                                            required
                                            class="form-control select"
                                            name="identification_mode"
                                            id="identification_mode"
                                        >
                                            <option value="{{ $active_identification_mode }}" @selected(true)>
                                                {{ ucfirst(str_replace("_", " ", $active_identification_mode)) }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="name">Tenant Name: <span class="text-danger">*</span></label>
                                        <input
                                            id="name"
                                            required
                                            name="name"
                                            value="{{ old('name') }}"
                                            type="text"
                                            class="form-control"
                                            placeholder="tenant name"
                                        />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="account_status">Account Status: <span class="text-danger">*</span></label>
                                        <select
                                            required
                                            data-placeholder="Select Status"
                                            class="form-control select"
                                            name="account_status"
                                            id="account_status"
                                        >
                                            @foreach (Usr::getAccountStatuses() as $status)
                                                <option
                                                    @selected(old('account_status') == $status)
                                                    value="{{ $status }}"
                                                >
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="payment_status">Payment Status: <span class="text-danger">*</span></label>
                                        <select
                                            required
                                            data-placeholder="Select Status"
                                            class="form-control select"
                                            name="payment_status"
                                            id="payment_status"
                                        >
                                            @foreach (Pay::getPaymentStatusOptions() as $key => $value)
                                                <option @selected(old('payment_status') == $key) value="{{ $key }}">
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="remarks">Remarks</label>
                                        <textarea
                                            id="remarks"
                                            name="remarks"
                                            class="form-control"
                                        >{{ old('remarks') }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div @class($active_identification_mode_is_domain ? [] : ['d-none'])>
                                        <div class="form-group">
                                            <label for="domain">Domain <span class="text-danger">*</span></label>
                                            <div class="input-group text-right">
                                                <label class="input-group-text">https://</label>
                                                <input
                                                    id="domain"
                                                    @required($active_identification_mode_is_domain)
                                                    name="domain"
                                                    value="{{ old('domain') }}"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="Eg., foo.localhost"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div @class($active_identification_mode_is_domain ? ['d-none'] : [])>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tenant_slug">School Slug <span class="text-danger">*</span></label>
                                                    <input
                                                        id="tenant_slug"
                                                        @required(! $active_identification_mode_is_domain)
                                                        name="tenant_slug"
                                                        value="{{ old('tenant_slug') }}"
                                                        type="text"
                                                        class="form-control"
                                                        placeholder="Eg., campus-a"
                                                    />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="is_template" class="w-100"
                                                        >Template Tenant
                                                        <i
                                                            class="material-symbols-rounded text-info float-right"
                                                            data-toggle="tooltip"
                                                            title="Whether other schools will sync from this school"
                                                        >info</i>
                                                    </label>
                                                    <select
                                                        required
                                                        data-placeholder="Select Status"
                                                        class="form-control select"
                                                        name="is_template"
                                                        id="is_template"
                                                    >
                                                        @foreach ([1 => 'Yes', 0 => 'No'] as $key => $value)
                                                            <option
                                                                @selected(old('is_template') == $key ?? $key === 0)
                                                                value="{{ $key }}"
                                                            >
                                                                {{ $value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="is_template" class="w-100"
                                                        >Lock Master Data
                                                        <i
                                                            class="material-symbols-rounded text-info float-right"
                                                            data-toggle="tooltip"
                                                            title="Whether only template tenant can modify master data"
                                                        >info</i>
                                                    </label>
                                                    <select
                                                        required
                                                        data-placeholder="Select Status"
                                                        class="form-control select"
                                                        name="lock_master_data"
                                                        id="lock_master_data"
                                                    >
                                                        @foreach ([1 => 'Yes', 0 => 'No'] as $key => $value)
                                                            <option
                                                                @selected(old('lock_master_data') == $key ?? $key === 0)
                                                                value="{{ $key }}"
                                                            >
                                                                {{ $value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="alert alert-info">
                                            This school will be accessed via
                                            <strong>the domain {{ url('/') }}</strong> using a cookie/header. A domain
                                            record is not created for this tenant.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" data-text="Creating..." class="btn btn-primary">
                                    Submit form <i class="material-symbols-rounded ml-2">send</i>
                                </button>
                            </div>
                        </form>
                    </div>
                @endcan

                <div class="tab-pane fade show active" id="manage-tenants">
                    <table class="datatable-button-html5-columns table" id="manage-tenants-table">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Account Status</th>
                                <th>Payment Status</th>
                                <th>Identification</th>
                                <th>Domain/Entry Domain</th>
                                <th>Created</th>
                                <th>Updated</th>
                                @if ($hasActions)
                                    <th class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tenants as $tenant)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $tenant->name }}
                                        @if (isset($tenant->active) && $tenant->active)
                                            <span class="badge badge-success badge-default">Active</span>
                                        @else
                                            <span class="badge badge-secondary badge-default">New</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($tenant->account_status) }}</td>
                                    <td>{{ ucfirst($tenant->payment_status) }}</td>
                                    <td>
                                        @if ($tenant->identification_mode === 'request_data')
                                            <span class="badge">Request Data</span>
                                        @else
                                            <span class="badge">Domain</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a
                                            href="{{ Qs::formatDomain($tenant->domain->domain ?? $tenant->domain) ?? url('/') . "/slug/{$tenant->tenant_slug}" }}"
                                            target="_blank"
                                        >{{ Qs::formatDomain($tenant->domain->domain ?? $tenant->domain) ?? url('/') . "/slug/{$tenant->tenant_slug}" }}</a>
                                    </td>
                                    <td>{{ $tenant->created_at->diffForHumans() }}</td>
                                    <td>{{ $tenant->updated_at->diffForHumans() }}</td>

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
                                                        @if ($canShow)
                                                            <a
                                                                href="{{ route('tenants.show', $tenant->id) }}"
                                                                class="dropdown-item"
                                                                ><i class="material-symbols-rounded">visibility</i> View
                                                                Profile</a>
                                                        @endif

                                                        @if ($canEdit)
                                                            <a
                                                                href="{{ route('tenants.edit', $tenant->id) }}"
                                                                class="dropdown-item"
                                                            ><i class="material-symbols-rounded">edit</i> Edit</a>
                                                        @endif

                                                        @if ($canDelete)
                                                            <a
                                                                id="tenant-delete-{{ $tenant->id }}"
                                                                onclick="confirmPermanentDelete(this.id)"
                                                                href="javascript:;"
                                                                class="dropdown-item text-danger"
                                                                @if ($tenant->active) data-toggle="tooltip" title="This tenant is active. Please proceed with caution." @endif
                                                            >
                                                                <i class="material-symbols-rounded">delete</i> Delete
                                                            </a>
                                                            <form
                                                                method="post"
                                                                id="item-delete-tenant-delete-{{ $tenant->id }}"
                                                                action="{{ route('tenants.destroy', $tenant->id) }}"
                                                                class="page-block hidden"
                                                            >
                                                                @csrf
                                                                @method('delete')
                                                            </form>
                                                        @endif

                                                        @if ($canRunCommand)
                                                            {{-- Safe migration – always available --}}
                                                            <a
                                                                id="tenant-migrate-{{ $tenant->id }}"
                                                                onclick="confirmOperationTwice(this.id)"
                                                                href="javascript:;"
                                                                class="dropdown-item text-warning"
                                                                ><i class="material-symbols-rounded">swap_vert</i>
                                                                Migrate</a>
                                                            <form
                                                                method="post"
                                                                id="item-confirm-operation-tenant-migrate-{{ $tenant->id }}"
                                                                action="{{ route('tenants.run_command', [0, $tenant->id]) }}"
                                                                class="page-block hidden"
                                                            >
                                                                @csrf
                                                                @method('post')
                                                            </form>
                                                            {{-- Destructive commands – only when inactive --}}
                                                            @if (! $tenant->active)
                                                                {{-- Migrate Fresh --}}
                                                                <a
                                                                    id="tenant-migrate-fresh-{{ $tenant->id }}"
                                                                    onclick="confirmOperationTwice(this.id)"
                                                                    href="javascript:;"
                                                                    class="dropdown-item text-danger"
                                                                    ><i class="material-symbols-rounded">swap_horiz</i>
                                                                    Migrate Fresh</a>
                                                                <form
                                                                    method="post"
                                                                    id="item-confirm-operation-tenant-migrate-fresh-{{ $tenant->id }}"
                                                                    action="{{ route('tenants.run_command', [1, $tenant->id]) }}"
                                                                    class="page-block hidden"
                                                                >
                                                                    @csrf
                                                                    @method('post')
                                                                </form>
                                                                {{-- Seed --}}
                                                                <a
                                                                    id="tenant-seed-{{ $tenant->id }}"
                                                                    onclick="confirmOperationTwice(this.id)"
                                                                    href="javascript:;"
                                                                    class="dropdown-item text-danger"
                                                                    ><i class="material-symbols-rounded"
                                                                        >filter_vintage</i>
                                                                    Seed</a>
                                                                <form
                                                                    method="post"
                                                                    id="item-confirm-operation-tenant-seed-{{ $tenant->id }}"
                                                                    action="{{ route('tenants.run_command', [2, $tenant->id]) }}"
                                                                    class="page-block hidden"
                                                                >
                                                                    @csrf
                                                                    @method('post')
                                                                </form>
                                                            @endif
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

    {{-- Tenant List Ends --}}

@endsection
