@extends('layouts.master')

@section('page_title', 'Recycle Bin')

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

@if($payments->isEmpty() && $users->isEmpty() && $my_classes->isEmpty() && $exams->isEmpty() && $threads->isEmpty())
<div class="text-center">
    <img src="{{ asset('global_assets/icons/bin-empty-2.gif') }}" alt="Bin empty gif"></img>
    <h6 class="text-muted mt-2">The bin is empty.</h6>
</div>

@else

<div class="alert alert-warning border-0 alert-dismissible has-do-not-show-again-button" id="bin-info">
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    <span>
        Deleting a record will permanently remove it along with all its associated data. This action cannot be undone. However, you can choose to restore any item if needed. Please proceed with caution.
    </span>
</div>

{{-- Payments --}}
@if($payments->isNotEmpty())
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Payments</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="all-payments">
                <table class="table datatable-button-html5-image">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Amount</th>
                            <th>Ref No</th>
                            <th>Class</th>
                            <th>Method</th>
                            <th>Info</th>
                            <th>Deleted At</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->title }}</td>
                            <td>{{ $p->amount }}</td>
                            <td>{{ $p->ref_no }}</td>
                            <td>{{ $p->my_class_id ? $p->my_class->name : 'All' }}</td>
                            <td>{{ ucwords($p->method) }}</td>
                            <td>{{ $p->description }}</td>
                            <td>{{ Qs::onlyDateFormat($p->deleted_at) }}</td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a class="material-symbols-rounded" href="javascript:;" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            {{-- Restore --}}
                                            @can($tenant_permissions['soft_deletes']['restore']['permission'])
                                            <a id="{{ $p->id }}" onclick="$('form#pay-restore-'+this.id).submit();" href="javascript:;" class="dropdown-item">
                                                <i class="material-symbols-rounded">undo</i> Restore
                                            </a>
                                            <form class="d-none" method="post" id="pay-restore-{{ $p->id }}" action="{{ route('payments.restore', $p->id) }}">
                                                @csrf
                                            </form>
                                            @endcan
                                            {{-- Delete --}}
                                            @can($tenant_permissions['soft_deletes']['payment_force_delete']['permission'])
                                            <a id="{{ $p->id }}" onclick="confirmForceDelete(this.id, 'payment')" href="javascript:;" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form class="d-none" method="post" id="payment-force-delete-{{ $p->id }}" action="{{ route('payments.force_delete', $p->id) }}">
                                                @csrf
                                            </form>
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
        </div>
    </div>
</div>
@endif

{{-- Users --}}
@if($users->isNotEmpty())
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Users</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="all-users">
                <table class="table datatable-button-html5-image">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Contact</th>
                            <th>Deleted At</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <img class="rounded-circle" style="height:40px;width:40px;" src="{{ str_starts_with($user->photo,'global_assets')?asset($user->photo):tenant_asset($user->photo) }}">
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->roles->pluck('name')->join(' ,') }}</td>
                            @if($user->hasRole(config('roles.default_roles.tenant.student')))
                            @php $s_recs = Usr::getStudentRecordByUserId($user->id,['my_class','section']) @endphp
                            <td class="text-center">{{ $s_recs->first()->my_class->name }} - {{ $s_recs->first()->section->name }}</td>
                            @else
                            <td>{{ $user->email ?? $user->phone ?? $user->phone2 ?? '-' }}</td>
                            @endif
                            <td>{{ Qs::onlyDateFormat($user->deleted_at) }}</td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a class="material-symbols-rounded" href="javascript:;" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            {{-- Restore --}}
                                            @can($tenant_permissions['soft_deletes']['user_restore']['permission'])
                                            <a id="{{ $user->id }}" onclick="$('form#user-restore-'+this.id).submit();" href="javascript:;" class="dropdown-item">
                                                <i class="material-symbols-rounded">undo</i> Restore
                                            </a>
                                            <form class="d-none" method="post" id="user-restore-{{ $user->id }}" action="{{ route('user.restore',$user->id) }}">
                                                @csrf
                                            </form>
                                            @endcan
                                            {{-- Delete --}}
                                            @can($tenant_permissions['soft_deletes']['user_force_delete']['permission'])
                                            <a id="{{ $user->id }}" onclick="confirmForceDelete(this.id,'user')" href="javascript:;" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form class="d-none" method="post" id="user-force-delete-{{ $user->id }}" action="{{ route('user.force_delete',$user->id) }}">
                                                @csrf
                                            </form>
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
        </div>
    </div>
</div>
@endif

{{-- My Classes --}}
@if($my_classes->isNotEmpty())
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Classes</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="card-body">
        <div class="tab-pane fade show active" id="all-classes">
            <table class="table datatable-button-html5-image">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Name</th>
                        <th>Class Type</th>
                        <th>Deleted At</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($my_classes as $c)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->class_type->name }}</td>
                        <td>{{ Qs::onlyDateFormat($c->deleted_at) }}</td>
                        <td class="text-center">
                            <div class="list-icons">
                                <div class="dropdown">
                                    <a class="material-symbols-rounded" href="javascript:;" data-toggle="dropdown">lists</a>
                                    <div class="dropdown-menu dropdown-menu-left">
                                        {{-- Restore --}}
                                        @can($tenant_permissions['soft_deletes']['my_class_restore']['permission'])
                                        <a id="{{ $c->id }}" onclick="$('form#my_class-restore-'+this.id).submit();" href="javascript:;" class="dropdown-item">
                                            <i class="material-symbols-rounded">undo</i> Restore
                                        </a>
                                        <form class="d-none" method="post" id="my_class-restore-{{ $c->id }}" action="{{ route('my_class.restore',$c->id) }}">
                                            @csrf
                                        </form>
                                        @endcan
                                        {{-- Delete --}}
                                        @can($tenant_permissions['soft_deletes']['my_class_force_delete']['permission'])
                                        <a id="{{ $c->id }}" onclick="confirmForceDelete(this.id,'my_class')" href="javascript:;" class="dropdown-item text-danger">
                                            <i class="material-symbols-rounded">delete</i> Delete
                                        </a>
                                        <form class="d-none" method="post" id="my_class-force-delete-{{ $c->id }}" action="{{ route('my_class.force_delete',$c->id) }}">
                                            @csrf
                                        </form>
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
    </div>
</div>
@endif

{{-- Exams --}}
@if($exams->isNotEmpty())
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Exams</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="card-body">
        <div class="tab-pane fade show active" id="all-exams">
            <table class="table datatable-button-html5-image">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Name</th>
                        <th>Term</th>
                        <th>Session</th>
                        <th>Category</th>
                        <th>Edit Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exams as $ex)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $ex->name }}</td>
                        <td>{{ "Term {$ex->term}" }}</td>
                        <td>{{ $ex->year }}</td>
                        <td>{{ $ex->category->name }}</td>
                        <td>{!! $ex->editable?'<i class="material-symbols-rounded">check</i>':'<i class="material-symbols-rounded">close</i>' !!}</td>
                        <td class="text-center">
                            <div class="list-icons">
                                <div class="dropdown">
                                    <a class="material-symbols-rounded" href="javascript:;" data-toggle="dropdown">lists</a>
                                    <div class="dropdown-menu dropdown-menu-left">
                                        {{-- Restore --}}
                                        @can($tenant_permissions['soft_deletes']['exam_restore']['permission'])
                                        <a id="{{ $ex->id }}" onclick="$('form#exam-restore-'+this.id).submit();" href="javascript:;" class="dropdown-item">
                                            <i class="material-symbols-rounded">undo</i> Restore
                                        </a>
                                        <form class="d-none" method="post" id="exam-restore-{{ $ex->id }}" action="{{ route('exam.restore',$ex->id) }}">
                                            @csrf
                                        </form>
                                        @endcan
                                        {{-- Delete --}}
                                        @can($tenant_permissions['soft_deletes']['exam_force_delete']['permission'])
                                        <a id="{{ $ex->id }}" onclick="confirmForceDelete(this.id,'exam')" href="javascript:;" class="dropdown-item text-danger">
                                            <i class="material-symbols-rounded">delete</i> Delete
                                        </a>
                                        <form class="d-none" method="post" id="exam-force-delete-{{ $ex->id }}" action="{{ route('exam.force_delete',$ex->id) }}">
                                            @csrf
                                        </form>
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
    </div>
</div>
@endif

{{-- Messages Threads --}}
@if($threads->isNotEmpty())
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Messages Threads</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="card-body">
        <div class="tab-pane fade show active" id="all-threads">
            <table class="table datatable-button-html5-image">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Subject</th>
                        <th>Creator</th>
                        <th>Latest Message</th>
                        <th>Participants</th>
                        <th>Created At</th>
                        <th>Deleted At</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($threads as $thread)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $thread->subject }}</td>
                        <td>{{ $thread->creator()->name }}</td>
                        <td>{{ $thread->getLatestMessageAttribute()->body ?? '' }}</td>
                        @include('pages/modals/subject_assigned_students')
                        <td>
                            <a href="javascript:;" data-toggle="modal" data-target="#thread-participants-{{ $thread->id }}">
                                View Participants
                            </a>
                        </td>
                        <td>{{ $thread->created_at }}</td>
                        <td>{{ $thread->deleted_at }}</td>
                        <td class="text-center">
                            <div class="list-icons">
                                <div class="dropdown">
                                    <a href="javascript:;" class="material-symbols-rounded" data-toggle="dropdown">lists</a>
                                    <div class="dropdown-menu dropdown-menu-left">
                                        {{-- Restore --}}
                                        @can($tenant_permissions['soft_deletes']['thread_restore']['permission'])
                                        <a id="{{ $thread->id }}" onclick="$('form#thread-restore-'+this.id).submit();" href="javascript:;" class="dropdown-item">
                                            <i class="material-symbols-rounded">undo</i> Restore
                                        </a>
                                        <form class="d-none" method="post" id="thread-restore-{{ $thread->id }}" action="{{ route('thread.restore',$thread->id) }}">
                                            @csrf
                                        </form>
                                        @endcan
                                        {{-- Delete --}}
                                        @can($tenant_permissions['soft_deletes']['thread_force_delete']['permission'])
                                        <a id="{{ $thread->id }}" onclick="confirmForceDelete(this.id,'thread')" href="javascript:;" class="dropdown-item text-danger">
                                            <i class="material-symbols-rounded">delete</i> Delete
                                        </a>
                                        <form class="d-none" method="post" id="thread-force-delete-{{ $thread->id }}" action="{{ route('thread.force_delete',$thread->id) }}">
                                            @csrf
                                        </form>
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
    </div>
</div>
@endif

@if(auth()->user()->hasRole(config('roles.default_roles.tenant.superadmin')))
@php
$model_names_to_remove = [];
if($payments->isEmpty()) $model_names_to_remove[] = 'payments';
if($users->isEmpty()) $model_names_to_remove[] = 'users';
if($my_classes->isEmpty()) $model_names_to_remove[] = 'my_classes';
if($exams->isEmpty()) $model_names_to_remove[] = 'exams';
if($threads->isEmpty()) $model_names_to_remove[] = 'threads';
@endphp
<div class="card">
    <div class="card-body d-flex flex-wrap">
        <label class="w-100 font-weight-bold">Empty Bin</label>
        @foreach(Usr::getCanSoftDeleteModelNames($model_names_to_remove) as $model_name)
        <div class="pr-2">
            <button id="{{ $model_name }}" type="button" onclick="confirmPermanentDeleteTwice(this.id)" class="btn btn-danger btn-sm">
                <i class="material-symbols-rounded">delete</i> Empty {{ ucfirst($model_name) }}
            </button>
            <form method="post" id="item-delete-{{ $model_name }}" action="{{ route('model.empty_soft_deleted', $model_name) }}" class="hidden">
                @csrf @method('delete')
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

@endif

@endsection
