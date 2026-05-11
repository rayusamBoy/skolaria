<?php

namespace App\Http\Controllers;

use App\Helpers\Usr;
use App\Http\Requests\Support\TicketCreate;
use App\Http\Requests\Support\TicketPropertiesCreate;
use App\Repositories\TicketRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class TicketController extends Controller
{
    protected TicketRepo $ticket;
    protected UserRepo $user;

    public function __construct(TicketRepo $ticket, UserRepo $user)
    {
        $this->permissionMap = config('permissions.names.tenant.tickets');
        parent::__construct();
        $this->ticket = $ticket;
        $this->user = $user;
    }

    /**
     * Priorities valid for a ticket.
     */
    private function getPriorities(): array
    {
        return ['low', 'medium', 'high'];
    }

    /**
     * Departments valid for a ticket.
     */
    private function getDepartments(): array
    {
        return ['help desk', 'sales', 'support', 'technical'];
    }

    /**
     * Get all tickets belonging to the current tenant and user.
     */
    private function getTenantUserTickets()
    {
        return $this->ticket->getTicketsByConditions([
            'tenant_id' => tenant()?->getTenantKey(),
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Display a listing of tenant tickets.
     */
    public function index(): View
    {
        $tickets = $this->getTenantUserTickets();

        $data['active_tickets'] = $tickets->where('is_archived', false);
        $data['archived_tickets'] = $tickets->where('is_archived', true);
        $data['departments'] = $this->getDepartments();
        $data['priorities'] = $this->getPriorities();
        $data['categories'] = $this->ticket->getCategories();
        $data['labels'] = $this->ticket->getLabels();
        $data['is_head'] = Usr::userIsHead();

        return view('pages.support.tickets.index', $data);
    }

    /**
     * Display the replies for a specific tenant ticket.
     */
    public function reply(string $ticket_id): View|RedirectResponse
    {
        $ticket_id = Usr::decodeHash($ticket_id);

        $tickets = $this->ticket->getTicketsByConditions([
            'tenant_id' => tenant()?->getTenantKey(),
            'user_id' => auth()->id(),
            'id' => $ticket_id,
        ]);

        if ($tickets->isEmpty()) {
            return back()->with('flash_warning', __('msg.rnf'));
        }

        $ticket = $tickets->first();
        $tenant_user = json_decode($ticket->tenant);

        $data['ticket'] = $ticket;
        $data['tenant_user'] = $ticket->get_tenant_user($tenant_user->tenancy_db_name, $ticket->user_id);
        $data['messages'] = $this->ticket->getMessagesQuery([
            'user_id' => auth()->id(),
            'ticket_id' => $ticket_id,
        ])->orderByDesc('created_at')->get();
        $data['priorities'] = $this->getPriorities();
        $data['categories'] = $this->ticket->getCategories();
        $data['labels'] = $this->ticket->getLabels()->pluck('name')->toArray();

        return view('pages.support.tickets.reply', $data);
    }

    /**
     * Display the central answer form for a ticket.
     */
    public function answer(string $ticket_id): View|RedirectResponse
    {
        $ticket_id = Usr::decodeHash($ticket_id);

        $where_ticket = Usr::userIsHead()
            ? ['id' => $ticket_id]
            : ['id' => $ticket_id, 'assigned_to' => auth()->id()];

        $tickets = $this->ticket->getTicketsByConditions($where_ticket);

        if ($tickets->isEmpty()) {
            return back()->with('flash_warning', __('msg.rnf'));
        }

        $ticket = $tickets->first();
        $tenant_user = json_decode($ticket->tenant);

        $data['ticket'] = $ticket;
        $data['tenant_user'] = $ticket->get_tenant_user($tenant_user->tenancy_db_name, $ticket->user_id);
        $data['messages'] = $this->ticket->getMessagesQuery(['ticket_id' => $ticket_id])
            ->orderByDesc('created_at')->get();
        $data['priorities'] = $this->getPriorities();
        $data['categories'] = $this->ticket->getCategories();
        $data['labels'] = $this->ticket->getLabels()->pluck('name')->toArray();

        return view('pages.central.support.tickets.answer', $data);
    }

    /**
     * Display a listing of central tickets.
     */
    public function index_central(): View
    {
        $where = ['assigned_to' => auth()->id(), 'is_archived' => false];
        $data['tickets'] = Usr::userIsHead()
            ? $this->ticket->getAllTickets()
            : $this->ticket->getTicketsByConditions($where)->where('status', '<>', 'closed');
        $data['users'] = $this->user->getAllUsers();
        $data['categories'] = $this->ticket->getCategories();
        $data['labels'] = $this->ticket->getLabels();

        return view('pages.central.support.tickets.index', $data);
    }

    /**
     * Store a newly created ticket (tenant side).
     */
    public function store(TicketCreate $request): RedirectResponse
    {
        $ticket_data = $request->only(['department', 'priority', 'subject', 'category_id']);
        $ticket_data['tenant_id'] = tenant()?->getTenantKey();
        $ticket_data['user_id'] = auth()->id();
        $labels_ids = $request->labels_ids ?? [];

        try {
            $ticket = $this->ticket->createTicket($ticket_data);

            $is_from_tenant = Usr::tenancyInitialized();
            $this->ticket->createMessage([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'body' => $request->message,
                'is_from_tenant' => $is_from_tenant,
            ]);

            if ($request->filled('labels_ids')) {
                $this->ticket->assignLabelsToTicket($ticket->id, $labels_ids);
            }
        } catch (\Exception $e) {
            // If ticket was created, delete it to rollback
            if (isset($ticket)) {
                $this->ticket->deleteTicket($ticket->id);
            }
            return back()->with('flash_error', __('msg.store_error'));
        }

        return back()->with('flash_success', __('msg.store_ok'));
    }

    /**
     * Store a new message in an existing ticket (tenant).
     */
    public function store_message(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'message' => 'required|string|max:400',
        ])->validate();

        $is_from_tenant = Usr::tenancyInitialized();
        $this->ticket->createMessage([
            'ticket_id' => $request->ticket_id,
            'user_id' => auth()->id(),
            'body' => $request->message,
            'is_from_tenant' => $is_from_tenant,
        ]);
        $this->ticket->updateTicket($request->ticket_id, ['status' => 'replied']);

        return back()->with('flash_success', __('msg.store_ok'));
    }

    /**
     * Store an operator (central) message in a ticket.
     */
    public function store_operator_message(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'message' => 'required|string|max:400',
        ])->validate();

        $is_from_tenant = Usr::tenancyInitialized();
        $this->ticket->createMessage([
            'ticket_id' => $request->ticket_id,
            'user_id' => auth()->id(),
            'body' => $request->message,
            'is_from_tenant' => $is_from_tenant,
        ]);
        $this->ticket->updateTicket($request->ticket_id, ['status' => 'answered']);

        return back()->with('flash_success', __('msg.store_ok'));
    }

    /**
     * Update ticket properties (categories and labels).
     */
    public function update_properties(TicketPropertiesCreate $request): JsonResponse
    {
        // Update categories
        $category_names = $request->input('categories.name') ?? [];
        $this->ticket->deleteCategoryWhereNotIn('name', $category_names);

        if ($request->has('categories')) {
            $descriptions = $request->input('categories.description');
            $visibilities = $request->input('categories.is_visible');

            foreach ($category_names as $index => $name) {
                $this->ticket->updateOrCreateCategory(['name' => $name], [
                    'description' => $descriptions[$index],
                    'is_visible' => $visibilities[$index],
                ]);
            }
        }

        // Update labels
        $label_names = $request->input('labels.name') ?? [];
        $this->ticket->deleteLabelWhereNotIn('name', $label_names);

        if ($request->has('labels')) {
            $descriptions = $request->input('labels.description');
            $visibilities = $request->input('labels.is_visible');

            foreach ($label_names as $index => $name) {
                $this->ticket->updateOrCreateLabel(['name' => $name], [
                    'description' => $descriptions[$index],
                    'is_visible' => $visibilities[$index],
                ]);
            }
        }

        return Usr::jsonUpdateOk();
    }

    /**
     * Close a tenant ticket.
     */
    public function close_ticket(string $ticket_id): RedirectResponse
    {
        $user_ticket_ids = $this->getTenantUserTickets()
            ->where('is_archived', false)
            ->pluck('id')
            ->toArray();
        $ticket_id = Usr::decodeHash($ticket_id);

        if (!\in_array($ticket_id, $user_ticket_ids)) {
            return back()->with('flash_danger', __('msg.denied'));
        }

        $this->ticket->updateTicket($ticket_id, ['status' => 'closed']);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Archive a tenant ticket (must be closed first).
     */
    public function archive_ticket(string $ticket_id): RedirectResponse
    {
        $user_ticket_ids = $this->getTenantUserTickets()
            ->where('is_archived', false)
            ->pluck('id')
            ->toArray();

        if (!\in_array($ticket_id, $user_ticket_ids)) {
            return back()->with('flash_danger', __('msg.denied'));
        }

        $ticket = $this->ticket->findTicket($ticket_id);
        if (!$ticket->is_closed()) {
            return back()->with('flash_warning', __('msg.close_ticket_before_archive'));
        }

        $this->ticket->updateTicket($ticket_id, ['is_archived' => true]);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Unarchive a tenant ticket.
     */
    public function unarchive_ticket(string $ticket_id): RedirectResponse
    {
        $user_ticket_ids = $this->getTenantUserTickets()
            ->where('is_archived', true)
            ->pluck('id')
            ->toArray();

        if (!\in_array($ticket_id, $user_ticket_ids)) {
            return back()->with('flash_danger', __('msg.denied'));
        }

        $this->ticket->updateTicket($ticket_id, ['is_archived' => false]);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Delete a ticket (central).
     */
    public function delete(string $id): RedirectResponse
    {
        $this->ticket->deleteTicket($id);

        return Usr::redirectDeleteSuccess('tickets.index_central');
    }

    /**
     * Lock a ticket.
     */
    public function lock_ticket(string $ticket_id): RedirectResponse
    {
        $this->ticket->updateTicket($ticket_id, ['is_locked' => true]);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Unlock a ticket.
     */
    public function unlock_ticket(string $ticket_id): RedirectResponse
    {
        $this->ticket->updateTicket($ticket_id, ['is_locked' => false]);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Update a ticket's assignee.
     */
    public function update_assignee(Request $request, $ticket_id): RedirectResponse
    {
        $this->ticket->updateTicket($ticket_id, $request->only('assigned_to'));

        return back()->with('flash_success', __('msg.update_ok'));
    }
}
