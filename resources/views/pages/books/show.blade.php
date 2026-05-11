@extends('layouts.master')

@section('page_title', "Book Details - {$book->name}")

@section('content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Book Details</h6>
            </div>

            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8">{{ $book->name }}</dd>

                    <dt class="col-sm-4">Class</dt>
                    <dd class="col-sm-8">{{ $book->my_class->name }} {{ $book->section->name ?? '' }}</dd>

                    <dt class="col-sm-4">Author</dt>
                    <dd class="col-sm-8">{{ $book->author }}</dd>

                    <dt class="col-sm-4">Book Type</dt>
                    <dd class="col-sm-8">{{ $book->book_type }}</dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">{{ $book->status }}</dd>

                    <dt class="col-sm-4">Description</dt>
                    <dd class="col-sm-8">{{ $book->description ?: '—' }}</dd>

                    <dt class="col-sm-4">URL</dt>
                    <dd class="col-sm-8">
                        @if($book->url)
                        <a href="{{ $book->url }}" target="_blank">{{ $book->url }}</a>
                        @else
                        —
                        @endif
                    </dd>

                    <dt class="col-sm-4">Total Copies</dt>
                    <dd class="col-sm-8">{{ $book->total_copies }}</dd>

                    <dt class="col-sm-4">Issued Copies</dt>
                    <dd class="col-sm-8">{{ $book->issued_copies }}</dd>

                    <dt class="col-sm-4">Available Copies</dt>
                    <dd class="col-sm-8">{{ $book->total_copies - $book->issued_copies }}</dd>

                    <dt class="col-sm-4">Location</dt>
                    <dd class="col-sm-8">{{ $book->location ?: '—' }}</dd>

                    <dt class="col-sm-4">Created At</dt>
                    <dd class="col-sm-8">{{ Qs::formatDate($book->created_at) }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

@endsection
