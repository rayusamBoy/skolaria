@extends('layouts.master')

@section('page_title', 'My Notifications')

@section('content')

@php
$user = auth()->user();
$canDelete = $user->can($tenant_permissions['notifications']['destroy']['permission']);
$canMarkAllRead = $user->can($tenant_permissions['notifications']['mark_all_read']['permission']);
$canMarkAsRead = $user->can($tenant_permissions['notifications']['mark_as_read']['permission']);
$hasActions = $canMarkAsRead || $canDelete;
@endphp

<div class="card card-collapsed">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Preferences</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning border-0 alert-dismissible has-do-not-show-again-button" id="notifications-info">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <span>
                    Enable push notifications by clicking "Request Permission" button or manually. Critical notifications are enabled by default for security and performance. Adjust other notifications below.
                </span>
            </div>
        </div>
    </div>

    @php
    $isGlobalEmailChannelOn = $settings->firstWhere('key',"enable_email_notification")->value == 1 ? true : false;
    $isGlobalPushChannelOn = $settings->firstWhere('key',"enable_push_notification")->value == 1 ? true : false;
    $isGlobalSmsChannelOn = $settings->firstWhere('key',"enable_sms_notification")->value == 1 ? true : false;
    $isGlobalNotificationOn = $isGlobalEmailChannelOn && $isGlobalPushChannelOn && $isGlobalSmsChannelOn;
    @endphp

    <div class="card-body">
        @can($tenant_permissions['notifications']['update_notification_preferences']['permission'])
        <form method="post" action="{{ route('notifications.update.notification_preferences') }}">
            @csrf @method('put')
            <div class="form-group row">
                <div class="col-lg-3">
                    <label for="email_channel" class="col-form-label font-weight-semibold">Email notification</label>
                    <div class="text-center">
                        <select class="form-control select" @disabled(!$isGlobalEmailChannelOn) name="on_email_channel" id="email_channel">
                            <option @selected($email_channel_on==true) value="true">On</option>
                            <option @selected($email_channel_on==false) value="false">Off</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3">
                    <label for="push_channel" class="col-form-label font-weight-semibold">Push notification</label>
                    <div class="text-center">
                        <select class="form-control select" @disabled(!$isGlobalPushChannelOn) name="on_push_channel" id="push_channel">
                            <option @selected($push_channel_on==true) value="true">On</option>
                            <option @selected($push_channel_on==false) value="false">Off</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3">
                    <label for="sms_channel" class="col-form-label font-weight-semibold">SMS notification</label>
                    <div class="text-center">
                        <select class="form-control select" @disabled(!$isGlobalSmsChannelOn) name="on_sms_channel" id="sms_channel">
                            <option @selected($sms_channel_on==true) value="true">On</option>
                            <option @selected($sms_channel_on==false) value="false">Off</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3">
                    <label for="status" class="col-form-label font-weight-semibold">Allow Notifications</label>
                    <div>
                        <select class="form-control select" @disabled(!$isGlobalNotificationOn) name="status" id="status">
                            <option @selected($notification_preferences==true) value="true">Yes</option>
                            <option @selected($notification_preferences==false) value="false">No</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="offset-lg-9 col-lg-3">
                    <button type="submit" @if(!$isGlobalNotificationOn) disabled data-toggle="tooltip" data-placement="top" title="You can't change this setting. Please contact your administrator." @endif class="btn btn-sm btn-danger float-right">
                        Submit form <span class="material-symbols-rounded ml-2 pb-2px">send</span>
                    </button>
                </div>
            </div>
        </form>
        @endcan

        {{-- Cloud messaging / Web push --}}
        <div class="row mt-2" id="notifications-row">
            <div class="col-12">
                <div id="token-div" class="display-none">
                    <div class="alert alert-info border-0"><span id="token-info"></span></div>
                    <button id="delete-token-button" class="btn btn-warning btn-sm float-right ml-true d-none">
                        Delete Token
                    </button>
                </div>
                <div id="permission-div">
                    <button id="request-permission-button" class="btn btn-primary btn-sm float-right">
                        Request Permission
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Notifications</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <table class="table datatable-button-html5-tab">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Subject</th>
                    <th>Created At</th>
                    <th>Read At</th>
                    @if($hasActions)
                    <th class="text-center">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($user->notifications as $notification)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        @php $data = $notification->data; @endphp
                        <a target="_blank" @if(isset($data['receipt'])) href="{{ route('payments.receipts', [Qs::hash($data['receipt']['payment_record_id']), $notification->id]) }}" @elseif(!empty($data['url'])) href="{{ $data['url'] }}" @endif>
                            <strong>{{ $data['subject'] }}</strong>
                        </a>
                    </td>
                    <td>{{ $notification->created_at }}</td>
                    <td>{{ $notification->read_at ?? 'Never' }}</td>
                    @if($hasActions)
                    <td class="text-center">
                        <div class="list-icons">
                            <div class="dropdown">
                                <a class="material-symbols-rounded" href="javascript:;" data-toggle="dropdown">lists</a>
                                <div class="dropdown-menu dropdown-menu-left">
                                    @if($notification->read_at === null && $canMarkAsRead)
                                    <a href="{{ route('notifications.mark_as_read', $notification->id) }}" class="dropdown-item">
                                        <i class="material-symbols-rounded">done</i> Mark as read
                                    </a>
                                    @endif

                                    @if($canDelete)
                                    <a id="{{ $notification->id }}" onclick="confirmDelete(this.id)" href="javascript:;" class="dropdown-item text-danger">
                                        <i class="material-symbols-rounded">delete</i> Delete
                                    </a>
                                    <form method="post" id="item-delete-{{ $notification->id }}" action="{{ route('notifications.destroy', $notification->id) }}" class="hidden">
                                        @csrf @method('delete')
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

        @if($canMarkAllRead)
        @if($user->unreadNotifications->isNotEmpty())
        <a href="javascript:;" id="mark_all_read" class="btn btn-warning float-right mt-3" onclick="confirmOperation(this.id)">
            Mark all Read<i class="material-symbols-rounded ml-1 pb-2px">done_all</i>
        </a>
        <form id="item-confirm-operation-mark_all_read" method="POST" action="{{ route('notifications.mark_all_read') }}">
            @csrf @method('PUT')
        </form>
        @endif
        @endif

    </div>
</div>

@endsection
