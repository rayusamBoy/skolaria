@extends('layouts.master')

@section('page_title', "Edit Tenant - $tenant->name")

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 id="ajax-title" class="card-title">Edit Tenant Details</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <form
                method="post"
                enctype="multipart/form-data"
                class="ajax-update"
                data-reload="#page-title"
                action="{{ route('tenants.update', $tenant->id) }}"
            >
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="font-weight-semibold">Identification Mode</label>
                            <div>
                                @if ($tenant->identification_mode === 'domain')
                                    <input disabled value="Domain" type="text" class="form-control" />
                                    <input
                                        name="identification_mode"
                                        value="domain"
                                        type="hidden"
                                        class="form-control"
                                    />
                                @else
                                    <input
                                        disabled
                                        value="Request Data (Multi‑School)"
                                        type="text"
                                        class="form-control"
                                    />
                                    <input
                                        name="identification_mode"
                                        value="request_data"
                                        type="hidden"
                                        class="form-control"
                                    />
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="name">Tenant Name: <span class="text-danger">*</span></label>
                            <input
                                id="name"
                                required
                                name="name"
                                value="{{ $tenant->name }}"
                                type="text"
                                class="form-control"
                            />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="account_status">Account Status: <span class="text-danger">*</span></label>
                            <select required class="form-control select" name="account_status" id="account_status">
                                @foreach (Usr::getAccountStatuses() as $status)
                                    <option @selected($tenant->account_status === $status) value="{{ $status }}">
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="payment_status">Payment Status: <span class="text-danger">*</span></label>
                            <select required class="form-control select" name="payment_status" id="payment_status">
                                @foreach (Pay::getPaymentStatusOptions() as $key => $status)
                                    <option @selected($tenant->payment_status === $key) value="{{ $key }}">
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="remarks" class="font-weight-semibold">Remarks</label>
                            <textarea id="remarks" name="remarks" class="form-control">{{ $tenant->remarks }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @if ($tenant->identification_mode === 'request_data')
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tenant_slug">School Slug <span class="text-danger">*</span></label>
                                <input
                                    id="tenant_slug"
                                    required
                                    name="tenant_slug"
                                    value="{{ $tenant->tenant_slug }}"
                                    type="text"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="is_template" class="w-100"
                                    >Template tenant
                                    <i
                                        class="material-symbols-rounded text-info float-right"
                                        data-toggle="tooltip"
                                        title="Whether other schools will sync from this school"
                                    >info</i>
                                </label>
                                <select
                                    required
                                    data-placeholder="Select Status"
                                    class="form-control select"
                                    name="is_template"
                                    id="is_template"
                                >
                                    @foreach ([1 => 'Yes', 0 => 'No'] as $key => $value)
                                        <option @selected($tenant->is_template == $key) value="{{ $key }}">
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="is_template" class="w-100"
                                    >Lock master data
                                    <i
                                        class="material-symbols-rounded text-info float-right"
                                        data-toggle="tooltip"
                                        title="Whether only template tenant can modify master data"
                                    >info</i>
                                </label>
                                <select
                                    required
                                    data-placeholder="Select Status"
                                    class="form-control select"
                                    name="lock_master_data"
                                    id="lock_master_data"
                                >
                                    @foreach ([1 => 'Yes', 0 => 'No'] as $key => $value)
                                        <option @selected($tenant->lock_master_data == $key) value="{{ $key }}">
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    @if ($tenant->identification_mode === 'domain')
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="domain" class="font-weight-semibold"
                                    >Domain <span class="text-danger">*</span></label>
                                <input
                                    id="domain"
                                    required
                                    name="domain"
                                    value="{{ $tenant->domain->domain ?? '' }}"
                                    type="text"
                                    class="form-control"
                                />
                            </div>
                        </div>
                    @endif
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">
                        Submit form <i class="material-symbols-rounded ml-2">send</i>
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
