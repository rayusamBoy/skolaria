@extends('layouts.master')

@section('page_title', 'Manage Books')

@section('content')

@php
$canEdit = auth()->user()->can($tenant_permissions['books']['edit']['permission']);
$canDelete = auth()->user()->can($tenant_permissions['books']['destroy']['permission']);
$hasActions = $canEdit || $canDelete;
@endphp

<div class="card w-fit wmin-100-pcnt">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Books</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item dropdown">
                <a href="javascript:;" class="nav-link dropdown-toggle active" data-toggle="dropdown">Manage Books</a>
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach($my_classes as $mc)
                    <a href="#book-{{ $mc->id }}" class="dropdown-item" data-toggle="tab">{{ $mc->name }}</a>
                    @endforeach
                </div>
            </li>

            @can($tenant_permissions['books']['store']['permission'])
            <li class="nav-item">
                <a href="#new-book" class="nav-link" data-toggle="tab">Add a Book</a>
            </li>
            @endcan
        </ul>

        <div class="tab-content">
            {{-- Add New Book Form --}}
            @can($tenant_permissions['books']['store']['permission'])
            <div class="tab-pane fade" id="new-book">
                <form method="post" enctype="multipart/form-data" class="ajax-store" action="{{ route('books.store') }}">
                    @csrf
                    <div class="row">
                        {{-- Class --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="my_class_id">Class: <span class="text-danger">*</span></label>
                                <select required name="my_class_id" id="my_class_id" class="form-control select" data-placeholder="Select Class">
                                    @foreach($my_classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- Name --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Name: <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Book Name" required>
                            </div>
                        </div>
                        {{-- Description --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Description:</label>
                                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {{-- Author --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Author: <span class="text-danger">*</span></label>
                                <input type="text" name="author" value="{{ old('author') }}" class="form-control" placeholder="Author or Authors" required>
                            </div>
                        </div>
                        {{-- Type --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Book Type:</label>
                                <input type="text" name="book_type" value="{{ old('book_type') }}" class="form-control" placeholder="Book Type">
                            </div>
                        </div>
                        {{-- URL --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>URL:</label>
                                <input type="url" name="url" value="{{ old('url') }}" class="form-control" placeholder="URL">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {{-- Total Copies --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Total Copies: <span class="text-danger">*</span></label>
                                <input type="number" name="total_copies" value="{{ old('total_copies') }}" min="1" class="form-control" placeholder="Eg., 123" required>
                            </div>
                        </div>
                        {{-- Status --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status:</label>
                                <select name="status" id="status" class="form-control select" required data-placeholder="Select...">
                                    @foreach(Usr::getBookStatuses() as $key => $value)
                                    <option value="{{ $key }}" title="{{ $value }}" @selected(old('status')==$key)>{{ $key }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- Location --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Location:</label>
                                <input type="text" name="location" value="{{ old('location') }}" class="form-control" placeholder="Location">
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <button id="ajax-btn" type="submit" class="btn btn-primary">
                            Submit <i class="material-symbols-rounded ml-2">send</i>
                        </button>
                    </div>
                </form>
            </div>
            @endcan

            {{-- Book List per Class --}}
            @foreach($my_classes as $mc)
            <div class="tab-pane fade show" id="book-{{ $mc->id }}">
                <strong class="status-styled">{{ $mc->name }}</strong>

                <table class="table datatable-button-html5-columns w-100">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Type</th>
                            <th>Copies</th>
                            <th>Status</th>
                            @if($hasActions)
                            <th class="text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($books->where('my_class.id', $mc->id) as $book)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span data-toggle="tooltip" title="{{ $book->description ?? '' }}">
                                    {{ $book->name }} <small class="text-muted">({{ $book->author }})</small>
                                </span>
                            </td>
                            <td>{{ $book->my_class->name }}</td>
                            <td>{{ $book->book_type ?: '—' }}</td>
                            <td>
                                {{ $book->total_copies }} / {{ $book->issued_copies }}
                                <small class="text-muted">(total / issued)</small>
                            </td>
                            <td>{{ $book->status }}</td>
                            @if($hasActions)
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a class="material-symbols-rounded" href="#" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            {{-- If can view then can show --}}
                                            <a href="{{ route('books.show', Qs::hash($book->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">visibility</i> View
                                            </a>
                                            @if($canEdit)
                                            <a href="{{ route('books.edit', Qs::hash($book->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">edit</i> Edit
                                            </a>
                                            @endif
                                            @if($canDelete)
                                            <a id="book-delete-{{ Qs::hash($book->id) }}" onclick="confirmDelete(this.id)" href="javascript:;" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form method="post" id="item-delete-book-delete-{{ Qs::hash($book->id) }}" action="{{ route('books.destroy', Qs::hash($book->id)) }}" class="hidden">
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
