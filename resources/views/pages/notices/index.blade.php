@extends('layouts.master')

@section('page_title', 'Manage Notices')

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Notices</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            @can($tenant_permissions['notices']['store']['permission'])
            <li class="nav-item">
                <a href="#add-notice" class="{{ in_array(Route::currentRouteName(), ['notices.edit']) ? 'nav-link' : 'nav-link active' }}" data-toggle="tab">Create a Notice</a>
            </li>
            @endcan

            @can($tenant_permissions['notices']['index']['permission'])
            <li class="{{ $notices->isEmpty() ? 'nav-item dropdown cursor-not-allowed' : 'nav-item dropdown' }}">
                <a href="#manage-notice" class="{{ $notices->isEmpty() ? 'nav-link pointer-events-none' : 'nav-link' }}" data-toggle="tab">Manage Notices</a>
            </li>
            @endcan

            @if($edit)
            @can($tenant_permissions['notices']['edit']['permission'])
            <li class="nav-item">
                <a href="#edit-notice" class="{{ $edit ? 'nav-link active show' : 'nav-link' }}" data-toggle="tab">Edit Notice</a>
            </li>
            @endcan
            @endif
        </ul>

        <div class="tab-content">
            {{-- Add Notice --}}
            @can($tenant_permissions['notices']['store']['permission'])
            <div class="{{ in_array(Route::currentRouteName(), ['notices.edit']) ? 'tab-pane fade' : 'tab-pane fade show active' }}" id="add-notice">
                <form class="ajax-store" method="post" action="{{ route('notices.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="body">Body <span class="text-danger">*</span></label>
                                <textarea rows="4" name="body" required placeholder="Body or Content of the Notice" class="form-control" id="body">{{ old('body') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="title">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Title of the Notice" class="form-control" id="title">
                            </div>
                            <div class="text-right">
                                <button id="ajax-btn" type="submit" class="btn btn-primary">
                                    Submit form <i class="material-symbols-rounded ml-2">send</i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @endcan

            {{-- Manage Notices --}}
            @can($tenant_permissions['notices']['index']['permission'])
            <div class="tab-pane fade" id="manage-notice">
                <table class="table table-sm datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Title</th>
                            <th>Body</th>
                            @can($tenant_permissions['users']['show']['permission'])
                            @if(auth()->user()->hasRole(config('roles.default_roles.tenant.superadmin')))
                            <th>By</th>
                            @endif
                            @endcan
                            <th>Viewers</th>
                            <th>Created at</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notices as $ntc)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="break-all break-spaces">{{ $ntc->title }}</td>
                            <td class="break-all break-spaces wmin-300">{{ $ntc->body }}</td>
                            @can($tenant_permissions['users']['show']['permission   '])
                            @if(auth()->user()->hasRole(config('roles.default_roles.tenant.superadmin')))
                            <td>
                                @php $user_id = $ntc->user->id @endphp
                                <a target="_blank" href="{{ route('users.show', Qs::hash($user_id)) }}">
                                    {{ Auth::id() == $user_id ? 'Me' : $ntc->user->name }}
                                </a>
                            </td>
                            @endif
                            @endcan
                            <td>
                                @if($ntc->viewers_ids)
                                <a href="javascript:;" data-toggle="modal" data-target="#notice-viewers-{{ $ntc->id }}">show</a>
                                @else
                                none
                                @endif
                            </td>
                            <td>{{ Qs::onlyDateFormat($ntc->created_at) }}</td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a class="material-symbols-rounded" href="javascript:;" data-toggle="dropdown">lists</a>

                                        <div class="dropdown-menu dropdown-menu-right">
                                            @can($tenant_permissions['notices']['edit']['permission'])
                                            <a href="{{ route('notices.edit', Qs::hash($ntc->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">edit</i> Edit
                                            </a>
                                            @endcan

                                            @can($tenant_permissions['notices']['destroy']['permission'])
                                            <a id="{{ $ntc->id }}" onclick="confirmPermanentDelete(this.id)" href="javascript:;" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form method="post" id="item-delete-{{ $ntc->id }}" action="{{ route('notices.destroy', $ntc->id) }}" class="hidden">
                                                @csrf
                                                @method('delete')
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
            @endcan

            {{-- Edit Notice --}}
            @if($edit)
            @can($tenant_permissions['notices']['edit']['permission'])
            <div class="{{ in_array(Route::currentRouteName(), ['notices.edit']) ? 'tab-pane fade show active' : 'tab-pane fade' }}" id="edit-notice">
                {{-- Alert if super_admin editing another’s notice --}}
                @if(Auth::id() != $notice->from_id && auth()->user()->hasRole(config('roles.default_roles.tenant.superadmin')))
                <div class="alert alert-danger border-0 alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <span><strong>Alert:</strong> You are editing the notice you did not create.</span>
                </div>
                @endif

                <form class="ajax-update" method="post" action="{{ route('notices.update', $notice->id) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label>Body <span class="text-danger">*</span></label>
                                <textarea rows="5" name="body" required placeholder="Body or Content of the Notice" class="form-control">{{ $notice->body }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" value="{{ $notice->title }}" required placeholder="Title of the Notice" class="form-control">
                                <div class="pt-1 col-xl-7">
                                    <small class="text-muted">
                                        Created - {{ Qs::onlyDateFormat($notice->created_at) }},
                                        by {{ $notice->from_id == Auth::id() ? 'Me' : $notice->user->name }}
                                    </small>
                                    @if($notice->editor_id)
                                    <hr class="m-1">
                                    <small class="text-muted">
                                        Last edited - {{ Qs::onlyDateFormat($notice->updated_at) }},
                                        by {{ $notice->editor_id == Auth::id()
                                                ? 'Me'
                                                : ($notice->editor->name . ' (' . str_replace('_', ' ', $notice->editor->role) . ')') }}
                                    </small>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <button id="ajax-btn" type="submit" class="btn btn-primary">
                                    Submit Form <i class="material-symbols-rounded ml-2">send</i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @endcan
            @endif

        </div>
    </div>
</div>

@include('pages/modals/notice_viewers')

@endsection
