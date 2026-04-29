@extends('layouts.master')

@section('page_title', 'Analytics')

@section('content')

{{-- Setup / Update Google Analytics --}}
<div class="{{ isset($google_analytics_setup_ok) ? 'card card-collapsed' : 'card' }}">
    <div class="card-header header-elements-inline">
        <h6 class="card-title font-weight-semibold">Set up or Update Google Analytics</h6>
        <div class="header-elements">
            <div class="list-icons">
                <span class="list-icons-item">
                    @if(isset($google_analytics_setup_ok))
                    <span class="badge bg-success font-size-base">active</span>
                    @else
                    <span class="badge bg-warning font-size-base">inactive</span>
                    @endif
                </span>
                <a class="list-icons-item" data-action="collapse"></a>
                <a class="list-icons-item" data-action="remove"></a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form enctype="multipart/form-data" method="post" action="{{ route('analytics.google_setup') }}">
            @csrf
            <div class="form-group row">
                {{-- Tag ID --}}
                <div class="col-lg-4">
                    <label>Tag ID</label>
                    @if(isset($gtag_code_structure_file))
                    <span class="display-i-on-hover float-right text-info">
                        <span class="material-symbols-rounded">info</span>
                        <i class="float-right text-info display-none dropdown-menu position-absolute top-auto p-2">
                            <div>
                                <span>A Google tag ID is a series of letters and numbers that usually starts with "G-".</span><br>
                                @php highlight_file($gtag_code_structure_file) @endphp
                            </div>
                        </i>
                    </span>
                    @endif
                    <input name="google_analytic_tag_id" type="text" value="{{ old('google_analytic_tag_id') ?? '' }}" data-mask="G-wwwwwwwww?www" class="form-control" placeholder="Ie., G-4BCDFR6HGE">
                    @if(empty($google_analytic_tag_id))
                    <span class="form-text text-warning-800">No Tag ID found.</span>
                    @else
                    <span class="form-text text-green-800">Found Tag ID: {{ $google_analytic_tag_id }}</span>
                    @endif
                </div>

                {{-- Property ID --}}
                <div class="col-lg-4">
                    <label class="d-block">Analytic Property ID</label>
                    <input id="google_analytic_property_id" name="google_analytic_property_id" value="{{ old('google_analytic_property_id') ?? '' }}" type="text" data-mask="999999999" class="form-control" placeholder="Ie., 123456789">
                    @if(empty($google_analytic_property_id))
                    <span class="form-text text-warning-800">No Property ID found.</span>
                    @else
                    <span class="form-text text-green-800">Found Property ID: {{ $google_analytic_property_id }}</span>
                    @endif
                </div>

                {{-- Credential File --}}
                <div class="col-lg-4">
                    <label>Upload Service Account Credential File:</label>
                    <input accept=".json" type="file" name="service_account_credential_file" class="form-input-styled">
                    <span class="form-text text-muted">Accepted File: json, Max file size 2Mb</span>

                    @if(isset($credential_file) && is_file($credential_file))
                    <span class="display-i-on-hover float-right text-info">
                        <span class="btn btn-sm">Hover to View File Contents</span>
                        <i class="float-right text-info display-none dropdown-menu position-absolute top-auto p-2">
                            <div>
                                @php highlight_file($credential_file) @endphp
                            </div>
                        </i>
                    </span>
                    @else
                    <span class="form-text text-warning-800">No Credential File found.</span>
                    @endif
                </div>
            </div>

            <div class="float-right d-flex">
                @if(!empty($google_analytic_tag_id) && !empty($google_analytic_property_id) && is_file($credential_file))
                @if(isset($google_analytics_setup_ok))
                <a href="{{ route('settings.disable_analytics') }}" class="btn btn-danger">Disable Analytics</a>
                @else
                <button type="submit" class="btn btn-success">Submit form <i class="material-symbols-rounded ml-2">send</i></button>
                @if(!$analytics_enabled)
                <a href="{{ route('settings.enable_analytics') }}" class="btn btn-primary ml-2">Enable Analytics</a>
                @endif
                @endif
                @else
                <button type="submit" class="btn btn-success">Submit form <i class="material-symbols-rounded ml-2">send</i></button>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Fetch & Display Analytics Data --}}
@if(isset($google_analytics_setup_ok))
<form class="page-block" method="get" action="{{ route('analytics.fetch_data') }}">
    @csrf
    <div class="form-group row">
        <div class="col-md-6 offset-md-6">
            <div class="row">
                <label class="col-lg-5 col-form-label font-weight-semibold">
                    The period ago to fetch data for <span class="text-danger">*</span>
                </label>
                <div class="col-lg-3">
                    <input name="period_number" value="{{ old('period_number') ?? $data['period_number'] }}" required type="number" min="1" class="form-control" placeholder="Type a number">
                </div>
                <div class="col-lg-4">
                    <select name="period_name" required class="select form-control" data-placeholder="Choose...">
                        @foreach(Qs::getSpatieAnalyticsPackagePeriods() as $key => $value)
                        <option value="{{ $key }}" {{ $key == $data['period_name'] ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row mt-2">
                <div class="col-12">
                    <button type="submit" class="btn btn-success float-right">
                        Fetch Data <i class="material-symbols-rounded ml-2">send</i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="card-columns">
    <!-- Visitors & Page Views -->
    <div class="card card-body bg-violet-400">
        <div class="media-body w-100">
            <div class="row mb-2">
                <strong class="col-6">Page Title</strong>
                <strong class="col-3">Active Users</strong>
                <strong class="col-3">Page Views</strong>
            </div>
            @foreach($vstrsAndPgVw as $a)
            <div class="row">
                <div class="col-6">{{ $a["pageTitle"] }}</div>
                <div class="col-3 counter">{{ $a["activeUsers"] }}</div>
                <div class="col-3 counter">{{ $a["screenPageViews"] }}</div>
            </div>
            @endforeach
            <div class="row mt-2">
                <strong class="text-capitalize float-right">Visitors and Page Views</strong>
            </div>
        </div>
    </div>

    <!-- Visitors & Page Views by Date -->
    <div class="card card-body bg-warning-400">
        <div class="media-body w-100">
            <div class="row mb-2">
                <strong class="col-6">Page Title</strong>
                <strong class="col-2">Active Users</strong>
                <strong class="col-2">Page Views</strong>
                <strong class="col-2">Date</strong>
            </div>
            @foreach($vstrsAndPgVwByDt as $b)
            <div class="row">
                <div class="col-6">{{ $b["pageTitle"] }}</div>
                <div class="col-2 counter">{{ $b["activeUsers"] }}</div>
                <div class="col-2 counter">{{ $b["screenPageViews"] }}</div>
                <div class="col-2 counter">{{ $b["date"] }}</div>
            </div>
            @endforeach
            <div class="row mt-2">
                <strong class="text-capitalize float-right">Visitors and Page Views by Date</strong>
            </div>
        </div>
    </div>

    <!-- Total Visitors & Page Views -->
    <div class="card card-body bg-success-400">
        <div class="media-body w-100">
            <div class="row mb-2">
                <strong class="col-6">Date</strong>
                <strong class="col-3">Visitors</strong>
                <strong class="col-3">Page Views</strong>
            </div>
            @foreach($ttlVstrsAndPgVw as $c)
            <div class="row">
                <div class="col-6">{{ $c["date"] }}</div>
                <div class="col-3 counter">{{ $c["activeUsers"] }}</div>
                <div class="col-3 counter">{{ $c["screenPageViews"] }}</div>
            </div>
            @endforeach
            <div class="row mt-2">
                <strong class="text-capitalize float-right">Total Visitors and Page Views</strong>
            </div>
        </div>
    </div>

    <!-- Most Visited Pages -->
    <div class="card card-body bg-pink-400">
        <div class="media-body w-100">
            <div class="row mb-2">
                <strong class="col-6">Full Page Url</strong>
                <strong class="col-3">Page Title</strong>
                <strong class="col-3">Page Views</strong>
            </div>
            @foreach($mostVstdPgs as $a)
            <div class="row">
                <div class="col-6">{{ $a["fullPageUrl"] }}</div>
                <div class="col-3 counter">{{ $a["pageTitle"] }}</div>
                <div class="col-3 counter">{{ $a["screenPageViews"] }}</div>
            </div>
            @endforeach
            <div class="row mt-2">
                <strong class="text-capitalize float-right">Most Visited Pages</strong>
            </div>
        </div>
    </div>

    <!-- Top Referrers -->
    <div class="card card-body bg-indigo-400">
        <div class="media-body w-100">
            <div class="row mb-2">
                <strong class="col-3">Page Views</strong>
                <strong class="col-9">Referrer</strong>
            </div>
            @foreach($topReferrers as $b)
            <div class="row">
                <div class="col-3 counter">{{ $b["screenPageViews"] }}</div>
                <div class="col-9 counter">{{ $b["pageReferrer"] }}</div>
            </div>
            @endforeach
            <div class="row mt-2">
                <strong class="text-capitalize float-right">Top Referrers</strong>
            </div>
        </div>
    </div>

    <!-- User Types -->
    <div class="card card-body bg-purple-400">
        <div class="media-body w-100">
            <div class="row mb-2">
                <strong class="col-6">Active Users</strong>
                <strong class="col-6">Status</strong>
            </div>
            @foreach($userTypes as $c)
            <div class="row">
                <div class="col-6 counter">{{ $c["activeUsers"] }}</div>
                <div class="col-6 counter">{{ $c["newVsReturning"] }}</div>
            </div>
            @endforeach
            <div class="row mt-2">
                <strong class="text-capitalize float-right">User Types</strong>
            </div>
        </div>
    </div>

    <!-- Top Browsers -->
    <div class="card card-body bg-orange-400">
        <div class="media-body w-100">
            <div class="row mb-2">
                <strong class="col-6">Page Views</strong>
                <strong class="col-6">Browser</strong>
            </div>
            @foreach($topBrowsers as $d)
            <div class="row">
                <div class="col-6 counter">{{ $d["screenPageViews"] }}</div>
                <div class="col-6 counter">{{ $d["browser"] }}</div>
            </div>
            @endforeach
            <div class="row mt-2">
                <strong class="text-capitalize float-right">Top Browsers</strong>
            </div>
        </div>
    </div>

    <!-- Top Countries -->
    <div class="card card-body bg-primary-400">
        <div class="media-body w-100">
            <div class="row mb-2">
                <strong class="col-6">Page Views</strong>
                <strong class="col-6">Country</strong>
            </div>
            @foreach($topCountries as $d)
            <div class="row">
                <div class="col-6 counter">{{ $d["screenPageViews"] }}</div>
                <div class="col-6 counter">{{ $d["country"] }}</div>
            </div>
            @endforeach
            <div class="row mt-2">
                <strong class="text-capitalize float-right">Top Countries</strong>
            </div>
        </div>
    </div>

    <!-- Top OSes -->
    <div class="card card-body bg-info-400">
        <div class="media-body w-100">
            <div class="row mb-2">
                <strong class="col-6">Page Views</strong>
                <strong class="col-6">Operating System</strong>
            </div>
            @foreach($topOses as $d)
            <div class="row">
                <div class="col-6 counter">{{ $d["screenPageViews"] }}</div>
                <div class="col-6 counter">{{ $d["operatingSystem"] }}</div>
            </div>
            @endforeach
            <div class="row mt-2">
                <strong class="text-capitalize float-right">Top Operating Systems</strong>
            </div>
        </div>
    </div>
</div>

<h6 class="font-weight-bold status-styled float-right">
    Showing Analytics Data for the past {{ $data["period_number"] . ' ' . $data["period_name"] }}.
</h6>
@endif

@endsection
