@extends('layouts.app')

@section('page_title', 'Confirm Access')

@pushOnce('css')
    <style>
        .page-content.app-cover {
            background-image: url("{{ Usr::getTenancyAwareImgOrDefaultBgUrl($settings->firstWhere('key' , 'logo')) }}");
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            background-blend-mode: multiply;
        }
    </style>
@endPushOnce

@section('content')
    <div class="page-content app-cover">
        <div class="content-wrapper">
            <div class="content d-flex justify-content-center align-items-center">
                <div class="row mw-sm-25em">
                    <div class="card box-shadow-none text-mupted col-12 border-none text-center"></div>
                    <div class="m-auto">
                        <i class="material-symbols-rounded symbol-2x text-danger border-danger rounded-round mt-1 mb-3 border-3 p-3">passkey</i>
                    </div>

                    <div class="card border-color-1d1d1d col-12 border-none">
                        <div class="card-header text-green">
                            <h5 class="text-danger mb-0"><strong>Confirm Access</strong></h5>
                        </div>

                        <div class="card-body text-white">
                            <form method="POST" action="{{ route('password.confirm') }}">
                                @csrf

                                <label for="password" class="col-form-label text-md-end text-muted">Password</label>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <input
                                            id="password"
                                            type="password"
                                            placeholder="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            name="password"
                                            required
                                            autocomplete="current-password"
                                        />

                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <span class="font-small">{{ $message }}</span>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-6">
                                        <button type="submit" class="btn btn-success">Confirm Password</button>
                                    </div>

                                    <div class="col-6">
                                        <a
                                            class="btn btn-link"
                                            href="{{ (url()->previous() === url()->current()) ? route('dashboard') : url()->previous() }}"
                                        >
                                            Return Back
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card box-shadow-none text-muted col-12 border-none text-center">
                        For security, confirm your password to access this operation. It expires after about 30 minutes
                        of inactivity.
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
