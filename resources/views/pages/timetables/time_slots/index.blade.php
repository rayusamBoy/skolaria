<div class="tab-pane fade show active" id="manage-ts">
    {{-- Add Time Slots --}}
    @includeWhen(auth()->user()->can('time_tables.store_time_slot'), 'pages.timetables.time_slots.add')
    {{-- Manage Time Slots --}}
    @include('pages.timetables.time_slots.manage')
</div>
