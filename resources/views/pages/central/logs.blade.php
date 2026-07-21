@extends('layouts.master')

@section('page_title', 'Manage Activity Log')

@section('content')

@php
// Pre‑compute permissions for Login Histories
$canViewProfile = auth()->user()->can($central_permissions['central_users']['show']['permission']);
$hasLoginActions = $canViewProfile;

// Pre‑compute permissions for Activity Logs
$canDeleteActivity = auth()->user()->can($central_permissions['central_logs']['delete_activity']['permission']);
@endphp

{{-- Login Histories --}}
<div class="card card-collapsed">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Login Histories</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item dropdown">
                <a href="javascript:;" class="nav-link active dropdown-toggle" data-toggle="dropdown">Select Users Role</a>
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach ($user_roles as $ur)
                    <a href="#ur-{{ Qs::hash($ur->id) }}" class="dropdown-item" data-toggle="tab">{{ $ur->name }}</a>
                    @endforeach
                </div>
            </li>
        </ul>

        <div class="tab-content">
            @foreach ($user_roles as $ur)
            @php
            $role_scoped_users_ids = Usr::getUsersByRoleNames([$ur->name])->pluck('id')->toArray();
            $roles_scoped_login_histories = $login_histories->whereIn('user.id', $role_scoped_users_ids)->groupBy('user.id');
            @endphp
            <div class="tab-pane fade" id="ur-{{ Qs::hash($ur->id) }}">
                <span class="status-styled">{{ $ur->name }}</span>

                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Login Count</th>
                            <th>First Login</th>
                            <th>Last Login</th>
                            @if($hasLoginActions)
                            <th class="text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles_scoped_login_histories as $lh)
                        @php
                        $user = $lh->first()->user;
                        $firstLogin = $lh->first();
                        $lastLogin = $lh->last();
                        $previousLogin = $lh->count() > 1
                        ? $lh->slice(-2, 1)->first()
                        : null;
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <img class="rounded-circle" style="height:40px; width:40px;" src="{{ Usr::getTenancyAwarePhotoUrl($user->photo_path) }}" alt="photo">
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $lh->count() }}</td>
                            <td>{{ $firstLogin ? Qs::formatDateTime($firstLogin->logged_in_at) : 'N/A' }}</td>
                            <td>{{ $previousLogin ? Qs::formatDateTime($previousLogin->logged_in_at) : 'N/A' }}</td>
                            @if($hasLoginActions)
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="javascript:;" class="material-symbols-rounded" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            @if($canViewProfile)
                                            <a href="{{ route('central_users.show', Qs::hash($user->id)) }}" class="dropdown-item"><i class="material-symbols-rounded">visibility</i>View Profile</a>
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
            @endforeach
        </div>
    </div>
</div>

{{-- Activity Logs --}}
<div class="card card-collapsed w-fit wmin-100-pcnt">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Activity Logs</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="all-payments">
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
                            @if($canDeleteActivity)
                            <th class="text-center">Action</th>
                            @endif
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
                                @include('pages.modals.activity_log_properties')
                                <a href="javascript:;" data-toggle="modal" data-target="#activity-log-properties-{{ $chk->id }}">show</a>
                            </td>
                            <td>{{ Qs::formatDate($chk->created_at) }}</td>
                            @if($canDeleteActivity)
                            <td class="text-center">
                                <a id="{{ $chk->id }}" onclick="confirmDelete(this.id)" href="javascript:;" class="btn btn-danger"><i class="material-symbols-rounded">delete</i> Delete</a>
                                <form class="d-none" method="post" id="item-delete-{{ $chk->id }}" action="{{ route('logs.activity_log_delete', $chk->id) }}">@csrf</form>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
