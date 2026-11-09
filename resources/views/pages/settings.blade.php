@extends('layouts.master')

@section('page_title', 'Manage System Settings')

@section('content')
    <form enctype="multipart/form-data" method="post" action="{{ route('settings.update') }}">
        @csrf
        @method('PUT')

        {{-- SCHOOL INFORMATION --}}
        <div class="card card-collapsed">
            <div class="card-header header-elements-inline">
                <h6 class="card-title font-weight-semibold">
                    <i class="material-symbols-rounded mr-2 align-middle">school</i>
                    School Information
                </h6>
                {!! Qs::getPanelOptionsHtml() !!}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        {{-- School Name --}}
                        <div class="form-group row">
                            <label for="system_name" class="col-lg-4 col-form-label font-weight-semibold"
                                >Name of School <span class="text-danger">*</span></label>
                            <div class="col-lg-8">
                                <input
                                    id="system_name"
                                    name="system_name"
                                    required
                                    type="text"
                                    class="form-control"
                                    value="{{ $settings->firstWhere('key', 'system_name')->value }}"
                                    placeholder="Name of School"
                                />
                            </div>
                        </div>

                        {{-- School Acronym --}}
                        <div class="form-group row">
                            <label for="system_title" class="col-lg-4 col-form-label font-weight-semibold"
                                >School Acronym</label>
                            <div class="col-lg-8">
                                <input
                                    id="system_title"
                                    name="system_title"
                                    type="text"
                                    class="form-control"
                                    value="{{ $settings->firstWhere('key','system_title')->value }}"
                                    placeholder="School Acronym"
                                />
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div class="form-group row">
                            <label for="phone" class="col-lg-4 col-form-label font-weight-semibold">Phone</label>
                            <div class="col-lg-8">
                                <input
                                    id="phone"
                                    name="phone"
                                    type="text"
                                    class="form-control"
                                    value="{{ $settings->firstWhere('key','phone')->value }}"
                                    placeholder="Phone"
                                />
                            </div>
                        </div>

                        {{-- School Email --}}
                        <div class="form-group row">
                            <label for="system_email" class="col-lg-4 col-form-label font-weight-semibold"
                                >School Email</label>
                            <div class="col-lg-8">
                                <input
                                    id="system_email"
                                    name="system_email"
                                    type="email"
                                    class="form-control"
                                    value="{{ $settings->firstWhere('key','system_email')->value }}"
                                    placeholder="School Email"
                                />
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="form-group row">
                            <label for="address" class="col-lg-4 col-form-label font-weight-semibold"
                                >School Address <span class="text-danger">*</span></label>
                            <div class="col-lg-8">
                                <input
                                    id="address"
                                    name="address"
                                    required
                                    type="text"
                                    class="form-control"
                                    value="{{ $settings->firstWhere('key','address')->value }}"
                                    placeholder="School Address"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACADEMIC CALENDAR --}}
        <div class="card card-collapsed">
            <div class="card-header header-elements-inline">
                <h6 class="card-title font-weight-semibold">
                    <i class="material-symbols-rounded mr-2 align-middle">calendar_month</i>
                    Academic Calendar
                </h6>
                {!! Qs::getPanelOptionsHtml() !!}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        {{-- Current Session --}}
                        <div class="form-group row">
                            <label for="current_session" class="col-lg-4 col-form-label font-weight-semibold"
                                >Current Session <span class="text-danger">*</span></label>
                            <div class="col-lg-8">
                                <select
                                    id="current_session"
                                    name="current_session"
                                    required
                                    class="select-search form-control"
                                    data-placeholder="Choose..."
                                >
                                    <option value=""></option>
                                    @for ($y = date('Y') - 3; $y <= date('Y') + 2; $y++)
                                        @php $session = ($y - 1).'-'.$y; @endphp
                                        <option
                                            value="{{ $session }}"
                                            @selected($settings->firstWhere('key', 'current_session')->value == $session)
                                        >
                                            {{ $session }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        {{-- Term Ends --}}
                        <div class="form-group row">
                            <label for="term_ends" class="col-lg-4 col-form-label font-weight-semibold"
                                >This Term Ends</label>
                            <div class="col-lg-5">
                                <input
                                    id="term_ends"
                                    name="term_ends"
                                    type="text"
                                    class="form-control date-pick"
                                    value="{{ $settings->firstWhere('key','term_ends')->value }}"
                                />
                            </div>
                            <div class="col-lg-3 m-lg-auto">
                                <span class="font-weight-bold font-italic text-info-800">{{ Usr::getDateFormat() }}</span>
                            </div>
                        </div>

                        {{-- Next Term Begins --}}
                        <div class="form-group row">
                            <label for="term_begins" class="col-lg-4 col-form-label font-weight-semibold"
                                >Next Term Begins</label>
                            <div class="col-lg-5">
                                <input
                                    id="term_begins"
                                    name="term_begins"
                                    type="text"
                                    class="form-control date-pick"
                                    value="{{ $settings->firstWhere('key','term_begins')->value }}"
                                />
                            </div>
                            <div class="col-lg-3 m-lg-auto">
                                <span class="font-weight-bold font-italic text-info-800">{{ Usr::getDateFormat() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- EXAM SETTINGS --}}
        <div class="card card-collapsed">
            <div class="card-header header-elements-inline">
                <h6 class="card-title font-weight-semibold">
                    <i class="material-symbols-rounded mr-2 align-middle">article</i>
                    Exam Settings
                </h6>
                {!! Qs::getPanelOptionsHtml() !!}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        {{-- Lock Exam --}}
                        <div class="form-group row">
                            <label for="lock_exam" class="col-lg-4 col-form-label font-weight-semibold"
                                >Lock Exam</label>
                            <div class="col-lg-3">
                                <select id="lock_exam" name="lock_exam" class="form-control select">
                                    <option value="1" @selected($settings->firstWhere('key', 'lock_exam')->value == 1)>
                                        Yes
                                    </option>
                                    <option value="0" @selected($settings->firstWhere('key', 'lock_exam')->value == 0)>
                                        No
                                    </option>
                                </select>
                            </div>
                            <div class="col-lg-5 m-lg-auto">
                                <span class="font-weight-bold font-italic text-info-800">{{ __('msg.lock_exam') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- NEXT TERM FEES --}}
        <div class="card card-collapsed">
            <div class="card-header header-elements-inline">
                <h6 class="card-title font-weight-semibold">
                    <i class="material-symbols-rounded mr-2 align-middle">payments</i>
                    Next Term Fees ({{ Pay::getDefaultCurrencySymbol() }})
                </h6>
                {!! Qs::getPanelOptionsHtml() !!}
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach ($class_types as $ct)
                        <div class="col-auto mb-2">
                            <div class="form-group mb-0">
                                <label
                                    for="next_term_fees_{{ strtolower($ct->code) }}"
                                    class="d-block font-weight-semibold text-nowrap"
                                >
                                    {{ $ct->name }}
                                </label>
                                <input
                                    id="next_term_fees_{{ strtolower($ct->code) }}"
                                    name="next_term_fees_{{ strtolower($ct->code) }}"
                                    type="text"
                                    class="form-control"
                                    style="width: 120px"
                                    value="{{ $settings->firstWhere('key', 'next_term_fees_' . strtolower($ct->code))->value }}"
                                    placeholder="{{ $ct->name }}"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- NOTIFICATIONS --}}
        <div class="card card-collapsed">
            <div class="card-header header-elements-inline">
                <h6 class="card-title font-weight-semibold">
                    <i class="material-symbols-rounded mr-2 align-middle">notifications</i>
                    Notifications (Global)
                </h6>
                {!! Qs::getPanelOptionsHtml() !!}
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach (['email', 'push', 'sms'] as $channel)
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label
                                    for="{{ $channel }}_channel"
                                    class="col-lg-5 col-form-label font-weight-semibold text-capitalize"
                                >
                                    {{ $channel }}
                                </label>
                                <div class="col-lg-7">
                                    <select
                                        id="{{ $channel }}_channel"
                                        name="enable_{{ $channel }}_notification"
                                        class="form-control select"
                                    >
                                        <option
                                            value="1"
                                            @selected($settings->firstWhere('key', "enable_{$channel}_notification")->value == 1)
                                        >
                                            On
                                        </option>
                                        <option
                                            value="0"
                                            @selected($settings->firstWhere('key', "enable_{$channel}_notification")->value == 0)
                                        >
                                            Off
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- BRANDING & APPEARANCE --}}
        <div class="card card-collapsed">
            <div class="card-header header-elements-inline">
                <h6 class="card-title font-weight-semibold">
                    <i class="material-symbols-rounded mr-2 align-middle">palette</i>
                    Branding & Appearance
                </h6>
                {!! Qs::getPanelOptionsHtml() !!}
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Logo --}}
                    <div class="col-lg-6">
                        <div class="form-group text-center">
                            <label for="logo" class="col-form-label font-weight-semibold">Change Logo:</label>
                            <div class="mb-3">
                                <img
                                    style="width: 100px; height: 100px"
                                    src="{{ Usr::getTenancyAwareImgOrDefaultBgUrl($settings->firstWhere('key', 'logo')) }}"
                                />
                            </div>
                            <input
                                id="logo"
                                name="logo"
                                type="file"
                                accept="image/*"
                                class="file-input"
                                data-show-caption="false"
                                data-show-upload="false"
                            />
                        </div>
                    </div>
                    {{-- Login Background --}}
                    <div class="col-lg-6">
                        <div class="form-group text-center">
                            <label for="login-and-related-pgs-bg-input" class="col-form-label font-weight-semibold">
                                Change Login Page Background:
                            </label>
                            <div class="mb-3">
                                <img
                                    id="login-and-related-pgs-bg"
                                    style="width: 100px; height: 100px"
                                    src="{{ Usr::getTenancyAwareImgOrDefaultBgUrl($settings->firstWhere('key', 'login_and_related_pages_bg')) }}"
                                />
                            </div>
                            <input
                                id="login-and-related-pgs-bg-input"
                                name="login_and_related_pages_bg"
                                type="file"
                                accept="image/*"
                                class="file-input"
                                data-show-caption="false"
                                data-show-upload="false"
                            />
                        </div>
                    </div>
                </div>
                {{-- Texts & BG Colors --}}
                <div class="form-group row">
                    <label for="texts_and_bg_colors" class="col-lg-4 col-form-label font-weight-semibold"
                        >Texts and background colors</label>
                    <div class="col-lg-8">
                        <div class="form-group input-group-text auth-pages-preview default border-0 bg-transparent p-0 text-center">
                            <select
                                disabled
                                class="form-control select"
                                name="texts_and_bg_colors"
                                id="texts_and_bg_colors"
                            >
                                <option selected value="default">Use default colors</option>
                                <option value="from_img">Generate from the image</option>
                            </select>
                            {{-- Show preview? --}}
                            <div class="form-check mt-auto mr-2 mb-auto ml-1">
                                <label class="form-check-label">
                                    Preview
                                    <input
                                        type="checkbox"
                                        name="show_login_and_related_pgs_preview"
                                        class="form-input-styled"
                                    />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                @if (session()->has('show_login_and_related_pgs_preview'))
                    @include('pages.modals.auth_pages_preview')
                @endif
            </div>
        </div>

        {{-- PASSWORD POLICY --}}
        <div class="card card-collapsed">
            <div class="card-header header-elements-inline">
                <h6 class="card-title font-weight-semibold">
                    <i class="material-symbols-rounded mr-2 align-middle">lock</i>
                    Password Policy
                </h6>
                {!! Qs::getPanelOptionsHtml() !!}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-lg-6 col-form-label font-weight-semibold">Minimum length</label>
                            <div class="col-lg-6">
                                <input
                                    type="number"
                                    min="6"
                                    max="64"
                                    name="password_min_length"
                                    value="{{ old('password_min_length', $settings->firstWhere('key', 'password_min_length')->value ?? 8) }}"
                                    class="form-control"
                                />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-6 col-form-label font-weight-semibold">Require confirmation</label>
                            <div class="col-lg-6">
                                <select class="form-control select" name="password_require_confirmation">
                                    <option
                                        value="1"
                                        @selected($settings->firstWhere('key', 'password_require_confirmation')->value == 1)
                                    >
                                        Yes
                                    </option>
                                    <option
                                        value="0"
                                        @selected($settings->firstWhere('key', 'password_require_confirmation')->value == 0)
                                    >
                                        No
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-6 col-form-label font-weight-semibold">Require mixed case</label>
                            <div class="col-lg-6">
                                <select class="form-control select" name="password_require_mixed_case">
                                    <option
                                        value="1"
                                        @selected($settings->firstWhere('key', 'password_require_mixed_case')->value == 1)
                                    >
                                        Yes
                                    </option>
                                    <option
                                        value="0"
                                        @selected($settings->firstWhere('key', 'password_require_mixed_case')->value == 0)
                                    >
                                        No
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-6 col-form-label font-weight-semibold">Require letters</label>
                            <div class="col-lg-6">
                                <select class="form-control select" name="password_require_letters">
                                    <option
                                        value="1"
                                        @selected($settings->firstWhere('key', 'password_require_letters')->value == 1)
                                    >
                                        Yes
                                    </option>
                                    <option
                                        value="0"
                                        @selected($settings->firstWhere('key', 'password_require_letters')->value == 0)
                                    >
                                        No
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-lg-6 col-form-label font-weight-semibold">Require numbers</label>
                            <div class="col-lg-6">
                                <select class="form-control select" name="password_require_numbers">
                                    <option
                                        value="1"
                                        @selected($settings->firstWhere('key', 'password_require_numbers')->value == 1)
                                    >
                                        Yes
                                    </option>
                                    <option
                                        value="0"
                                        @selected($settings->firstWhere('key', 'password_require_numbers')->value == 0)
                                    >
                                        No
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-6 col-form-label font-weight-semibold">Require symbols</label>
                            <div class="col-lg-6">
                                <select class="form-control select" name="password_require_symbols">
                                    <option
                                        value="1"
                                        @selected($settings->firstWhere('key', 'password_require_symbols')->value == 1)
                                    >
                                        Yes
                                    </option>
                                    <option
                                        value="0"
                                        @selected($settings->firstWhere('key', 'password_require_symbols')->value == 0)
                                    >
                                        No
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-6 col-form-label font-weight-semibold">Reject compromised passwords</label>
                            <div class="col-lg-6">
                                <select class="form-control select" name="password_require_uncompromised">
                                    <option
                                        value="1"
                                        @selected($settings->firstWhere('key', 'password_require_uncompromised')->value == 1)
                                    >
                                        Yes
                                    </option>
                                    <option
                                        value="0"
                                        @selected($settings->firstWhere('key', 'password_require_uncompromised')->value == 0)
                                    >
                                        No
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-6 col-form-label font-weight-semibold">
                                Remember last N passwords
                                <i
                                    class="material-symbols-rounded text-info float-right"
                                    data-toggle="tooltip"
                                    title="Set to 0 to disable password history checking."
                                >info</i>
                            </label>
                            <div class="col-lg-6">
                                <input
                                    type="number"
                                    min="0"
                                    max="20"
                                    name="password_history_count"
                                    value="{{ old('password_history_count', $settings->firstWhere('key', 'password_history_count')->value ?? 0) }}"
                                    class="form-control"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ADMIN CONTACTS --}}
        <div class="card card-collapsed">
            <div class="card-header header-elements-inline">
                <h6 class="card-title font-weight-semibold">
                    <i class="material-symbols-rounded mr-2 align-middle">contact_phone</i>
                    Admin Contacts
                </h6>
                {!! Qs::getPanelOptionsHtml() !!}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label for="admin_email" class="col-lg-4 col-form-label font-weight-semibold"
                                >Admin Email <span class="text-danger">*</span></label>
                            <div class="col-lg-8">
                                <input
                                    id="admin_email"
                                    name="admin_email"
                                    required
                                    type="email"
                                    class="form-control"
                                    value="{{ $settings->firstWhere('key','admin_email')->value }}"
                                    placeholder="Email"
                                />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="admin_whatsapp_link" class="col-lg-4 col-form-label font-weight-semibold"
                                >WhatsApp Link</label>
                            <div class="col-lg-8">
                                <input
                                    id="admin_whatsapp_link"
                                    name="admin_whatsapp_link"
                                    type="text"
                                    class="form-control"
                                    value="{{ $settings->firstWhere('key','admin_whatsapp_link')->value }}"
                                    placeholder="https://wa.me/255123456789"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label for="admin_facebook_link" class="col-lg-4 col-form-label font-weight-semibold"
                                >Facebook Link</label>
                            <div class="col-lg-8">
                                <input
                                    id="admin_facebook_link"
                                    name="admin_facebook_link"
                                    type="text"
                                    class="form-control"
                                    value="{{ $settings->firstWhere('key','admin_facebook_link')->value }}"
                                    placeholder="https://www.facebook.com/username"
                                />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="admin_github_link" class="col-lg-4 col-form-label font-weight-semibold"
                                >GitHub Link</label>
                            <div class="col-lg-8">
                                <input
                                    id="admin_github_link"
                                    name="admin_github_link"
                                    type="text"
                                    class="form-control"
                                    value="{{ $settings->firstWhere('key','admin_github_link')->value }}"
                                    placeholder="https://github.com/username"
                                />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="admin_x_link" class="col-lg-4 col-form-label font-weight-semibold"
                                >X (Twitter) Link</label>
                            <div class="col-lg-8">
                                <input
                                    id="admin_x_link"
                                    name="admin_x_link"
                                    type="text"
                                    class="form-control"
                                    value="{{ $settings->firstWhere('key','admin_x_link')->value }}"
                                    placeholder="https://x.com/username"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SUBMIT (floating so it's always reachable) --}}
        <div class="position-fixed" style="bottom: 1rem; right: 1rem; z-index: 1050">
            <button type="submit" class="btn btn-danger shadow">
                Save Settings <i class="material-symbols-rounded ml-2">save</i>
            </button>
        </div>
    </form>

    {{-- FEATURE MODULES (outside form — saves instantly via AJAX) --}}
    @php
        $moduleService = app(\App\Services\FeatureModuleService::class);
    @endphp

    <div class="card card-collapsed">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">
                <i class="material-symbols-rounded mr-2 align-middle">extension</i>
                Feature Modules
            </h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>
        <div class="card-body">
            <p class="text-muted">
                Turn optional modules on or off. Disabling a module removes it from every menu and blocks all of its
                permissions, even if a role still has them.
            </p>

            <div class="row">
                @foreach ($moduleService->all() as $key => $module)
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="d-flex justify-content-between align-items-center card-body">
                                <div>
                                    <h6 class="d-flex align-items-center mb-0">
                                        <i class="material-symbols-rounded mr-2 align-middle">{{ $module['icon'] ?? 'extension' }}</i>
                                        <span>{{ $module['label'] }}</span>
                                    </h6>
                                    <small class="text-muted">{{ $module['description'] }}</small>
                                </div>
                                <label class="form-switch m-0">
                                    <input
                                        type="checkbox"
                                        class="module-toggle"
                                        data-module="{{ $key }}"
                                        data-url="{{ route('settings.modules.toggle', $key) }}"
                                        @checked($moduleService->isEnabled($key))
                                    />
                                    <i></i>
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection
