@extends('layouts.master')

@section('page_title', 'Manage System Settings')

@section('content')

@use('Database\Seeders\NonTenancySettingsTableSeeder')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title font-weight-semibold">Update System Settings</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <form enctype="multipart/form-data" method="post" action="{{ route('settings.update') }}">
            @csrf @method('PUT')

            <div class="row">
                <div class="col-md-6 border-right-blue-400">
                    {{-- School Name --}}
                    <div class="form-group row">
                        <label for="system_name" class="col-lg-3 col-form-label font-weight-semibold">Name of School <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <input id="system_name" name="system_name" required type="text" class="form-control" value="{{ $settings->firstWhere('key', 'system_name')->value }}" placeholder="Name of School">
                        </div>
                    </div>

                    {{-- Current Session --}}
                    <div class="form-group row">
                        <label for="current_session" class="col-lg-3 col-form-label font-weight-semibold">Current Session <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <select id="current_session" name="current_session" required class="select-search form-control" data-placeholder="Choose...">
                                <option value=""></option>
                                @for($y = date('Y') - 3; $y <= date('Y') + 2; $y++) @php $session=($y-1) . '-' . $y; @endphp <option value="{{ $session }}" @selected($settings->firstWhere('key', 'current_session')->value == $session)>
                                    {{ $session }} </option>
                                    @endfor
                            </select>
                        </div>
                    </div>

                    {{-- School Acronym --}}
                    <div class="form-group row">
                        <label for="system_title" class="col-lg-3 col-form-label font-weight-semibold">School Acronym</label>
                        <div class="col-lg-9">
                            <input id="system_title" name="system_title" type="text" class="form-control" value="{{ $settings->firstWhere('key','system_title')->value }}" placeholder="School Acronym">
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div class="form-group row">
                        <label for="phone" class="col-lg-3 col-form-label font-weight-semibold">Phone</label>
                        <div class="col-lg-9">
                            <input id="phone" name="phone" type="text" class="form-control" value="{{ $settings->firstWhere('key','phone')->value }}" placeholder="Phone">
                        </div>
                    </div>

                    {{-- School Email --}}
                    <div class="form-group row">
                        <label for="system_email" class="col-lg-3 col-form-label font-weight-semibold">School Email</label>
                        <div class="col-lg-9">
                            <input id="system_email" name="system_email" type="email" class="form-control" value="{{ $settings->firstWhere('key','system_email')->value }}" placeholder="School Email">
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="form-group row">
                        <label for="address" class="col-lg-3 col-form-label font-weight-semibold">School Address <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <input id="address" name="address" required type="text" class="form-control" value="{{ $settings->firstWhere('key','address')->value }}" placeholder="School Address">
                        </div>
                    </div>

                    {{-- Term Ends --}}
                    <div class="form-group row">
                        <label for="term_ends" class="col-lg-3 col-form-label font-weight-semibold">This Term Ends</label>
                        <div class="col-lg-6">
                            <input id="term_ends" name="term_ends" type="text" class="form-control date-pick" value="{{ $settings->firstWhere('key','term_ends')->value }}">
                        </div>
                        <div class="col-lg-3 m-lg-auto">
                            <span class="font-weight-bold font-italic text-info-800">{{ Usr::getDateFormat() }}</span>
                        </div>
                    </div>

                    {{-- Next Term Begins --}}
                    <div class="form-group row">
                        <label for="term_begins" class="col-lg-3 col-form-label font-weight-semibold">Next Term Begins</label>
                        <div class="col-lg-6">
                            <input id="term_begins" name="term_begins" type="text" class="form-control date-pick" value="{{ $settings->firstWhere('key','term_begins')->value }}">
                        </div>
                        <div class="col-lg-3 m-lg-auto">
                            <span class="font-weight-bold font-italic text-info-800">{{ Usr::getDateFormat() }}</span>
                        </div>
                    </div>

                    {{-- Lock Exam --}}
                    <div class="form-group row">
                        <label for="lock_exam" class="col-lg-3 col-form-label font-weight-semibold">Lock Exam</label>
                        <div class="col-lg-2">
                            <select id="lock_exam" name="lock_exam" class="form-control select">
                                <option value="1" @selected($settings->firstWhere('key','lock_exam')->value == 1)>Yes</option>
                                <option value="0" @selected($settings->firstWhere('key','lock_exam')->value == 0)>No</option>
                            </select>
                        </div>
                        <div class="col-lg-7 m-lg-auto">
                            <span class="font-weight-bold font-italic text-info-800">{{ __('msg.lock_exam') }}</span>
                        </div>
                    </div>

                    {{-- Enable Notifications --}}
                    <div class="form-group row">
                        <div class="col-12">
                            <label class="col-form-label font-weight-semibold">Enable Notifications (Globally)</label>
                            <div class="form-group row text-center">
                                @foreach(['email','push','sms'] as $channel)
                                <div class="col-4">
                                    <label for="{{ $channel }}_channel" class="col-form-label font-weight-semibold text-capitalize float-left">{{ $channel }}</label>
                                    <select id="{{ $channel }}_channel" name="enable_{{ $channel }}_notification" class="form-control select">
                                        <option value="1" @selected($settings->firstWhere('key',"enable_{$channel}_notification")->value == 1)>On</option>
                                        <option value="0" @selected($settings->firstWhere('key',"enable_{$channel}_notification")->value == 0)>Off</option>
                                    </select>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    {{-- Next Term Fees --}}
                    <fieldset>
                        <h6 class="font-weight-semibold">Next Term Fees ({{ Pay::getDefaultCurrencySymbol() }})</h6>
                        @foreach($class_types as $ct)
                        <div class="form-group row">
                            <label for="next_term_fees_{{ strtolower($ct->code) }}" class="col-lg-8 col-form-label font-weight-semibold">{{ $ct->name }}</label>
                            <div class="col-lg-4">
                                <input id="next_term_fees_{{ strtolower($ct->code) }}" name="next_term_fees_{{ strtolower($ct->code) }}" type="text" class="form-control" value="{{ $settings->firstWhere('key', 'next_term_fees_' . strtolower($ct->code))->value }}" placeholder="{{ $ct->name }}">
                            </div>
                        </div>
                        @endforeach
                    </fieldset>

                    <hr>

                    <div class="row">
                        {{-- Logo --}}
                        <div class="col-lg-6">
                            <div class="form-group text-center">
                                <label for="logo" class="col-form-label font-weight-semibold">Change Logo:</label>
                                <div class="mb-3">
                                    <img style="width:100px;height:100px" src="{{ Usr::getTenancyAwareImgOrDefaultBgUrl($settings->firstWhere('key', 'logo')) }}">
                                </div>
                                <input id="logo" name="logo" type="file" accept="image/*" class="file-input" data-show-caption="false" data-show-upload="false">
                            </div>
                        </div>
                        {{-- Login Background --}}
                        <div class="col-lg-6">
                            <div class="form-group text-center">
                                <label for="login-and-related-pgs-bg-input" class="col-form-label font-weight-semibold">
                                    Change Login Page Background:
                                </label>
                                <div class="mb-3">
                                    <img id="login-and-related-pgs-bg" style="width: 100px; height: 100px" src="{{ Usr::getTenancyAwareImgOrDefaultBgUrl($settings->firstWhere('key', 'login_and_related_pages_bg')) }}">
                                </div>
                                <input id="login-and-related-pgs-bg-input" name="login_and_related_pages_bg" type="file" accept="image/*" class="file-input" data-show-caption="false" data-show-upload="false">
                            </div>
                        </div>
                    </div>
                    {{-- Texts & BG Colors --}}
                    <div class="form-group row">
                        <label for="texts_and_bg_colors" class="col-lg-4 col-form-label font-weight-semibold">Texts and background colors</label>
                        <div class="col-lg-8">
                            <div class="form-group text-center input-group-text p-0 border-0 bg-transparent auth-pages-preview default">
                                <select disabled class="form-control select" name="texts_and_bg_colors" id="texts_and_bg_colors">
                                    <option selected value="default">Use default colors</option>
                                    <option value="from_img">Generate from the image</option>
                                </select>
                                {{-- Show preview? --}}
                                <div class="form-check mb-auto mt-auto ml-1 mr-2">
                                    <label class="form-check-label">
                                        Preview
                                        <input disabled type="checkbox" name="show_login_and_related_pgs_preview" class="form-input-styled">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(session()->has('show_login_and_related_pgs_preview'))
                    @include('pages.modals.auth_pages_preview')
                    @endif

                </div>
            </div>

            <hr>

            <div class="row">
                {{-- Admin Contacts --}}
                <div class="col-md-6">
                    <h6 class="font-weight-semibold">Admin Contacts</h6>
                    <div class="form-group row">
                        <label for="admin_email" class="col-lg-3 col-form-label font-weight-semibold">Admin Email <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <input id="admin_email" name="admin_email" required type="email" class="form-control" value="{{ $settings->firstWhere('key','admin_email')->value }}" placeholder="Email">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="admin_whatsapp_link" class="col-lg-3 col-form-label font-weight-semibold">WhatsApp Link</label>
                        <div class="col-lg-9">
                            <input id="admin_whatsapp_link" name="admin_whatsapp_link" type="text" class="form-control" value="{{ $settings->firstWhere('key','admin_whatsapp_link')->value }}" placeholder="https://wa.me/255123456789">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label for="admin_facebook_link" class="col-lg-3 col-form-label font-weight-semibold">Facebook Link</label>
                        <div class="col-lg-9">
                            <input id="admin_facebook_link" name="admin_facebook_link" type="text" class="form-control" value="{{ $settings->firstWhere('key','admin_facebook_link')->value }}" placeholder="https://www.facebook.com/username">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="admin_github_link" class="col-lg-3 col-form-label font-weight-semibold">Github Link</label>
                        <div class="col-lg-9">
                            <input id="admin_github_link" name="admin_github_link" type="text" class="form-control" value="{{ $settings->firstWhere('key','admin_github_link')->value }}" placeholder="https://github.com/username">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="admin_x_link" class="col-lg-3 col-form-label font-weight-semibold">X (formerly Twitter) Link</label>
                        <div class="col-lg-9">
                            <input id="admin_x_link" name="admin_x_link" type="text" class="form-control" value="{{ $settings->firstWhere('key','admin_x_link')->value }}" placeholder="https://www.linkedin.com/in/username">
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
</div>

@endsection
