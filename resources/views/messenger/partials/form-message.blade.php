@if($user_as_participant->isNotEmpty())
<!-- Message Form Input -->
<form class="message ajax-store ajax-not-global" action="{{ route('threads.messages.add', $thread->id) }}" method="post">
    @method('PUT')
    @csrf

    <em id="message-textarea-actions-subtle-hint" class="text-info display-none float-right"><strong>Press Enter to send, Shift+Enter for new line.</strong></em>
    <div class="form-group">
        <div id="message" class="overtype-editor"></div>
        <!-- Submit Form Input -->
        <button id="send-message-btn" type="submit" data-text=" " class="btn btn-sm float-right mb-3 mt-2"><i class="material-symbols-rounded">send</i></button>
    </div>

    @if($users->count() > 0)
    <div>
        <select id="recipients-ids" name="recipients[]" multiple="multiple" data-placeholder="Select to add recipient(s)" title="Select to add recipient(s)" class="form-control select">
            <option value="">Select to add recipient(s)</option>
            @foreach($users as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    @endif
</form>

@else
<em class="position-fixed bottom-0 bg-info pr-1 pl-1 right-2">Sorry, you are no longer a participant of this thread. You can't send a message.</em>
@endif
