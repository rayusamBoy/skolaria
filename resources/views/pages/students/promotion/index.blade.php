@extends('layouts.master')

@section('page_title', 'Student Promotion')

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title font-weight-bold">
            Student Promotion From
            <span class="text-danger">{{ $old_year }}</span>
            TO
            <span class="text-success">{{ $new_year }}</span> Session
        </h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        @include('pages.students.promotion.selector')
    </div>
</div>

@can($tenant_permissions['promotions']['promote']['permission'])
@if($selected)
@php
$fromClass = $my_classes->firstWhere('id', $fc);
$fromSection = $sections->firstWhere('id', $fs);
$toClass = $my_classes->firstWhere('id', $tc);
$toSection = $sections->firstWhere('id', $ts);
@endphp

<div class="card w-fit wmin-100-pcnt">
    <div class="card-header header-elements-inline">
        <h6 class="card-title font-weight-bold">
            Promote Students From
            <span class="text-teal">{{ $fromClass->name }} {{ $fromSection->name }}</span>
            TO
            <span class="text-purple">{{ $toClass->name }} {{ $toSection->name }}</span>
        </h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        @include('pages.students.promotion.promote')
    </div>
</div>
@endif
@endcan

{{--Student Promotion End--}}

@endsection
