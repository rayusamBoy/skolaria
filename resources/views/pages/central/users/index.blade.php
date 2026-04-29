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
            <li class="nav-item dropdown">
                <a href="javascript:;" class="nav-link dropdown-toggle active" data-toggle="dropdown">Manage Users</a>
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach ($roles as $role)
                    <a href="#ur-{{ Qs::hash($role->id) }}" class="dropdown-item" data-toggle="tab">{{ $role->name }}</a>
                    @endforeach
                </div>
            </li>
            @can($central_permissions['central_users']['store']['permission'])
            <li class="nav-item"><a href="#new-user" class="nav-link" data-toggle="tab">Create New User</a></li>
            @endcan
        </ul>

        <div class="tab-content">
            @can($central_permissions['central_users']['store']['permission'])
            <div class="tab-pane fade" id="new-user">
                <form method="post" enctype="multipart/form-data" class="wizard-form steps-validation ajax-store" action="{{ route('central_users.store') }}">
                    @csrf

                    {{-- PERSONAL DATA --}}
                    <h6>Personal Data</h6>
                    <fieldset>
                        <div class="row">
                            {{-- ROLES --}}
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="roles">Select Role(s): <span class="text-danger">*</span></label>
                                    <select class="form-control select" multiple="multiple" placeholder="Select Role(s)" name="roles[]" id="roles">
                                        @foreach ($roles as $role)
                                        <option value="{{ $role->name }}" data-permissions='@json($role->permissions->pluck("name"))' data-all="{{ in_array($role->name, Usr::getTenancyAwareRootRoles()) }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
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
                                    <input value="{{ old('address') }}" class="form-control" placeholder="Address" name="address" id="address" type="text" required>
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
                                        <option value="{{ $perm->name }}" title="{{ $perm->description ?? $perm->label ?? $perm->name }}">{{ $perm->label ?? $perm->name }}</option>
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
                                    <input value="{{ old('email') }}" type="email" name="email" id="email" class="form-control" placeholder="your@email.com">
                                </div>
                            </div>
                            {{-- USERNAME --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="username">Username: </label>
                                    <input value="{{ old('username') }}" type="text" name="username" id="username" class="form-control" placeholder="Username">
                                </div>
                            </div>
                            {{-- PHONE --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="phone">Phone:</label>
                                    <input value="{{ old('phone') }}" type="text" name="phone" id="phone" class="form-control" placeholder="+255123456789">
                                </div>
                            </div>
                            {{-- TELEPHONE --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="phone2">Telephone:</label>
                                    <input value="{{ old('phone2') }}" type="text" name="phone2" id="phone2" class="form-control" placeholder="+255123456789">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- BLOOD GROUP --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="bg_id">Blood Group: </label>
                                    <select class="select form-control" id="bg_id" name="bg_id" data-placeholder="Choose..">
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
                                    <select class="select form-control" id="gender" name="gender" required data-placeholder="Choose..">
                                        <option value=""></option>
                                        @foreach (Usr::getGenders() as $gender)
                                        <option @selected(old('gender')==$gender) value="{{ $gender }}">{{ ucfirst($gender) }}</option>
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
                                        <option @selected(old('nal_id')==$nal->id) value="{{ $nal->id }}">{{ $nal->name }}</option>
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
                                    <input value="{{ old('primary_id') }}" type="text" name="primary_id" id="primary_id" data-mask="www?wwwwwwwwwwwwwwwww" class="form-control" placeholder="123456789">
                                </div>
                            </div>
                            {{-- SECONDARY ID --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="secondary_id" class="d-block">Secondary ID:</label>
                                    <input value="{{ old('secondary_id') }}" type="text" name="secondary_id" id="secondary_id" data-mask="www?wwwwwwwwwwwwwwwww" class="form-control" placeholder="12345678123451234512">
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
                                    <input name="dob" id="dob" value="{{ old('dob') }}" type="text" class="form-control date-pick" placeholder="Select Date...">
                                </div>
                            </div>
                            {{-- PASSPORT --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="photo" class="d-block">Upload Passport Photo:</label>
                                    <input value="{{ old('photo') }}" accept="image/*" type="file" name="photo" id="photo" class="form-input-styled">
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
                                    <input autocomplete="off" id="emp_date" name="emp_date" value="{{ old('emp_date') }}" type="text" class="form-control date-pick" placeholder="Select Date...">
                                </div>
                            </div>
                            {{-- DATE OF CONFIRMATION --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="confirmation_date">Date of Confirmation:</label>
                                    <input autocomplete="off" id="confirmation_date" name="confirmation_date" value="{{ old('confirmation_date') }}" type="text" class="form-control date-pick" placeholder="Select Date...">
                                </div>
                            </div>
                            {{-- LICENCE NUMBER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="licence_number">Licence Number:</label>
                                    <input name="licence_number" id="licence_number" value="{{ old('licence_number') }}" type="text" class="form-control" placeholder="Ie., LTT 12345">
                                </div>
                            </div>
                            {{-- FILE NUMBER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="file_number">File Number:</label>
                                    <input name="file_number" id="file_number" value="{{ old('file_number') }}" type="text" class="form-control" placeholder="EFP/123/12/1">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- SOCIAL SECURITY NUMBER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="ss_number">Social Security Number:</label>
                                    <input name="ss_number" id="ss_number" value="{{ old('ss_number') }}" type="number" min="1" class="form-control">
                                </div>
                            </div>
                            {{-- EMPLOYMENT NUMBER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="emp_no">Employment Number:</label>
                                    <input name="emp_no" id="emp_no" value="{{ old('emp_no') }}" type="number" min="1" class="form-control">
                                </div>
                            </div>
                            {{-- TIN NUMBER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tin_number">TIN Number:</label>
                                    <input name="tin_number" id="tin_number" value="{{ old('tin_number') }}" type="text" class="form-control">
                                </div>
                            </div>
                            {{-- BANK ACCOUNT NUMBER --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="bank_acc_no">Bank Account Number:</label>
                                    <input name="bank_acc_no" id="bank_acc_no" value="{{ old('bank_acc_no') }}" type="number" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- BANK NAME --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="bank_name">Bank Name:</label>
                                    <input name="bank_name" id="bank_name" value="{{ old('bank_name') }}" type="text" class="form-control">
                                </div>
                            </div>
                            {{-- EDUCATION LEVEL --}}
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="education_level">Education Level: </label>
                                    <select class="select-search form-control" id="education_level" name="education_level" data-placeholder="Choose..">
                                        <option value=""></option>
                                        @foreach (Usr::getEducationLevels() as $lv)
                                        <option @selected(old('education_level')==$lv) value="{{ $lv }}">{{ $lv }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- COLLEGE ATTENDED --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="college_attended">College Attended: </label>
                                    <input name="college_attended" id="college_attended" value="{{ old('college_attended') }}" type="text" class="form-control">
                                </div>
                            </div>
                            {{-- PLACE OF LIVING --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="place_of_living">Place of Living: </label>
                                    <input type="text" class="form-control" name="place_of_living" id="place_of_living" placeholder="Where you live?">
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
                                        @for ($y = date('Y', strtotime('- 30 years')); $y <= date('Y'); $y++) <option @selected(old('year_graduated')==$y) value="{{ $y }}">{{ $y }} </option> @endfor
                                    </select>
                                </div>
                            </div>
                            {{-- ROLE --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="role">Role: </label>
                                    <select class="select form-control" id="role" name="role" data-placeholder="Choose..">
                                        <option value=""></option>
                                        @foreach (Usr::getStaffRoles() as $role)
                                        <option @selected(old('role')==$role) value="{{ $role }}">{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- SUBJECTS STUDIED --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subjects_studied">Subjects Studied <span class="text-info">(comma (,) separated)</span>: </label>
                                    <div>
                                        <textarea name="subjects_studied" id="subjects_studied" class="form-control" placeholder="ie., Subject one, Subject two, ...">{{ old('subjects_studied') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
            @endcan

            @foreach ($roles as $role)
            <div class="tab-pane fade show" id="ur-{{ Qs::hash($role->id) }}">
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
                            <th>Blocked</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(Usr::getUsersByRoleNames([$role->name]) as $u)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ Usr::getTenancyAwarePhoto($u->photo) }}" alt="photo"></td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->username }}</td>
                            <td><a href="tel: {{ $u->phone }}">{{ $u->phone }}</a></td>
                            <td><a href="mailto: {{ $u->email }}">{{ $u->email }}</a></td>
                            <td>
                                @if(isset($u->staff->first()->staff_data_edit) && auth()->user()->can($central_permissions['central_users']['toggle_user_block']['permission']) && !Usr::userIsHead($u->id))
                                <label class="form-switch m-0"><input id="checkbox-user-{{ $u->id }}" onchange="updateUserBlockedState({{ $u->id }}, this)" type="checkbox" @checked($u->blocked)><i></i></label>
                                @else
                                <span class="status-styled">Unavailable</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                $user_can_modify_head = !Usr::userIsHead($u->id) || Usr::userIsHead();
                                @endphp
                                @if($user_can_modify_head)
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a class="material-symbols-rounded" href="javascript:;" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            @if(auth()->user()->can($central_permissions['central_users']['edit']['permission']) && $user_can_modify_head)
                                            <a href="{{ route('central_users.edit', Qs::hash($u->id)) }}" class="dropdown-item"><i class="material-symbols-rounded">edit</i> Edit</a>
                                            @endif
                                            @if(auth()->user()->can($central_permissions['central_users']['show']['permission']) && $user_can_modify_head)
                                            <a href="{{ route('central_users.show', Qs::hash($u->id)) }}" class="dropdown-item"><i class="material-symbols-rounded">visibility</i> View Profile</a>
                                            @endif
                                            @if(auth()->user()->can($central_permissions['central_users']['reset_password']['permission']) && $user_can_modify_head)
                                            <a href="javascript:;" data-href="{{ route('central_users.password.reset',Qs::hash($u->id)) }}" class="dropdown-item needs-reset-pass-confirmation"><i class="material-symbols-rounded">lock_reset</i> Reset Password</a>
                                            @endif
                                            @if(auth()->user()->can($central_permissions['central_users']['destroy']['permission']) && $user_can_modify_head)
                                            <a href="javascript:;" id="{{ Qs::hash($u->id) }}" onclick="confirmDelete(this.id)" class="dropdown-item text-danger"><i class="material-symbols-rounded">delete</i> Delete</a>
                                            <form id="item-delete-{{ Qs::hash($u->id) }}" action="{{ route('central_users.destroy', Qs::hash($u->id)) }}" method="post" class="hidden">@csrf @method('delete')</form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
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

{{-- Users List Ends --}}

@endsection
