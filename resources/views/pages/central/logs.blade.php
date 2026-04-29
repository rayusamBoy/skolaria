@extends('layouts.master')

@section('page_title', 'Manage Activity Log')

@section('content')

<div class="card card-collapsed">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Login Histories</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            {{--Login Histories--}}
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
            @endphp
            <div class="tab-pane fade" id="ur-{{ Qs::hash($ur->id) }}">
                <span class="status-styled">{{ $ur->name }}</span>

                <table class="table datatable-button-html5-columns ">
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
                            <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ Usr::getTenancyAwarePhoto($lh->user->photo) }}" alt="photo"></td>
                            <td>{{ $lh->user->name }}</td>
                            <td>{{ $lh->login_times }}</td>
                            <td>{{ Qs::fullDateTimeFormat($lh->created_at) }}</td>
                            <td>{{ ($lh->last_login == NULL) ? 'Never' : Qs::fullDateTimeFormat($lh->last_login) }}</td>
                            <td>{{ ($lh->last_reset == NULL) ? 'Never' : Qs::fullDateTimeFormat($lh->last_reset) }}</td>

                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="javascript:;" class="material-symbols-rounded" data-toggle="dropdown">lists</a>

                                        <div class="dropdown-menu dropdown-menu-left">
                                            {{-- View Profile --}}
                                            @can($central_permissions['central_users']['show']['permission'])
                                            <a href="{{ route('central_users.show', Qs::hash($lh->user->id)) }}" class="dropdown-item"><i class="material-symbols-rounded">visibility</i> View Profile</a>
                                            @endcan
                                            {{--Reset Login history--}}
                                            @can($central_permissions['central_logs']['reset_login_hist']['permission'])
                                            <a href="javascript:;" onclick="confirmReset(null, '{{ route('logs.login_history_reset', Qs::hash($lh->user->id)) }}')" class="dropdown-item"><i class="material-symbols-rounded">history</i> Reset Login History</a>
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

<div class="card card-collapsed w-fit wmin-100-pcnt">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Activity Logs</h6>
        {!! Qs::getPanelOptions() !!}
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
                            @can($central_permissions['central_logs']['delete_activity']['permission'])
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
                                <a href="javascript:;" data-toggle="modal" data-target="#activity-log-properties-{{ $chk->id }}">show</a>
                            </td>
                            <td>{{ Qs::onlyDateFormat($chk->created_at) }}</td>
                            @can($central_permissions['central_logs']['delete_activity']['permission'])
                            <td class="text-center">
                                {{--Delete--}}
                                <a id="{{ $chk->id }}" onclick="confirmDelete(this.id)" href="javascript:;" class="btn btn-danger"><i class="material-symbols-rounded">delete</i> Delete</a>
                                <form class="d-none" method="post" id="item-delete-{{ $chk->id }}" action="{{ route('logs.activity_log_delete', $chk->id) }}" class="hidden">@csrf</form>
                            </td>
                            @endcan
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
