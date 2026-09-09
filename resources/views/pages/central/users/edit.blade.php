@extends('layouts.master')

@section('page_title', "Edit User - $user->name")

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Edit User Details</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <form
                method="post"
                enctype="multipart/form-data"
                class="wizard-form steps-validation ajax-update"
                data-reload="#page-title"
                action="{{ route('central_users.update', Qs::hash($user->id)) }}"
            >
                @csrf
                @method('PUT')
                <h6>Personal Data</h6>

                <fieldset>
                    <div class="row">
                        {{-- USER ROLES --}}
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="roles">Select Role(s): <span class="text-danger">*</span></label>
                                <select
                                    class="form-control select"
                                    multiple="multiple"
                                    placeholder="Select Role(s)"
                                    name="roles[]"
                                    id="roles"
                                >
                                    @foreach ($roles as $role)
                                        <option
                                            @selected($user->hasRole($role->name))
                                            data-permissions='@json($role->permissions->pluck('name'))'
                                            data-all="{{ in_array($role->name, Usr::getTenancyAwareRootRoles()) }}"
                                            value="{{ $role->name }}"
                                        >
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- NAME --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Full Name: <span class="text-danger">*</span></label>
                                <input
                                    id="name"
                                    value="{{ $user->name }}"
                                    required
                                    type="text"
                                    name="name"
                                    placeholder="Full Name"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        {{-- ADDRESS --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="address">Address: <span class="text-danger">*</span></label>
                                <input
                                    id="address"
                                    value="{{ $user->address }}"
                                    class="form-control"
                                    placeholder="Address"
                                    name="address"
                                    type="text"
                                    required
                                />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            {{-- PERMISSIONS --}}
                            <div class="form-group">
                                <label for="permissions">Select Permission(s) (Optional):</label>
                                <select
                                    class="form-control select"
                                    multiple="multiple"
                                    placeholder="Select Permission(s)"
                                    name="permissions[]"
                                    id="permissions"
                                >
                                    @foreach ($permissions as $perm)
                                        <option
                                            @selected($user->can($perm->name))
                                            value="{{ $perm->name }}"
                                            title="{{ $perm->description ?? $perm->label ?? $perm->name }}"
                                        >
                                            {{ $perm->label ?? $perm->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {{-- EMAIL ADDRESS --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="email">Email address: </label>
                                <input
                                    id="email"
                                    value="{{ $user->email }}"
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="your@email.com"
                                />
                            </div>
                        </div>
                        {{-- MOBILES --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="primary_mobile">Primary Mobile:</label>
                                <input
                                    id="primary_mobile"
                                    value="{{ $user->primary_mobile }}"
                                    type="text"
                                    name="primary_mobile"
                                    class="form-control"
                                    placeholder="+255123456789"
                                />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="secondary_mobile">Secondary Mobile:</label>
                                <input
                                    id="secondary_mobile"
                                    value="{{ $user->secondary_mobile }}"
                                    type="text"
                                    name="secondary_mobile"
                                    class="form-control"
                                    placeholder="+255123456789"
                                />
                            </div>
                        </div>
                        {{-- BLOOD GROUP --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="blood_group_id">Blood Group: </label>
                                <select
                                    class="select form-control"
                                    id="blood_group_id"
                                    name="blood_group_id"
                                    data-placeholder="Choose.."
                                >
                                    <option value=""></option>
                                    @foreach ($blood_groups as $bg)
                                        <option @selected($user->blood_group_id == $bg->id) value="{{ $bg->id }}">
                                            {{ $bg->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- GENDER --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="gender">Gender: <span class="text-danger">*</span></label>
                                <select
                                    class="select form-control"
                                    id="gender"
                                    name="gender"
                                    required
                                    data-placeholder="Choose.."
                                >
                                    <option value=""></option>
                                    @foreach (Usr::getGenders() as $gender)
                                        <option @selected($user->gender == $gender) value="{{ $gender }}">
                                            {{ ucfirst($gender) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- NATIONALITY --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nationality_id">Nationality: <span class="text-danger">*</span></label>
                                <select
                                    onchange="getState(this.value)"
                                    data-placeholder="Choose..."
                                    required
                                    name="nationality_id"
                                    id="nationality_id"
                                    class="select-search form-control"
                                >
                                    <option value=""></option>
                                    @foreach ($nationals as $nal)
                                        <option @selected($user->nationality_id == $nal->id) value="{{ $nal->id }}">
                                            {{ $nal->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- STATE OR REGION --}}
                        <div class="col-md-3">
                            <label for="state_id">State/Reion: <span class="text-danger">*</span></label>
                            <select
                                onchange="getLGA(this.value)"
                                required
                                data-placeholder="Select Nationality First.."
                                class="select-search form-control"
                                name="state_id"
                                id="state_id"
                            >
                                <option value="{{ $user->state_id ?? '' }}">{{ $user->state->name ?? '' }}</option>
                            </select>
                        </div>
                        {{-- LGA --}}
                        <div class="col-md-3">
                            <label for="lga_id">LGA: <span class="text-danger">*</span></label>
                            <select
                                required
                                data-placeholder="Select State First"
                                class="select-search form-control"
                                name="lga_id"
                                id="lga_id"
                            >
                                <option value="{{ $user->lga_id ?? '' }}">{{ $user->lga->name ?? '' }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        {{-- PRIMARY ID --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="primary_identification_number" class="d-block">Primary ID number:</label>
                                <input
                                    id="primary_identification_number"
                                    value="{{ $user->primary_identification_number ?? '' }}"
                                    type="text"
                                    name="primary_identification_number"
                                    class="form-control"
                                    placeholder="123456789"
                                />
                            </div>
                        </div>
                        {{-- SECONDARY ID --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="secondary_identification_number" class="d-block"
                                    >Secondary ID number:</label>
                                <input
                                    id="secondary_identification_number"
                                    value="{{ $user->secondary_identification_number ?? '' }}"
                                    type="text"
                                    name="secondary_identification_number"
                                    class="form-control"
                                    placeholder="12345678123451234512"
                                />
                            </div>
                        </div>
                        {{-- RELIGION --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="religion">Religion: <span class="text-danger">*</span></label>
                                <select
                                    required
                                    data-placeholder="Choose..."
                                    name="religion"
                                    id="religion"
                                    class="select-search form-control"
                                >
                                    <option value=""></option>
                                    @foreach (Usr::getReligions() as $rel)
                                        <option @selected($user->religion == $rel) value="{{ $rel }}">
                                            {{ $rel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- DOB --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth:</label>
                                <input
                                    id="date_of_birth"
                                    name="date_of_birth"
                                    value="{{ $user->date_of_birth }}"
                                    type="text"
                                    class="form-control date-pick"
                                    placeholder="Select Date..."
                                />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- PASSPORT --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="photo" class="d-block">Upload Passport Photo:</label>
                                <input
                                    id="photo"
                                    value="{{ old('photo') }}"
                                    accept="image/*"
                                    type="file"
                                    name="photo"
                                    class="form-input-styled"
                                />
                                <span class="form-text text-muted">Accepted Images: jpeg, png. Max file size 2Mb</span>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <h6>Staff Data</h6>
                <fieldset>
                    <div class="row">
                        {{-- DATE OF EMPLOYMENT --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="emp-date">Date of Employment:</label>
                                <input
                                    autocomplete="off"
                                    id="emp-date"
                                    name="employment_date"
                                    value="{{ $staff_rec->employment_date ?? '' }}"
                                    type="text"
                                    class="form-control date-pick"
                                    placeholder="Select Date..."
                                />
                            </div>
                        </div>
                        {{-- DATE OF CONFIRMATION --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="confirmation-date">Date of Confirmation:</label>
                                <input
                                    autocomplete="off"
                                    id="confirmation-date"
                                    name="confirmation_date"
                                    value="{{ $staff_rec->confirmation_date ?? '' }}"
                                    type="text"
                                    class="form-control date-pick"
                                    placeholder="Select Date..."
                                />
                            </div>
                        </div>
                        {{-- LICENCE NUMBER --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="licence_number">Licence Number:</label>
                                <input
                                    id="licence_number"
                                    name="licence_number"
                                    value="{{ $staff_rec->licence_number ?? '' }}"
                                    type="text"
                                    class="form-control"
                                    placeholder="Ie., LTT 12345"
                                />
                            </div>
                        </div>
                        {{-- FILE NUMBER --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="file_number">File Number:</label>
                                <input
                                    id="file_number"
                                    name="file_number"
                                    value="{{ $staff_rec->file_number ?? '' }}"
                                    type="text"
                                    class="form-control"
                                    placeholder="EPP/123/12/1"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- SOCIAL SECURITY NUMBER --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="social_security_number">Social Security Number:</label>
                                <input
                                    id="social_security_number"
                                    name="social_security_number"
                                    value="{{ $staff_rec->social_security_number ?? '' }}"
                                    type="number"
                                    min="1"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        {{-- EMPLOYMENT NUMBER --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="employment_number">Employment Number:</label>
                                <input
                                    id="employment_number"
                                    name="employment_number"
                                    value="{{ $staff_rec->employment_number ?? '' }}"
                                    type="number"
                                    min="1"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        {{-- TIN NUMBER --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tin_number">TIN Number:</label>
                                <input
                                    id="tin_number"
                                    name="tin_number"
                                    value="{{ $staff_rec->tin_number ?? '' }}"
                                    type="text"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        {{-- BANK ACCOUNT NUMBER --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Bank Account Number:</label>
                                <input
                                    name="bank_account_number"
                                    value="{{ $staff_rec->bank_account_number ?? '' }}"
                                    type="number"
                                    class="form-control"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- BANK NAME --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="bank_name">Bank Name:</label>
                                <input
                                    id="bank_name"
                                    name="bank_name"
                                    value="{{ $staff_rec->bank_name ?? '' }}"
                                    type="text"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        {{-- EDUCATION LEVEL --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="education_level">Education Level: </label>
                                <select
                                    class="select-search form-control"
                                    id="education_level"
                                    name="education_level"
                                    data-placeholder="Choose.."
                                >
                                    <option value=""></option>
                                    @foreach (Usr::getEducationLevels() as $lv)
                                        <option @selected($staff_rec?->education_level == $lv) value="{{ $lv }}">
                                            {{ $lv }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- COLLEGE ATTENDED --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="college_attended">College Ateended: </label>
                                <input
                                    name="college_attended"
                                    value="{{ $staff_rec->college_attended ?? '' }}"
                                    type="text"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        {{-- PLACE OF LIVING --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="place_of_living">Place of Living: </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="place_of_living"
                                    id="place_of_living"
                                    value="{{ $staff_rec->place_of_living ?? '' }}"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- GRADUATION YEAR --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="year_graduated">Graduation Year: </label>
                                <select
                                    name="year_graduated"
                                    data-placeholder="Choose..."
                                    id="year_graduated"
                                    class="select-search form-control"
                                >
                                    <option value=""></option>
                                    @for ($y = date('Y', strtotime('- 30 years')); $y <= date('Y'); $y++)
                                        <option @selected($staff_rec?->year_graduated == $y) value="{{ $y }}">
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        {{-- DESIGNATION --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="role">Designation: </label>
                                <select class="select form-control" id="role" name="role" data-placeholder="Choose..">
                                    <option value=""></option>
                                    @foreach (Usr::getStaffDesignations() as $designation)
                                        <option
                                            @selected($staff_rec?->designation == $designation)
                                            value="{{ $designation }}"
                                        >
                                            {{ $designation }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- SUBJECTS STUDIED --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subjects_studied">Subjects Studied <span class="text-info">(comma (,) separated)</span>:</label>
                                <div>
                                    <textarea
                                        name="subjects_studied"
                                        class="form-control"
                                        placeholder="ie., Subject one, Subject two, ..."
                                    >{{ $staff_rec->subjects_studied ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>

@endsection
