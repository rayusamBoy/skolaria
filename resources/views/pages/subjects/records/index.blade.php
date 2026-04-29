@extends('layouts.master')

@section('page_title', 'Manage Subject Record')

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Records</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item dropdown">
                <a href="javascript:;" class="nav-link active dropdown-toggle" data-toggle="dropdown">Manage Records</a>
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach($my_classes as $c)
                    <a href="#c{{ $c->id }}" class="dropdown-item" data-toggle="tab">{{ $c->name }}</a>
                    @endforeach
                </div>
            </li>
            @can($tenant_permissions['subjects']['store_record']['permission'])
            <li class="nav-item"><a href="#new-subject" class="nav-link" data-toggle="tab">Add Record</a></li>
            @endcan
        </ul>

        <div class="tab-content">
            @can($tenant_permissions['subjects']['store_record']['permission'])
            <div class="tab-pane show fade" id="new-subject">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info border-0 alert-dismissible has-do-not-show-again-button" id="add-subject-info">
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                            <span>
                                If the Subject you are adding applies to all sections or does not apply at all just leave <strong>Not Applicable</strong> in the <strong>Section</strong> field.
                                Otherwise select the section that the subject applies to. If it only applies to specific student(s), select the student(s) from the <strong>Select Student(s)</strong> field.
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <form class="ajax-store" method="post" action="{{ route('subject_records.store') }}">
                            @csrf
                            <div class="form-group row">
                                <label for="my_class_id" class="col-lg-3 col-form-label font-weight-semibold">Select Class <span class="text-danger">*</span></label>
                                <div class="col-lg-9">
                                    <select required data-placeholder="Select Class" onchange="getClassSubjects(this.value);getClassSections(this.value, '#sec_id_add_not_applicable');getClassStudents(this.value)" class="form-control select" name="my_class_id" id="my_class_id">
                                        <option value=""></option>
                                        @foreach($my_classes as $c)
                                        <option data-class_type_id="{{ $c->class_type_id }}" @selected(old('my_class_id')==$c->id) value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="subject_id" class="col-lg-3 col-form-label font-weight-semibold">Subject <span class="text-danger">*</span></label>
                                <div class="col-lg-9">
                                    <select required data-placeholder="Select class first" class="form-control select" name="subject_id" id="subject_id">
                                        <option disabled value="">Select class first</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="sec_id_add_not_applicable" class="col-lg-3 col-form-label font-weight-semibold">Section:</label>
                                <div class="col-lg-9">
                                    <select id="sec_id_add_not_applicable" name="section_id" data-placeholder="Not Apllicable" class="form-control select">
                                        <option selected value="">Not Applicable</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="students-ids" class="col-lg-3 col-form-label font-weight-semibold">Select Student(s)</label>
                                <div class="col-lg-9">
                                    <select id="students-ids" onchange="updateSectionDisableState()" name="students_ids[]" multiple="multiple" class="form-control select">
                                        <option disabled value="">Select class first</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="teacher_id" class="col-lg-3 col-form-label font-weight-semibold">Teacher <span class="text-danger">*</span></label>
                                <div class="col-lg-9">
                                    <select required data-placeholder="Select Teacher" class="form-control select-search" name="teacher_id" id="teacher_id">
                                        <option value=""></option>
                                        @foreach($teachers as $t)
                                        <option @selected(old('teacher_id')==Qs::hash($t->id)) value="{{ Qs::hash($t->id) }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Submit form <i class="material-symbols-rounded ml-2">send</i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            @foreach($my_classes as $c)
            <div class="tab-pane fade show" id="c{{ $c->id }}">
                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Teacher</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjects->where('my_class_id', $c->id) as $s)
                        @if($s->record->first())
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span data-toggle="tooltip" title="{{ "Short Name: {$s->slug}, Is Core: " . ($s->is_core ? 'Yes' : 'No') }}">{{ $s->name }}</span></td>
                            <td>{{ $s->my_class->name }}</td>
                            <td>
                                @foreach($s->record as $key => $rec)
                                @if(isset($rec->section->name))
                                {{ $rec->section->name }}
                                @elseif(isset($rec->students_ids))
                                @include('pages/modals/subject_assigned_students')
                                <a href="javascript:;" data-toggle="modal" data-target="#subjects-assigned-students-{{ $rec->id }}">View Assigned</a>
                                @else
                                Not Applicable
                                @endif
                                @if($key != array_key_last($s->record->toArray()))
                                <hr class="m-0" />
                                @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach($s->record as $key => $rec)
                                {{ $rec->teacher->name }}
                                @if($key != array_key_last($s->record->toArray()))
                                <hr class="m-0" />
                                @endif
                                @endforeach
                            </td>
                            <td class="text-center">
                                @foreach($s->record as $key => $rec)
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a class="material-symbols-rounded" href="javascript:;" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            @can($tenant_permissions['subjects']['edit_record']['permission'])
                                            <a href="{{ route('subject_records.edit', $rec->id) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">edit</i> Edit
                                            </a>
                                            @endcan
                                            @can($tenant_permissions['subjects']['delete_record']['permission'])
                                            <a id="{{ $s->id }}" onclick="confirmDelete(this.id)" href="javascript:;" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form method="post" id="item-delete-{{ $s->id }}" action="{{ route('subject_records.delete', [$rec->id]) }}" class="hidden">
                                                @csrf
                                                @method('delete')
                                            </form>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                                @if($key != array_key_last($s->record->toArray()))
                                <br />
                                @endif
                                @endforeach
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
