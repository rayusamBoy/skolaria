@extends('layouts.login_master')

@section('page_title', 'Password Recovery')

@section('content')
    <div class="page-content login-cover">
        <div class="content-wrapper">
            <div class="content d-flex justify-content-center align-items-center">
                <!-- Password recovery form -->
                <form class="login-form" method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="card mb-0">
                        <div class="card-body">
                            @if (session('status'))
                                <div class="alert alert-success alert-styled-left" role="alert">{{ session('status') }}</div>
                            @endif
                            @if ($errors->has('email'))
                                <div class="alert alert-danger alert-styled-left" role="alert">
                                    <i>{{ $errors->first('email') }}</i>
                                </div>
                            @endif

                            <div class="mb-3 text-center">
                                <i class="material-symbols-rounded symbol-2x text-warning border-warning rounded-round mt-1 mb-3 border-3 p-3">passkey</i>
                                <h5 class="text-info mb-0">Password recovery</h5>
                                <span class="d-block text-muted">We'll send you instructions in email</span>
                            </div>

                            <div class="form-group">
                                <input
                                    name="email"
                                    required
                                    type="email"
                                    class="form-control"
                                    value="{{ old('email') }}"
                                    placeholder="Your email"
                                />
                            </div>

                            <button type="submit" class="btn bg-blue d-flex w-100">
                                <i class="material-symbols-rounded mr-1 ml-auto">lock_reset</i>
                                <span class="mr-auto">Reset password</span>
                            </button>
                        </div>
                    </div>
                </form>
                <!-- /password recovery form -->
            </div>
        </div>
    </div>

@endsection
