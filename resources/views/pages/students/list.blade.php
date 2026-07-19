@php
$my_class = $my_classes->where('id', $class_id)->first();
@endphp

@extends('layouts.master')
@section('page_title', "Student Information - {$my_class->name} (" . $my_class->class_type->name . ")")

@section('content')

@php
// Pre‑compute all relevant permissions for this view
$canViewProfile = auth()->user()->can($tenant_permissions['student_records']['show']['permission']);
$canEdit = auth()->user()->can($tenant_permissions['student_records']['edit']['permission']);
$canResetPassword = auth()->user()->can($tenant_permissions['student_records']['reset_password']['permission']);
$canMarksheet = auth()->user()->can($tenant_permissions['marks']['year_selector']['permission']);
$canAssessmentsheet = auth()->user()->can($tenant_permissions['assessments']['show']['permission']);
$canDelete = auth()->user()->can($tenant_permissions['users']['destroy']['permission']);

// Block/Unblock permissions
$canBlockAll = auth()->user()->can($tenant_permissions['student_records']['block_all_class']['permission']);
$canUnblockAll = auth()->user()->can($tenant_permissions['student_records']['unblock_all_class']['permission']);
$canToggleBlock = $canBlockAll || $canUnblockAll;

// Does the user have any action at all?
$hasActions = $canViewProfile || $canEdit || $canResetPassword || $canMarksheet || $canAssessmentsheet || $canDelete;

$showBlockUnblockButtons = $canBlockAll || $canUnblockAll;
@endphp

<div class="card w-fit wmin-100-pcnt">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Students List</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item">
                <a href="#all-students" class="nav-link active" data-toggle="tab">
                    All {{ $my_class->name }} Students
                </a>
            </li>
            <li class="nav-item dropdown">
                <a href="javascript:;" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    Sections
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach($sections as $s)
                    <a href="#s{{ $s->id }}" class="dropdown-item" data-toggle="tab">
                        {{ "{$my_class->name} {$s->name}" }}
                    </a>
                    @endforeach
                </div>
            </li>
        </ul>

        <div class="tab-content">
            {{-- All students --}}
            <div class="tab-pane fade show active" id="all-students">
                @if($showBlockUnblockButtons)
                @php
                $unique_blocked_values = $students->pluck('user')->pluck('is_blocked')->unique();
                @endphp
                <div class="card mb-3">
                    <div class="card-body text-center">
                        @if($canBlockAll)
                        <button id="students-block-all-active" @disabled($students->isEmpty() || $unique_blocked_values->hasSole(fn($value, $key) => (bool) $value === true)) value="{{ $my_class->id }}" url="{{ route('students.block_all_class') }}" class="btn btn-danger">
                            Block All {{ $my_class->name }} Students
                        </button>
                        @endif

                        @if($canUnblockAll)
                        <button id="students-unblock-all-active" @disabled($students->isEmpty() || $unique_blocked_values->hasSole(fn($value, $key) => (bool) $value === false)) value="{{ $my_class->id }}" url="{{ route('students.unblock_all_class') }}" class="btn btn-success">
                            Unblock All {{ $my_class->name }} Students
                        </button>
                        @endif
                    </div>
                </div>
                @endif

                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Exam No</th>
                            <th>ADM No</th>
                            <th>Section</th>
                            <th>Parent</th>
                            <th>Blocked</th>
                            @if($hasActions)
                            <th class="text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students->sortBy('user.gender') as $s)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <img class="rounded-circle" style="height:40px;width:40px;" src="{{ Usr::getTenancyAwarePhotoUrl($s->user->photo_path) }}" alt="photo">
                            </td>
                            <td>{{ $s->user->name }}</td>
                            <td>{{ $s->user->exam_record->first()->exam_number ?? "not set" }}</td>
                            <td>{{ $s->admission_number }}</td>
                            <td>{{ $s->section->name }}</td>
                            <td>
                                @if($s->my_parent)
                                <a href="{{ route('users.show', Qs::hash($s->my_parent_id)) }}">
                                    {{ $s->my_parent->name }}
                                </a>
                                @else &mdash; @endif
                            </td>

                            <td>
                                @if($canToggleBlock)
                                <label class="form-switch m-0">
                                    <input id="checkbox-user-{{ $s->user->id }}" onchange="updateUserBlockedState({{ $s->user->id }}, this)" type="checkbox" @checked($s->user->is_blocked)>
                                    <i></i>
                                </label>
                                @else
                                <label class="form-switch m-0">
                                    <input type="checkbox" @disabled(true) @checked($s->user->is_blocked)>
                                    <i></i>
                                </label>
                                @endif
                            </td>

                            @if($hasActions)
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="javascript:;" data-toggle="dropdown" class="material-symbols-rounded">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            @if($canViewProfile)
                                            <a href="{{ route('students.show', Qs::hash($s->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">visibility</i>View Profile
                                            </a>
                                            @endif
                                            @if($canEdit)
                                            <a href="{{ route('students.edit', Qs::hash($s->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">edit</i>Edit
                                            </a>
                                            @endif
                                            @if($canResetPassword)
                                            <a href="javascript:;" data-default_pass="student" data-href="{{ route('students.password.reset', Qs::hash($s->user->id)) }}" class="dropdown-item needs-reset-pass-confirmation">
                                                <i class="material-symbols-rounded">lock_reset</i>Reset Password
                                            </a>
                                            @endif
                                            @if($canMarksheet)
                                            <a target="_blank" href="{{ route('marks.year_selector', Qs::hash($s->user->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">bottom_sheets</i>Marksheet
                                            </a>
                                            @endif
                                            @if($canAssessmentsheet)
                                            <a target="_blank" href="{{ route('assessments.year_selector', Qs::hash($s->user->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">two_pager</i>Assessmentsheet
                                            </a>
                                            @endif
                                            @if($canDelete)
                                            <a id="{{ Qs::hash($s->user->id) }}" onclick="confirmDelete(this.id)" href="javascript:;" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i>Delete
                                            </a>
                                            <form method="post" id="item-delete-{{ Qs::hash($s->user->id) }}" action="{{ route('users.destroy', Qs::hash($s->user->id)) }}" class="hidden">
                                                @csrf @method('delete')
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- By section --}}
            @foreach($sections as $se)
            <div class="tab-pane fade" id="s{{ $se->id }}">
                <strong class="status-styled">{{ "{$my_class->name} {$se->name}" }}</strong>

                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>ADM No</th>
                            <th>Parent</th>
                            <th>Blocked</th>
                            @if($hasActions)
                            <th class="text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students->where('section_id', $se->id) as $sr)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <img class="rounded-circle" style="height:40px;width:40px;" src="{{ Usr::getTenancyAwarePhotoUrl($sr->user->photo_path) }}" alt="photo">
                            </td>
                            <td>{{ $sr->user->name }}</td>
                            <td>{{ $sr->admission_number }}</td>
                            <td>
                                @if($sr->my_parent)
                                <a href="{{ route('users.show', Qs::hash($sr->my_parent_id)) }}">
                                    {{ $sr->my_parent->name }}
                                </a>
                                @else &mdash; @endif
                            </td>

                            <td>
                                @if($canToggleBlock)
                                <label class="form-switch m-0">
                                    <input id="checkbox-user-{{ $sr->user->id }}" onchange="updateUserBlockedState({{ $sr->user->id }}, this)" type="checkbox" @checked($sr->user->is_blocked)>
                                    <i></i>
                                </label>
                                @else
                                <label class="form-switch m-0">
                                    <input type="checkbox" @disabled(true) @checked($sr->user->is_blocked)>
                                    <i></i>
                                </label>
                                @endif
                            </td>

                            @if($hasActions)
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="javascript:;" data-toggle="dropdown" class="material-symbols-rounded">lists</a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if($canViewProfile)
                                            <a href="{{ route('students.show', Qs::hash($sr->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">visibility</i>View Profile
                                            </a>
                                            @endif
                                            @if($canEdit)
                                            <a href="{{ route('students.edit', Qs::hash($sr->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">edit</i>Edit
                                            </a>
                                            @endif
                                            @if($canMarksheet)
                                            <a href="{{ route('marks.year_selector', Qs::hash($sr->user->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">bottom_sheets</i>Marksheet
                                            </a>
                                            @endif
                                            @if($canDelete)
                                            <a id="{{ Qs::hash($sr->user->id) }}" onclick="confirmDelete(this.id)" href="javascript:;" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i>Delete
                                            </a>
                                            <form method="post" id="item-delete-{{ Qs::hash($sr->user->id) }}" action="{{ route('users.destroy', Qs::hash($sr->user->id)) }}" class="hidden">
                                                @csrf @method('delete')
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @endif
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
