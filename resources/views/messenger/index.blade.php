@extends('layouts.master')

@section('page_title', 'Message Threads')

@section('content')
    @include('messenger.partials.flash')

    @each('messenger.partials.thread', $threads, 'thread', 'messenger.partials.no-threads')

    @can('message_threads.store')
        @include('messenger.create')
        <button
            type="button"
            id="new-message"
            onclick="showFormCard()"
            class="btn btn-success btn-sm position-fixed bottom-p5em right-3 float-right"
        >
            New Message thread
        </button>
    @endcan
@stop
