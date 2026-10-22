@extends('layouts.master')

@section('page_title', 'Manage Exams')

@section('content')
    @php
        $canEdit = auth()->user()->can($tenant_permissions['exams']['edit']['permission']);
        $canPublish = auth()->user()->can($tenant_permissions['exams']['publish']['permission']);
        $canDelete = auth()->user()->can($tenant_permissions['exams']['destroy']['permission']);
        $canToggleLock = auth()->user()->can($tenant_permissions['exams']['update_lock_state']['permission']);
        $canAnnounce = auth()->user()->can($tenant_permissions['exams']['announce']['permission']);
        $hasActions = $canEdit || $canPublish || $canDelete;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Exams</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                {{-- Manage exam item --}}
                <li class="nav-item">
                    <a
                        href="#all-exams"
                        class="{{ session('announce_exam') || $errors->any() ? 'nav-link' : 'nav-link active' }}"
                        data-toggle="tab"
                    >Manage Exam</a>
                </li>
                {{-- Add exam item --}}
                @can($tenant_permissions['exams']['store']['permission'])
                    <li class="nav-item">
                        <a
                            href="#new-exam"
                            class="{{ $errors->any() ? 'nav-link active' : 'nav-link' }}"
                            data-toggle="tab"
                        >Add Exam</a>
                    </li>
                @endcan
                {{-- Announce exam item --}}
                @if (session('announce_exam') && $canAnnounce)
                    <li class="nav-item">
                        <a href="#announce-exam" class="nav-link active" data-toggle="tab"><i></i> Announce the Exam</a>
                    </li>
                @endif
            </ul>

            <div class="tab-content">
                {{-- Manage exam tab --}}
                <div
                    class="{{ session('announce_exam') || $errors->any() ? 'tab-pane fade show' : 'tab-pane fade show active' }}"
                    id="all-exams"
                >
                    <table class="datatable-button-html5-columns table" id="manage-exams-table">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Term</th>
                                <th>Session</th>
                                <th>Category</th>
                                <th>Class Type</th>
                                <th>Locked</th>
                                @if ($hasActions)
                                    <th class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($exams as $ex)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $ex->name }}
                                        @if ($ex->is_published)
                                            <span class="badge bg-success">published</span>
                                        @else
                                            <span class="badge bg-danger">unpublished</span>
                                        @endif
                                        @if ($ex->hasAssessment())
                                            <span class="badge bg-info">has assessment</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst(Number::spellOrdinal($ex->term)) . " term" }}</td>
                                    <td>{{ $ex->year }}</td>
                                    <td>{{ $ex->category->name }}</td>
                                    <td>{{ $class_types->where('id', $ex->class_type_id)->first()->name }}</td>

                                    {{-- Locked toggle --}}
                                    @if ($canToggleLock)
                                        <td>
                                            <label class="form-switch m-0"
                                                ><input
                                                    id="checkbox-exam-lock-{{ $ex->id }}"
                                                    onchange="updateExamLockState({{ $ex->id }}, this)"
                                                    type="checkbox"
                                                    @checked($ex->is_locked) /><i></i
                                            ></label>
                                        </td>
                                    @else
                                        <td>
                                            <label class="form-switch disabled m-0"
                                                ><input type="checkbox" @checked($ex->is_locked) /><i></i
                                            ></label>
                                        </td>
                                    @endif

                                    @if ($hasActions && ! $ex->is_published)
                                        <td class="text-center">
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a
                                                        href="javascript:;"
                                                        data-toggle="dropdown"
                                                        class="material-symbols-rounded"
                                                    >lists</a>
                                                    <div class="dropdown-menu dropdown-menu-left">
                                                        @if ($canEdit && ! $ex->is_locked)
                                                            <a
                                                                href="{{ route('exams.edit', $ex->id) }}"
                                                                class="dropdown-item"
                                                            ><i class="material-symbols-rounded">edit</i> Edit</a>
                                                        @endif
                                                        @if ($canPublish)
                                                            <a
                                                                id="{{ $ex->id }}"
                                                                onclick="confirmPublish(this.id)"
                                                                href="javascript:;"
                                                                class="dropdown-item text-warning"
                                                                title="Publish this exam to make results visible to students, parents, teachers etc,. Once published, grades, divisions, marks, and all related records are frozen — no further edits can be made."
                                                                data-toggle="tooltip"
                                                                ><i class="material-symbols-rounded">visibility</i>
                                                                Publish</a>
                                                            <form
                                                                method="post"
                                                                id="item-publish-{{ $ex->id }}"
                                                                action="{{ route('exams.publish', $ex->id) }}"
                                                                class="hidden"
                                                            >
                                                                @csrf
                                                            </form>
                                                        @endif
                                                        @if ($canDelete)
                                                            <a
                                                                id="{{ $ex->id }}"
                                                                onclick="confirmDelete(this.id)"
                                                                href="javascript:;"
                                                                class="dropdown-item text-danger"
                                                                ><i class="material-symbols-rounded">delete</i>
                                                                Delete</a>
                                                            <form
                                                                method="post"
                                                                id="item-delete-{{ $ex->id }}"
                                                                action="{{ route('exams.destroy', $ex->id) }}"
                                                                class="hidden"
                                                            >
                                                                @csrf
                                                                @method('delete')
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

                {{-- Add exam tab --}}
                @can($tenant_permissions['exams']['store']['permission'])
                    <div class="{{ $errors->any() ? 'tab-pane fade show active' : 'tab-pane fade' }}" id="new-exam">
                        <div class="row">
                            <div class="col-md-12">
                                <div
                                    class="alert alert-info alert-dismissible has-do-not-show-again-button alert-styled-left"
                                    id="exam-alert-info"
                                >
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                    <span
                                        >You are creating an Exam for the Current Session
                                        <strong>{{ $current_session }}</strong>.</span>
                                    <span>
                                        You may set the format for the exam number to be generated. Structure the number
                                        however you like. Use
                                        <strong>{{ Usr::getStudentExamNumberPlaceholder() }}</strong> character where
                                        the students numbers need to be generated automatically ie.,
                                        <strong
                                            >{{ Usr::getStudentExamNumberPlaceholder() }} (1 digit), {{ str_repeat(Usr::getStudentExamNumberPlaceholder(), 2) }} (2
                                            digit) etc.</strong
                                        >, while other characters will remain constant. Allowed characters are both
                                        cases letters, slashes, dash and numbers. You may edit the numbers later during
                                        print.
                                    </span>
                                </div>
                            </div>
                        </div>
                        <form
                            method="post"
                            action="{{ route('exams.store') }}"
                            class="ajax-store"
                            data-reload="#manage-exams-table"
                        >
                            <div class="row">
                                <div class="col-md-7">
                                    @csrf
                                    {{-- Name --}}
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <input
                                                name="name"
                                                required
                                                type="text"
                                                class="form-control"
                                                placeholder="Name of Exam"
                                            />
                                        </div>
                                    </div>
                                    {{-- Category --}}
                                    <div class="form-group row">
                                        <label for="category" class="col-lg-3 col-form-label font-weight-semibold"
                                            >Category <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <select
                                                data-placeholder="Select"
                                                class="form-control select-search"
                                                name="category_id"
                                                id="category"
                                            >
                                                <option selected disabled value="">Select</option>
                                                @foreach ($exams_cat as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Class Type --}}
                                    <div class="form-group row">
                                        <label for="class-type" class="col-lg-3 col-form-label font-weight-semibold"
                                            >Class Type <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <select
                                                data-placeholder="Select"
                                                class="form-control select-search"
                                                name="class_type_id"
                                                id="class-type"
                                            >
                                                <option selected disabled value="">Select</option>
                                                @foreach ($class_types as $ct)
                                                    <option value="{{ $ct->id }}">{{ $ct->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Term --}}
                                    <div class="form-group row">
                                        <label for="term" class="col-lg-3 col-form-label font-weight-semibold"
                                            >Term <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <select
                                                data-placeholder="Select"
                                                class="form-control select"
                                                name="term"
                                                id="term"
                                            >
                                                <option selected disabled value="">Select</option>
                                                <option value="1">First Term</option>
                                                <option selected value="2">Second Term</option>
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Exam Denominator --}}
                                    <div class="form-group row">
                                        <label class="col-lg-7 col-form-label font-weight-semibold">Exam Denominator <span class="text-danger">*</span></label>
                                        <div class="col-lg-5">
                                            <input
                                                name="exam_denominator"
                                                min="0"
                                                max="100"
                                                value="100"
                                                required
                                                type="number"
                                                class="form-control"
                                            />
                                        </div>
                                    </div>
                                    {{-- Exam Student Position By Value --}}
                                    <div class="form-group row">
                                        <label class="col-lg-5 col-form-label font-weight-semibold"
                                            >Calculate Exam Student Position By
                                            <span class="text-danger">*</span></label>
                                        <div class="col-lg-7">
                                            @foreach (Mk::getStudentExamPositionByValues() as $key => $value)
                                                <div class="d-flex mb-2">
                                                    <span
                                                        ><input
                                                            type="radio"
                                                            id="exm-pos-by-{{ $key }}"
                                                            name="exam_student_position_by_value"
                                                            value="{{ $key }}"
                                                            class="form-input-styled text-center"
                                                    /></span>
                                                    <label
                                                        for="exm-pos-by-{{ $key }}"
                                                        class="m-auto-0 ml-2 w-50 cursor-pointer"
                                                    >{{ $value }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    {{-- Sort order for exam position --}}
                                    <div class="form-group row" id="exam-position-sort-order">
                                        <label class="col-lg-5 col-form-label font-weight-semibold">Position Sort Order <span class="text-danger">*</span></label>
                                        <div class="col-lg-7">
                                            <div class="d-flex mb-2">
                                                <span
                                                    ><input
                                                        type="radio"
                                                        checked
                                                        id="exam-sort-desc"
                                                        name="exam_student_position_sort_order"
                                                        value="desc"
                                                        class="form-input-styled text-center"
                                                /></span>
                                                <label for="exam-sort-desc" class="ml-2 cursor-pointer"
                                                    >Highest is best</label>
                                            </div>
                                            <div class="d-flex mb-2">
                                                <span
                                                    ><input
                                                        type="radio"
                                                        id="exam-sort-asc"
                                                        name="exam_student_position_sort_order"
                                                        value="asc"
                                                        class="form-input-styled text-center"
                                                /></span>
                                                <label for="exam-sort-asc" class="ml-2 cursor-pointer"
                                                    >Lowest is best</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-warning alert-styled-left">
                                                <span>
                                                    Set whether this exam includes Assessments (CA) before publishing.
                                                    After publishing, the exam cannot be edited, so this choice cannot
                                                    be changed.
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Has Continous Assessment (CA)? --}}
                                    <div class="form-group row">
                                        <label for="has_assessment" class="col-lg-8 col-form-label font-weight-semibold"
                                            >Has Continous Assessment (CA)? <span class="text-danger">*</span></label>
                                        <div class="col-lg-4">
                                            <select
                                                class="form-control select"
                                                name="has_assessment"
                                                id="has_assessment"
                                            >
                                                <option value="1">Yes</option>
                                                <option selected value="0">No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="ca-setup display-none">
                                        <strong>Exam CA setup</strong>
                                        {{-- Class Work Denominator --}}
                                        <div class="form-group row">
                                            <label class="col-lg-7 col-form-label font-weight-semibold">Class Work Denominator <span class="text-danger">*</span></label>
                                            <div class="col-lg-5">
                                                <input
                                                    name="cw_denominator"
                                                    min="0"
                                                    max="100"
                                                    value="10"
                                                    type="number"
                                                    class="form-control"
                                                />
                                            </div>
                                        </div>
                                        {{-- Home Work Denominator --}}
                                        <div class="form-group row">
                                            <label class="col-lg-7 col-form-label font-weight-semibold">Home Work Denominator <span class="text-danger">*</span></label>
                                            <div class="col-lg-5">
                                                <input
                                                    name="hw_denominator"
                                                    min="0"
                                                    max="100"
                                                    value="10"
                                                    type="number"
                                                    class="form-control"
                                                />
                                            </div>
                                        </div>
                                        {{-- Topic Test Denominator --}}
                                        <div class="form-group row">
                                            <label class="col-lg-7 col-form-label font-weight-semibold">Topic Test Denominator <span class="text-danger">*</span></label>
                                            <div class="col-lg-5">
                                                <input
                                                    name="tt_denominator"
                                                    min="0"
                                                    max="100"
                                                    value="20"
                                                    type="number"
                                                    class="form-control"
                                                />
                                            </div>
                                        </div>
                                        {{-- Termed Test Denominator --}}
                                        <div class="form-group row">
                                            <label class="col-lg-7 col-form-label font-weight-semibold">Termed Test Denominator <span class="text-danger">*</span></label>
                                            <div class="col-lg-5">
                                                <input
                                                    name="tdt_denominator"
                                                    min="0"
                                                    max="100"
                                                    value="60"
                                                    type="number"
                                                    class="form-control"
                                                />
                                            </div>
                                        </div>
                                        {{-- CA Student Position By Value --}}
                                        <div class="form-group row">
                                            <label class="col-lg-7 col-form-label font-weight-semibold"
                                                >Calculate CA Student Position By
                                                <span class="text-danger">*</span></label>
                                            <div class="col-lg-5">
                                                @foreach (Mk::getStudentCAPositionByValues() as $key => $value)
                                                    <div class="d-flex mb-2">
                                                        <span
                                                            ><input
                                                                type="radio"
                                                                id="ca-position-by-{{ $key }}"
                                                                name="ca_student_position_by_value"
                                                                value="{{ $key }}"
                                                                class="form-input-styled text-center"
                                                        /></span>
                                                        <label
                                                            for="ca-position-by-{{ $key }}"
                                                            class="m-auto-0 ml-2 w-50 cursor-pointer"
                                                        >{{ $value }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        {{-- Sort order for CA position --}}
                                        <div class="form-group row" id="ca-position-sort-order">
                                            <label class="col-lg-7 col-form-label font-weight-semibold">Position Sort Order <span class="text-danger">*</span></label>
                                            <div class="col-lg-5">
                                                <div class="d-flex mb-2">
                                                    <span
                                                        ><input
                                                            type="radio"
                                                            checked
                                                            id="ca-sort-desc"
                                                            name="ca_student_position_sort_order"
                                                            value="desc"
                                                            class="form-input-styled text-center"
                                                    /></span>
                                                    <label for="ca-sort-desc" class="ml-2 cursor-pointer"
                                                        >Highest is best</label>
                                                </div>
                                                <div class="d-flex mb-2">
                                                    <span
                                                        ><input
                                                            type="radio"
                                                            id="ca-sort-asc"
                                                            name="ca_student_position_sort_order"
                                                            value="asc"
                                                            class="form-input-styled text-center"
                                                    /></span>
                                                    <label for="ca-sort-asc" class="ml-2 cursor-pointer"
                                                        >Lowest is best</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        {{-- Number Format --}}
                                        <div class="number-formats col-12">
                                            <label class="col-form-label font-weight-semibold">Number Format</label>
                                            <div class="text-info"><strong>Select Class Type first</strong></div>
                                            @foreach ($my_classes as $class)
                                                <div class="mb-2 div number-format-{{ $class->class_type_id }} d-none">
                                                    <span class="m-auto">
                                                        <input
                                                            type="checkbox"
                                                            id="class_id_{{ $class->id }}"
                                                            name="class_id_{{ $class->id }}"
                                                            value="{{ $class->id }}"
                                                            class="form-input-styled text-center"
                                                        />
                                                    </span>
                                                    <span
                                                        for="class_id_{{ $class->id }}"
                                                        class="m-auto-0 ml-2 w-50 cursor-pointer"
                                                    >{{ $class->name }}</span>
                                                    <input
                                                        name="exam_number_format_{{ $class->id }}"
                                                        type="text"
                                                        class="form-control"
                                                        placeholder="ie., S6237-CLASS-{{ $class->id }}-{{ str_repeat(Usr::getStudentExamNumberPlaceholder(), 2) }}-{{ date('y') }}"
                                                    />
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">
                                    Submit form <i class="material-symbols-rounded ml-2">send</i>
                                </button>
                            </div>
                        </form>
                    </div>
                @endcan

                {{-- Announce exam tab --}}
                @if (session('announce_exam') && $canAnnounce)
                    <div class="tab-pane fade show active" id="announce-exam">
                        <div class="row">
                            <div class="col-md-12">
                                <div
                                    class="alert alert-info alert-dismissible has-do-not-show-again-button alert-styled-left"
                                    id="announce-exam-info"
                                >
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                    You have just published the exam titled "{{ session('exam_name') }}". Would you like
                                    to announce it to the public (i.e., have it appear on the login page)? If
                                    <strong>yes</strong>, please fill in the form below to proceed.
                                    <strong
                                        >The form can only be submitted once at this time, and once submitted, the
                                        content cannot be edited</strong
                                    >. If you prefer not to announce it, you may ignore this step; it is optional.
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <form method="post" class="ajax-store" action="{{ route('exams.announce') }}">
                                    @csrf
                                    <input type="hidden" name="exam_id" value="{{ session('exam_id') }}" />
                                    <div class="row">
                                        {{-- Message --}}
                                        <div class="form-group col-md-6">
                                            <label class="col-form-label font-weight-semibold">Message <span class="text-danger">*</span></label>
                                            <textarea name="message" required class="form-control text-capitalize"
                                                >{{ session('exam_name') }} exam results have been published, signin and check them now</textarea>
                                        </div>
                                        {{-- Duration --}}
                                        <div class="form-group col-md-6">
                                            <label for="duration" class="col-form-label font-weight-semibold"
                                                >Duration <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input
                                                    type="duration"
                                                    value="259200"
                                                    class="form-control"
                                                    min="0"
                                                    max="2505600"
                                                    id="duration"
                                                    name="duration"
                                                    required
                                                />
                                                <div class="input-group-append">
                                                    <span class="input-group-text">seconds</span>
                                                </div>
                                            </div>
                                            <span class="status-styled pt-2 pb-1">(default: 3 days = 259200 seconds)</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">
                                            Submit form <i class="material-symbols-rounded ml-2">send</i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
