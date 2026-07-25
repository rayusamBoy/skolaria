@extends('layouts.master')

@section('page_title', 'Select School')

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Select School</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        @if($schools->isEmpty())
        <div class="alert alert-info text-center">
            No schools are currently linked to your account.
        </div>
        @else
        <table class="table datatable-button-html5-columns">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Name</th>
                    <th>Domain / Entry</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schools as $school)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $school->name }}</td>
                    <td>
                        @if($school->domain)
                        <a href="https://{{ $school->domain->domain }}" target="_blank">{{ $school->domain->domain }}</a>
                        @elseif($school->tenant_entry_domain)
                        <span class="text-muted">{{ $school->tenant_entry_domain }} / {{ $school->tenant_slug }}</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('tenant.switch', $school->id) }}" class="btn btn-primary btn-sm">
                            <i class="material-symbols-rounded mr-1">login</i> Enter
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

@endsection
