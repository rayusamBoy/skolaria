<div id="page-header" class="page-header">
    <div class="page-header-content header-elements-md-inline">
        <div class="page-title d-flex">
            <h4 class="d-flex" id="page-title"><i class="material-symbols-rounded mr-2">dashboard_customize</i> <span class="font-weight-semibold">@yield('page_title')</span></h4>
            <a href="javascript:;" class="header-elements-toggle text-default d-md-none"><i class="material-symbols-rounded">more_vert</i></a>
        </div>

        <div class="header-elements d-none">
            <div class="d-flex justify-content-center">

                <a href="javascript:;" data-toggle="popover" data-placement="bottom" data-html="true" data-content="
                    <small>
                        <span class='text-default'>{{ config('app.name') }}</span><br />
                        <em><i class='material-symbols-rounded text-default'>arrow_right</i><span class='text-default'>version: {{ config('app.version') }}</span></em><br />
                    </small>" class="btn btn-link btn-float text-default">
                    <i class="material-symbols-rounded text-primary">info</i> <span class="d-none d-sm-block">About</span>
                </a>

                @if(Usr::tenancyInitialized())
                {{-- Analytics --}}
                @can($tenant_permissions['analytics']['index']['permission'])
                <a href="{{ route('analytics.index') }}" class="btn btn-link btn-float text-default"><i class="material-symbols-rounded text-primary">analytics</i> <span class="d-none d-sm-block">Analytics</span></a>
                @endcan
                {{-- Statistics --}}
                @can($tenant_permissions['statistics']['index']['permission'])
                <a href="{{ route('statistics.index') }}" class="btn btn-link btn-float text-default"><i class="material-symbols-rounded text-primary">query_stats</i><span class="d-none d-sm-block">Statistics</span></a>
                @endcan
                {{-- Invoices --}}
                {{-- <a href="javascript:;" class="btn btn-link btn-float text-default"><i class="material-symbols-rounded text-primary">calculate</i> <span class="d-none d-sm-block">Invoices</span></a> --}}
                {{-- Schedule --}}
                @can($tenant_permissions['schedules']['index']['permission'])
                <a href="{{ route('schedule.index') }}" class="btn btn-link btn-float text-default"><i class="material-symbols-rounded text-primary">calendar_month</i> <span class="d-none d-sm-block">Schedule</span></a>
                @endcan
                <a href="{{ auth()->user()->canany(array_column($tenant_permissions['settings'], 'permission')) ? route('settings.index') : '' }}" class="btn btn-link btn-float text-default"><i class="material-symbols-rounded text-primary d-none d-sm-block">arrow_downward_alt</i> <span class="font-weight-semibold d-flex" id="current-session"><span class="d-none d-sm-block pr-1">Current Session:</span>{{ $settings->where('key', 'current_session')->value('value') }}</span></a>
                @endif
            </div>
        </div>
    </div>

    <div class="breadcrumb-line header-elements-md-inline">
        <div class="d-flex">
            {{ Breadcrumbs::render() }}
            <a href="javascript:;" class="header-elements-toggle text-default d-md-none"><span class="material-symbols-rounded">more_vert</span></a>
        </div>

        <div class="header-elements d-none">
            <div class="breadcrumb justify-content-center" id="breadcrumb">
                <div class="breadcrumb-elements-item d-none d-md-block">
                    @include('pages.notifications.badge')
                </div>

                @if(Usr::tenancyInitialized())
                @can($tenant_permissions['message_threads']['index']['permission'])
                <a class="breadcrumb-elements-item d-none d-md-block" data-toggle="tooltip" title="Messages" href="{{ route('message_threads.index') }}">@include('messenger.unread-count')</a>
                @endcan
                @can($tenant_permissions['tickets']['index']['permission'])
                <div class="breadcrumb-elements-item dropdown">
                    <a href="javascript:;" class="breadcrumb-elements-item" data-toggle="dropdown">
                        <i class="material-symbols-rounded mr-1">support</i>
                        <span class="align-middle">Support</span>
                        <i class="material-symbols-rounded m-auto">keyboard_arrow_down</i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-left">
                        <a target="_blank" href="#" class="dropdown-item"><i class="material-symbols-rounded">quick_reference</i>Knowledgebase</a>
                        <a href="{{ route('tickets.index') }}" class="dropdown-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}"><i class="material-symbols-rounded">local_activity</i>Tickets</a>
                    </div>
                </div>
                @endcan
                @elseif(auth()->user()->can($central_permissions['tickets']['index_central']['permission']))
                <div class="breadcrumb-elements-item d-none d-md-block">
                    @include('pages.central.support.tickets.badge')
                </div>
                @endif

                @if(Usr::tenancyInitialized() && auth()->user()->can($tenant_permissions['soft_deletes']['bin']['permission']))
                <div class="breadcrumb-elements-item">
                    <a href="{{ route('bin') }}" @if(request()->routeIs('bin')) class="breadcrumb-elements-item is-disabled d-block" @else class="breadcrumb-elements-item" @endif>
                        <i class="material-symbols-rounded mr-1 align-middle">delete_forever</i>
                        <span class="align-middle">Bin</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
