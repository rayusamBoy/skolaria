@extends('layouts.master')

@section('page_title', 'Manage Payments')

@section('content')
    @php
        $canEdit = auth()->user()->can($tenant_permissions['payments']['edit']['permission']);
        $canDelete = auth()->user()->can($tenant_permissions['payments']['destroy']['permission']);
        $hasActions = $canEdit || $canDelete;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="material-symbols-rounded">price_check</i> Select year</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            @can($tenant_permissions['payments']['select_year']['permission'])
                <form method="post" action="{{ route('payments.select_year') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 offset-md-3">
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <label for="year" class="col-form-label font-weight-bold"
                                            >Select Year <span class="text-danger">*</span></label>
                                        <select
                                            data-placeholder="Select Year"
                                            required
                                            id="year"
                                            name="year"
                                            class="form-control select"
                                        >
                                            @foreach ($years as $yr)
                                                <option
                                                    @selected($selected && $year == $yr->year)
                                                    value="{{ $yr->year }}"
                                                >
                                                    {{ $yr->year }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2 mt-4">
                                    <div class="mt-1 text-right">
                                        <button type="submit" class="btn btn-primary">
                                            Submit <i class="material-symbols-rounded ml-2">send</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endcan
        </div>
    </div>

    @if ($selected)
        <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Manage Payments for {{ $year }} Session</h6>
                {!! Qs::getPanelOptionsHtml() !!}
            </div>

            <div class="card-body">
                @can($tenant_permissions['payments']['index']['permission'])
                    <ul class="nav nav-tabs nav-tabs-highlight">
                        <li class="nav-item">
                            <a href="#all-payments" class="nav-link active" data-toggle="tab">All Payments</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="javascript:;" class="nav-link dropdown-toggle" data-toggle="dropdown"
                                >Class Payments</a>
                            <div class="dropdown-menu dropdown-menu-right">
                                @foreach ($my_classes as $mc)
                                    <a
                                        href="#pc-{{ $mc->id }}"
                                        class="dropdown-item"
                                        data-toggle="tab"
                                    >{{ $mc->name }}</a>
                                @endforeach
                            </div>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- All Payments --}}
                        <div class="tab-pane fade show active" id="all-payments">
                            <table class="datatable-button-html5-columns table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Amount</th>
                                        <th>Ref No</th>
                                        <th>Class</th>
                                        <th>Method</th>
                                        <th>Info</th>
                                        @if ($hasActions)
                                            <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payments as $p)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $p->title }}</td>
                                            <td>{{ $p->amount }}</td>
                                            <td>{{ $p->reference_number }}</td>
                                            <td>{{ $p->my_class_id ? $p->my_class->name : 'All' }}</td>
                                            <td>{{ ucwords($p->method) }}</td>
                                            <td>{{ $p->description }}</td>
                                            @if ($hasActions)
                                                <td class="text-center">
                                                    <div class="list-icons">
                                                        <div class="dropdown">
                                                            <a
                                                                class="material-symbols-rounded"
                                                                href="javascript:;"
                                                                data-toggle="dropdown"
                                                            >lists</a>
                                                            <div class="dropdown-menu dropdown-menu-left">
                                                                @if ($canEdit)
                                                                    <a
                                                                        href="{{ route('payments.edit', $p->id) }}"
                                                                        class="dropdown-item"
                                                                    >
                                                                        <i class="material-symbols-rounded">edit</i>
                                                                        Edit
                                                                    </a>
                                                                @endif
                                                                @if ($canDelete)
                                                                    <a
                                                                        id="{{ $p->id }}"
                                                                        onclick="confirmDelete(this.id)"
                                                                        href="javascript:;"
                                                                        class="dropdown-item text-danger"
                                                                    >
                                                                        <i class="material-symbols-rounded">delete</i>
                                                                        Delete
                                                                    </a>
                                                                    <form
                                                                        method="post"
                                                                        id="item-delete-{{ $p->id }}"
                                                                        action="{{ route('payments.destroy', $p->id) }}"
                                                                        class="hidden"
                                                                    >
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

                        {{-- Per Class --}}
                        @foreach ($my_classes as $mc)
                            <div class="tab-pane fade" id="pc-{{ $mc->id }}">
                                <table class="datatable-button-html5-columns table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>Amount</th>
                                            <th>Ref No</th>
                                            <th>Class</th>
                                            <th>Method</th>
                                            <th>Info</th>
                                            @if ($hasActions)
                                                <th class="text-center">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($payments->where('my_class_id', $mc->id) as $p)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $p->title }}</td>
                                                <td>{{ $p->amount }}</td>
                                                <td>{{ $p->reference_number }}</td>
                                                <td>{{ $p->my_class->name }}</td>
                                                <td>{{ ucwords($p->method) }}</td>
                                                <td>{{ $p->description }}</td>
                                                @if ($hasActions)
                                                    <td class="text-center">
                                                        <div class="list-icons">
                                                            <div class="dropdown">
                                                                <a
                                                                    class="material-symbols-rounded"
                                                                    href="javascript:;"
                                                                    data-toggle="dropdown"
                                                                >lists</a>
                                                                <div class="dropdown-menu dropdown-menu-left">
                                                                    @if ($canEdit)
                                                                        <a
                                                                            href="{{ route('payments.edit', $p->id) }}"
                                                                            class="dropdown-item"
                                                                            ><i class="material-symbols-rounded"
                                                                                >edit</i>
                                                                            Edit</a>
                                                                    @endif
                                                                    @if ($canDelete)
                                                                        <a
                                                                            id="{{ $p->id }}"
                                                                            onclick="confirmDelete(this.id)"
                                                                            href="javascript:;"
                                                                            class="dropdown-item text-danger"
                                                                            ><i class="material-symbols-rounded"
                                                                                >delete</i>
                                                                            Delete</a>
                                                                        <form
                                                                            method="post"
                                                                            id="item-delete-{{ $p->id }}"
                                                                            action="{{ route('payments.destroy', $p->id) }}"
                                                                            class="hidden"
                                                                        >
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
                        @endforeach
                    </div>
                @endcan
            </div>
        </div>
    @endif

    {{-- Payments List Ends --}}

@endsection
