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
                    <table class="table-bordered table">
                        <tbody>
                            <tr>
                                <td class="font-weight-bold">Name</td>
                                <td class="break-all">{{ $book->name }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Class</td>
                                <td class="break-all">{{ $book->my_class->name }} {{ $book->section->name ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Author</td>
                                <td class="break-all">{{ $book->author }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Book Type</td>
                                <td class="break-all">{{ $book->book_type ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Status</td>
                                <td class="break-all">{{ $book->status }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Description</td>
                                <td class="break-all">{{ $book->description ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">URL</td>
                                <td class="break-all">
                                    @if ($book->url)
                                        <a href="{{ $book->url }}" target="_blank">{{ $book->url }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Total Copies</td>
                                <td class="break-all">{{ $book->total_copies }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Issued Copies</td>
                                <td class="break-all">{{ $book->issued_copies }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Available Copies</td>
                                <td class="break-all">{{ $book->total_copies - $book->issued_copies }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Location</td>
                                <td class="break-all">{{ $book->location ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Created At</td>
                                <td class="break-all">{{ Qs::formatDate($book->created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
