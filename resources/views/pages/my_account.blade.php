@extends('layouts.master')

@section('page_title', 'My Account')

@section('content')
    @php
        $errors_has_password_error = str_contains($errors->first() ?? '', 'password');
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">My Account</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                {{-- Change Password Tab --}}
                <li class="nav-item">
                    <a
                        href="#change-pass"
                        class="{{
                            $errors_has_password_error
                            ? 'nav-link active'
                            : ($errors->any()
                            ? 'nav-link'
                            : 'nav-link active')
                        }}"
                        data-toggle="tab"
                    >
                        Change Password
                    </a>
                </li>

                {{-- Edit Profile Tab --}}
                <li class="nav-item">
                    <a
                        href="#edit-profile"
                        class="{{
                            $errors_has_password_error
                            ? 'nav-link'
                            : ($errors->any()
                            ? 'nav-link active'
                            : 'nav-link')
                        }}"
                        data-toggle="tab"
                    >
                        Manage Profile
                    </a>
                </li>

                {{-- Preferences Settings Tab --}}
                <li class="nav-item">
                    <a href="#preferences" class="nav-link" data-toggle="tab"> Preferences </a>
                </li>
            </ul>

            <div class="tab-content">
                {{-- Change Password Pane --}}
                <div
                    id="change-pass"
                    class="tab-pane fade {{
                        $errors_has_password_error
                        ? 'show active'
                        : ($errors->any()
                        ? 'show'
                        : 'show active')
                    }}"
                >
                    <div class="row">
                        <div class="col-md-8">
                            <form method="post" action="{{ route('my_account.change_pass') }}">
                                @csrf
                                @method('put')
                                <div class="form-group row">
                                    <label for="current_password" class="col-lg-3 col-form-label font-weight-semibold"
                                        >Current Password <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input
                                            id="current_password"
                                            name="current_password"
                                            required
                                            type="password"
                                            class="form-control"
                                            autocomplete="current-password"
                                        />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="password" class="col-lg-3 col-form-label font-weight-semibold"
                                        >New Password <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input
                                            id="password"
                                            name="password"
                                            required
                                            type="password"
                                            class="form-control"
                                            autocomplete="password"
                                        />
                                    </div>
                                </div>
                                @if ($settings->firstWhere('key', 'password_require_confirmation')->value == 1)
                                    <div class="form-group row">
                                        <label
                                            for="password_confirmation"
                                            class="col-lg-3 col-form-label font-weight-semibold"
                                        >Confirm Password <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <input
                                                id="password_confirmation"
                                                name="password_confirmation"
                                                required
                                                type="password"
                                                class="form-control"
                                                autocomplete="password"
                                            />
                                        </div>
                                    </div>
                                @endif
                                <div class="float-right">
                                    <button type="submit" class="btn btn-danger">
                                        Submit form <i class="material-symbols-rounded ml-2">send</i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Edit Profile Pane --}}
                <div
                    id="edit-profile"
                    class="tab-pane fade {{
                        $errors_has_password_error
                        ? ''
                        : ($errors->any()
                        ? 'show active'
                        : '')
                    }}"
                >
                    <form enctype="multipart/form-data" method="post" action="{{ route('my_account.update') }}">
                        @csrf
                        @method('put')
                        <div class="row">
                            <div class="col-md-10">
                                {{-- Name (disabled) --}}
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Name</label>
                                    <div class="col-lg-9">
                                        <input disabled class="form-control" type="text" value="{{ $my->name }}" />
                                    </div>
                                </div>

                                {{-- Username --}}
                                @if ($my->username)
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label font-weight-semibold">Username</label>
                                        <div class="col-lg-9">
                                            <input
                                                disabled
                                                class="form-control"
                                                type="text"
                                                value="{{ $my->username }}"
                                            />
                                        </div>
                                    </div>
                                @else
                                    <div class="form-group row">
                                        <label for="username" class="col-lg-3 col-form-label font-weight-semibold"
                                            >Username</label>
                                        <div class="col-lg-9">
                                            <input
                                                id="username"
                                                name="username"
                                                type="text"
                                                class="form-control"
                                                value="{{ old('username') }}"
                                            />
                                        </div>
                                    </div>
                                @endif

                                {{-- Date of Birth --}}
                                <div class="form-group row">
                                    <label for="date_of_birth" class="col-lg-3 col-form-label font-weight-semibold"
                                        >Date of Birth</label>
                                    <div class="col-lg-9">
                                        <input
                                            id="date_of_birth"
                                            name="date_of_birth"
                                            value="{{ $my->date_of_birth ?? old('date_of_birth') }}"
                                            type="text"
                                            class="form-control date-pick"
                                            placeholder="Select Date..."
                                        />
                                    </div>
                                </div>

                                {{-- Email --}}
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold"
                                        >Email
                                        <span class="text-{{ auth()->user()->hasVerifiedEmail() ? 'success' : 'warning' }}">
                                            ({{ auth()->user()->hasVerifiedEmail() ? 'verified' : 'not verified' }})
                                        </span>
                                    </label>
                                    <div class="input-group col-lg-9">
                                        <input
                                            id="email"
                                            name="email"
                                            type="email"
                                            class="form-control"
                                            value="{{ $my->email ?? old('email') }}"
                                        />
                                        @unless (auth()->user()->hasVerifiedEmail())
                                            <div class="input-group-append">
                                                <span class="input-group-text border-0 bg-transparent">
                                                    <a
                                                        href="{{ route('verification.send') }}"
                                                        class="text-success font-weight-semibold"
                                                    >Send Link</a>
                                                </span>
                                            </div>
                                        @endunless
                                    </div>
                                </div>

                                {{-- Primary Mobile --}}
                                <div class="form-group row">
                                    <label for="primary_mobile" class="col-lg-3 col-form-label font-weight-semibold"
                                        >Primary Mobile</label>
                                    <div class="col-lg-9">
                                        <input
                                            id="primary_mobile"
                                            name="primary_mobile"
                                            type="text"
                                            class="form-control"
                                            value="{{ $my->primary_mobile ?? old('primary_mobile') }}"
                                        />
                                    </div>
                                </div>

                                {{-- Secondary Mobile --}}
                                <div class="form-group row">
                                    <label for="secondary_mobile" class="col-lg-3 col-form-label font-weight-semibold"
                                        >Secondary Mobile</label>
                                    <div class="col-lg-9">
                                        <input
                                            id="secondary_mobile"
                                            name="secondary_mobile"
                                            type="text"
                                            class="form-control"
                                            value="{{ $my->secondary_mobile ?? old('secondary_mobile') }}"
                                        />
                                    </div>
                                </div>

                                {{-- Primary ID --}}
                                <div class="form-group row">
                                    <label
                                        for="primary_identification_number"
                                        class="col-lg-3 col-form-label font-weight-semibold"
                                    >Primary ID number</label>
                                    <div class="col-lg-9">
                                        <input
                                            id="primary_identification_number"
                                            name="primary_identification_number"
                                            type="text"
                                            class="form-control"
                                            value="{{ $my->primary_identification_number ?? old('primary_identification_number') }}"
                                        />
                                    </div>
                                </div>

                                {{-- Secondary ID --}}
                                <div class="form-group row">
                                    <label
                                        for="secondary_identification_number"
                                        class="col-lg-3 col-form-label font-weight-semibold"
                                    >Secondary ID number</label>
                                    <div class="col-lg-9">
                                        <input
                                            id="secondary_identification_number"
                                            name="secondary_identification_number"
                                            type="text"
                                            class="form-control"
                                            value="{{ $my->secondary_identification_number ?? old('secondary_identification_number') }}"
                                        />
                                    </div>
                                </div>

                                {{-- Address --}}
                                <div class="form-group row">
                                    <label for="address" class="col-lg-3 col-form-label font-weight-semibold"
                                        >Address</label>
                                    <div class="col-lg-9">
                                        <input
                                            id="address"
                                            name="address"
                                            type="text"
                                            class="form-control"
                                            value="{{ $my->address ?? old('address') }}"
                                        />
                                    </div>
                                </div>

                                {{-- Photo --}}
                                <div class="form-group row">
                                    <label for="photo" class="col-lg-3 col-form-label font-weight-semibold"
                                        >Change Photo</label>
                                    <div class="col-lg-9">
                                        <input
                                            id="photo"
                                            name="photo"
                                            type="file"
                                            accept="image/*"
                                            class="form-input-styled"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="float-right">
                            <button type="submit" class="btn btn-danger">
                                Submit form <i class="material-symbols-rounded ml-2">send</i>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Preferences Settings Pane --}}
                <div id="preferences" class="tab-pane fade">
                    <form method="post" action="{{ route('my_account.update_preferences') }}">
                        @csrf
                        @method('put')
                        <div class="row">
                            <div class="col-md-4 d-none d-md-block">
                                <div class="form-group">
                                    <label for="always_minimize_sidebar" class="col-form-label font-weight-semibold"
                                        >Always Minimize Sidebar <span class="text-danger">*</span></label>
                                    <select
                                        id="always_minimize_sidebar"
                                        name="always_minimize_sidebar"
                                        class="form-control select"
                                    >
                                        <option value="1" @selected($user_preferences['always_minimize_sidebar'] == 1)>
                                            Yes
                                        </option>
                                        <option value="0" @selected($user_preferences['always_minimize_sidebar'] == 0)>
                                            No
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="allow_system_sounds" class="col-form-label font-weight-semibold"
                                        >Allow System Sounds <span class="text-danger">*</span></label>
                                    <select
                                        id="allow_system_sounds"
                                        name="allow_system_sounds"
                                        class="form-control select"
                                    >
                                        <option value="1" @selected($user_preferences['allow_system_sounds'] == 1)>
                                            Yes
                                        </option>
                                        <option value="0" @selected($user_preferences['allow_system_sounds'] == 0)>
                                            No
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="active_system_skin" class="col-form-label font-weight-semibold"
                                        >System Skin <span class="text-danger">*</span></label>
                                    <select
                                        id="active_system_skin"
                                        name="active_system_skin"
                                        class="form-control select"
                                    >
                                        @foreach (Usr::getAvailableAppSkins() as $skin => $description)
                                            <option
                                                value="{{ $skin }}"
                                                @selected($user_preferences['active_system_skin'] == $skin)
                                                title="{{ $description }}"
                                            >
                                                {{ ucfirst($skin) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning alert-styled-left mb-3">
                                    <strong>Offline access</strong> — when enabled, pages you visit are stored on this
                                    device so you can keep working without internet. Anyone with physical access to this
                                    device may be able to view the cached pages. Enable it only if you trust everyone
                                    using this device.
                                </div>
                                <div class="form-group">
                                    <label for="allow_offline_access" class="col-form-label font-weight-semibold">
                                        Allow offline access <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        id="allow_offline_access"
                                        name="allow_offline_access"
                                        class="form-control select"
                                    >
                                        <option
                                            value="1"
                                            @selected(($user_preferences['allow_offline_access'] ?? 0) == 1)
                                        >
                                            Yes
                                        </option>
                                        <option
                                            value="0"
                                            @selected(($user_preferences['allow_offline_access'] ?? 0) == 0)
                                        >
                                            No
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="float-right text-right">
                                    <button type="submit" class="btn btn-danger">
                                        Submit form <i class="material-symbols-rounded ml-2">send</i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <hr />

                    <div class="form-group row">
                        <label class="col-form-label font-weight-semibold col-8">Reveal all hidden alert messages</label>
                        <div class="col-4 text-right">
                            <button
                                id="clear-do-not-show-again-alert-msgs"
                                type="button"
                                class="btn btn-warning btn-sm"
                            >
                                Reveal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
