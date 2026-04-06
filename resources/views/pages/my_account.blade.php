@extends('layouts.master')

@section('page_title', 'My Account')

@section('content')

@php
$errors_has_password_error = str_contains($errors->first() ?? '', 'password');
@endphp

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">My Account</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">

            {{-- Change Password Tab --}}
            <li class="nav-item">
                <a href="#change-pass" class="{{ $errors_has_password_error 
                       ? 'nav-link active' 
                       : ($errors->any() 
                           ? 'nav-link' 
                           : 'nav-link active') }}" data-toggle="tab">
                    Change Password
                </a>
            </li>

            {{-- Edit Profile Tab --}}
            <li class="nav-item">
                <a href="#edit-profile" class="{{ $errors_has_password_error 
                       ? 'nav-link' 
                       : ($errors->any() 
                           ? 'nav-link active' 
                           : 'nav-link') }}" data-toggle="tab">
                    Manage Profile
                </a>
            </li>

            {{-- Preferences Settings Tab --}}
            <li class="nav-item">
                <a href="#preferences" class="nav-link" data-toggle="tab">
                    Preferences
                </a>
            </li>
        </ul>

        <div class="tab-content">

            {{-- Change Password Pane --}}
            <div id="change-pass" class="tab-pane fade {{ $errors_has_password_error 
                     ? 'show active' 
                     : ($errors->any() 
                         ? 'show' 
                         : 'show active') }}">
                <div class="row">
                    <div class="col-md-8">
                        <form method="post" action="{{ route('my_account.change_pass') }}">
                            @csrf @method('put')
                            <div class="form-group row">
                                <label for="current_password" class="col-lg-3 col-form-label font-weight-semibold">Current Password <span class="text-danger">*</span></label>
                                <div class="col-lg-9">
                                    <input id="current_password" name="current_password" required type="password" class="form-control" autocomplete="current-password">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="new_password" class="col-lg-3 col-form-label font-weight-semibold">New Password <span class="text-danger">*</span></label>
                                <div class="col-lg-9">
                                    <input id="new_password" name="new_password" required type="password" class="form-control" autocomplete="new-password">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="new_password_confirmation" class="col-lg-3 col-form-label font-weight-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <div class="col-lg-9">
                                    <input id="new_password_confirmation" name="new_password_confirmation" required type="password" class="form-control" autocomplete="new-password">
                                </div>
                            </div>
                            <div class="float-right">
                                <button type="submit" class="btn btn-danger">Submit form <i class="material-symbols-rounded ml-2">send</i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Edit Profile Pane --}}
            <div id="edit-profile" class="tab-pane fade {{ $errors_has_password_error 
                    ? '' 
                    : ($errors->any() 
                        ? 'show active' 
                        : '') }}">
                <form enctype="multipart/form-data" method="post" action="{{ route('my_account.update') }}">
                    @csrf @method('put')
                    <div class="row">
                        <div class="col-md-10">
                            {{-- Name (disabled) --}}
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Name</label>
                                <div class="col-lg-9">
                                    <input disabled class="form-control" type="text" value="{{ $my->name }}">
                                </div>
                            </div>

                            {{-- Username --}}
                            @if($my->username)
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Username</label>
                                <div class="col-lg-9">
                                    <input disabled class="form-control" type="text" value="{{ $my->username }}">
                                </div>
                            </div>
                            @else
                            <div class="form-group row">
                                <label for="username" class="col-lg-3 col-form-label font-weight-semibold">Username</label>
                                <div class="col-lg-9">
                                    <input id="username" name="username" type="text" class="form-control" value="{{ old('username') }}">
                                </div>
                            </div>
                            @endif

                            {{-- Date of Birth --}}
                            <div class="form-group row">
                                <label for="dob" class="col-lg-3 col-form-label font-weight-semibold">Date of Birth</label>
                                <div class="col-lg-9">
                                    <input id="dob" name="dob" value="{{ $my->dob ?? old('dob') }}" type="text" class="form-control date-pick" placeholder="Select Date...">
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Email
                                    <span class="text-{{ auth()->user()->hasVerifiedEmail() ? 'success' : 'warning' }}">
                                        ({{ auth()->user()->hasVerifiedEmail() ? 'verified' : 'not verified' }})
                                    </span>
                                </label>
                                <div class="input-group col-lg-9">
                                    <input id="email" name="email" type="email" class="form-control" value="{{ $my->email ?? old('email') }}">
                                    @unless(auth()->user()->hasVerifiedEmail())
                                    <div class="input-group-append">
                                        <span class="input-group-text border-0 bg-transparent">
                                            <a href="{{ route('verification.send') }}" class="text-success font-weight-semibold">Send Link</a>
                                        </span>
                                    </div>
                                    @endunless
                                </div>
                            </div>

                            {{-- Phone --}}
                            <div class="form-group row">
                                <label for="phone" class="col-lg-3 col-form-label font-weight-semibold">Phone</label>
                                <div class="col-lg-9">
                                    <input id="phone" name="phone" type="text" data-mask="+9999?999999999" class="form-control" value="{{ $my->phone ?? old('phone') }}">
                                </div>
                            </div>

                            {{-- Secondary Phone --}}
                            <div class="form-group row">
                                <label for="phone2" class="col-lg-3 col-form-label font-weight-semibold">Telephone</label>
                                <div class="col-lg-9">
                                    <input id="phone2" name="phone2" type="text" data-mask="+9999?999999999" class="form-control" value="{{ $my->phone2 ?? old('phone2') }}">
                                </div>
                            </div>

                            {{-- Primary ID --}}
                            <div class="form-group row">
                                <label for="primary_id" class="col-lg-3 col-form-label font-weight-semibold">Primary ID</label>
                                <div class="col-lg-9">
                                    <input id="primary_id" name="primary_id" type="text" data-mask="www?wwwwwwwwwwwwwwwww" class="form-control" value="{{ $my->primary_id ?? old('primary_id') }}">
                                </div>
                            </div>

                            {{-- Secondary ID --}}
                            <div class="form-group row">
                                <label for="secondary_id" class="col-lg-3 col-form-label font-weight-semibold">Secondary ID</label>
                                <div class="col-lg-9">
                                    <input id="secondary_id" name="secondary_id" type="text" data-mask="www?wwwwwwwwwwwwwwwww" class="form-control" value="{{ $my->secondary_id ?? old('secondary_id') }}">
                                </div>
                            </div>

                            {{-- Address --}}
                            <div class="form-group row">
                                <label for="address" class="col-lg-3 col-form-label font-weight-semibold">Address</label>
                                <div class="col-lg-9">
                                    <input id="address" name="address" type="text" class="form-control" value="{{ $my->address ?? old('address') }}">
                                </div>
                            </div>

                            {{-- Photo --}}
                            <div class="form-group row">
                                <label for="photo" class="col-lg-3 col-form-label font-weight-semibold">Change Photo</label>
                                <div class="col-lg-9">
                                    <input id="photo" name="photo" type="file" accept="image/*" class="form-input-styled" data-fouc>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="float-right">
                        <button type="submit" class="btn btn-danger">Submit form <i class="material-symbols-rounded ml-2">send</i></button>
                    </div>
                </form>
            </div>

            {{-- Preferences Settings Pane --}}
            <div id="preferences" class="tab-pane fade">
                <form method="post" action="{{ route('my_account.update_preferences') }}">
                    @csrf @method('put')
                    <div class="row">
                        <div class="col-md-4 d-none d-md-block">
                            <div class="form-group">
                                <label for="always_minimize_sidebar" class="col-form-label font-weight-semibold">Always Minimize Sidebar <span class="text-danger">*</span></label>
                                <select id="always_minimize_sidebar" name="always_minimize_sidebar" class="form-control select">
                                    <option value="1" @selected($user_preferences['always_minimize_sidebar']==1)>Yes</option>
                                    <option value="0" @selected($user_preferences['always_minimize_sidebar']==0)>No</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="allow_system_sounds" class="col-form-label font-weight-semibold">Allow System Sounds <span class="text-danger">*</span></label>
                                <select id="allow_system_sounds" name="allow_system_sounds" class="form-control select">
                                    <option value="1" @selected($user_preferences['allow_system_sounds']==1)>Yes</option>
                                    <option value="0" @selected($user_preferences['allow_system_sounds']==0)>No</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="active_system_skin" class="col-form-label font-weight-semibold">System Skin <span class="text-danger">*</span></label>
                                <select id="active_system_skin" name="active_system_skin" class="form-control select">
                                    @foreach (Usr::getAvailableAppSkins() as $skin => $description)
                                    <option value="{{ $skin }}" @selected($user_preferences['active_system_skin']==$skin) title="{{ $description }}">{{ ucfirst($skin) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="text-right float-right">
                                <button type="submit" class="btn btn-danger">Submit form <i class="material-symbols-rounded ml-2">send</i></button>
                            </div>
                        </div>
                    </div>
                </form>

                <hr>

                <div class="form-group row">
                    <label class="col-8 col-form-label font-weight-semibold">Reveal all hidden alert messages</label>
                    <div class="col-4 text-right">
                        <button id="clear-do-not-show-again-alert-msgs" type="button" class="btn btn-warning btn-sm">Reveal</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
