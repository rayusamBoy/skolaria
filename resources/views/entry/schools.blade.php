@extends('layouts.login_master')

@section('page_title', 'Select School')

@section('content')

<div class="page-content login-cover">
    <div class="content-wrapper">
        <div class="content d-flex justify-content-center align-items-center">
            <div class="col-md-10 col-lg-8 col-xl-7">

                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <span class="material-symbols-rounded symbol-2x text-primary-400 border-primary-400 border-3 rounded-round p-3 mb-3 mt-1">school</span>
                            <h5 class="mb-0 text-info">Choose your school</h5>
                            <span class="d-block text-muted">Select the institution you would like to access</span>
                        </div>

                        @if($schools->isEmpty())
                        <div class="alert alert-info text-center">
                            No schools are currently available. Please contact the administrator.
                        </div>
                        @else
                        <div class="card-columns">
                            @foreach($schools as $school)
                            @php
                            $url = $school->tenant_slug ? "slug/{$school->tenant_slug}" : "/select/{$school->id}";
                            @endphp
                            <div class="card h-100 position-relative hover-effect">
                                <div class="card-body text-center">
                                    <strong class="card-title"><a href="{{ $url }}" class="stretched-link">{{ $school->name }}</a></strong>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <div class="text-center mt-3">
                            {{-- The form is defined in resources\views\partials\login\footer.blade.php --}}
                            <a href="javascript:;" onclick="document.getElementById('tenant-logout-form').submit();" class="text-muted">Back to Central login</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
