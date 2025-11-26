@extends('layouts.master')

@section('page_title', 'Schedule')

@section('content')

{{-- Calendar Events --}}
@can('view_events')
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Calendar Events</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    @include('pages.schedule.calendar.index')
</div>
@endcan

{{-- Manage Events --}}
@can('manage_events')
@include('pages.schedule.calendar.manage')
@endcan

@endsection
