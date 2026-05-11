@extends('layouts.master')

@section('page_title', "Edit User - {$user->name}")

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Edit User Details</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <form method="post" enctype="multipart/form-data" class="wizard-form steps-validation ajax-update" data-reload="#page-title" action="{{ route('users.update', Qs::hash($user->id)) }}">
            @csrf @method('PUT')
            <h6>Personal Data</h6>

            <fieldset>
                <div class="row">
                    {{-- USER ROLES --}}
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="roles">Select Role(s): <span class="text-danger">*</span></label>
                            <select class="form-control select" multiple="multiple" placeholder="Select Role(s)" name="roles[]" id="roles">
                                @foreach ($roles as $role)
                                <option @selected($user->hasRole($role->name)) data-permissions='@json($role->permissions->pluck("name"))' data-all="{{ in_array($role->name, Usr::getTenancyAwareRootRoles()) }}" value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- NAME --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="name">Full Name: <span class="text-danger">*</span></label>
                            <input value="{{ $user->name }}" required type="text" id="name" name="name" placeholder="Full Name" class="form-control">
                        </div>
                    </div>
                    {{-- ADDRESS --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="address">Address: <span class="text-danger">*</span></label>
                            <input value="{{ $user->address }}" class="form-control" placeholder="Address" id="address" name="address" type="text" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        {{-- PERMISSIONS --}}
                        <div class="form-group">
                            <label for="permissions">Additional Permissions (Optional):</label>
                            <select class="form-control select" multiple="multiple" placeholder="Select Permission(s)" name="permissions[]" id="permissions">
                                @foreach ($permissions as $perm)
                                <option @selected($user->can($perm->name)) value="{{ $perm->name }}" title="{{ $perm->description ?? $perm->label ?? $perm->name }}">{{ $perm->label ?? $perm->name }}</option>
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
                            <input value="{{ $user->email }}" type="email" id="email" name="email" class="form-control" placeholder="your@email.com">
                        </div>
                    </div>
                    {{-- MOBILES --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="primary_mobile">Primary Mobile:</label>
                            <input value="{{ $user->primary_mobile }}" type="text" id="primary_mobile" name="primary_mobile" class="form-control" placeholder="+255123456789">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="secondary_mobile">Secondary Mobile:</label>
                            <input value="{{ $user->secondary_mobile }}" type="text" id="secondary_mobile" name="secondary_mobile" class="form-control" placeholder="+255123456789">
                        </div>
                    </div>
                    {{-- BLOOD GROUP --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="blood_group_id">Blood Group: </label>
                            <select class="select form-control" id="blood_group_id" name="blood_group_id" data-placeholder="Choose..">
                                <option value=""></option>
                                @foreach($blood_groups as $bg)
                                <option @selected($user->blood_group_id == $bg->id) value="{{ $bg->id }}">{{ $bg->name }}</option>
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
                            <select class="select form-control" id="gender" name="gender" required data-placeholder="Choose..">
                                <option value=""></option>
                                @foreach (Usr::getGenders() as $gender)
                                <option @selected($user->gender == $gender) value="{{ $gender }}">{{ ucfirst($gender) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- NATIONALITY --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nationality_id">Nationality: <span class="text-danger">*</span></label>
                            <select onchange="getState(this.value)" data-placeholder="Choose..." required name="nationality_id" id="nationality_id" class="select-search form-control">
                                <option value=""></option>
                                @foreach($nationals as $nal)
                                <option @selected($user->nationality_id == $nal->id) value="{{ $nal->id }}">{{ $nal->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- STATE OR REGION --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="state_id">State/Reion: <span class="text-danger">*</span></label>
                            <select onchange="getLGA(this.value)" required data-placeholder="Select Nationality First.." class="select-search form-control" name="state_id" id="state_id">
                                <option value="{{ $user->state_id ?? '' }}">{{ $user->state->name ?? '' }}</option>
                            </select>
                        </div>
                    </div>
                    {{-- LGA --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="lga_id">LGA: <span class="text-danger">*</span></label>
                            <select required data-placeholder="Select State First" class="select-search form-control" name="lga_id" id="lga_id">
                                <option value="{{ $user->lga_id ?? '' }}">{{ $user->lga->name ?? '' }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- PRIMARY ID --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="primary_identification_number" class="d-block">Primary ID number:</label>
                            <input value="{{ $user->primary_identification_number ?? '' }}" type="text" id="primary_identification_number" name="primary_identification_number" class="form-control" placeholder="123456789">
                        </div>
                    </div>
                    {{-- SECONDARY ID --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="secondary_identification_number" class="d-block">Secondary ID number:</label>
                            <input value="{{ $user->secondary_identification_number ?? '' }}" type="text" id="secondary_identification_number" name="secondary_identification_number" class="form-control" placeholder="12345678123451234512">
                        </div>
                    </div>
                    {{-- RELIGION --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="religion">Religion: <span class="text-danger">*</span></label>
                            <select required data-placeholder="Choose..." name="religion" id="religion" class="select-search form-control">
                                <option value=""></option>
                                @foreach(Usr::getReligions() as $rel)
                                <option @selected($user->religion == $rel) value="{{ $rel }}">{{ $rel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- DOB --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth:</label>
                            <input name="date_of_birth" value="{{ $user->date_of_birth }}" id="date_of_birth" type="text" class="form-control date-pick" placeholder="Select Date...">
                        </div>
                    </div>
                </div>

                @php $p_relative = Qs::getParentRelativeByUserId($user->id) @endphp
                <div id="parent-data" @class($user->hasRole(config('roles.default_roles.tenant.parent')) ? [] : ['display-none'])>
                    <div class="row">
                        {{-- CLOSE RELATIVE NAME --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="relative_name">Close Relative Name: <span class="text-danger">*</span></label>
                                <input autocomplete="off" id="relative_name" name="relative_name" required value="{{ $p_relative->name ?? '' }}" type="text" class="form-control text-capitalize" placeholder="Full name">
                            </div>
                        </div>
                        {{-- RELATION WITH PARENT --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="relation">Parent's Relation with the Relative: <span class="text-danger">*</span></label>
                                <input value="{{ $p_relative->relation ?? '' }}" type="text" id="relation" required name="relation" class="form-control" placeholder="">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- PARENT OCCUPATION --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="occupation">Parent Occupation: <span class="text-danger">*</span></label>
                                <input autocomplete="off" id="occupation" required name="occupation" value="{{ $user->occupation ?? '' }}" type="text" class="form-control" placeholder="The Occupation of the parent">
                            </div>
                        </div>
                        {{-- CLOSE RELATIVE MOBILES --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="relative_primary_mobile">Close Relative Primary Mobile: <span class="text-danger">*</span></label>
                                <input value="{{ $p_relative->primary_mobile ?? ''  }}" required type="text" id="relative_primary_mobile" name="relative_primary_mobile" class="form-control" placeholder="+255123456789">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="relative_secondary_mobile">Close Relative Secondary Mobile: </label>
                                <input value="{{ $p_relative->secondary_mobile ?? ''  }}" type="text" id="relative_secondary_mobile" name="relative_secondary_mobile" class="form-control" placeholder="+255123456789">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- PASSPORT --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="photo" class="d-block">Upload Passport Photo:</label>
                            <input value="{{ old('photo') }}" accept="image/*" type="file" id="photo" name="photo" class="form-input-styled">
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
                            <label for="employment_date">Date of Employment:</label>
                            <input autocomplete="off" id="employment_date" name="employment_date" value="{{ $staff_rec->employment_date ?? '' }}" type="text" class="form-control date-pick" placeholder="Select Date...">
                        </div>
                    </div>
                    {{-- DATE OF CONFIRMATION --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="confirmation_date">Date of Confirmation:</label>
                            <input autocomplete="off" id="confirmation_date" name="confirmation_date" value="{{ $staff_rec->confirmation_date ?? '' }}" type="text" class="form-control date-pick" placeholder="Select Date...">
                        </div>
                    </div>
                    {{-- LICENCE NUMBER --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="licence_number">Licence Number:</label>
                            <input name="licence_number" id="licence_number" value="{{ $staff_rec->licence_number ?? '' }}" type="text" class="form-control" placeholder="Ie., LTT 12345">
                        </div>
                    </div>
                    {{-- FILE NUMBER --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="file_number">File Number:</label>
                            <input name="file_number" id="file_number" value="{{ $staff_rec->file_number ?? '' }}" type="text" class="form-control" placeholder="EPP/123/12/1">
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- SOCIAL SECURITY NUMBER --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="social_security_number">Social Security Number:</label>
                            <input name="social_security_number" id="social_security_number" value="{{ $staff_rec->social_security_number ?? '' }}" type="number" min="1" 0 class="form-control">
                        </div>
                    </div>
                    {{-- EMPLOYMENT NUMBER --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="employment_number">Employment Number:</label>
                            <input name="employment_number" id="employment_number" value="{{ $staff_rec->employment_number ?? '' }}" type="number" min="1" class="form-control">
                        </div>
                    </div>
                    {{-- TIN NUMBER --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tin_number">TIN Number:</label>
                            <input name="tin_number" id="tin_number" value="{{ $staff_rec->tin_number ?? '' }}" type="text" class="form-control">
                        </div>
                    </div>
                    {{-- BANK ACCOUNT NUMBER --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="bank_account_number">Bank Account Number:</label>
                            <input name="bank_account_number" id="bank_account_number" value="{{ $staff_rec->bank_account_number ?? '' }}" type="number" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- BANK NAME --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="bank_name">Bank Name:</label>
                            <input name="bank_name" id="bank_name" value="{{ $staff_rec->bank_name ?? '' }}" type="text" class="form-control">
                        </div>
                    </div>
                    {{-- EDUCATION LEVEL --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="education_level">Education Level: </label>
                            <select class="select-search form-control" id="education_level" name="education_level" data-placeholder="Choose..">
                                <option value=""></option>
                                @foreach(Usr::getEducationLevels() as $lv)
                                <option @selected(isset($staff_rec->education_level) && $staff_rec->education_level == $lv) value="{{ $lv }}">{{ $lv }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- COLLEGE ATTENDED --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="college_attended">College Ateended: </label>
                            <input name="college_attended" id="college_attended" value="{{ $staff_rec->college_attended ?? '' }}" type="text" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- GRADUATION YEAR --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="year_graduated">Graduation Year: </label>
                            <select name="year_graduated" data-placeholder="Choose..." id="year_graduated" class="select-search form-control">
                                <option value=""></option>
                                @for($y=date('Y', strtotime('- 30 years')); $y<=date('Y'); $y++) <option @selected((isset($staff_rec->year_graduated) && $staff_rec->year_graduated == $y)) value="{{ $y }}">{{ $y }} </option> @endfor
                            </select>
                        </div>
                    </div>
                    {{-- DESIGNATION --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="designation">Designation: </label>
                            <select class="select form-control" id="designation" name="designation" data-placeholder="Choose..">
                                <option value=""></option>
                                @foreach(Usr::getStaffDesignations() as $designation)
                                <option @selected($staff_rec?->designation == $designation) value="{{ $designation }}">{{ $designation }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- NUMBER OF PERIODS --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="number_of_periods">Number of Periods:</label>
                            <select name="number_of_periods" id="number_of_periods" data-placeholder="Choose..." id="number_of_periods" class="select-search form-control">
                                <option value=""></option>
                                @for($i = 1; $i <= 30; $i++) <option @selected(isset($staff_rec->number_of_periods) && $staff_rec->number_of_periods == $i) value="{{ $i }}">{{ $i }} </option> @endfor
                            </select>
                        </div>
                    </div>
                    {{-- PLACE OF BIRTH --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="place_of_living">Place of Living: </label>
                            <input type="text" class="form-control" name="place_of_living" id="place_of_living" value="{{ $staff_rec->place_of_living ?? '' }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- SUBJECTS STUDIED --}}
                    <div class="col-12">
                        <div class="form-group">
                            <label for="subjects_studied">Subjects Studied <span class="text-info">(comma (,) separated)</span>: </label>
                            <div>
                                <textarea name="subjects_studied" id="subjects_studied" class="form-control" placeholder="ie., Subject one, Subject two, ...">{{ $staff_rec->subjects_studied ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>

@endsection

@push('scripts')
@if($user->hasRole(config('roles.default_roles.tenant.parent')))
<script>
    $(document).ready(function() {
        // Trigger element change so that the listener can process user role change.
        $("select#roles").trigger('change');
    })

</script>

@else
<script>
    // Disable parent data input fields.
    $("#parent-data input").attr("disabled", "true");

</script>
@endif
@endpush
