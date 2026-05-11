@php
$isOwnMessage = optional($message->user)->id == auth()->id();
$userName = optional($message->user)->name ?? ($message->user_id ? 'Deleted User' : 'Unknown User');
$userPhoto = optional($message->user)->photo_path;
$userColor = optional($message->user)->message_media_heading_color ?? 'inherit';
$deletor = $message->deletor; // may be null
@endphp

<div class="col-lg-8 offset-lg-2 col-xl-6 offset-xl-3" id="message-{{ $message->id }}">
    <li class="position-relative {{ optional($message->user)->id == auth()->id() ? 'float-right' : 'float-left' }}">
        {{-- Other user's avatar (left side) --}}
        @if (!$isOwnMessage)
        <img src="{{ Usr::getTenancyAwarePhotoUrl($userPhoto) }}" alt="{{ $userName }}" class="rounded-circle f-left">
        @endif

        {{-- Super admin "delete" button for other people's messages --}}
        @if (!$isOwnMessage && $message->deleted_at === null)
        @can($tenant_permissions['messages']['user_delete']['permission'])
        <button data-toggle="popover" data-placement="right" data-html="true" class="material-symbols-rounded position-absolute right-neg-25 text-info-400 opacity-75 opacity-100-on-hover border-0 outline-0 action-btn" data-content='
            <div class="d-flex justify-center">
                <button type="button" data-text="Deleting..." id="{{ $message->id }}"
                        onclick="prepareDeleteUserMessageForm(this.id)" data-toggle="modal"
                        data-target="#confirm-message-delete"
                        class="btn btn-sm p-0 pr-1 pl-1 m-auto text-danger w-100 text-left bg-transparent">
                    <span class="text-danger">Delete</span>
                </button>
            </div>'>
            more_vert
        </button>
        @endcan
        @endif

        {{-- Message bubble --}}
        <div class="{{ $isOwnMessage ? 'right' : 'left' }}">
            <span class="media-body break-all">
                <small class="media-heading" style="color: {{ $userColor }}">
                    {{ $isOwnMessage ? 'Me' : $userName }}
                    @if (!$isOwnMessage && optional($message->user)->roles)
                    ({{ $message->user->roles->pluck('name')->join(', ') }})
                    @endif
                </small>
                <br>

                @if ($message->deleted_at !== null)
                @if ($message->deleted_by == auth()->id())
                <p class="text-muted"><i class="material-symbols-rounded font-size-sm">block</i><i> You deleted this message.</i></p>
                @else
                @if ($deletor && optional($deletor->roles)->pluck('name')->contains(config('roles.default_roles.tenant.superadmin')) && $message->user_id != $message->deleted_by)
                <p class="text-muted"><i class="material-symbols-rounded font-size-sm">block</i><i> {{ "This message was deleted by super admin {$deletor->name}." }}</i></p>
                @else
                <p class="text-muted"><i class="material-symbols-rounded font-size-sm">block</i><i> This message was deleted.</i></p>
                @endif
                @endif
                @else
                {!! Qs::parseMarkdown($message->body) !!}
                <span class="text-muted float-right">
                    <small><i>{{ date('d M Y, h:m:s a', strtotime($message->created_at)) }}</i></small>
                </span>
                @endif
            </span>
        </div>

        {{-- Own message actions and avatar (right side) --}}
        @if ($isOwnMessage)
        @if ($message->deleted_at === null)
        <button data-toggle="popover" data-placement="left" data-html="true" class="material-symbols-rounded position-absolute left-neg-p5rem text-info-400 opacity-75 opacity-100-on-hover border-0 outline-0 action-btn" data-content='
            <div class="d-flex justify-center">
                <button type="button" data-text="Deleting..." id="{{ $message->id }}"
                        onclick="prepareDeleteUserMessageForm(this.id)" data-toggle="modal"
                        data-target="#confirm-message-delete"
                        class="btn btn-sm p-0 pr-1 pl-1 m-auto text-danger w-100 text-left bg-transparent">
                    <span class="text-danger">Delete</span>
                </button>
            </div>'>
            more_vert
        </button>
        @endif

        <span>
            <img src="{{ Usr::getTenancyAwarePhotoUrl($userPhoto) }}" alt="{{ $userName }}" class="rounded-circle f-right">
        </span>
        @endif
    </li>
</div>
