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
                            @include('partials.user_fields', ['mode' => 'invite', 'prefilled' => []])
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
                                {{-- Always show Actions so Preview is reachable --}}
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invitations as $inv)
                                @php
                                    $rowCanApprove = $canApprove && $inv->canBeApproved();
                                    $rowCanReject = $canReject && $inv->canBeRejected();
                                    $rowCanCancel = $canCancel && $inv->canBeCancelled();
                                    $hasRowActions = $rowCanApprove || $rowCanReject || $rowCanCancel;

                                    $hasSubmittedData = ! empty($inv->submitted_data);
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

                                    <td class="text-center">
                                        @if ($hasRowActions || $hasSubmittedData)
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a
                                                        href="javascript:;"
                                                        class="material-symbols-rounded"
                                                        data-toggle="dropdown"
                                                    >lists</a>
                                                    <div class="dropdown-menu dropdown-menu-left">
                                                        {{-- PREVIEW --}}
                                                        @if ($hasSubmittedData)
                                                            <a
                                                                href="javascript:;"
                                                                class="dropdown-item text-info preview-invitation-btn"
                                                                data-submitted='@json($inv->submitted_data)'
                                                            >
                                                                <i class="material-symbols-rounded">visibility</i>
                                                                Preview
                                                            </a>
                                                        @endif

                                                        {{-- APPROVE --}}
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

                                                        {{-- REJECT --}}
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

                                                        {{-- CANCEL --}}
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- REGISTRATION PREVIEW MODAL (single shared instance per page) --}}
    {{-- ============================================================ --}}
    <div
        class="modal fade"
        id="preview-invitation-modal"
        tabindex="-1"
        aria-labelledby="preview-invitation-heading"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold break-spaces" id="preview-invitation-heading">
                        Registration Request Preview
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">Close</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="preview-invitation-form" onsubmit="return false;" autocomplete="off">
                        @include('partials.user_fields', [
                            'mode' => 'preview',
                            'prefilled' => [],
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        /* ------------------------------------------------------------
         | Invite user form
         * ---------------------------------------------------------- */
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
                                flash({ msg: error, type: 'error' });
                            });
                        }
                    } else {
                        flash({ msg: 'An error occurred. Please try again.', type: 'error' });
                    }
                },
            });
        });

        /* ------------------------------------------------------------
         | Registration preview modal
         * ---------------------------------------------------------- */

        // Fields belonging to the parent block – used to decide visibility.
        const PREVIEW_PARENT_FIELDS = @json(Qs::getParentRelativeRecordFields());

        /**
         * Reset every input/select/textarea inside the preview form,
         * populate it from `data`, then disable everything.
         */
        function fillPreviewForm(data) {
            const form = document.getElementById('preview-invitation-form');
            if (!form) return;

            const $form = window.jQuery ? $(form) : null;
            const fields = form.querySelectorAll('input, select, textarea');

            // -------- Reset --------
            fields.forEach((el) => {
                el.disabled = false;
                const type = (el.type || '').toLowerCase();

                if (type === 'file') return;

                if (type === 'checkbox' || type === 'radio') {
                    el.checked = false;
                    return;
                }

                if (el.tagName === 'SELECT') {
                    if ($form) {
                        $(el)
                            .val(el.multiple ? [] : '')
                            .trigger('change.select2');
                    } else {
                        Array.from(el.options).forEach((o) => (o.selected = false));
                        if (!el.multiple) el.selectedIndex = 0;
                    }
                    return;
                }

                el.value = '';
            });

            // -------- Populate --------
            if (data && typeof data === 'object') {
                fields.forEach((el) => {
                    const rawName = el.name || '';
                    if (!rawName) return;

                    const name = rawName.replace(/\[\]$/, '');
                    if (!Object.prototype.hasOwnProperty.call(data, name)) return;

                    const value = data[name];
                    const type = (el.type || '').toLowerCase();

                    if (el.tagName === 'SELECT') {
                        if (el.multiple) {
                            let values = Array.isArray(value) ? value : value != null && value !== '' ? [value] : [];

                            // Make sure each value has a matching <option>.
                            values.forEach((v) => {
                                if (!Array.from(el.options).some((o) => o.value == v)) {
                                    const opt = document.createElement('option');
                                    opt.value = v;
                                    opt.textContent = v;
                                    el.appendChild(opt);
                                }
                            });

                            if ($form) {
                                $(el).val(values).trigger('change.select2');
                            } else {
                                Array.from(el.options).forEach((o) => (o.selected = values.some((v) => v == o.value)));
                            }
                        } else {
                            if (value != null && value !== '' && !Array.from(el.options).some((o) => o.value == value)) {
                                const opt = document.createElement('option');
                                opt.value = value;
                                opt.textContent = value;
                                el.appendChild(opt);
                            }

                            if ($form) {
                                $(el)
                                    .val(value != null ? value : '')
                                    .trigger('change.select2');
                            } else {
                                el.value = value != null ? value : '';
                            }
                        }
                    } else if (type === 'checkbox' || type === 'radio') {
                        el.checked = el.value == value;
                    } else if (type === 'file') {
                        // File inputs cannot be pre-populated; leave empty.
                    } else {
                        el.value = value != null ? value : '';
                    }
                });
            }

            // -------- Show / hide parent data block --------
            const parentBlock = form.querySelector('#parent-data');
            if (parentBlock) {
                const hasParentData = data && PREVIEW_PARENT_FIELDS.some((f) => data[f] != null && data[f] !== '');
                parentBlock.classList.toggle('display-none', !hasParentData);
            }

            // -------- 4. Disable everything --------
            fields.forEach((el) => (el.disabled = true));

            if ($form) {
                $form.find('input, select, textarea, button').prop('disabled', true);
                // Sync Select2 visuals
                $form.find('.select, .select-search').each(function () {
                    try {
                        $(this).trigger('change.select2');
                    } catch (e) {
                        /* ignore */
                    }
                });
            }
        }

        // Delegate click – works even after DataTables redraws the table.
        $(document).on('click', '.preview-invitation-btn', function (e) {
            e.preventDefault();

            const data = $(this).data('submitted') || {};
            fillPreviewForm(data);

            $('#preview-invitation-modal').modal('show');
        });

        // Safety net – reset the form each time the modal is fully hidden.
        $('#preview-invitation-modal').on('hidden.bs.modal', function () {
            const form = document.getElementById('preview-invitation-form');
            if (!form) return;
            form.querySelectorAll('input, select, textarea').forEach((el) => {
                el.disabled = false;
                const type = (el.type || '').toLowerCase();
                if (type === 'file') return;
                if (type === 'checkbox' || type === 'radio') {
                    el.checked = false;
                    return;
                }
                if (el.tagName === 'SELECT') {
                    Array.from(el.options).forEach((o) => (o.selected = false));
                    if (!el.multiple) el.selectedIndex = 0;
                    return;
                }
                el.value = '';
            });
        });
    </script>
@endpush
