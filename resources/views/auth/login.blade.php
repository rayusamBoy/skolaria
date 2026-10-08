@extends('layouts.login_master')

@section('page_title', 'Login')

@section('content')
    <div class="page-content login-cover">
        <!-- Main content -->
        <div class="content-wrapper">
            <!-- Content area -->
            <div class="content d-flex justify-content-center">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <div class="introductory-section">
                            <div class="card intro border-0 bg-transparent">
                                <div class="card-body">
                                    <h4 class="card-title"><strong>Welcome to the {{ config('app.name') }}</strong></h4>
                                    <div class="card-text">
                                        <p>
                                            A multi‑tenant platform that centralises student records, academic
                                            management, fee collections, and communication – accessible even offline.
                                            Ideal for schools and colleges.
                                        </p>
                                        <h5><strong>Key capabilities</strong></h5>
                                        <ul class="pl-3">
                                            <li>Publish exam results and generate marksheets</li>
                                            <li>Manage classes, subjects, timetables, and assessments</li>
                                            <li>Track student information, payments, and library books</li>
                                            <li>
                                                Role‑specific dashboards for teachers, parents, accountants, and more
                                            </li>
                                        </ul>
                                        <p class="mt-2">Please sign in with your credentials to continue.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Login card -->
                    <div class="col-md-5">
                        <form class="login-form" method="post" action="{{ route('login') }}">
                            @csrf
                            <div class="card mb-0">
                                <div class="card-body">
                                    <div class="mb-3 text-center">
                                        <span class="material-symbols-rounded symbol-2x text-warning-400 border-warning-400 rounded-round mt-1 mb-3 border-3 p-3">security</span>
                                        <h5 class="text-info mb-0">Login to your account</h5>
                                        <span class="d-block text-muted">Your credentials</span>
                                    </div>

                                    @if ($errors->any())
                                        <div class="alert alert-danger alert-styled-left alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert">
                                                <span>&times;</span>
                                            </button>
                                            <i>Oops! {{ implode('<br>', $errors->all()) }}</i>
                                        </div>
                                    @endif

                                    {{-- Error message for a blocked user --}}
                                    @if (session()->has('access_denied'))
                                        <div class="alert alert-warning alert-styled-left alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert">
                                                <span>&times;</span>
                                            </button>
                                            <i>{{ session("access_denied") }}</i>
                                        </div>
                                    @endif

                                    {{-- Invitation registration submitted --}}
                                    @if (session()->has('invitation_registration_submitted'))
                                        <div class="alert alert-success alert-styled-left alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert">
                                                <span>&times;</span>
                                            </button>
                                            <i>{{ session("invitation_registration_submitted") }}</i>
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <input
                                            type="text"
                                            class="form-control"
                                            name="identity"
                                            value="{{ old('identity') }}"
                                            placeholder="Username or Email"
                                            autocomplete="identity"
                                        />
                                    </div>

                                    <div class="form-group">
                                        <input
                                            required
                                            name="password"
                                            type="password"
                                            class="form-control"
                                            placeholder="{{ __('Password') }}"
                                            autocomplete="password"
                                        />
                                    </div>

                                    <div class="form-group d-flex align-items-center">
                                        <div class="form-check mb-0">
                                            <label class="form-check-label">
                                                <input
                                                    type="checkbox"
                                                    name="remember"
                                                    class="form-input-styled"
                                                    @checked(old('remember'))
                                                />
                                                <span class="text-info">Remember Me</span>
                                            </label>
                                        </div>
                                        <a href="{{ route('password.request') }}" class="ml-auto">Forgot password?</a>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn bg-blue d-flex w-100">
                                            <i class="material-symbols-rounded mr-1 ml-auto">login</i>
                                            <span class="mr-auto">Sign in</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            const params = new URLSearchParams(window.location.search);
            if (params.get('reason') !== 'timeout') return;

            // Read the identity the session manager stored for this tab
            let identity = null;
            try {
                identity = JSON.parse(sessionStorage.getItem('session_reauth_identity') || 'null');
            } catch (_) {}

            const form = document.querySelector('form.login-form');
            if (!form) return;

            const cardBody = form.querySelector('.card-body');
            const submitBtn = form.querySelector('button[type="submit"]');

            // If we have no identity, fall back to normal login (but show the timeout hint)
            if (!identity?.user_id || !identity?.token) {
                if (cardBody) {
                    const note = document.createElement('div');
                    note.className = 'alert alert-info alert-styled-left';
                    note.innerHTML = 'Your session expired. Please sign in again to continue.';
                    cardBody.insertBefore(note, cardBody.firstChild);
                }
                return;
            }

            // Hide the identity field — the user is already known
            const identityEl = form.querySelector('input[name="identity"]');
            if (identityEl) identityEl.closest('.form-group').style.display = 'none';

            // Info banner with a "start fresh" link
            if (cardBody) {
                const banner = document.createElement('div');
                banner.className = 'alert alert-info alert-styled-left';
                banner.innerHTML =
                    'Your session expired. Enter your password to continue, ' +
                    'or <a href="#" id="start-fresh-login">start a fresh login</a>.';
                cardBody.insertBefore(banner, cardBody.firstChild);

                banner.querySelector('#start-fresh-login')?.addEventListener('click', (e) => {
                    e.preventDefault();
                    sessionStorage.removeItem('session_reauth_identity');
                    window.location.href = "{{ route('login') }}";
                });
            }

            // Intercept submission
            form.addEventListener(
                'submit',
                async (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    const password = form.querySelector('input[name="password"]').value;
                    if (!password) return;

                    submitBtn.disabled = true;
                    cardBody.querySelector('.alert-danger')?.remove();

                    try {
                        const res = await fetch("{{ route('session.reauth') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                user_id: identity.user_id,
                                password: password,
                                token: identity.token,
                            }),
                        });

                        const data = await res.json().catch(() => ({}));

                        if (res.ok && data.ok) {
                            // Wipe the tab-scoped identity — a fresh one will be seeded on the next page
                            try {
                                sessionStorage.removeItem('session_reauth_identity');
                            } catch (_) {}
                            window.location.href = data.redirect || "{{ route('dashboard') }}";
                            return;
                        }

                        const err = document.createElement('div');
                        err.className = 'alert alert-danger alert-styled-left';
                        err.textContent = data.msg || 'Authentication failed.';
                        cardBody.insertBefore(err, cardBody.firstChild);
                    } catch (_) {
                        const err = document.createElement('div');
                        err.className = 'alert alert-danger alert-styled-left';
                        err.textContent = 'Network error. Please try again.';
                        cardBody.insertBefore(err, cardBody.firstChild);
                    } finally {
                        submitBtn.disabled = false;
                    }
                },
                true,
            );
        })();
    </script>
@endpush
