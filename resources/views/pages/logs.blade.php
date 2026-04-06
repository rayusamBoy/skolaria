@extends('layouts.master')

@section('page_title', 'Manage Logs')

@section('content')

{{-- Login Histories --}}
<div class="card card-collapsed">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Login Histories</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item dropdown">
                <a href="javascript:;" class="nav-link dropdown-toggle" data-toggle="dropdown">Select User Role</a>
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach ($user_roles as $ur)
                    <a href="#ut-{{ Qs::hash($ur->id) }}" class="dropdown-item" data-toggle="tab">{{ Str::plural($ur->name) }}</a>
                    @endforeach
                </div>
            </li>
        </ul>

        <div class="tab-content">
            @foreach ($user_roles as $ur)
            @php
            $role_scoped_users_ids = Usr::getUsersByRoleNames([$ur->name])->pluck('id')->toArray();
            @endphp
            <div class="tab-pane fade" id="ut-{{ Qs::hash($ur->id) }}">
                <span class="status-styled">{{ $ur->name }}</span>
                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Login Times</th>
                            <th>First Login</th>
                            <th>Last Login</th>
                            <th>Last Reset</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($login_histories->whereIn('user.id', $role_scoped_users_ids) as $lh)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><img class="rounded-circle" style="height:40px; width:40px;" src="{{ Usr::getTenancyAwarePhoto($lh->user->photo) }}" alt="photo"></td>
                            <td>{{ $lh->user->name }}</td>
                            <td>{{ $lh->login_times }}</td>
                            <td>{{ Qs::fullDateTimeFormat($lh->created_at) }}</td>
                            <td>{{ $lh->last_login ? Qs::fullDateTimeFormat($lh->last_login) : 'Never' }}</td>
                            <td>{{ $lh->last_reset ? Qs::fullDateTimeFormat($lh->last_reset) : 'Never' }}</td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="javascript:;" class="material-symbols-rounded" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            {{-- View Profile --}}
                                            @can($tenant_permissions['users']['show']['permission'])
                                            <a href="{{ route('users.show', Qs::hash($lh->user->id)) }}" class="dropdown-item"><i class="material-symbols-rounded">visibility</i> View Profile</a>
                                            @endcan
                                            {{-- Reset Login History --}}
                                            @can($tenant_permissions['logs']['reset_login_hist']['permission'])
                                            <a href="javascript:;" onclick="confirmReset(null, '{{ route('login_histories.reset', Qs::hash($lh->user->id)) }}')" class="dropdown-item">
                                                <i class="material-symbols-rounded">history</i> Reset Login History
                                            </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Activity Logs --}}
<div class="card card-collapsed w-fit wmin-100-pcnt">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Activity Logs</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <table class="table datatable-button-html5-columns">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Event</th>
                    <th>Subject Type</th>
                    <th>Causer Type</th>
                    <th>Causer</th>
                    <th>Properties</th>
                    <th>Created At</th>
                    @can($tenant_permissions['logs']['delete_activity']['permission'])
                    <th class="text-center">Action</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @foreach($activities->chunk(800) as $chunk)
                @foreach($chunk as $chk)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $chk->event }}</td>
                    @php $subject = explode("\\",$chk->subject_type); @endphp
                    <td>{{ end($subject) }}</td>
                    @php $causer = $chk->causer_type ? explode("\\",$chk->causer_type) : []; @endphp
                    <td>{{ $chk->causer_type ? end($causer) : 'N/A' }}</td>
                    <td>{{ $chk->causer?->name ?? 'N/A' }}</td>
                    <td>
                        @include('pages/modals/activity_log_properties')
                        <a href="javascript:;" data-toggle="modal" data-target="#activity-log-properties-{{ $chk->id }}">
                            show
                        </a>
                    </td>
                    <td>{{ Qs::fullDateTimeFormat($chk->created_at) }}</td>
                    @can($tenant_permissions['logs']['delete_activity']['permission'])
                    <td class="text-center">
                        {{-- Delete --}}
                        <a id="{{ $chk->id }}" onclick="confirmDelete(this.id)" href="javascript:;" class="btn btn-danger">
                            <i class="material-symbols-rounded">delete</i> Delete
                        </a>
                        <form method="post" id="item-delete-{{ $chk->id }}" action="{{ route('activity_log.delete',$chk->id) }}" class="d-none">@csrf</form>
                    </td>
                    @endcan
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Clean Activity Logs --}}
    @can($tenant_permissions['logs']['clean_activity_log']['permission'])
    <div class="m-2">
        <button class="btn btn-danger float-right" onclick="confirmOperation(this.id)" id="activity_log_clean" data-toggle="tooltip" title="Deletes all logs older than {{ config('activitylog.delete_records_older_than_days') }} days">
            Activity Log Clean
        </button>
        <form method="post" id="item-confirm-operation-activity_log_clean" action="{{ route('activity_log.clean') }}" class="hidden ajax-update page-block">@csrf</form>
    </div>
    @endcan
</div>

@endsection
