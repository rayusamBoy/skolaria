@extends('layouts.master')

@section('page_title', 'Edit Subject Record - '.$sub_rec->subject->name.' ('.$sub_rec->subject->my_class->name.')')

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Edit Subject Record</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <form
                        class="ajax-update"
                        method="post"
                        action="{{ route('subject_records.update', $sub_rec->id) }}"
                        data-reload="#page-title"
                    >
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="subject_id" value="{{ $sub_rec->subject_id }}" />

                        <div class="form-group row">
                            <label for="my_class_id" class="col-lg-3 col-form-label font-weight-semibold">Class</label>
                            <div class="col-lg-9">
                                <input
                                    type="text"
                                    class="form-control"
                                    readonly
                                    value="{{ $sub_rec->subject->my_class->name }}"
                                />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="section_id" class="col-lg-3 col-form-label font-weight-semibold">Section</label>
                            <div class="col-lg-9">
                                <select
                                    id="section_id"
                                    data-placeholder="Not Apllicable"
                                    name="section_id"
                                    class="form-control select"
                                >
                                    <option @selected($sub_rec->section_id == null) value="">Not Applicable</option>
                                    @foreach ($my_class_sections as $section)
                                        <option
                                            @selected($section->id == $sub_rec->section_id)
                                            value="{{ $section->id }}"
                                        >
                                            {{ $section->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="students-ids" class="col-lg-3 col-form-label font-weight-semibold"
                                >Select Student(s)</label>
                            <div class="col-lg-9">
                                <select
                                    id="students-ids"
                                    name="students_ids[]"
                                    multiple="multiple"
                                    data-placeholder="Select"
                                    class="form-control select"
                                    onchange="updateSectionDisableState()"
                                >
                                    @foreach (Usr::getClassStudents($sub_rec->subject->my_class_id) as $st)
                                        <option
                                            @selected($sub_rec->students_ids != null && isset($st->user->id) && in_array($st->user->id, $sub_rec->students_ids))
                                            value="{{ $st->user->id }}"
                                        >
                                            {{ $st->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Subject Name</label>
                            <div class="col-lg-9">
                                <input
                                    type="text"
                                    disabled
                                    class="form-control"
                                    value="{{ $sub_rec->subject->name }}"
                                />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Short Name</label>
                            <div class="col-lg-9">
                                <input
                                    name="slug"
                                    disabled
                                    value="{{ $sub_rec->subject->slug }}"
                                    type="text"
                                    class="form-control"
                                    placeholder="Short Name"
                                />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="is_core" class="col-lg-3 col-form-label font-weight-semibold"
                                >Core Subject?</label>
                            <div class="col-lg-2">
                                <select disabled class="form-control select" name="is_core" id="is_core">
                                    <option @selected($sub_rec->subject->is_core)>Yes</option>
                                    <option @selected(! $sub_rec->subject->is_core)>No</option>
                                </select>
                            </div>
                            <div class="col-lg-7 m-lg-auto">
                                <span class="font-weight-bold font-italic text-info-800">{{ __('msg.core_subject') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="teacher_id" class="col-lg-3 col-form-label font-weight-semibold"
                                >Teacher <span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <select
                                    data-placeholder="Select Teacher"
                                    class="form-control select-search"
                                    name="teacher_id"
                                    id="teacher_id"
                                >
                                    <option value=""></option>
                                    @foreach ($teachers as $t)
                                        <option
                                            @selected($sub_rec->teacher_id == $t->id)
                                            value="{{ Qs::hash($t->id) }}"
                                        >
                                            {{ $t->name }}
                                        </option>
                                    @endforeach
                                </select>
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
    </div>

    {{--subject Edit Ends--}}

@endsection
