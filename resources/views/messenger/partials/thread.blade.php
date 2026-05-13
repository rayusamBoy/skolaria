@if($thread->hasParticipant(auth()->id()) || auth()->user()->hasRole(config('roles.default_roles.tenant.superadmin')))
@php $unread = $thread->userUnreadMessagesCount(auth()->id()) @endphp
<div class="{{ $unread == 0 ? 'card card-collapsed' : 'card' }}">
    <div class="card-header header-elements-inline">
        <h6 class="media-heading card-title">
            <a href="{{ route('message_threads.show', Qs::hash($thread->id)) }}">{{ $thread->subject }}</a>
            <small> ({{ $unread }} unread)</small>
        </h6>
        <div class="header-elements print-none">
            <div class="list-icons">
                @can($tenant_permissions['message_threads']['destroy']['permission'])
                <a id="{{ $thread->id }}" type="button" onclick="confirmDelete(this.id)" title="Delete Thread" class="btn btn-danger btn-sm"><i class="material-symbols-rounded">delete</i></button>
                <form method="post" id="item-delete-{{ $thread->id }}" action="{{ route('message_threads.destroy', $thread->id) }}" class="hidden">@csrf @method('delete')</form>
                @endcan
                <a class="list-icons-item" data-action="collapse"></a>
                <a class="list-icons-item" data-action="remove"></a>
            </div>
        </div>
    </div>
    @php
    $class = $thread->isUnread(auth()->id()) ? 'alert-info' : '';
    @endphp

    <div class="media alert {{ $class }} border-none">
        <div class="row w-100">
            <div class="col-md-7">
                <small class="mb-1"><strong>Latest Message</strong></small>
                <p>
                    {{ $thread->latestMessage->body ?? 'The thread has no message(s) yet. Or, the message(s) were deleted.' }}
                </p>
                <p>
                    <small><strong>Creator:</strong> {{ $thread->creator()->name }}</small>
                </p>
            </div>
            <div class="col-md-5">
                <small><strong>Participants:</strong></small>
                <p>
                    @include('pages/modals/participants')
                    <td><a href="javascript:;" data-toggle="modal" data-target="#thread-participants-{{ $thread->id }}">View Participants</a></td>
                </p>
            </div>
        </div>
    </div>
</div>

@elseif($thread->isDirtyParticipant(auth()->id()))
<p>Sorry, you are no longer a participant of the thread with the subject: <strong>{{ $thread->subject }}</strong>.</p>

@endif
