@extends('layouts.master')

@section('page_title', 'Manage Books')

@section('content')

<div class="card w-fit wmin-100-pcnt">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Books</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item">
                <a href="#new-book" class="nav-link active" data-toggle="tab">Add a Book</a>
            </li>

            @if($books->isNotEmpty())
            <li class="nav-item dropdown">
                <a href="javascript:;" class="nav-link dropdown-toggle" data-toggle="dropdown">Manage Books</a>
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach($books->groupBy('my_class.id') as $group)
                    <a href="#grp-{{ $group->first()->my_class->id }}" class="dropdown-item" data-toggle="tab">
                        {{ $group->first()->my_class->name }}
                    </a>
                    @endforeach
                </div>
            </li>
            @endif
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="new-book">
                <form method="post" enctype="multipart/form-data" class="ajax-store" action="{{ route('books.store') }}" data-fouc>
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
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control text-capitalize" placeholder="Book Name" required>
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
                                    <option value="{{ $key }}" title="{{ $value }}" @selected(old('status')==$key)>
                                        {{ $key }}
                                    </option>
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

            @foreach($books->groupBy('my_class.id') as $group)
            <div class="tab-pane fade" id="grp-{{ $group->first()->my_class->id }}">
                <table class="table datatable-button-html5-columns w-100">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Author</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Class</th>
                            <th>Type</th>
                            <th>URL</th>
                            <th>Total Copies</th>
                            <th>Issued Copies</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group as $book)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $book->author }}</td>
                            <td>{{ $book->name }}</td>
                            <td>{{ $book->description }}</td>
                            <td>{{ $book->my_class->name }}</td>
                            <td>{{ $book->book_type }}</td>
                            <td>{{ $book->url }}</td>
                            <td>{{ $book->total_copies }}</td>
                            <td>{{ $book->issued_copies }}</td>
                            <td>{{ $book->location }}</td>
                            <td>{{ $book->status }}</td>
                            <td>{{ Qs::fullDateTimeFormat($book->created_at) }}</td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a class="material-symbols-rounded" href="#" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            <a href="{{ route('books.edit', Qs::hash($book->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">edit</i> Edit
                                            </a>

                                            <a id="book-delete-{{ Qs::hash($book->id) }}" onclick="confirmDelete(this.id)" href="javascript:;" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form method="post" id="item-delete-book-delete-{{ Qs::hash($book->id) }}" action="{{ route('books.destroy', Qs::hash($book->id)) }}" class="hidden">
                                                @csrf
                                                @method('delete')
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
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
