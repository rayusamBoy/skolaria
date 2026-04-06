@extends('layouts.master')
@section('page_title', "Student Information - {$my_class->name}")

@section('content')
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Students List</h6>
        {!! Qs::getPanelOptions() !!}
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

            {{-- Block / Unblock All --}}
            @canany([$tenant_permissions['student_records']['block_all_class']['permission'], $tenant_permissions['student_records']['unblock_all_class']['permission']])
            <div class="card mb-3">
                <div class="card-body text-center">
                    @can($tenant_permissions['student_records']['block_all_class']['permission'])
                    <button id="students-block-all-active" value="{{ $my_class->id }}" class="btn btn-danger">
                        Block All {{ $my_class->name }} Students
                    </button>
                    @endcan

                    @can($tenant_permissions['student_records']['unblock_all_class']['permission'])
                    <button id="students-unblock-all-active" value="{{ $my_class->id }}" class="btn btn-success ml-2">
                        Unblock All {{ $my_class->name }} Students
                    </button>
                    @endcan
                </div>
            </div>
            @endcanany

            {{-- All students --}}
            <div class="tab-pane fade show active" id="all-students">
                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>ADM No</th>
                            <th>Section</th>
                            <th>Parent</th>
                            <th>Blocked</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students->sortBy('user.gender') as $s)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <img class="rounded-circle" style="height:40px;width:40px;" src="{{ Usr::getTenancyAwarePhoto($s->user->photo) }}" alt="photo">
                            </td>
                            <td>{{ $s->user->name }}</td>
                            <td>{{ $s->adm_no }}</td>
                            <td>{{ $s->section->name }}</td>
                            <td>
                                @if($s->my_parent)
                                <a href="{{ route('users.show', Qs::hash($s->my_parent_id)) }}">
                                    {{ $s->my_parent->name }}
                                </a>
                                @else &mdash; @endif
                            </td>

                            @if(auth()->user()->can([$tenant_permissions['student_records']['block_all_class']['permission'], $tenant_permissions['student_records']['unblock_all_class']['permission']]))
                            <td>
                                <label class="form-switch m-0">
                                    <input id="checkbox-user-{{ $s->user->id }}" onchange="updateUserBlockedState({{ $s->user->id }}, this)" type="checkbox" @checked($s->user->blocked)>
                                    <i></i>
                                </label>
                            </td>
                            @else
                            <td>
                                <label class="form-switch m-0">
                                    <input type="checkbox" @disabled(true) @checked($s->user->blocked)>
                                    <i></i>
                                </label>
                            </td>
                            @endif

                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="javascript:;" data-toggle="dropdown" class="material-symbols-rounded">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            {{-- View Profile --}}
                                            @can($tenant_permissions['student_records']['show']['permission'])
                                            <a href="{{ route('students.show', Qs::hash($s->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">visibility</i>View Profile
                                            </a>
                                            @endcan
                                            {{-- Edit --}}
                                            @can($tenant_permissions['student_records']['edit']['permission'])
                                            <a href="{{ route('students.edit', Qs::hash($s->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">edit</i>Edit
                                            </a>
                                            @endcan
                                            {{-- Reset Password --}}
                                            @can($tenant_permissions['student_records']['reset_password']['permission'])
                                            <a href="javascript:;" data-default_pass="student" data-href="{{ route('students.password.reset', Qs::hash($s->user->id)) }}" class="dropdown-item needs-reset-pass-confirmation">
                                                <i class="material-symbols-rounded">lock_reset</i>Reset Password
                                            </a>
                                            @endcan
                                            {{-- Marksheet --}}
                                            @can($tenant_permissions['marks']['year_selector']['permission'])
                                            <a target="_blank" href="{{ route('marks.year_selector', Qs::hash($s->user->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">bottom_sheets</i>Marksheet
                                            </a>
                                            @endcan
                                            {{-- Assessmentsheet --}}
                                            @can($tenant_permissions['assessments']['show']['permission'])
                                            <a target="_blank" href="{{ route('assessments.year_selector', Qs::hash($s->user->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">two_pager</i>Assessmentsheet
                                            </a>
                                            @endcan
                                            {{-- Delete --}}
                                            @can($tenant_permissions['student_records']['destroy']['permission'])
                                            <a id="{{ Qs::hash($s->user->id) }}" onclick="confirmDelete(this.id)" href="javascript:;" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i>Delete
                                            </a>
                                            <form method="post" id="item-delete-{{ Qs::hash($s->user->id) }}" action="{{ route('students.destroy', Qs::hash($s->user->id)) }}" class="hidden">
                                                @csrf @method('delete')
                                            </form>
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

            {{-- By section --}}
            @foreach($sections as $se)
            <div class="tab-pane fade" id="s{{ $se->id }}">
                <strong class="status-styled">
                    {{ "{$my_class->name} {$se->name}" }}
                </strong>
                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>ADM No</th>
                            <th>Parent</th>
                            <th>Blocked</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students->where('section_id', $se->id) as $sr)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <img class="rounded-circle" style="height:40px;width:40px;" src="{{ Usr::getTenancyAwarePhoto($sr->user->photo) }}" alt="photo">
                            </td>
                            <td>{{ $sr->user->name }}</td>
                            <td>{{ $sr->adm_no }}</td>
                            <td>
                                @if($sr->my_parent)
                                <a href="{{ route('users.show', Qs::hash($sr->my_parent_id)) }}">
                                    {{ $sr->my_parent->name }}
                                </a>
                                @else &mdash; @endif
                            </td>

                            @if(auth()->user()->can([$tenant_permissions['student_records']['block_all_class']['permission'], $tenant_permissions['student_records']['unblock_all_class']['permission']]))
                            <td>
                                <label class="form-switch m-0">
                                    <input id="checkbox-user-{{ $sr->user->id }}" onchange="updateUserBlockedState({{ $sr->user->id }}, this)" type="checkbox" @checked($sr->user->blocked)>
                                    <i></i>
                                </label>
                            </td>
                            @else
                            <td>
                                <label class="form-switch m-0">
                                    <input type="checkbox" @disabled(true) @checked($sr->user->blocked)>
                                    <i></i>
                                </label>
                            </td>
                            @endif

                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="javascript:;" data-toggle="dropdown" class="material-symbols-rounded">lists</a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            {{-- View Profile --}}
                                            @can($tenant_permissions['student_records']['show']['permission'])
                                            <a href="{{ route('students.show', Qs::hash($sr->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">visibility</i>View Profile
                                            </a>
                                            @endcan
                                            {{-- Edit --}}
                                            @can($tenant_permissions['student_records']['edit']['permission'])
                                            <a href="{{ route('students.edit', Qs::hash($sr->id)) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">edit</i>Edit
                                            </a>
                                            @endcan
                                            {{-- Marksheet --}}
                                            @can($tenant_permissions['marks']['year_selector']['permission'])
                                            <a href="javascript:;" class="dropdown-item">
                                                <i class="material-symbols-rounded">bottom_sheets</i>Marksheet
                                            </a>
                                            @endcan
                                            {{-- Delete --}}
                                            @can($tenant_permissions['student_records']['destroy']['permission'])
                                            <a id="{{ Qs::hash($sr->user->id) }}" onclick="confirmDelete(this.id)" href="javascript:;" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i>Delete
                                            </a>
                                            <form method="post" id="item-delete-{{ Qs::hash($sr->user->id) }}" action="{{ route('students.destroy', Qs::hash($sr->user->id)) }}" class="hidden">
                                                @csrf @method('delete')
                                            </form>
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

@pushOnce('scripts')
<script>
    // Block all / Unblock all JS…
    $('#students-block-all-active').on('click', function() {
        let cid = $(this).val();
        confirmRequest(cid, 'block');
        return false;
    });
    $('#students-unblock-all-active').on('click', function() {
        let cid = $(this).val();
        confirmRequest(cid, 'unblock');
        return false;
    });

    function confirmRequest(class_id, type) {
        const text = type === 'block' ?
            'This will block selected students from accessing the system' :
            'This will allow selected students to access the system';
        Swal.fire({
            title: 'Are you sure?', 
            tex, 
            showCancelButton: true, 
            reverseButtons: true, 
            cancelButtonText: 'No, cancel'
        }).then((res) => {
            if (res.isConfirmed) ajaxProcessRequest(class_id, type);
        });
    }

    function ajaxProcessRequest(class_id, type) {
        let url = type === 'block' ?
            "{{ route('students.block_all_class') }}" :
            "{{ route('students.unblock_all_class') }}";
        $.post(url, {
            class_id, 
            _token: $('meta[name="csrf-token"]').attr('content')
        }, resp => {
            if (type === 'block')
                $("label.form-switch input").attr("checked", "checked");
            else
                $("label.form-switch input").removeAttr("checked");
            flash({
                msg: resp.msg, 
                type: 'success'
            });
        });
    }

</script>
@endPushOnce
