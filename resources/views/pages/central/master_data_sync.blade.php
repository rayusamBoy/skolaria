@extends('layouts.master')

@section('page_title', 'Sync Master Academic Data')

@section('content')
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Sync Master Data from Template Tenant</h6>
    </div>
    <div class="card-body">
        @if(!$templateTenant)
        <div class="alert alert-warning">
            No template tenant found. Please mark a tenant as template first
            <a href="{{ route('tenants.index') }}">here</a>.
        </div>
        @endif

        <form method="POST" class="page-block" action="{{ route('master_data_sync.sync') }}">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Source Template Tenant <span class="text-danger">*</span></label>
                        <select name="source_tenant_id" class="form-control select" required>
                            <option value="">Select</option>
                            @foreach($tenants as $t)
                            <option value="{{ $t->id }}" @selected($templateTenant && $t->id == $templateTenant->id)>
                                {{ $t->name }} @if($t->is_template ?? false) (Template) @endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label>Tables to Sync <span class="text-danger">*</span></label>
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll('#tables-group')">
                            Select All Tables
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll('#tables-group')">
                            Deselect All Tables
                        </button>
                    </div>
                    <div id="tables-group">
                        @foreach($tables as $table)
                        <div class="form-group custom-control custom-checkbox">
                            <input type="checkbox" name="tables[]" value="{{ $table }}" class="custom-control-input" id="{{ Str::slug($table) }}">
                            <label class="checkbox-inline mr-3 custom-control-label" for="{{ Str::slug($table) }}">{{ Str::headline($table) }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Target Tenants <span class="text-danger">*</span></label>
                <div class="mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll('#tenants-group')">
                        Select All Tenants
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll('#tenants-group')">
                        Deselect All Tenants
                    </button>
                </div>
                <div id="tenants-group" class="border-1 border-warning rounded p-2">
                    @foreach($tenants as $t)
                    @if(!($t->is_template ?? false) || $t->id != ($templateTenant->id ?? 0))
                    <div class="form-group custom-control custom-checkbox">
                        <input type="checkbox" name="target_tenants[]" value="{{ $t->id }}" class="custom-control-input" id="tenant-target-{{ $t->id }}">
                        <label class="checkbox-inline mr-3 custom-control-label" for="tenant-target-{{ $t->id }}">{{ $t->name }}</label>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <div class="form-group custom-control custom-checkbox">
                    <input type="checkbox" name="lock_after_sync" value="1" class="custom-control-input" id="lock_after_sync">
                    <label class="checkbox-inline mr-3 custom-control-label" for="lock_after_sync"><strong>Lock synced tables</strong> on target tenants (read‑only)</label>
                </div>
                <p class="text-muted">When checked, users in the target tenant will be unable to create, update or delete records in the synced tables.</p>
            </div>

            <div id="exclude_lock_section" style="display:none;">
                <div class="form-group">
                    <label>Exclude the following tenants from locking (they will still receive the data but remain editable)</label>
                    <div class="border-1 border-success rounded p-2">
                        @foreach($tenants as $t)
                        @if(!($t->is_template ?? false))
                        <div class="form-group custom-control custom-checkbox">
                            <input type="checkbox" name="exclude_lock_tenants[]" value="{{ $t->id }}" class="custom-control-input" id="tenant-exclude-{{ $t->id }}">
                            <label class="checkbox-inline mr-3 custom-control-label" for="tenant-exclude-{{ $t->id }}">{{ $t->name }}</label>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">Sync Now</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle all checkboxes inside a group
    function toggleAll(containerSelector) {
        document.querySelectorAll(containerSelector + ' input[type="checkbox"]').forEach(function(checkbox) {
            checkbox.checked = true;
        });
    }

    // Deselect all checkboxes inside a group
    function deselectAll(containerSelector) {
        document.querySelectorAll(containerSelector + ' input[type="checkbox"]').forEach(function(checkbox) {
            checkbox.checked = false;
        });
    }

    $(function() {
        $('#lock_after_sync').change(function() {
            $('#exclude_lock_section').toggle(this.checked);
        });
    });

</script>
@endpush
