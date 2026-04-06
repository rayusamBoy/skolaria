<div class="{{ $selected ? 'card' : 'card card-collapsed' }}">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Events</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        {{-- only users with manage_events can see tabs and actions --}}
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item">
                <a href="#all-events" class="{{ $selected ? 'nav-link' : 'nav-link active' }}" data-toggle="tab">
                    Manage Events
                </a>
            </li>
            @if($selected && auth()->user()->can($tenant_permissions['schedules']['update_event']['permission']))
            <li class="nav-item">
                <a href="#edit-event" class="{{ $selected ? 'nav-link active' : 'nav-link' }}" data-toggle="tab">
                    Edit Event
                </a>
            </li>
            @endif
        </ul>

        <div class="tab-content">
            {{-- All Events --}}
            <div class="{{ $selected ? 'tab-pane fade' : 'tab-pane fade show active' }}" id="all-events">
                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Year</th>
                            <th>Month</th>
                            <th>Day</th>
                            <th>Status</th>
                            <th>By</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $evt)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="break-all break-spaces">{{ $evt->name }}</td>
                            <td class="break-all break-spaces">{{ $evt->description }}</td>
                            <td>{{ $evt->year }}</td>
                            <td>{{ $evt->month }}</td>
                            <td>{{ $evt->day }}</td>
                            <td>{{ $evt->status }}</td>
                            <td>{{ $evt->user->name }}</td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a class="material-symbols-rounded" href="#" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            {{-- Edit --}}
                                            @can($tenant_permissions['schedules']['edit_event']['permission'])
                                            <a href="{{ route('schedule.events.edit', $evt->id) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">edit</i> Edit
                                            </a>
                                            @endcan
                                            {{-- Delete --}}
                                            @can($tenant_permissions['schedules']['delete_event']['permission'])
                                            <a id="{{ $evt->id }}" onclick="confirmDelete(this.id)" href="javascript:;" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form method="get" id="item-delete-{{ $evt->id }}" action="{{ route('schedule.events.delete', $evt->id) }}" class="d-none">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $evt->id }}">
                                            </form>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($selected && auth()->user()->can($tenant_permissions['schedules']['update_event']['permission']))
            {{-- Edit Event --}}
            <div class="tab-pane fade show active" id="edit-event">
                <form class="ajax-update" method="post" action="{{ route('schedule.events.update', $event->id) }}">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-5">
                            <label for="name" class="font-weight-semibold">Name <span class="text-danger">*</span></label>
                            <input maxlength="50" name="name" required type="text" class="form-control" value="{{ $event->name }}" id="name">
                        </div>
                        <div class="form-group col-md-7">
                            <label for="description" class="font-weight-semibold">Description <span class="text-danger">*</span></label>
                            <input maxlength="150" name="description" required type="text" class="form-control" value="{{ $event->description }}" id="description">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="year" class="font-weight-semibold">Year <span class="text-danger">*</span></label>
                            <input name="year" required type="number" min="2000" class="form-control" value="{{ $event->year }}" id="year">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="month" class="font-weight-semibold">Month <span class="text-danger">*</span></label>
                            <input name="month" required type="number" min="1" max="12" class="form-control" value="{{ $event->month }}" id="month">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="day" class="font-weight-semibold">Day <span class="text-danger">*</span></label>
                            <input name="day" required type="number" min="1" max="31" class="form-control" value="{{ $event->day }}" id="day">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="status" class="font-weight-semibold">Status</label>
                            <select required class="form-control select" name="status" id="status">
                                @foreach(Evt::getStatuses() as $st)
                                <option @selected($event->status == $st) value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" id="ajax-btn" class="btn btn-primary">
                            Submit form <i class="material-symbols-rounded ml-2">send</i>
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
