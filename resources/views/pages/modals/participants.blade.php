<!-- Modal -->
<div class="modal fade" id="thread-participants-{{ $thread->id }}" tabindex="-1" aria-labelledby="message-thread-participants" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-center" id="message-thread-participants">Mesage Thread Participants</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">Close</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="card-body">
                    <ul class="nav nav-tabs-highlight">
                        @if(count($thread->participantsArray(auth()->id())) <= 0) 
                        <i>The message thread has no partitipants</i>
                        @else
                        @foreach($thread->participantsArray(auth()->id()) as $pt)
                        <span class="participant">
                            <li><a target="_blank" href="{{ route('users.show', Qs::hash($pt->id)) }}">{{ $loop->iteration }} - {{ $pt->name }}</a></li>
                            @can($tenant_permissions['message_threads']['remove_participant']['permission'])
                            <a onclick="event.preventDefault(); document.getElementById('remove-participant-form').submit();" href="javascript:;" class="material-symbols-rounded pl-1" aria-label="remove" title="Remove {{ $pt->name }}">close</a>
                            <form id="remove-participant-form" action="{{ route('threads.participants.remove', [Qs::hash($thread->id), Qs::hash($pt->id)]) }}" method="POST" class="d-none">
                                @csrf @method('DELETE')
                            </form>
                            @endcan
                        </span>
                        @endforeach
                        @endif
                    </ul>
                </div>
            </div>
            @if($thread->hasDirtyParticipant($thread->id) && auth()->user()->can($tenant_permissions['message_threads']['activate_all_participants']['permission']))
            <div class="modal-footer">
                <a onclick="event.preventDefault(); document.getElementById('activate-participants-form').submit();" href="javascript:;" type="button" class="btn">Activate all removed participants</a>
                <form id="activate-participants-form" action="{{ route('threads.participants.activate_all', Qs::hash($thread->id)) }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
