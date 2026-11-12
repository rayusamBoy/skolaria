@extends('layouts.master')

@section('page_title', 'Manage Book Requests')

@section('content')
    @php
        $canEdit = auth()->user()->can('book_requests.edit');
        $canDelete = auth()->user()->can('book_requests.destroy');
        $hasActions = $canEdit || $canDelete;
    @endphp

    <div class="card wmin-100-pcnt w-fit">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Book Requests</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item dropdown">
                    <a href="javascript:;" class="nav-link active dropdown-toggle" data-toggle="dropdown"
                        >Manage Requests</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach ($books as $book)
                            <a href="#book{{ $book->id }}" class="dropdown-item" data-toggle="tab">{{ $book->name }}</a>
                        @endforeach
                    </div>
                </li>

                {{-- Only show the “Add Request” tab if the user can create requests --}}
                @can('book_requests.store')
                    <li class="nav-item">
                        <a href="#new-request" class="nav-link" data-toggle="tab">Add Request</a>
                    </li>
                @endcan
            </ul>

            <div class="tab-content">
                @can('book_requests.store')
                    <div class="tab-pane fade" id="new-request">
                        <form
                            method="post"
                            enctype="multipart/form-data"
                            class="ajax-store"
                            action="{{ route('book_requests.store') }}"
                        >
                            @csrf
                            <div class="row">
                                {{-- Book --}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="book_id">Book: <span class="text-danger">*</span></label>
                                        <select
                                            required
                                            name="book_id"
                                            id="book_id"
                                            class="form-control select select-search"
                                            data-placeholder="Select Book"
                                        >
                                            @foreach ($books as $book)
                                                <option value="{{ $book->id }}">
                                                    {{ $book->name }}
                                                    @if ($book->status) — ({{ $book->status }}) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- Borrower --}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="borrower_id">Borrower: <span class="text-danger">*</span></label>
                                        <select
                                            required
                                            name="borrower_id"
                                            id="borrower_id"
                                            class="form-control select select-search"
                                            data-placeholder="Select User"
                                        >
                                            <option value="">Select User</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- Start Date --}}
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Start Date: <span class="text-danger">*</span></label>
                                        <input
                                            required
                                            autocomplete="off"
                                            name="start_date"
                                            value="{{ old('start_date') }}"
                                            type="text"
                                            class="form-control date-pick"
                                            placeholder="Select Date..."
                                        />
                                    </div>
                                </div>
                                {{-- End Date --}}
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>End Date: <span class="text-danger">*</span></label>
                                        <input
                                            required
                                            autocomplete="off"
                                            name="end_date"
                                            value="{{ old('end_date') }}"
                                            type="text"
                                            class="form-control date-pick"
                                            placeholder="Select Date..."
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button id="ajax-btn" type="submit" class="btn btn-primary">
                                    Submit form <i class="material-symbols-rounded ml-2">send</i>
                                </button>
                            </div>
                        </form>
                    </div>
                @endcan

                @foreach ($books as $book)
                    <div class="tab-pane fade show" id="book{{ $book->id }}">
                        <table class="datatable-button-html5-columns table w-100">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Book</th>
                                    <th>Borrower</th>
                                    <th>Is Returned?</th>
                                    <th>Remarks</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Issued By</th>
                                    @if ($hasActions)
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests->where('book_id', $book->id) as $book_req)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $book_req->book->name }}</td>
                                        <td>
                                            <a href="{{ route('users.show', Qs::hash($book_req->borrower->id)) }}">{{ $book_req->borrower->name }}</a>
                                        </td>
                                        <td>{{ $book_req->is_returned ? 'Yes' : 'No' }}</td>
                                        <td>{{ $book_req->remarks ?? '—' }}</td>
                                        <td>{{ $book_req->start_date }}</td>
                                        <td>{{ $book_req->end_date }}</td>
                                        <td>
                                            <a href="{{ route('users.show', Qs::hash($book_req->user->id)) }}">{{ $book_req->user->name }}</a>
                                        </td>
                                        @if ($hasActions)
                                            <td class="text-center">
                                                <div class="list-icons">
                                                    <div class="dropdown">
                                                        <a
                                                            class="material-symbols-rounded"
                                                            href="#"
                                                            data-toggle="dropdown"
                                                        >lists</a>
                                                        <div class="dropdown-menu dropdown-menu-left">
                                                            @if ($canEdit)
                                                                <a
                                                                    href="{{ route('book_requests.edit', Qs::hash($book_req->id)) }}"
                                                                    class="dropdown-item"
                                                                >
                                                                    <i class="material-symbols-rounded">edit</i> Edit
                                                                </a>
                                                            @endif
                                                            @if ($canDelete)
                                                                <a
                                                                    id="br-delete-{{ Qs::hash($book_req->id) }}"
                                                                    onclick="confirmDelete(this.id)"
                                                                    href="javascript:;"
                                                                    class="dropdown-item text-danger"
                                                                >
                                                                    <i class="material-symbols-rounded">delete</i>
                                                                    Delete
                                                                </a>
                                                                <form
                                                                    method="post"
                                                                    id="item-delete-br-delete-{{ Qs::hash($book_req->id) }}"
                                                                    action="{{ route('book_requests.destroy', Qs::hash($book_req->id)) }}"
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
        </div>
    </div>

@endsection
