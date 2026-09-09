@php $assigned_tickets_is_not_empty = Usr::userAssignedTickets()->isNotEmpty(); @endphp
@if ($assigned_tickets_is_not_empty)
    <a href="{{ route('tickets.index_central') }}" class="badge"
        ><span class="material-symbols-rounded mr-1">local_activity</span
        ><span class="font-family-nunito unread-messages-count align-middle">{{ $assigned_tickets->count() }}</span></a>
    <span class="sr-only">unclosed tickets</span>

@else
    <span class="badge"
        ><span class="material-symbols-rounded mr-1">local_activity</span
        ><span class="font-family-nunito unread-messages-count align-middle">0</span></span>
    <span class="sr-only">no unclosed tickets</span>
@endif
