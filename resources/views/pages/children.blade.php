@extends('layouts.master')

@section('page_title', 'My Children')

@section('content')

@php
$canViewProfile = auth()->user()->can($tenant_permissions['student_records']['show']['permission']);
$canMarksheet = auth()->user()->can($tenant_permissions['marks']['year_selector']['permission']);
$canAssessmentsheet = auth()->user()->can($tenant_permissions['assessments']['year_selector']['permission']);
$canPaymentStatus = auth()->user()->can($tenant_permissions['payments']['status']['permission']);
$hasActions = $canViewProfile || $canMarksheet || $canAssessmentsheet || $canPaymentStatus;
@endphp

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">My Children</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <table class="table datatable">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>ADM No</th>
                    <th>Section</th>
                    @if($hasActions)
                    <th class="text-center">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <img class="rounded-circle" style="height:40px; width:40px;" src="{{ Usr::getTenancyAwarePhotoUrl($s->user->photo_path) }}" alt="photo">
                    </td>
                    <td>{{ $s->user->name }}</td>
                    <td>{{ $s->admission_number }}</td>
                    <td>{{ $s->my_class->name . ' ' . $s->section->name }}</td>
                    @if($hasActions)
                    <td class="text-center">
                        <div class="list-icons">
                            <div class="dropdown">
                                <a class="material-symbols-rounded" href="javascript:;" data-toggle="dropdown">lists</a>
                                <div class="{{ $students->count() > 3 
                                        ? 'dropdown-menu dropdown-menu-left' 
                                        : 'dropdown-menu dropdown-menu-left dropdown-menu-position-static-md-desc' }}">
                                    @if($canViewProfile)
                                    <a href="{{ route('students.show', Qs::hash($s->id)) }}" class="dropdown-item">
                                        <i class="material-symbols-rounded">visibility</i> View Profile
                                    </a>
                                    @endif
                                    @if($canMarksheet)
                                    <a target="_self" href="{{ route('marks.year_selector', Qs::hash($s->user->id)) }}" class="dropdown-item">
                                        <i class="material-symbols-rounded">bottom_sheets</i> Marksheet
                                    </a>
                                    @endif
                                    @if($canAssessmentsheet)
                                    <a target="_self" href="{{ route('assessments.year_selector', Qs::hash($s->user->id)) }}" class="dropdown-item">
                                        <i class="material-symbols-rounded">two_pager</i> Assessment Sheet
                                    </a>
                                    @endif
                                    @if($canPaymentStatus)
                                    <div class="dropdown-divider"></div>
                                    <ul class="list-unstyled mb-0">
                                        <li>
                                            <a href="{{ route('payments.status', [Qs::hash($s->user_id)]) }}" class="dropdown-item">&xrArr; <span class="pl-2">All Payments</span></a>
                                        </li>
                                        <li>
                                            <a href="{{ route('payments.status', [Qs::hash($s->user_id), $settings->firstWhere('key' , 'current_session')->value]) }}" class="dropdown-item">&xrArr; <span class="pl-2">{{ $settings->firstWhere('key' , 'current_session')->value }}</span></a>
                                        </li>
                                    </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
