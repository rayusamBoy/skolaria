@extends('layouts.master')

@section('page_title', 'Impersonate Tenant User')

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Select Tenant</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            @if ($tenants->isEmpty())
                <div class="alert alert-info text-center">No tenants are currently available.</div>
            @else
                <table class="datatable-button-html5-columns table">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Name</th>
                            <th>Domain / Entry</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tenants as $tenant)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $tenant->name }}</td>
                                <td>
                                    @if ($tenant->domain)
                                        <a
                                            href="{{ Qs::formatDomain($tenant->domain->domain ?? $tenant->domain) }}"
                                            target="_blank"
                                        >{{ Qs::formatDomain($tenant->domain->domain ?? $tenant->domain) }}</a>
                                    @elseif ($tenant->tenant_entry_domain)
                                        <span class="text-muted">{{ $tenant->tenant_entry_domain }} / {{ $tenant->tenant_slug }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($current_tenant_id == $tenant->id)
                                        <span class="btn btn-secondary disabled btn-sm">
                                            <i class="material-symbols-rounded mr-1">blur_on</i> Current
                                        </span>
                                    @else
                                        <a
                                            href="{{ route('tenant.impersonation.redirect', ['tenant_id' => $tenant->id, 'user_id' => $tenant_super_admin_user_id]) }}"
                                            class="btn btn-primary btn-sm"
                                        >
                                            <i class="material-symbols-rounded mr-1">login</i> Login As SuperAdmin
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

@endsection
