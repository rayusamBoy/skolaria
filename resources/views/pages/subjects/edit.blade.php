@extends('layouts.master')

@section('page_title', "Edit Subject - {$sub->name} (".$sub->my_class->name.')')

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Edit Subject</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <form
                        class="ajax-update"
                        method="post"
                        action="{{ route('subjects.update', $sub->id) }}"
                        data-reload="#page-title"
                    >
                        @csrf
                        @method('PUT')
                        {{-- Class --}}
                        <div class="form-group row">
                            <label for="my_class_id" class="col-lg-3 col-form-label font-weight-semibold">
                                Class
                            </label>
                            <div class="col-lg-9">
                                <input
                                    type="text"
                                    class="form-control"
                                    disabled
                                    value="{{ $sub->my_class->name ?? 'Not Applicable' }}"
                                />
                                <input type="hidden" name="my_class_id" value="{{ $sub->my_class_id }}" />
                            </div>
                        </div>
                        {{-- Other Classes --}}
                        @if ($my_classes->isNotEmpty())
                        <div class="form-group row">
                            <label for="subject_ids" class="col-lg-3 col-form-label font-weight-semibold">
                                Other Classes
                                <i
                                    class="material-symbols-rounded text-warning float-right"
                                    data-toggle="tooltip"
                                    title="These classes share the same exact subject details as the main class. Turn on the switch to update the subject for these classes as well."
                                >info</i>
                            </label>

                            <div class="col-lg-7">
                                <select
                                    required
                                    class="form-control select"
                                    multiple="multiple"
                                    name="subject_ids[]"
                                    id="subject_ids"
                                >
                                    <option value=""></option>
                                    @foreach ($my_classes as $subject_id => $my_class)
                                        <option selected value="{{ $subject_id }}">{{ $my_class->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-switch m-0">
                                    <input name="update_for_other_classes" type="checkbox" />
                                    <i></i>
                                </label>
                            </div>
                        </div>
                        @endif
                        {{-- Name --}}
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Name</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" name="name" value="{{ $sub->name }}" />
                            </div>
                        </div>
                        {{-- Slug --}}
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Short Name</label>
                            <div class="col-lg-9">
                                <input
                                    name="slug"
                                    value="{{ $sub->slug }}"
                                    type="text"
                                    class="form-control"
                                    placeholder="Short Name"
                                />
                            </div>
                        </div>
                        {{-- Credits --}}
                        <div class="form-group row">
                            <label for="credits" class="col-lg-3 col-form-label font-weight-semibold"
                                >Credits <span class="text-danger">*</span></label>
                            <div class="col-lg-3">
                                <input
                                    name="credits"
                                    id="credits"
                                    min="0"
                                    value="{{ $sub->credits }}"
                                    required
                                    type="number"
                                    class="form-control"
                                    placeholder="E.g., 5"
                                />
                            </div>
                            <div class="col-lg-6 m-lg-auto">
                                <span class="font-weight-bold font-italic text-info-800"
                                    >{{ __('msg.credits_info') }}<i
                                        class="material-symbols-rounded text-info float-right"
                                        data-toggle="tooltip"
                                        title="GPA = Σ (Grade point × Subject credits) / Σ (Subject credits)"
                                        >info</i
                                    ></span>
                            </div>
                        </div>
                        {{-- Core Subject? --}}
                        <div class="form-group row">
                            <label for="is_core" class="col-lg-3 col-form-label font-weight-semibold"
                                >Core Subject?</label>
                            <div class="col-lg-2">
                                <select class="form-control select" name="is_core" id="is_core">
                                    <option @selected($sub->is_core) value="1">Yes</option>
                                    <option @selected(! $sub->is_core) value="0">No</option>
                                </select>
                            </div>
                            <div class="col-lg-7 m-lg-auto">
                                <span class="font-weight-bold font-italic text-info-800">{{ __('msg.core_subject') }}</span>
                            </div>
                        </div>
                        {{-- Submit Button --}}
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
