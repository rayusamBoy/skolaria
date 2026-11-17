<div id="page-header" class="page-header">
    <div class="page-header-content header-elements-md-inline">
        <div class="page-title d-flex">
            <h4 class="d-flex" id="page-title">
                <i class="material-symbols-rounded mr-2">dashboard_customize</i>
                <span class="font-weight-semibold">@yield('page_title')</span>
            </h4>
            <a href="javascript:;" class="header-elements-toggle text-default d-md-none"
                ><i class="material-symbols-rounded">more_vert</i></a>
        </div>

        <div class="header-elements d-none">
            <div class="d-flex justify-content-center">
                <a
                    href="javascript:;"
                    data-toggle="popover"
                    data-placement="bottom"
                    data-html="true"
                    data-content="
                    <small>
                        <span class='text-default'>{{ config('app.name') }}</span><br />
                        <em><i class='material-symbols-rounded'>arrow_right</i><span class='text-default'>version: {{ config('app.version') }}</span></em><br />
                    </small>"
                    class="btn btn-link btn-float"
                >
                    <i class="material-symbols-rounded text-primary">info</i>
                    <span class="d-none d-sm-block">About</span>
                </a>

                @if ($is_tenant_context)
                    {{-- Analytics --}}
                    @can('analytics.view')
                        <a href="{{ route('analytics.index') }}" class="btn btn-link btn-float text-default"
                            ><i class="material-symbols-rounded text-primary">analytics</i>
                            <span class="d-none d-sm-block">Analytics</span></a>
                    @endcan
                    {{-- Statistics --}}
                    @can('statistics.view')
                        <a href="{{ route('statistics.index') }}" class="btn btn-link btn-float text-default"
                            ><i class="material-symbols-rounded text-primary">query_stats</i
                            ><span class="d-none d-sm-block">Statistics</span></a>
                    @endcan
                    {{-- Invoices --}}
                    {{-- <a href="javascript:;" class="btn btn-link btn-float text-default"><i class="material-symbols-rounded text-primary">calculate</i> <span class="d-none d-sm-block">Invoices</span></a> --}}
                    {{-- Schedule --}}
                    @can('schedules.view')
                        <a href="{{ route('schedule.index') }}" class="btn btn-link btn-float text-default"
                            ><i class="material-symbols-rounded text-primary">calendar_month</i>
                            <span class="d-none d-sm-block">Schedule</span></a>
                    @endcan
                    <a
                        href="{{ auth()->user()->can('settings.view') ? route('settings.index') : '' }}"
                        class="btn btn-link btn-float text-default"
                        ><i class="material-symbols-rounded text-primary d-none d-sm-block">arrow_downward_alt</i>
                        <span class="font-weight-semibold d-flex" id="current-session"
                            ><span class="d-none d-sm-block pr-1">Current Session:</span
                            >{{ $settings->where('key', 'current_session')->value('value') }}</span
                        ></a>
                @endif
            </div>
        </div>
    </div>

    <div class="breadcrumb-line header-elements-md-inline">
        <div class="d-flex">
            {{ Breadcrumbs::render() }}
            <a href="javascript:;" class="header-elements-toggle text-default d-md-none"
                ><span class="material-symbols-rounded">more_vert</span></a>
        </div>

        <div class="header-elements d-none">
            <div class="breadcrumb justify-content-center" id="breadcrumb">
                <div class="breadcrumb-elements-item d-none d-md-block">
                    @include('pages.notifications.badge')
                </div>

                @if ($is_tenant_context)
                    @can('message_threads.view')
                        <a
                            class="breadcrumb-elements-item d-none d-md-block"
                            data-toggle="tooltip"
                            title="Messages"
                            href="{{ route('message_threads.index') }}"
                        >
                            @include('messenger.unread-count')
                        </a>
                    @endcan
                    @can('tickets.view')
                        <div class="breadcrumb-elements-item dropdown">
                            <a href="javascript:;" class="breadcrumb-elements-item" data-toggle="dropdown">
                                <i class="material-symbols-rounded mr-1">support</i>
                                <span class="align-middle">Support</span>
                                <i class="material-symbols-rounded m-auto">keyboard_arrow_down</i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-left">
                                <a target="_blank" href="#" class="dropdown-item"
                                    ><i class="material-symbols-rounded">quick_reference</i>Knowledgebase</a>
                                <a
                                    href="{{ route('tickets.index') }}"
                                    class="dropdown-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}"
                                ><i class="material-symbols-rounded">local_activity</i>Tickets</a>
                            </div>
                        </div>
                    @endcan
                @elseif (auth()->user()->can('tickets.index_central'))
                    <div class="breadcrumb-elements-item d-none d-md-block">
                        @include('pages.central.support.tickets.badge')
                    </div>
                @endif

                @if ($is_tenant_context)
                    @can('central.soft_deletes.bin')
                        <div class="breadcrumb-elements-item">
                            <a
                                href="{{ route('bin') }}"
                                @if (request()->routeIs('bin')) class="breadcrumb-elements-item is-disabled d-block" @else class="breadcrumb-elements-item" @endif
                            >
                                <i class="material-symbols-rounded mr-1 align-middle">delete_forever</i>
                                <span class="align-middle">Bin</span>
                            </a>
                        </div>
                    @endcan
                @else
                    @can('central.soft_deletes.bin')
                        <div class="breadcrumb-elements-item">
                            <a
                                href="{{ route('central_bin') }}"
                                @if (request()->routeIs('central_bin')) class="breadcrumb-elements-item is-disabled d-block" @else class="breadcrumb-elements-item" @endif
                            >
                                <i class="material-symbols-rounded mr-1 align-middle">delete_forever</i>
                                <span class="align-middle">Bin</span>
                            </a>
                        </div>
                    @endcan
                @endif
            </div>
        </div>
    </div>
</div>
