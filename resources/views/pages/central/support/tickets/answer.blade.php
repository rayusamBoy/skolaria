@extends('layouts.master')

@section('page_title', 'My Conversation')

@section('content')
    <div class="row">
        <div class="col-md-9 order-md-last">
            @if (! $ticket->is_closed())
                <div class="row">
                    <div class="col-12">
                        <div class="card card-collapsed card-ticket">
                            <div class="card-header header-elements-inline">
                                <h6 class="card-title">Answer</span></h6>
                                {!! Qs::getPanelOptionsHtml() !!}
                            </div>

                            <div class="card-body">
                                <form method="post" action="{{ route('tickets.store_operator_message') }}">
                                    <div class="row">
                                        <div class="col-12">
                                            @csrf
                                            @method('put')

                                            <input type="hidden" value="{{ $ticket->id }}" name="ticket_id" />

                                            <div class="row pb-2">
                                                <div class="col-12">
                                                    <span>Subject</span>
                                                    <p>{{ $ticket->subject }}</p>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12">
                                                    <label for="markdown-editor">Message <span class="text-danger">*</span></label>
                                                    <div class="form-group row">
                                                        <div class="col-12">
                                                            <div
                                                                class="overtype-editor"
                                                                data-ot-toolbar="true"
                                                                data-ot-show-stats="true"
                                                            ></div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row float-right">
                                                        <div class="col-6">
                                                            {{-- Submit button --}}
                                                            <div class="form-group">
                                                                <button type="submit" class="btn btn-sm btn-success">
                                                                    Reply<span
                                                                        class="material-symbols-rounded pb-2px ml-2"
                                                                        >reply</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            {{-- Cancel button --}}
                                                            <div class="form-group">
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm btn-warning cancel-editor"
                                                                >
                                                                    Cancel<span
                                                                        class="material-symbols-rounded pb-2px ml-2"
                                                                        >cancel</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @foreach ($messages as $msg)
                <div class="row">
                    <div class="col-12">
                        <div class="card @if(! $msg->is_from_tenant) card-operator @endif">
                            <div class="card-body px-0 py-2">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="row px-3">
                                            <div class="col-7">
                                                <span>
                                                    @if ($msg->is_from_tenant)
                                                        {{ $tenant_user->name }}
                                                        <br
                                                        /><span class="badge bg-success">Owner</span>
                                                    @else
                                                        {{ $msg->central_user($msg->user_id)->name }}
                                                        <br
                                                        /><span class="badge bg-warning">Operator</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="col-5 text-right">
                                                <small>{{ Qs::formatDateTime($msg->created_at) }}</small>
                                            </div>
                                        </div>

                                        <hr class="my-2" />

                                        <div class="row px-3">
                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-12">{!! Qs::parseMarkdown($msg->body) !!}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="col-md-3 order-md-first">
            <div class="card">
                <div class="card-body">
                    <div class="space-between d-flex space-btn flex-wrap pb-2">
                        <span class="text-info pr-2">Ticket Information</span>
                        <span
                            ><span class="text-muted">Status </span
                            ><span
                                class="badge {{ $ticket->is_closed() ? 'text-success' : 'text-danger' }} font-size-sm"
                                >{{ $ticket->status }}</span
                            ></span>
                    </div>
                    <span class="text-muted">Requestor</span>

                    <p>{{ $tenant_user->name }} <span class="badge bg-success">Owner</span></p>
                    <span class="text-muted">Contact</span>

                    @if (auth()->user()->hasVerifiedEmail())
                        <p>{{ auth()->user()->email }}</p>
                    @elseif (auth()->user()->primary_mobile || auth()->user()->secondary_mobile)
                        <p>{{ auth()->user()->primary_mobile ?? auth()->user()->secondary_mobile }}</p>
                    @else
                        <p>None</p>
                    @endif

                    <span class="text-muted">Department</span>
                    <p>{{ $ticket->department }}</p>
                    <span class="text-muted">Priority</span>
                    <p>{{ $ticket->priority }}</p>

                    <span class="text-muted">Category</span>
                    <p>{{ $ticket->categories->name }}</p>

                    <span class="text-muted">Submitted</span>
                    <p>{{ Qs::formatDateTime($ticket->created_at) }}</p>

                    <span class="text-muted">Last Updated</span>
                    <p>{{ $ticket->updated_at->diffForHumans() }}</p>

                    <span class="text-muted">Labels</span>
                    <p>{!! implode(", ", $labels) !!}</p>
                </div>
            </div>
        </div>
    </div>

@endsection
