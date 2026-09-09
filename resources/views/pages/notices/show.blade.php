{{--Unviewed Notices--}}
@if (isset($unviewed_notices) && $unviewed_notices->isNotEmpty())
    <div
        class="card m-0 border-0"
        id="unviewed"
        data-pagination-meta="{{ $unviewed_notices->total() . ':' . $unviewed_notices->perPage() . ':' . $unviewed_notices->lastPage() }}"
    >
        @foreach ($unviewed_notices->groupBy('user.name') as $name => $values)
            <div class="card-header">
                <span class="status-styled float-left pr-10">Unviewed</span
                ><i class="status-styled float-right">Posted by {{ ucwords($name) }}</i>
            </div>
            <div class="card-body p-1">
                @foreach ($values as $untc)
                    <div id="accordion-{{ $untc->id }}">
                        <div class="card mb-1 border-0">
                            <div class="card-header" id="headingOne-{{ $untc->id }}">
                                <h5 class="d-flex mb-0">
                                    <span class="text-muted iteration font-size-xs pr-1">{{ $loop->iteration }}</span>
                                    <button
                                        id="{{ $untc->id }}"
                                        class="btn btn-link border-left-1 border-left-info unviewed w-100 p-0 pl-1"
                                        data-toggle="collapse"
                                        data-target="#collapseOne-{{ $untc->id }}"
                                        aria-expanded="true"
                                        aria-controls="collapseOne"
                                    >
                                        <span class="title float-left pr-10">{{ $untc->title }}</span
                                        ><small class="text-muted float-right"
                                            >posted {{ $untc->created_at->diffForHumans() }}</small>
                                    </button>
                                </h5>
                            </div>

                            <div
                                id="collapseOne-{{ $untc->id }}"
                                class="collapse"
                                aria-labelledby="headingOne-{{ $untc->id }}"
                                data-parent="#accordion-{{ $untc->id }}"
                            >
                                <div class="card-body">{{ $untc->body }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="p-1">
            <span class="font-size-sm text-muted status-styled">Showing {{ Qs::getPaginationFromToTotalAsString($unviewed_notices) }} unviewed notices</span>
            <span class="float-right">{{ $unviewed_notices->onEachSide(5)->links() }}</span>
        </div>
    </div>
@endif

{{--Viewed Notices--}}
@if (isset($viewed_notices) && $viewed_notices->isNotEmpty())
    <div
        class="card m-0 border-0"
        id="viewed"
        data-pagination-meta="{{ $viewed_notices->total() . ':' . $viewed_notices->perPage() . ':' . $viewed_notices->lastPage() }}"
    >
        @foreach ($viewed_notices->groupBy('user.name') as $name => $values)
            <div class="card-header">
                <span class="status-styled float-left pr-10">Viewed</span
                ><i class="status-styled float-right">Posted by {{ ucwords($name) }}</i>
            </div>
            <div class="card-body p-1">
                @foreach ($values as $vntc)
                    <div id="accordion-{{ $vntc->id }}">
                        <div class="card mb-1 border-0">
                            <div class="card-header" id="headingOne-{{ $vntc->id }}">
                                <h5 class="d-flex mb-0">
                                    <button
                                        id="{{ $vntc->id }}"
                                        class="btn btn-link border-left-1 border-left-info w-100 p-0 pl-1"
                                        data-toggle="collapse"
                                        data-target="#collapseOne-{{ $vntc->id }}"
                                        aria-expanded="true"
                                        aria-controls="collapseOne"
                                    >
                                        <span
                                            class="title break-spaces float-left pr-10 text-left break-all"
                                            >{{ $vntc->title }}</span
                                        ><small class="text-muted float-right"
                                            >posted {{ $vntc->created_at->diffForHumans() }}</small>
                                    </button>
                                </h5>
                            </div>

                            <div
                                id="collapseOne-{{ $vntc->id }}"
                                class="collapse"
                                aria-labelledby="headingOne-{{ $vntc->id }}"
                                data-parent="#accordion-{{ $vntc->id }}"
                            >
                                <div class="card-body">{{ $vntc->body }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="p-1">
            <span class="font-size-sm text-muted status-styled">Showing {{ Qs::getPaginationFromToTotalAsString($viewed_notices) }} viewed notices</span>
            <span class="float-right">{{ $viewed_notices->onEachSide(5)->links() }}</span>
        </div>
    </div>
@endif
