@extends('layouts.master')

@section('page_title', "Edit Exam - {$ex->name} ({$ex->year})")

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Edit Exam</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="row">
            <div class="col-md-12">
                <div
                    class="alert alert-info alert-dismissible has-do-not-show-again-button alert-styled-left"
                    id="exam-alert-info"
                >
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    <span>
                        You may set the format for the exam number to be generated. Structure the number however you
                        like. Use <strong>{{ Usr::getStudentExamNumberPlaceholder() }}</strong> character where the
                        students numbers need to be generated automatically ie.,
                        <strong
                            >{{ Usr::getStudentExamNumberPlaceholder() }} (1 digit), {{ str_repeat(Usr::getStudentExamNumberPlaceholder(), 2) }} (2
                            digit) etc.</strong
                        >, while other characters will remain constant. Allowed characters are both cases letters,
                        slashes, dash and numbers. You may edit the numbers later during print.
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form
                method="post"
                class="ajax-update"
                data-reload="#page-title"
                action="{{ route('exams.update', $ex->id) }}"
            >
                <div class="row">
                    <div class="col-md-7">
                        @csrf
                        @method('PUT')
                        <input type="hidden" value="{{ $ex->year }}" name="year" />
                        {{-- Name --}}
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input
                                    name="name"
                                    value="{{ $ex->name }}"
                                    required
                                    type="text"
                                    class="form-control"
                                    placeholder="Name of Exam"
                                />
                            </div>
                        </div>
                        {{-- Category --}}
                        <div class="form-group row">
                            <label for="category" class="col-lg-3 col-form-label font-weight-semibold">Category</label>
                            <div class="col-lg-9">
                                <select disabled class="form-control select-search" name="category_id" id="category">
                                    <option value="{{ $ex->category->id }}">{{ $ex->category->name }}</option>
                                </select>
                            </div>
                        </div>
                        {{-- Class Type --}}
                        <div class="form-group row">
                            <label for="class-type" class="col-lg-3 col-form-label font-weight-semibold"
                                >Class Type</label>
                            <div class="col-lg-9">
                                <select
                                    disabled
                                    class="form-control select-search"
                                    name="class_type_id"
                                    id="class-type"
                                >
                                    <option value="{{ $ex->class_type->id }}">{{ $ex->class_type->name }}</option>
                                </select>
                            </div>
                        </div>
                        {{-- Term --}}
                        <div class="form-group row">
                            <label for="term" class="col-lg-3 col-form-label font-weight-semibold">Term</label>
                            <div class="col-lg-9">
                                <select
                                    data-placeholder="Select Teacher"
                                    class="form-control select"
                                    name="term"
                                    id="term"
                                >
                                    <option @selected($ex->term == 1) value="1">First Term</option>
                                    <option @selected($ex->term == 2) value="2">Second Term</option>
                                </select>
                            </div>
                        </div>
                        {{-- Number Format --}}
                        <div class="form-group row">
                            <div class="col-12">
                                <label class="col-form-label font-weight-semibold">Number Format</label>
                                @foreach ($my_classes as $class)
                                    <div class="d-flex mb-2">
                                        <input
                                            type="hidden"
                                            name="number_format_{{ $class->id }}"
                                            value="{{ $ex->number_format->where('my_class_id', $class->id)->value('id') }}"
                                        />
                                        <span class="m-auto">
                                            <input
                                                @checked($ex->number_format->where('my_class_id', $class->id)->value('my_class_id') == $class->id)
                                                type="checkbox"
                                                id="class_id_{{ $class->id }}"
                                                name="class_id_{{ $class->id }}"
                                                value="{{ $class->id }}"
                                                class="form-input-styled text-center"
                                            />
                                        </span>
                                        <label
                                            for="class_id_{{ $class->id }}"
                                            class="m-auto-0 ml-2 w-50 cursor-pointer"
                                        >{{ $class->name }}</label>
                                        <input
                                            name="exam_number_format_{{ $class->id }}"
                                            type="text"
                                            value="{{ $ex->number_format->where('my_class_id', $class->id)->value('format') }}"
                                            class="form-control"
                                            placeholder="ie., S6237-CLASS-{{ $class->id }}-{{ str_repeat(Usr::getStudentExamNumberPlaceholder(), 2) }}-{{ date('y') }}"
                                        />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        {{-- Exam Denominator --}}
                        <div class="form-group row">
                            <label class="col-lg-7 col-form-label font-weight-semibold">Exam Denominator <span class="text-danger">*</span></label>
                            <div class="col-lg-5">
                                <input
                                    name="exam_denominator"
                                    min="0"
                                    max="100"
                                    value="{{ $ex->exam_denominator }}"
                                    required
                                    type="number"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        {{-- Calculate Position By --}}
                        <div class="form-group row">
                            <label class="col-lg-5 col-form-label font-weight-semibold">Calculate Position By <span class="text-danger">*</span></label>
                            <div class="col-lg-7">
                                @foreach (Mk::getStudentExamPositionByValues() as $key => $value)
                                    <div class="d-flex mb-2">
                                        <span
                                            ><input
                                                @checked($key == $ex->exam_student_position_by_value)
                                                type="radio"
                                                id="exam_mark-pos-by-{{ $key }}"
                                                name="exam_student_position_by_value"
                                                value="{{ $key }}"
                                                class="form-input-styled text-center"
                                        /></span>
                                        <label
                                            for="exam_mark-pos-by-{{ $key }}"
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
                                            @checked($ex->exam_student_position_sort_order == 'desc')
                                            id="exam-sort-desc"
                                            name="exam_student_position_sort_order"
                                            value="desc"
                                            class="form-input-styled text-center"
                                    /></span>
                                    <label for="exam-sort-desc" class="ml-2 cursor-pointer">Highest is best</label>
                                </div>
                                <div class="d-flex mb-2">
                                    <span
                                        ><input
                                            type="radio"
                                            @checked($ex->exam_student_position_sort_order == 'asc')
                                            id="exam-sort-asc"
                                            name="exam_student_position_sort_order"
                                            value="asc"
                                            class="form-input-styled text-center"
                                    /></span>
                                    <label for="exam-sort-asc" class="ml-2 cursor-pointer">Lowest is best</label>
                                </div>
                            </div>
                        </div>
                        {{-- Has Continous Assessment (CA)? --}}
                        <div class="form-group row">
                            <label for="has_assessment" class="col-lg-8 col-form-label font-weight-semibold"
                                >Has Continous Assessment (CA)? <span class="text-danger">*</span></label>
                            <div class="col-lg-4">
                                <select class="form-control select" name="has_assessment" id="has_assessment">
                                    <option @selected($ex->hasAssessment()) value="1">Yes</option>
                                    <option @selected(! $ex->hasAssessment()) value="0">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="ca-setup {{ $ex->hasAssessment() ? '' : 'display-none' }}">
                            <strong>Exam CA setup</strong>
                            {{-- Cw Denominator --}}
                            <div class="form-group row">
                                <label class="col-lg-7 col-form-label font-weight-semibold">Class Work Denominator <span class="text-danger">*</span></label>
                                <div class="col-lg-5">
                                    <input
                                        name="cw_denominator"
                                        min="0"
                                        max="100"
                                        value="{{ $ex->cw_denominator }}"
                                        type="number"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            {{-- HW Denominator --}}
                            <div class="form-group row">
                                <label class="col-lg-7 col-form-label font-weight-semibold">Home Work Denominator <span class="text-danger">*</span></label>
                                <div class="col-lg-5">
                                    <input
                                        name="hw_denominator"
                                        min="0"
                                        max="100"
                                        value="{{ $ex->hw_denominator }}"
                                        type="number"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            {{-- TT Denominator --}}
                            <div class="form-group row">
                                <label class="col-lg-7 col-form-label font-weight-semibold">Topic Test Denominator <span class="text-danger">*</span></label>
                                <div class="col-lg-5">
                                    <input
                                        name="tt_denominator"
                                        min="0"
                                        max="100"
                                        value="{{ $ex->tt_denominator }}"
                                        type="number"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            {{-- TDT Denominator --}}
                            <div class="form-group row">
                                <label class="col-lg-7 col-form-label font-weight-semibold">Termed Test Denominator <span class="text-danger">*</span></label>
                                <div class="col-lg-5">
                                    <input
                                        name="tdt_denominator"
                                        min="0"
                                        max="100"
                                        value="{{ $ex->tdt_denominator }}"
                                        type="number"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            {{-- Calculate Position By --}}
                            <div class="form-group row">
                                <label class="col-lg-5 col-form-label font-weight-semibold">Calculate Position By <span class="text-danger">*</span></label>
                                <div class="col-lg-7">
                                    @foreach (Mk::getStudentCAPositionByValues() as $key => $value)
                                        <div class="d-flex mb-2">
                                            <span
                                                ><input
                                                    @checked($key == $ex->ca_student_position_by_value)
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
                                                @checked($ex->ca_student_position_sort_order == 'desc')
                                                id="ca-sort-desc"
                                                name="ca_student_position_sort_order"
                                                value="desc"
                                                class="form-input-styled text-center"
                                        /></span>
                                        <label for="ca-sort-desc" class="ml-2 cursor-pointer">Highest is best</label>
                                    </div>
                                    <div class="d-flex mb-2">
                                        <span
                                            ><input
                                                type="radio"
                                                @checked($ex->ca_student_position_sort_order == 'asc')
                                                id="ca-sort-asc"
                                                name="ca_student_position_sort_order"
                                                value="asc"
                                                class="form-input-styled text-center"
                                        /></span>
                                        <label for="ca-sort-asc" class="ml-2 cursor-pointer">Lowest is best</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Submit --}}
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                                Submit form <i class="material-symbols-rounded ml-2">send</i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Exams Edit Ends--}}

@endsection
