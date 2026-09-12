{{-- PERSONAL DATA --}}
<h6 class="text-white">Personal Data</h6>
<fieldset>
    <div class="row">
        {{-- ROLES --}}
        <div class="col-md-5">
            <div class="form-group">
                <label for="roles">Select Role(s): <span class="text-danger">*</span></label>
                <select
                    class="form-control select"
                    @disabled($mode === 'invitation')
                    name="roles"
                    multiple="multiple"
                    placeholder="Select Role(s)"
                    id="roles"
                >
                    @foreach ($roles as $role)
                        <option
                            @selected($mode === 'invitation')
                            value="{{ $role->name }}"
                            data-permissions='@json($role->permissions->pluck('name'))'
                            data-all="{{ in_array($role->name, Usr::getTenancyAwareRootRoles()) }}"
                        >
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- FULL NAME --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="name"
                    >Full Name:
                    @if ($mode !== 'invite')
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <input
                    value="{{ old('name') }}"
                    @required($mode !== 'invite')
                    type="text"
                    name="name"
                    id="name"
                    placeholder="Full Name"
                    class="form-control text-capitalize"
                />
            </div>
        </div>
        {{-- ADDRESS --}}
        <div class="col-md-4">
            <div class="form-group">
                <label for="address"
                    >Address:
                    @if ($mode !== 'invite')
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <input
                    value="{{ old('address') }}"
                    class="form-control"
                    id="address"
                    placeholder="Address"
                    name="address"
                    type="text"
                    @required($mode !== 'invite')
                />
            </div>
        </div>
    </div>
    @if ($mode === 'create')
        <div class="row">
            <div class="col-12">
                {{-- PERMISSIONS --}}
                <div class="form-group">
                    <label for="permissions">Additional Permissions (Optional):</label>
                    <select
                        class="form-control select"
                        multiple="multiple"
                        placeholder="Select Permission(s)"
                        name="permissions[]"
                        id="permissions"
                    >
                        @foreach ($permissions as $perm)
                            <option
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
    @endif

    <div class="row">
        {{-- EMAIL ADDRESS --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="email">Email address: </label>
                <input
                    value="{{ old('email') }}"
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="your@email.com"
                />
            </div>
        </div>
        {{-- USERNAME --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="username">Username: </label>
                <input
                    value="{{ old('username') }}"
                    type="text"
                    id="username"
                    name="username"
                    class="form-control"
                    placeholder="Username"
                />
            </div>
        </div>
        {{-- MOBILES --}}
        <div class="col-md-3">
            <div class="form-group">
                <label id="primary_mobile">Primary Mobile:</label>
                <input
                    value="{{ old('primary_mobile') }}"
                    type="text"
                    name="primary_mobile"
                    id="primary_mobile"
                    class="form-control"
                    placeholder="+255123456789"
                />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="secondary_mobile">Secondary Mobile:</label>
                <input
                    value="{{ old('secondary_mobile') }}"
                    type="text"
                    id="secondary_mobile"
                    name="secondary_mobile"
                    class="form-control"
                    placeholder="+255123456789"
                />
            </div>
        </div>
    </div>

    <div class="row">
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
                        <option @selected(old('blood_group_id') == $bg->id) value="{{ $bg->id }}">
                            {{ $bg->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- PASSWORD --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="password">Password: </label>
                <input
                    id="password"
                    type="password"
                    autocomplete="current-password"
                    name="password"
                    class="form-control"
                    @disabled($mode === 'invite')
                />
            </div>
        </div>
        {{-- GENDER --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="gender"
                    >Gender:
                    @if ($mode !== 'invite')
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <select
                    class="select form-control"
                    id="gender"
                    name="gender"
                    @required($mode !== 'invite')
                    data-placeholder="Choose.."
                >
                    <option value=""></option>
                    @foreach (Usr::getGenders() as $gender)
                        <option @selected(old('gender') == $gender) value="{{ $gender }}">{{ $gender }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- NATIONALITY --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="nationality_id"
                    >Nationality:
                    @if ($mode !== 'invite')
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <select
                    onchange="getState(this.value)"
                    data-placeholder="Choose..."
                    @required($mode !== 'invite')
                    name="nationality_id"
                    id="nationality_id"
                    class="select-search form-control"
                >
                    <option value=""></option>
                    @foreach ($nationals as $nal)
                        <option @if (old('nationality_id') == $nal->id) selected @endif value="{{ $nal->id }}">
                            {{ $nal->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- STATE --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="state_id"
                    >State/Region:
                    @if ($mode !== 'invite')
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <select
                    onchange="getLGA(this.value)"
                    @required($mode !== 'invite')
                    data-placeholder="Select Nationality First"
                    class="select-search form-control"
                    name="state_id"
                    id="state_id"
                >
                    <option value=""></option>
                </select>
            </div>
        </div>
        {{-- LGA --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="lga_id"
                    >LGA:
                    @if ($mode !== 'invite')
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <select
                    @required($mode !== 'invite')
                    data-placeholder="Select State First"
                    class="select-search form-control"
                    name="lga_id"
                    id="lga_id"
                >
                    <option value=""></option>
                </select>
            </div>
        </div>
        {{-- PRIMARY ID --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="primary_identification_number" class="d-block">Primary ID number:</label>
                <input
                    value="{{ old('primary_identification_number') }}"
                    type="text"
                    id="primary_identification_number"
                    name="primary_identification_number"
                    class="form-control"
                    placeholder="123456789"
                />
            </div>
        </div>
        {{-- SECONDARY ID --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="secondary_identification_number" class="d-block">Secondary ID number:</label>
                <input
                    value="{{ old('secondary_identification_number') }}"
                    type="text"
                    id="secondary_identification_number"
                    name="secondary_identification_number"
                    class="form-control"
                    placeholder="12345678123451234512"
                />
            </div>
        </div>
    </div>

    <div class="display-none" id="parent-data">
        <div class="row">
            {{-- CLOSE RELATIVE NAME --}}
            <div class="col-md-6">
                <div class="form-group">
                    <label for="relative_name"
                        >Close Relative Name:
                        @if ($mode !== 'invite')
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <input
                        disabled
                        autocomplete="off"
                        id="relative_name"
                        name="relative_name"
                        @required($mode !== 'invite')
                        value="{{ old('relative_name') }}"
                        type="text"
                        class="form-control text-capitalize"
                        placeholder="Full name"
                    />
                </div>
            </div>
            {{-- RELATION WITH PARENT --}}
            <div class="col-md-6">
                <div class="form-group">
                    <label for="relation"
                        >Parent's Relation with the Relative:
                        @if ($mode !== 'invite')
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <input
                        disabled
                        value="{{ old('relation') }}"
                        id="relation"
                        type="text"
                        @required($mode !== 'invite')
                        name="relation"
                        class="form-control"
                        placeholder=""
                    />
                </div>
            </div>
        </div>

        <div class="row">
            {{-- PARENT OCCUPATION --}}
            <div class="col-md-6">
                <div class="form-group">
                    <label for="occupation"
                        >Parent Occupation:
                        @if ($mode !== 'invite')
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <input
                        disabled
                        autocomplete="off"
                        id="occupation"
                        @required($mode !== 'invite')
                        name="occupation"
                        value="{{ old('occupation') }}"
                        type="text"
                        class="form-control"
                        placeholder="The Occupation of the parent"
                    />
                </div>
            </div>
            {{-- CLOSE RELATIVE MOBILES --}}
            <div class="col-md-3">
                <div class="form-group">
                    <label for="relative_primary_mobile"
                        >Close Relative Primary Mobile:
                        @if ($mode !== 'invite')
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <input
                        disabled
                        value="{{ old('relative_primary_mobile') }}"
                        @required($mode !== 'invite')
                        type="text"
                        id="relative_primary_mobile"
                        name="relative_primary_mobile"
                        class="form-control"
                        placeholder="+255123456789"
                    />
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="relative_secondary_mobile">Close Relative Secondary Mobile: </label>
                    <input
                        disabled
                        value="{{ old('relative_secondary_mobile') }}"
                        type="text"
                        id="relative_secondary_mobile"
                        name="relative_secondary_mobile"
                        class="form-control"
                        placeholder="+255123456789"
                    />
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- RELIGION --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="religion"
                    >Religion:
                    @if ($mode !== 'invite')
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <select
                    @required($mode !== 'invite')
                    data-placeholder="Choose..."
                    name="religion"
                    id="religion"
                    class="select-search form-control"
                >
                    <option value=""></option>
                    @foreach (Usr::getReligions() as $rel)
                        <option @selected(old('religion') == $rel) value="{{ $rel }}">{{ $rel }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- DOB --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="date_of_birth">Date of Birth:</label>
                <input
                    name="date_of_birth"
                    value="{{ old('date_of_birth') }}"
                    type="text"
                    id="date_of_birth"
                    class="form-control date-pick"
                    placeholder="Select Date..."
                />
            </div>
        </div>
        {{-- PASSPORT --}}
        @if ($mode !== 'invite')
            <div class="col-md-6">
                <div class="form-group">
                    <label for="photo" class="d-block">Upload Passport Photo:</label>
                    <input
                        value="{{ old('photo') }}"
                        accept="image/*"
                        type="file"
                        id="photo"
                        name="photo"
                        class="form-input-styled"
                    />
                    <span class="form-text text-muted">Accepted Images: jpeg, png. Max file size 2Mb</span>
                </div>
            </div>
        @endif
    </div>
</fieldset>

{{-- STAFF DATA --}}
<h6>Staff Data</h6>
<fieldset>
    <div class="row">
        {{-- DATE OF EMPLOYMENT --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="employment_date">Date of Employment:</label>
                <input
                    type="text"
                    name="employment_date"
                    id="employment_date"
                    class="form-control date-pick"
                    placeholder="Select Date..."
                    value="{{ old('employment_date') }}"
                />
            </div>
        </div>
        {{-- DATE OF CONFIRMATION --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="confirmation_date">Date of Confirmation:</label>
                <input
                    type="text"
                    name="confirmation_date"
                    id="confirmation_date"
                    class="form-control date-pick"
                    placeholder="Select Date..."
                    value="{{ old('confirmation_date') }}"
                />
            </div>
        </div>
        {{-- LICENCE NUMBER --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="licence_number">Licence Number:</label>
                <input
                    type="text"
                    name="licence_number"
                    id="licence_number"
                    class="form-control"
                    placeholder="Ie., LTT 12345"
                    value="{{ old('licence_number') }}"
                />
            </div>
        </div>
        {{-- FILE NUMBER --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="file_number">File Number:</label>
                <input
                    type="text"
                    name="file_number"
                    id="file_number"
                    class="form-control"
                    placeholder="EFP/123/12/1"
                    value="{{ old('file_number') }}"
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
                    type="number"
                    name="social_security_number"
                    id="social_security_number"
                    class="form-control"
                    min="1"
                    value="{{ old('social_security_number') }}"
                />
            </div>
        </div>
        {{-- EMPLOYMENT NUMBER --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="employment_number">Employment Number:</label>
                <input
                    type="number"
                    name="employment_number"
                    id="employment_number"
                    class="form-control"
                    min="1"
                    value="{{ old('employment_number') }}"
                />
            </div>
        </div>
        {{-- TIN NUMBER --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="tin_number">TIN Number:</label>
                <input
                    type="text"
                    name="tin_number"
                    id="tin_number"
                    class="form-control"
                    value="{{ old('tin_number') }}"
                />
            </div>
        </div>
        {{-- BANK ACCOUNT NUMBER --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="bank_account_number">Bank Account Number:</label>
                <input
                    type="number"
                    name="bank_account_number"
                    id="bank_account_number"
                    class="form-control"
                    value="{{ old('bank_account_number') }}"
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
                    type="text"
                    name="bank_name"
                    id="bank_name"
                    class="form-control"
                    value="{{ old('bank_name') }}"
                />
            </div>
        </div>
        {{-- EDUCATION LEVEL --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="education_level">Education Level:</label>
                <select
                    name="education_level"
                    id="education_level"
                    class="form-control select-search"
                    data-placeholder="Choose.."
                >
                    <option value=""></option>
                    @foreach (Usr::getEducationLevels() as $lv)
                        <option value="{{ $lv }}" @selected(old('education_level') == $lv)>{{ $lv }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- COLLEDGE --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="college_attended">College Attended:</label>
                <input
                    type="text"
                    name="college_attended"
                    id="college_attended"
                    class="form-control"
                    value="{{ old('college_attended') }}"
                />
            </div>
        </div>
    </div>
    <div class="row">
        {{-- GRADUATION YEAR --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="year_graduated">Graduation Year:</label>
                <select
                    name="year_graduated"
                    id="year_graduated"
                    class="form-control select-search"
                    data-placeholder="Choose.."
                >
                    <option value=""></option>
                    @for ($y = date('Y', strtotime('-30 years')); $y <= date('Y'); $y++)
                        <option value="{{ $y }}" @selected(old('year_graduated') == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
        {{-- DESIGNATION --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="designation">Designation:</label>
                <select name="designation" id="designation" class="form-control select" data-placeholder="Choose..">
                    <option value=""></option>
                    @foreach (Usr::getStaffDesignations() as $designation)
                        <option value="{{ $designation }}" @selected(old('designation') == $designation)>
                            {{ $designation }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- NUMBER OF PERIODS --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="number_of_periods">Number of Periods:</label>
                <select
                    name="number_of_periods"
                    id="number_of_periods"
                    class="form-control select-search"
                    data-placeholder="Choose.."
                >
                    <option value=""></option>
                    @for ($i = 1; $i <= 30; $i++)
                        <option value="{{ $i }}" @selected(old('number_of_periods') == $i)>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
        {{-- PLACE OF LIVING --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="place_of_living">Place of Living:</label>
                <input
                    type="text"
                    name="place_of_living"
                    id="place_of_living"
                    class="form-control"
                    placeholder="Where you live?"
                    value="{{ old('place_of_living') }}"
                />
            </div>
        </div>
    </div>
    <div class="row">
        {{-- SUBJECTS STUDIED --}}
        <div class="col-12">
            <div class="form-group">
                <label for="subjects_studied">Subjects Studied <span class="text-info">(comma separated)</span></label>
                <textarea
                    name="subjects_studied"
                    id="subjects_studied"
                    class="form-control"
                    placeholder="ie., Math, Science"
                >{{ old('subjects_studied') }}</textarea>
            </div>
        </div>
    </div>
</fieldset>
