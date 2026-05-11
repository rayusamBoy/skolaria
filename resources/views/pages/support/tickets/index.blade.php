@extends('layouts.master')

@section('page_title', 'Manage Tickets')

@section('content')

@php
$canReply = auth()->user()->can($tenant_permissions['tickets']['reply']['permission']);
$canArchive = auth()->user()->can($tenant_permissions['tickets']['archive_ticket']['permission']);
$canUnarchive = auth()->user()->can($tenant_permissions['tickets']['unarchive_ticket']['permission']);
$hasActions = $canReply || $canArchive;
@endphp

<div class="card card-collapsed card-ticket">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Open a Ticket</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <form method="post" action="{{ route('tickets.store') }}">
            @csrf @method('post')
            <div class="row">
                <div class="col-12">
                    <p class="text-muted">Information</p>
                    <div class="form-group row">
                        <div class="col-lg-6">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" disabled class="form-control" value="{{ auth()->user()->name }}" readonly>
                        </div>
                        <div class="col-lg-6">
                            <label for="contact">Contact</label>
                            @if(auth()->user()->hasVerifiedEmail())
                            <input type="text" name="email" id="contact" disabled class="form-control" value="{{ auth()->user()->email }}" readonly>
                            @elseif(auth()->user()->primary_mobile || auth()->user()->secondary_mobile)
                            <input type="text" name="mobile" id="contact" disabled class="form-control" value="{{ auth()->user()->primary_mobile ?? auth()->user()->secondary_mobile }}" readonly>
                            @else
                            <input type="text" id="contact" disabled class="form-control" placeholder="Not provided" readonly>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-lg-3">
                            <label for="department">Department <span class="text-danger">*</span></label>
                            <select id="department" name="department" class="form-control select" required>
                                @foreach($departments as $department)
                                <option value="{{ $department }}">{{ ucfirst($department) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label for="priority">Priority <span class="text-danger">*</span></label>
                            <select id="priority" name="priority" class="form-control select" required>
                                @foreach($priorities as $prs)
                                <option value="{{ $prs }}">{{ ucfirst($prs) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label for="category">Category <span class="text-danger">*</span></label>
                            <select id="category" data-placeholder="Select Category" name="category_id" class="form-control select" required>
                                <option value=""></option>
                                @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" title="{{ $cat->description }}">{{ ucfirst($cat->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label for="label">Labels</label>
                            <select id="label" data-placeholder="Select Labels" name="labels_ids[]" class="form-control select" multiple="multiple">
                                <option value=""></option>
                                @foreach($labels as $lbl)
                                <option value="{{ $lbl->id }}" title="{{ $lbl->description }}">{{ ucfirst($lbl->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p class="text-muted">Message</p>

                    <div class="row pb-2">
                        <div class="col-12">
                            <label for="subject">Subject <span class="text-danger">*</span></label>
                            <input type="text" id="subject" name="subject" class="form-control" value="{{ old('subject') ?? '' }}" placeholder="Enter the subject of your message" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="mt-3">Message <span class="text-danger">*</span></label>
                            <div class="form-group row">
                                <div class="col-12">
                                    <div class="overtype-editor" data-ot-toolbar="true" data-ot-show-stats="true"></div>
                                </div>
                            </div>
                            <div class="form-group row float-right">
                                <div class="col-6">
                                    <button type="submit" class="btn btn-sm btn-success">Submit<span class="material-symbols-rounded ml-2 pb-2px">send</span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Active Tickets</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="all-users">
                <table class="table datatable-button-html5-image">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Department</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            @if($hasActions)
                            <th class="text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($active_tickets as $ticket)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $ticket->department }}</td>
                            <td>{{ $ticket->subject }}</td>
                            <td>{{ ucwords($ticket->status) }}</td>
                            <td>{{ Qs::formatDateTime($ticket->updated_at) }}</td>
                            @if($hasActions)
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a class="material-symbols-rounded" href="javascript:;" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            @if($canReply)
                                            <a href="{{ route('tickets.reply', Qs::hash($ticket->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">reply</i> Reply
                                            </a>
                                            @endif
                                            @if($canArchive)
                                            <a id="{{ $ticket->id }}" onclick="confirmArchive(this.id)" href="javascript:;" class="dropdown-item text-warning">
                                                <i class="material-symbols-rounded">archive</i> Archive
                                            </a>
                                            <form class="d-none" method="post" id="item-archive-{{ $ticket->id }}" action="{{ route('tickets.archive', $ticket->id) }}">
                                                @method('patch') @csrf
                                            </form>
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
    </div>
</div>

@if ($archived_tickets->isNotEmpty() && $canUnarchive)
<div class="card card-collapsed">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Archived Tickets</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="all-archived">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Department</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            @if($canUnarchive)
                            <th class="text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archived_tickets as $ticket)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $ticket->department }}</td>
                            <td>{{ $ticket->subject }}</td>
                            <td>{{ ucwords($ticket->status) }}</td>
                            <td>{{ Qs::formatDateTime($ticket->updated_at) }}</td>
                            @if($canUnarchive)
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a class="material-symbols-rounded" href="javascript:;" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            <a id="{{ $ticket->id }}" onclick="$('form#item-unarchive-'+this.id).submit()" href="javascript:;" class="dropdown-item text-warning">
                                                <i class="material-symbols-rounded">unarchive</i> Unarchive
                                            </a>
                                            <form class="d-none" method="post" id="item-unarchive-{{ $ticket->id }}" action="{{ route('tickets.unarchive', $ticket->id) }}">
                                                @method('patch') @csrf
                                            </form>
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
    </div>
</div>
@endif

@endsection
