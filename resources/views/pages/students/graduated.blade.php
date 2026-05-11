@extends('layouts.master')

@section('page_title', 'Graduated Students')

@section('content')

<div class="card w-fit wmin-100-pcnt">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Students Graduated</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item">
                <a href="#all-students" class="nav-link active" data-toggle="tab">
                    All Graduated Students
                </a>
            </li>
            <li class="nav-item dropdown">
                <a href="javascript:;" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    Select Class
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach($my_classes as $c)
                    <a href="#c{{ $c->id }}" class="dropdown-item" data-toggle="tab">
                        {{ $c->name }}
                    </a>
                    @endforeach
                </div>
            </li>
        </ul>

        <div class="tab-content">

            {{-- All Graduates --}}
            <div class="tab-pane fade show active" id="all-students">
                {{-- Bulk Block/Unblock --}}
                @canany([$tenant_permissions['student_records']['block_all_graduated']['permission'], $tenant_permissions['student_records']['unblock_all_graduated']['permission']])
                @php
                $unique_blocked_values = $grad_students->pluck('user')->pluck('is_blocked')->unique();
                @endphp
                <div class="card mb-3">
                    <div class="card-body text-center">
                        @can($tenant_permissions['student_records']['block_all_graduated']['permission'])
                        <button id="students-block-all-gradudated" @disabled($grad_students->isEmpty() || $unique_blocked_values->hasSole(fn($value, $key) => (bool) $value === true)) class="btn btn-danger">
                            Block All Graduated Students
                        </button>
                        @endcan

                        @can($tenant_permissions['student_records']['unblock_all_graduated']['permission'])
                        <button id="students-unblock-all-graduated" @disabled($grad_students->isEmpty() || $unique_blocked_values->hasSole(fn($value, $key) => (bool) $value === false)) class="btn btn-success ml-2">
                            Unblock All Graduated Students
                        </button>
                        @endcan
                    </div>
                </div>
                @endcanany

                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>ADM No</th>
                            <th>Section</th>
                            <th>Grad Year</th>
                            <th>Remarks</th>
                            <th>Blocked</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grad_students as $gs)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <img src="{{ Usr::getTenancyAwarePhotoUrl($gs->user->photo_path) }}" class="rounded-circle" style="width:40px;height:40px;" alt="photo">
                            </td>
                            <td>{{ $gs->user->name }}</td>
                            <td>{{ $gs->student->admission_number }}</td>
                            <td>{{ $gs->fc->name }} {{ $gs->fs->name }}</td>
                            <td>{{ $gs->from_session }}</td>
                            <td>{{ $gs->remarks }}</td>

                            @if(auth()->user()->canAny([$tenant_permissions['student_records']['block_all_graduated']['permission'], $tenant_permissions['student_records']['unblock_all_graduated']['permission']]))
                            <td>
                                <label class="form-switch m-0">
                                    <input type="checkbox" id="checkbox-user-{{ $gs->student_id }}" onchange="updateUserBlockedState({{ $gs->student_id }}, this)" @checked($gs->user->is_blocked)>
                                    <i></i>
                                </label>
                            </td>
                            @else
                            <td>
                                <label class="form-switch m-0">
                                    <input type="checkbox" @disabled(true) @checked($gs->user->is_blocked)>
                                    <i></i>
                                </label>
                            </td>
                            @endif

                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="javascript:;" data-toggle="dropdown" class="material-symbols-rounded">
                                            lists
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            {{-- View --}}
                                            <a href="{{ route('students.show', Qs::hash($gs->student_id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">visibility</i> View
                                            </a>
                                            {{-- Edit --}}
                                            <a href="{{ route('students.edit', Qs::hash($gs->student_id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">edit</i> Edit
                                            </a>
                                            {{-- Reset Password --}}
                                            <a href="javascript:;" data-default_pass="student" data-href="{{ route('students.password.reset', Qs::hash($gs->student_id)) }}" class="dropdown-item needs-reset-pass-confirmation">
                                                <i class="material-symbols-rounded">lock_reset</i> Reset Password
                                            </a>
                                            {{-- Not Graduated --}}
                                            <a id="{{ Qs::hash($gs->student_id) }}" href="javascript:;" onclick="confirmReset(this.id)" class="dropdown-item">
                                                <i class="material-symbols-rounded">close</i> Not Graduated
                                            </a>
                                            <form id="item-reset-{{ Qs::hash($gs->student_id) }}" action="{{ route('students.mark_not_graduated', [Qs::hash($gs->student_id), $gs->id]) }}" method="POST" class="hidden">
                                                @csrf @method('PUT')
                                            </form>
                                            {{-- Marksheet --}}
                                            <a target="_blank" href="{{ route('marks.year_selector', Qs::hash($gs->user_id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">bottom_sheets</i> Marksheet
                                            </a>
                                            {{-- Delete --}}
                                            <a id="{{ Qs::hash($gs->user->id) }}" href="javascript:;" onclick="confirmDelete(this.id)" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form id="item-delete-{{ Qs::hash($gs->user->id) }}" action="{{ route('users.destroy', Qs::hash($gs->user->id)) }}" method="POST" class="hidden">
                                                @csrf @method('DELETE')
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- By Class --}}
            @foreach($my_classes as $mc)
            <div class="tab-pane fade" id="c{{ $mc->id }}">
                <strong class="status-styled">{{ $mc->name }}</strong>

                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>ADM No</th>
                            <th>Section</th>
                            <th>Graduated Year</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grad_students->where('previous_my_class_id', $mc->id) as $gs)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <img src="{{ Usr::getTenancyAwarePhotoUrl($gs->user->photo_path) }}" class="rounded-circle" style="width:40px;height:40px;" alt="photo">
                            </td>
                            <td>{{ $gs->user->name }}</td>
                            <td>{{ $gs->student->admission_number }}</td>
                            <td>{{ $gs->fc->name }} {{ $gs->fs->name }}</td>
                            <td>{{ $gs->graduated_session }}</td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="javascript:;" data-toggle="dropdown" class="material-symbols-rounded">
                                            lists
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            {{-- View --}}
                                            <a href="{{ route('students.show', Qs::hash($gs->student_id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">visibility</i> View
                                            </a>
                                            {{-- Edit --}}
                                            <a href="{{ route('students.edit', Qs::hash($gs->student_id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">edit</i> Edit
                                            </a>
                                            {{-- Not Graduated --}}
                                            <a id="{{ Qs::hash($gs->student_id) }}" href="javascript:;" onclick="confirmReset(this.id)" class="dropdown-item">
                                                <i class="material-symbols-rounded">close</i> Not Graduated
                                            </a>
                                            <form id="item-reset-{{ Qs::hash($gs->student_id) }}" action="{{ route('students.mark_not_graduated', [Qs::hash($gs->student_id), $gs->id]) }}" method="POST" class="hidden">
                                                @csrf @method('PUT')
                                            </form>
                                            {{-- Marksheet --}}
                                            <a target="_blank" href="{{ route('marks.year_selector', Qs::hash($gs->user_id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">bottom_sheets</i> Marksheet
                                            </a>
                                            {{-- Delete --}}
                                            <a id="{{ Qs::hash($gs->user->id) }}" href="javascript:;" onclick="confirmDelete(this.id)" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form id="item-delete-{{ Qs::hash($gs->user->id) }}" action="{{ route('users.destroy', Qs::hash($gs->user->id)) }}" method="POST" class="hidden">
                                                @csrf @method('DELETE')
                                            </form>

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
