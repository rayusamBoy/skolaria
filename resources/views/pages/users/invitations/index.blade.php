@extends('layouts.master')

@section('page_title', 'Manage Invitations')

@section('content')
    @php
        $canApprove = auth()->user()->can($tenant_permissions['users']['approve']['permission']);
        $canReject = auth()->user()->can($tenant_permissions['users']['reject']['permission']);
        $canCancel = auth()->user()->can($tenant_permissions['users']['cancel']['permission']);
        $hasActions = $canApprove || $canReject || $canCancel;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Invitations</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item">
                    <a href="#pending-users" class="nav-link active" data-toggle="tab">Registrations</a>
                </li>
                @can($tenant_permissions['users']['invite_user']['permission'])
                    <li class="nav-item">
                        <a href="#invite-user" class="nav-link" data-toggle="tab">Invite User</a>
                    </li>
                @endcan
            </ul>

            <div class="tab-content">
                @can($tenant_permissions['users']['invite_user']['permission'])
                    <div class="tab-pane fade" id="invite-user">
                        <form id="invite-user-form" class="mb-3">
                            <strong>Prefill User Details. If filled the invitee will not be able to change them.</strong>
                            @include('partials.user_fields', ['mode' => 'invite'])
                            <hr />
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Expiry <span class="text-info">(default: 3 days)</span></label>
                                        <input
                                            type="text"
                                            name="expires_at"
                                            class="form-control date-pick"
                                            placeholder="Select Date..."
                                            value="{{ now()->addDays(3)->format('Y-m-d') }}"
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
                @endcan

                <div class="tab-pane fade active show" id="pending-users">
                    <table class="datatable-button-html5-columns table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Roles</th>
                                <th>Submitted</th>
                                <th>Approved</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th>URL</th>
                                @if ($hasActions)
                                    <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invitations as $inv)
                                @php
                                    $rowCanApprove = $canApprove && $inv->canBeApproved();
                                    $rowCanReject = $canReject && $inv->canBeRejected();
                                    $rowCanCancel = $canCancel && $inv->canBeCancelled();
                                    $hasRowActions = $rowCanApprove || $rowCanReject || $rowCanCancel;
                                @endphp

                                <tr>
                                    <td>{{ $inv->submitted_data['name'] ?? '—' }}</td>
                                    <td>{{ $inv->submitted_data['email'] ?? '—' }}</td>
                                    <td>{{ \is_array($inv->roles) ? implode(', ', $inv->roles) : $inv->roles }}</td>
                                    <td>{{ $inv->submitted_at?->diffForHumans() ?? '—' }}</td>
                                    <td>{{ $inv->approved_at?->diffForHumans() ?? '—' }}</td>
                                    <td>{{ $inv->expires_at ?? '—' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $inv->status_color }} badge-default">
                                            {{ $inv->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            @if ($inv->hasUsableUrl())
                                                <a
                                                    href="{{ url("/register/{$inv->token}") }}"
                                                    target="_blank"
                                                    class="btn btn-link btn-sm item-output d-flex"
                                                >
                                                    <span class="text-truncate" style="max-width: 300px">
                                                        {{ url("/register/{$inv->token}") }}
                                                    </span>
                                                    <button
                                                        data-toggle="tooltip"
                                                        title="Copy URL"
                                                        data-title="Copy URL"
                                                        class="item-copy material-symbols-rounded btn font-size-xl bg-transparent p-0"
                                                    >
                                                        content_copy
                                                    </button>
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </td>

                                    @if ($hasActions)
                                        <td class="text-center">
                                            @if ($hasRowActions)
                                                <div class="list-icons">
                                                    <div class="dropdown">
                                                        <a
                                                            href="javascript:;"
                                                            class="material-symbols-rounded"
                                                            data-toggle="dropdown"
                                                        >lists</a>
                                                        <div class="dropdown-menu dropdown-menu-left">
                                                            @if ($rowCanApprove)
                                                                <a
                                                                    href="javascript:;"
                                                                    id="approve-{{ $inv->id }}"
                                                                    onclick="confirmOperation(this.id)"
                                                                    class="dropdown-item text-success"
                                                                >
                                                                    <i class="material-symbols-rounded">check</i>
                                                                    Approve
                                                                </a>
                                                                <form
                                                                    id="item-confirm-operation-approve-{{ $inv->id }}"
                                                                    action="{{ route('users.registrations.approve', $inv->id) }}"
                                                                    method="post"
                                                                    class="hidden"
                                                                >
                                                                    @csrf
                                                                </form>
                                                            @endif

                                                            @if ($rowCanReject)
                                                                <a
                                                                    href="javascript:;"
                                                                    id="reject-{{ $inv->id }}"
                                                                    onclick="confirmOperation(this.id)"
                                                                    class="dropdown-item text-danger"
                                                                >
                                                                    <i class="material-symbols-rounded">close</i>
                                                                    Reject
                                                                </a>
                                                                <form
                                                                    id="item-confirm-operation-reject-{{ $inv->id }}"
                                                                    action="{{ route('users.registrations.reject', $inv->id) }}"
                                                                    method="post"
                                                                    class="hidden"
                                                                >
                                                                    @csrf
                                                                </form>
                                                            @endif

                                                            @if ($rowCanCancel)
                                                                <a
                                                                    href="javascript:;"
                                                                    id="cancel-{{ $inv->id }}"
                                                                    onclick="confirmOperation(this.id)"
                                                                    class="dropdown-item text-warning"
                                                                >
                                                                    <i class="material-symbols-rounded">cancel</i>
                                                                    Cancel
                                                                </a>
                                                                <form
                                                                    id="item-confirm-operation-cancel-{{ $inv->id }}"
                                                                    action="{{ route('users.registrations.cancel', $inv->id) }}"
                                                                    method="post"
                                                                    class="hidden"
                                                                >
                                                                    @csrf
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                —
                                            @endif
                                        </td>
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
                    } else {
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
