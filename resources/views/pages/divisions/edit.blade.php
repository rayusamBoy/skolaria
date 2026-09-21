@extends('layouts.master')

@section('page_title', "Edit Division - {$dv->name}".' ('.($dv->class_type?->name ?? 'Not Applicable').')')

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Edit Division</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <form
                        method="post"
                        action="{{ route('divisions.update', $dv->id) }}"
                        class="ajax-update"
                        data-reload="#page-title"
                    >
                        @csrf
                        @method('PUT')
                        {{-- Name --}}
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input
                                    name="name"
                                    value="{{ $dv->name }}"
                                    required
                                    type="text"
                                    class="form-control"
                                    placeholder="Eg. C4"
                                />
                            </div>
                        </div>
                        {{-- Class Type --}}
                        <div class="form-group row">
                            <label for="class_type_id" class="col-lg-3 col-form-label font-weight-semibold"
                                >Class Type</label>
                            <div class="col-lg-9">
                                <select class="form-control select" disabled id="class_type_id">
                                    <option selected>{{ $dv->class_type?->name ?? 'Not Applicable' }}</option>
                                </select>
                                <input type="hidden" name="class_type_id" value="{{ $dv->class_type?->id }}">
                            </div>
                        </div>
                        {{-- Points From --}}
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Points From <span class="text-danger">*</span></label>
                            <div class="col-lg-3">
                                <input
                                    name="points_from"
                                    min="0"
                                    max="100"
                                    value="{{ $dv->points_from }}"
                                    required
                                    type="number"
                                    class="form-control"
                                    placeholder="0"
                                />
                            </div>
                        </div>
                        {{-- Points To --}}
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Points To <span class="text-danger">*</span></label>
                            <div class="col-lg-3">
                                <input
                                    name="points_to"
                                    min="0"
                                    max="100"
                                    value="{{ $dv->points_to }}"
                                    required
                                    type="number"
                                    class="form-control"
                                    placeholder="0"
                                />
                            </div>
                        </div>
                        {{-- Remarks --}}
                        <div class="form-group row">
                            <label for="remarks" class="col-lg-3 col-form-label font-weight-semibold">Remarks</label>
                            <div class="col-lg-9">
                                <select
                                    class="form-control select"
                                    data-placeholder="Select Remark"
                                    name="remarks"
                                    id="remarks"
                                >
                                    <option value="">Select Remark</option>
                                    @foreach (Mk::getRemarks() as $rem)
                                        <option @selected($dv->remarks == $rem) value="{{ $rem }}">{{ $rem }}</option>
                                    @endforeach
                                </select>
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

@endsection
