<div class="tab-pane fade show active" id="manage-ts">
    {{-- Add Time Slots --}}
    @includeWhen(auth()->user()->can($tenant_permissions['time_tables']['store_time_slot']['permission']), 'pages.timetables.time_slots.add')
    {{-- Manage Time Slots --}}
    @include('pages.timetables.time_slots.manage')
</div>
