@extends('layouts.master')

@section('page_title', 'Statistics')

@section('content')

@can('view_statistics')
{{-- Charts --}}
@include('pages.statistics.charts')
@else
<div class="alert alert-warning">
    You do not have permission to view statistics.
</div>
@endcan

@endsection
