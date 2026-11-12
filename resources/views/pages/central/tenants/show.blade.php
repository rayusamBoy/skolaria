@extends('layouts.master')

@section('page_title', "Tenant Profile - {$tenant->name}")

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title">Tenant Information</h6>
                    {!! Qs::getPanelOptionsHtml() !!}
                </div>
                <div class="card-body">
                    <table class="table-bordered table">
                        <tbody>
                            <tr>
                                <td class="font-weight-bold">Name</td>
                                <td class="break-all">{{ $tenant->name }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Identification Mode</td>
                                <td>
                                    <span class="badge">{{ Str::headline($tenant->identification_mode) }}</span>
                                    @php
                                        $tenantDomain = $tenant->domain->domain ?? $tenant->domain;
                                    @endphp
                                    @if ($tenantDomain)
                                        <a
                                            href="{{ Qs::formatDomain($tenantDomain) }}"
                                            target="_blank"
                                        >{{ Qs::formatDomain($tenantDomain) }}</a>
                                    @elseif ($tenant->tenant_entry_domain)
                                        <span class="text-muted">{{ $tenant->tenant_entry_domain }} / {{ $tenant->tenant_slug }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Remarks</td>
                                <td>{{ $tenant->remarks ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Database Name</td>
                                <td>{{ $tenant->tenancy_db_name }}</td>
                            </tr>
                            <tr></tr>
                            <td class="font-weight-bold">Account Status</td>
                            <td>{{ ucfirst($tenant->account_status) }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Payment Status</td>
                                <td>{{ ucfirst($tenant->payment_status) }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Is Template</td>
                                <td>{{ $tenant->is_template == 1 ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Lock Master Data</td>
                                <td>{{ $tenant->lock_master_data == 1 ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Created At</td>
                                <td>{{ $tenant->created_at }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Updated At</td>
                                <td>{{ $tenant->updated_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{--Tenant Profile Ends--}}

@endsection
