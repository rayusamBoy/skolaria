@extends('layouts.master')

@section('page_title', 'Manage Invitations')

@section('content')
    @php
        $canEdit = auth()->user()->can($tenant_permissions['users']['edit']['permission']);
        $canView = auth()->user()->can($tenant_permissions['users']['show']['permission']);
        $canResetPassword = auth()->user()->can($tenant_permissions['users']['reset_password']['permission']);
        $canDelete = auth()->user()->can($tenant_permissions['users']['destroy']['permission']);
        $hasActions = $canEdit || $canView || $canResetPassword || $canDelete;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Invitations</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item">
                    <a href="#pending-users" class="nav-link active" data-toggle="tab">Pending Registrations</a>
                </li>
                @can($tenant_permissions['users']['store']['permission'])
                    <li class="nav-item">
                        <a href="#invite-user" class="nav-link" data-toggle="tab">Invite User</a>
                    </li>
                @endcan
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade" id="invite-user">
                    <form id="invite-user-form" class="mb-3" class="wizard-form steps-validation">
                        @include('partials.user_fields', ['mode' => 'invite'])
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Expiry</label>
                                    <input
                                        type="text"
                                        name="expires_at"
                                        class="form-control date-pick"
                                        placeholder="Select Date..."
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Generate Link</button>
                        </div>
                    </form>
                    <div id="invite-link-result" class="mt-2"></div>
                </div>

                <div class="tab-pane fade active show" id="pending-users">
                    <table class="datatable-button-html5-columns table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Roles</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>URL</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invitations as $inv)
                                <tr>
                                    <td>{{ $inv->submitted_data['name'] ?? '—' }}</td>
                                    <td>{{ $inv->submitted_data['email'] ?? '—' }}</td>
                                    <td>{{ implode(', ', $inv->roles) }}</td>
                                    <td>{{ $inv->submitted_at?->diffForHumans() ?? '—' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $inv->status === 'approved' ? 'success' : 'danger' }} badge-default">{{ ucfirst($inv->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px">
                                            @if ($inv->isPending() && ! $inv->submitted_data)
                                                <a
                                                    href="{{ url("/register/{$inv->token}") }}"
                                                    target="_blank"
                                                    class="btn btn-link btn-sm"
                                                >{{ url("/register/{$inv->token}") }}</a>
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </td>
                                    @if ($inv->isPending() && $inv->submitted_data)
                                        <td class="text-center">
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a
                                                        href="javascript:;"
                                                        class="material-symbols-rounded"
                                                        data-toggle="dropdown"
                                                    >lists</a>
                                                    <div class="dropdown-menu dropdown-menu-left">
                                                        <a
                                                            href="javascript:;"
                                                            id="{{ $inv->id }}"
                                                            onclick="confirmOperation(this.id)"
                                                            class="dropdown-item text-success"
                                                        ><i class="material-symbols-rounded">check</i> Approve</a>
                                                        <form
                                                            id="item-confirm-operation-{{ $inv->id }}"
                                                            action="{{ route('users.registrations.approve', $inv->id) }}"
                                                            method="post"
                                                            class="hidden"
                                                        >
                                                            @csrf
                                                        </form>
                                                        <a
                                                            href="javascript:;"
                                                            id="{{ $inv->id }}"
                                                            onclick="confirmOperation(this.id)"
                                                            class="dropdown-item text-danger"
                                                        ><i class="material-symbols-rounded">cancel</i> Reject</a>
                                                        <form
                                                            id="item-confirm-operation-{{ $inv->id }}"
                                                            action="{{ route('users.registrations.reject', $inv->id) }}"
                                                            method="post"
                                                            class="hidden"
                                                        >
                                                            @csrf
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    @else
                                        <td class="text-center">—</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $('#invite-user-form').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('users.invitations.store') }}",
                type: 'POST',
                data: $(this).serialize(),
                success: function (res) {
                    if (res.ok) {
                        $('#invite-link-result').html(
                            '<div class="alert alert-success">Invitation Link: <a href="' +
                                res.link +
                                '" target="_blank">' +
                                res.link +
                                '</a></div>',
                        );
                    }
                },
                error: function (res) {
                    // Validation errors
                    if (res.status === 422) {
                        let errors = res.responseJSON.errors;
                        for (let field in errors) {
                            errors[field].forEach(function (error) {
                                flash({
                                    msg: error,
                                    type: 'error',
                                });
                            });
                        }
                    }
                    // Other errors
                    else {
                        flash({
                            msg: 'An error occurred. Please try again.',
                            type: 'error',
                        });
                    }
                },
            });
        });
    </script>
@endpush
