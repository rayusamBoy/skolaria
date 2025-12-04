@extends('layouts.master')

@section('page_title', 'Manage Users')

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Users</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item"><a href="#new-user" class="nav-link active" data-toggle="tab">Create New User</a></li>
            <li class="nav-item dropdown">
                <a href="javascript:;" class="nav-link dropdown-toggle" data-toggle="dropdown">Manage Users</a>
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach ($roles as $role)
                    <a href="#ur-{{ Qs::hash($role->id) }}" class="dropdown-item" data-toggle="tab">{{ $role->name }}</a>
                    @endforeach
                </div>
            </li>
        </ul>

        <div class="tab-content">

            <div class="tab-pane fade show active" id="new-user">
                <form method="post" enctype="multipart/form-data" class="wizard-form steps-validation ajax-store" action="{{ route('users.store') }}" data-fouc>
                    @csrf

                    {{-- PERSONAL DATA --}}
                    <h6 class="text-white">Personal Data</h6>
                    <fieldset>
                        <div class="row">
                            {{-- ROLES --}}
                            <div class="col-md-5">
                                <label for="roles">Select Role(s): <span class="text-danger">*</span></label>
                                <select class="form-control select" multiple="multiple" placeholder="Select Role(s)" name="roles[]" id="roles">
                                    @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" data-permissions='@json($role->permissions->pluck("name"))' data-all="{{ in_array($role->name, Usr::getTenancyAwareRootRoles()) }}" @selected(auth()->user()->hasRole($role->name))>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- FULL NAME --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Full Name: <span class="text-danger">*</span></label>
                                    <input value="{{ old('name') }}" required type="text" name="name" id="name" placeholder="Full Name" class="form-control text-capitalize">
                                </div>
                            </div>
                            {{-- ADDRESS --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address">Address: <span class="text-danger">*</span></label>
                                    <input value="{{ old('address') }}" class="form-control" id="address" placeholder="Address" name="address" type="text" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                {{-- PERMISSIONS --}}
                                <div class="form-group">
                                    <label for="permissions">Select Permission(s) (Optional):</label>
                                    <select class="form-control select" multiple="multiple" placeholder="Select Permission(s)" name="permissions[]" id="permissions">
                                        @foreach ($permissions as $perm)
                                        <option value="{{ $perm->name }}">{{ $perm->name }}</option>
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
                                    <input value="{{ old('email') }}" type="email" id="email" name="email" class="form-control" placeholder="your@email.com">
                                </div>
                            </div>
                            {{-- USERNAME --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="username">Username: </label>
                                    <input value="{{ old('username') }}" type="text" id="username" name="username" class="form-control" placeholder="Username">
                                </div>
                            </div>
                            {{-- PHONE --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label id="phone">Phone:</label>
                                    <input value="{{ old('phone') }}" type="text" name="phone" id="phone" data-mask="+9999?999999999" class="form-control" placeholder="+255123456789">
                                </div>
                            </div>
                            {{-- TELEPHONE --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="phone2">Telephone:</label>
                                    <input value="{{ old('phone2') }}" type="text" id="phone2" name="phone2" data-mask="+9999?999999999" class="form-control" placeholder="+255123456789">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- BLOOD GROUP --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="bg_id">Blood Group: </label>
                                    <select class="select form-control" id="bg_id" name="bg_id" data-fouc data-placeholder="Choose..">
                                        <option value=""></option>
                                        @foreach ($blood_groups as $bg)
                                        <option @selected(old('bg_id')==$bg->id) value="{{ $bg->id }}">{{ $bg->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- PASSWORD --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="password">Password: </label>
                                    <input id="password" type="password" autocomplete="current-password" name="password" class="form-control">
                                </div>
                            </div>
                            {{-- GENDER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="gender">Gender: <span class="text-danger">*</span></label>
                                    <select class="select form-control" id="gender" name="gender" required data-fouc data-placeholder="Choose..">
                                        <option value=""></option>
                                        @foreach (Usr::getGenders() as $gender)
                                        <option @selected(old('gender')==$gender) value="{{ $gender }}">{{ $gender }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- NATIONALITY --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nal_id">Nationality: <span class="text-danger">*</span></label>
                                    <select onchange="getState(this.value)" data-placeholder="Choose..." required name="nal_id" id="nal_id" class="select-search form-control">
                                        <option value=""></option>
                                        @foreach ($nationals as $nal)
                                        <option @if (old('nal_id')==$nal->id) selected @endif value="{{ $nal->id }}">{{ $nal->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- STATE --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="state_id">State/Region: <span class="text-danger">*</span></label>
                                    <select onchange="getLGA(this.value)" required data-placeholder="Select Nationality First" class="select-search form-control" name="state_id" id="state_id">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                            {{-- LGA --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="lga_id">LGA: <span class="text-danger">*</span></label>
                                    <select required data-placeholder="Select State First" class="select-search form-control" name="lga_id" id="lga_id">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                            {{-- PRIMARY ID --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="primary_id" class="d-block">Primary ID:</label>
                                    <input value="{{ old('primary_id') }}" type="text" id="primary_id" name="primary_id" data-mask="www?wwwwwwwwwwwwwwwww" class="form-control" placeholder="123456789">
                                </div>
                            </div>
                            {{-- SECONDARY ID --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="secondary_id" class="d-block">Secondary ID:</label>
                                    <input value="{{ old('secondary_id') }}" type="text" id="secondary_id" name="secondary_id" data-mask="www?wwwwwwwwwwwwwwwww" class="form-control" placeholder="12345678123451234512">
                                </div>
                            </div>
                        </div>

                        <div class="display-none" id="parent-data">
                            <div class="row">
                                {{-- CLOSE RELATIVE NAME --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name2">Close Relative Name: <span class="text-danger">*</span></label>
                                        <input disabled autocomplete="off" id="name2" name="name2" required value="{{ old('name2') }}" type="text" class="form-control text-capitalize" placeholder="Full name">
                                    </div>
                                </div>
                                {{-- RELATION WITH PARENT --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="relation">Parent's Relation with the Relative: <span class="text-danger">*</span></label>
                                        <input disabled value="{{ old('relation') }}" id="relation" type="text" required name="relation" class="form-control" placeholder="">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{-- PARENT WORK --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="parent_work">Parent Work: <span class="text-danger">*</span></label>
                                        <input disabled autocomplete="off" id="parent_work" required name="work" value="{{ old('work') }}" type="text" class="form-control" placeholder="The Work he/she does">
                                    </div>
                                </div>
                                {{-- CLOSE RELATIVE NUMBERS --}}
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="phone3">Close Relative Phone: <span class="text-danger">*</span></label>
                                        <input disabled value="{{ old('phone3') }}" required type="text" id="phone3" name="phone3" class="form-control" placeholder="+255123456789">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="phone4">Close Relative Mobile: </label>
                                        <input disabled value="{{ old('phone4') }}" type="text" id="phone4" name="phone4" class="form-control" placeholder="+255123456789">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- RELIGION --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="religion">Religion: <span class="text-danger">*</span></label>
                                    <select required data-placeholder="Choose..." name="religion" id="religion" class="select-search form-control">
                                        <option value=""></option>
                                        @foreach (Usr::getReligions() as $rel)
                                        <option @selected(old('religion')==$rel) value="{{ $rel }}">{{ $rel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- DOB --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="dob">Date of Birth:</label>
                                    <input name="dob" value="{{ old('dob') }}" type="text" id="dob" class="form-control date-pick" placeholder="Select Date...">
                                </div>
                            </div>
                            {{-- PASSPORT --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="photo" class="d-block">Upload Passport Photo:</label>
                                    <input value="{{ old('photo') }}" accept="image/*" type="file" id="photo" name="photo" class="form-input-styled" data-fouc>
                                    <span class="form-text text-muted">Accepted Images: jpeg, png. Max file size 2Mb</span>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    {{-- STAFF DATA --}}
                    <h6>Staff Data</h6>
                    <fieldset>
                        <div class="row">
                            {{-- DATE OF EMPLOYMENT --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="emp_date">Date of Employment:</label>
                                    <input type="text" name="emp_date" id="emp_date" class="form-control date-pick" placeholder="Select Date..." value="{{ old('emp_date') }}">
                                </div>
                            </div>
                            {{-- DATE OF CONFIRMATION --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="confirmation_date">Date of Confirmation:</label>
                                    <input type="text" name="confirmation_date" id="confirmation_date" class="form-control date-pick" placeholder="Select Date..." value="{{ old('confirmation_date') }}">
                                </div>
                            </div>
                            {{-- LICENCE NUMBER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="licence_number">Licence Number:</label>
                                    <input type="text" name="licence_number" id="licence_number" class="form-control" placeholder="Ie., LTT 12345" value="{{ old('licence_number') }}">
                                </div>
                            </div>
                            {{-- FILE NUMBER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="file_number">File Number:</label>
                                    <input type="text" name="file_number" id="file_number" class="form-control" placeholder="EFP/123/12/1" value="{{ old('file_number') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            {{-- SOCIAL SECURITY NUMBER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="ss_number">Social Security Number:</label>
                                    <input type="number" name="ss_number" id="ss_number" class="form-control" min="1" value="{{ old('ss_number') }}">
                                </div>
                            </div>
                            {{-- EMPLOYMENT NUMBER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="emp_no">Employment Number:</label>
                                    <input type="number" name="emp_no" id="emp_no" class="form-control" min="1" value="{{ old('emp_no') }}">
                                </div>
                            </div>
                            {{-- TIN NUMBER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tin_number">TIN Number:</label>
                                    <input type="text" name="tin_number" id="tin_number" class="form-control" value="{{ old('tin_number') }}">
                                </div>
                            </div>
                            {{-- BANK ACCOUNT NUMBER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="bank_acc_no">Bank Account Number:</label>
                                    <input type="number" name="bank_acc_no" id="bank_acc_no" class="form-control" value="{{ old('bank_acc_no') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            {{-- BANK NAME --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="bank_name">Bank Name:</label>
                                    <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ old('bank_name') }}">
                                </div>
                            </div>
                            {{-- EDUCATION LEVEL --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="education_level">Education Level:</label>
                                    <select name="education_level" id="education_level" class="form-control select-search" data-placeholder="Choose..">
                                        <option value=""></option>
                                        @foreach(Usr::getEducationLevels() as $lv)
                                        <option value="{{ $lv }}" @selected(old('education_level')==$lv)>{{ $lv }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- COLLEDGE --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="college_attended">College Attended:</label>
                                    <input type="text" name="college_attended" id="college_attended" class="form-control" value="{{ old('college_attended') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            {{-- GRADUATION YEAR --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="year_graduated">Graduation Year:</label>
                                    <select name="year_graduated" id="year_graduated" class="form-control select-search" data-placeholder="Choose..">
                                        <option value=""></option>
                                        @for($y=date('Y', strtotime('-30 years')); $y<=date('Y'); $y++) <option value="{{ $y }}" @selected(old('year_graduated')==$y)>{{ $y }}</option>
                                            @endfor
                                    </select>
                                </div>
                            </div>
                            {{-- ROLE --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="role">Role:</label>
                                    <select name="role" id="role" class="form-control select" data-placeholder="Choose..">
                                        <option value=""></option>
                                        @foreach(Usr::getStaffRoles() as $role)
                                        <option value="{{ $role }}" @selected(old('role')==$role)>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- NUMBER OF PERIODS --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="no_of_periods">Number of Periods:</label>
                                    <select name="no_of_periods" id="no_of_periods" class="form-control select-search" data-placeholder="Choose..">
                                        <option value=""></option>
                                        @for($i=1; $i<=30; $i++) <option value="{{ $i }}" @selected(old('no_of_periods')==$i)>{{ $i }}</option>
                                            @endfor
                                    </select>
                                </div>
                            </div>
                            {{-- PLACE OF LIVING --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="place_of_living">Place of Living:</label>
                                    <input type="text" name="place_of_living" id="place_of_living" class="form-control" placeholder="Where you live?" value="{{ old('place_of_living') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            {{-- SUBJECTS STUDIED --}}
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="subjects_studied">Subjects Studied <span class="text-info">(comma separated)</span></label>
                                    <textarea name="subjects_studied" id="subjects_studied" class="form-control" placeholder="ie., Math, Science">{{ old('subjects_studied') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

            @foreach($roles as $role)
            <div class="tab-pane fade" id="ur-{{ Qs::hash($role->id) }}">
                <span class="status-styled">{{ $role->name }}</span>

                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Staff Data Editable</th>
                            <th>Blocked</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach(Usr::getUsersByRoleNames([$role->name]) as $u)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><img src="{{ Usr::getTenantAwarePhoto($u->photo) }}" class="rounded-circle" style="height:40px;width:40px;"></td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->username }}</td>
                            <td><a href="tel:{{ $u->phone }}">{{ $u->phone }}</a></td>
                            <td><a href="mailto:{{ $u->email }}">{{ $u->email }}</a></td>
                            @can('edit_staff_data')
                            <td>
                                @if(isset($u->staff->first()->staff_data_edit))
                                <label class="form-switch m-0"><input type="checkbox" id="checkbox-staff-{{ $u->id }}" onchange="updateStaffDataEdtiState({{ $u->id }},this)" @checked($u->staff->first()->staff_data_edit)><i></i></label>
                                @else
                                <span class="status-styled">Unavailable</span>
                                @endif
                            </td>
                            @endcan
                            @can('block_user')
                            <td><label class="form-switch m-0"><input type="checkbox" id="checkbox-user-{{ $u->id }}" onchange="updateUserBlockedState({{ $u->id }},this)" @checked($u->blocked)><i></i></label></td>
                            @endcan
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="javascript:;" class="material-symbols-rounded" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            @can('edit_user')<a href="{{ route('users.edit', Qs::hash($u->id)) }}" class="dropdown-item"><i class="material-symbols-rounded">edit</i> Edit</a>@endcan
                                            @can('view_user')<a href="{{ route('users.show', Qs::hash($u->id)) }}" class="dropdown-item"><i class="material-symbols-rounded">visibility</i> View Profile</a>@endcan
                                            @can('reset_password')<a href="javascript:;" data-href="{{ route('users.password.reset',Qs::hash($u->id)) }}" class="dropdown-item needs-reset-pass-confirmation"><i class="material-symbols-rounded">lock_reset</i> Reset Password</a>@endcan
                                            @can('delete_user')
                                            <a href="javascript:;" id="{{ Qs::hash($u->id) }}" onclick="confirmDelete(this.id)" class="dropdown-item text-danger"><i class="material-symbols-rounded">delete</i> Delete</a>
                                            <form id="item-delete-{{ Qs::hash($u->id) }}" action="{{ route('users.destroy', Qs::hash($u->id)) }}" method="post" class="hidden">@csrf @method('delete')</form>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach

        </div>
    </div>
</div>

@endsection
