<div class="card">
    <div class="card-header header-elements-inline bg-success">
        <h6 class="font-weight-bold card-title">Manage Time Slots - {{ $ttr->name }}</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body collapse">
        @php
        $canEdit = auth()->user()->can($tenant_permissions['time_tables']['edit_time_slot']['permission']);
        $canDelete = auth()->user()->can($tenant_permissions['time_tables']['delete_time_slot']['permission']);
        $hasActions = $canEdit || $canDelete;
        @endphp

        <table id="time_slots_table" class="table datatable-button-html5-columns">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    @if($hasActions)
                    <th class="text-center">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($time_slots as $tms)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $tms->time_from }}</td>
                    <td>{{ $tms->time_to }}</td>
                    @if($hasActions)
                    <td class="text-center">
                        <div class="list-icons">
                            <div class="dropdown">
                                <a class="material-symbols-rounded" href="#" data-toggle="dropdown">lists</a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @if($canEdit)
                                    <a href="{{ route('ts.edit', $tms->id) }}" class="dropdown-item">
                                        <i class="material-symbols-rounded">edit</i> Edit
                                    </a>
                                    @endif
                                    @if($canDelete)
                                    <a id="ts-delete-{{ $tms->id }}" onclick="confirmPermanentDelete(this.id)" href="javascript:;" class="dropdown-item text-danger">
                                        <i class="material-symbols-rounded">delete</i> Delete
                                    </a>
                                    <form method="post" id="item-delete-ts-delete-{{ $tms->id }}" action="{{ route('ts.destroy', $tms->id) }}" class="hidden">
                                        @csrf
                                        @method('delete')
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
