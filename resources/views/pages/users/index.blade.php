@extends('layouts.master')

@section('page_title', 'Manage Users')

@section('content')
    @php
        $canEdit = auth()->user()->can($tenant_permissions['users']['edit']['permission']);
        $canView = auth()->user()->can($tenant_permissions['users']['show']['permission']);
        $canResetPassword = auth()->user()->can($tenant_permissions['users']['reset_password']['permission']);
        $canDelete = auth()->user()->can($tenant_permissions['users']['destroy']['permission']);
        $canToggleBlock = auth()->user()->can($tenant_permissions['users']['toggle_user_block']['permission']);
        $canToggleStaffEdit = auth()->user()->can($tenant_permissions['users']['toggle_staff_data_editing']['permission']);
        $hasActions = $canEdit || $canView || $canResetPassword || $canDelete;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Users</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item dropdown">
                    <a href="javascript:;" class="nav-link dropdown-toggle active" data-toggle="dropdown"
                        >Manage Users</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach ($roles as $role)
                            <a
                                href="#ur-{{ Qs::hash($role->id) }}"
                                class="dropdown-item"
                                data-toggle="tab"
                            >{{ $role->name }}</a>
                        @endforeach
                    </div>
                </li>
                @can($tenant_permissions['users']['store']['permission'])
                    <li class="nav-item"><a href="#new-user" class="nav-link" data-toggle="tab">Create New User</a></li>
                @endcan
            </ul>

            <div class="tab-content">
                @can($tenant_permissions['users']['store']['permission'])
                    <div class="tab-pane fade" id="new-user">
                        <form
                            method="post"
                            enctype="multipart/form-data"
                            class="wizard-form steps-validation ajax-store"
                            action="{{ route('users.store') }}"
                            data-fouc
                        >
                            @include('partials.user_fields', ['mode' => 'create', 'prefilled' => []])
                        </form>
                    </div>
                @endcan

                @foreach ($roles as $role)
                    <div class="tab-pane fade show" id="ur-{{ Qs::hash($role->id) }}">
                        <span class="status-styled">{{ Str::plural($role->name) }}</span>

                        <table class="datatable-button-html5-columns table">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Staff Data Editable</th>
                                    <th>Blocked</th>
                                    @if ($hasActions)
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody>
                                @foreach (Usr::getUsersByRoleNames([$role->name], ['staff']) as $u)
                                    @php
                                        $can_be_modified = ! Usr::userIsHead($u->id) || Usr::userIsHead();
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <img
                                                src="{{ Usr::getTenancyAwarePhotoUrl($u->photo_path) }}"
                                                class="rounded-circle"
                                                style="height: 40px; width: 40px"
                                            />
                                        </td>
                                        <td>{{ $u->name }}</td>
                                        <td>{{ $u->username }}</td>
                                        <td>
                                            <a href="tel:{{ $u->primary_mobile ?? $u->secondary_mobile }}">{{ $u->primary_mobile ?? $u->secondary_mobile }}</a>
                                        </td>
                                        <td><a href="mailto:{{ $u->email }}">{{ $u->email }}</a></td>
                                        <td>
                                            @if ($u->staff?->first()?->allow_staff_data_edit && $canToggleStaffEdit && $can_be_modified)
                                                <label class="form-switch m-0"
                                                    ><input
                                                        type="checkbox"
                                                        id="checkbox-staff-{{ $u->id }}"
                                                        onchange="updateStaffDataEdtiState({{ $u->id }},this)"
                                                        @checked($u->staff->first()->allow_staff_data_edit) /><i></i
                                                ></label>
                                            @else
                                                <span class="status-styled">Unavailable</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($canToggleBlock && $can_be_modified)
                                                <label class="form-switch m-0"
                                                    ><input
                                                        type="checkbox"
                                                        id="checkbox-user-{{ $u->id }}"
                                                        onchange="updateUserBlockedState({{ $u->id }},this)"
                                                        @checked($u->is_blocked) /><i></i
                                                ></label>
                                            @else
                                                <span class="status-styled">Unavailable</span>
                                            @endif
                                        </td>
                                        @if ($hasActions)
                                            <td class="text-center">
                                                @if ($can_be_modified)
                                                    <div class="list-icons">
                                                        <div class="dropdown">
                                                            <a
                                                                href="javascript:;"
                                                                class="material-symbols-rounded"
                                                                data-toggle="dropdown"
                                                            >lists</a>
                                                            <div class="dropdown-menu dropdown-menu-left">
                                                                @if ($canEdit)
                                                                    <a
                                                                        href="{{ route('users.edit', Qs::hash($u->id)) }}"
                                                                        class="dropdown-item"
                                                                        ><i class="material-symbols-rounded">edit</i>
                                                                        Edit</a>
                                                                @endif
                                                                @if ($canView)
                                                                    <a
                                                                        href="{{ route('users.show', Qs::hash($u->id)) }}"
                                                                        class="dropdown-item"
                                                                        ><i class="material-symbols-rounded"
                                                                            >visibility</i>
                                                                        View Profile</a>
                                                                @endif
                                                                @if ($canResetPassword)
                                                                    <a
                                                                        href="javascript:;"
                                                                        data-href="{{ route('users.password.reset', Qs::hash($u->id)) }}"
                                                                        class="dropdown-item needs-reset-pass-confirmation"
                                                                        ><i class="material-symbols-rounded"
                                                                            >lock_reset</i>
                                                                        Reset Password</a>
                                                                @endif
                                                                @if ($canDelete)
                                                                    <a
                                                                        href="javascript:;"
                                                                        id="{{ Qs::hash($u->id) }}"
                                                                        onclick="confirmDelete(this.id)"
                                                                        class="dropdown-item text-danger"
                                                                        ><i class="material-symbols-rounded">delete</i>
                                                                        Delete</a>
                                                                    <form
                                                                        id="item-delete-{{ Qs::hash($u->id) }}"
                                                                        action="{{ route('users.destroy', Qs::hash($u->id)) }}"
                                                                        method="post"
                                                                        class="hidden"
                                                                    >
                                                                        @csrf
                                                                        @method('delete')
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
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

@endsection
