@extends('layouts.master')

@section('page_title', "Edit Class Type - {$class_type->name}")

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Edit Class Type</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <form
                        class="ajax-update"
                        method="post"
                        action="{{ route('class_types.update', $class_type->id) }}"
                        data-reload="#page-title"
                    >
                        @csrf
                        @method('PUT')
                        {{-- Name --}}
                        <div class="form-group row">
                            <label class="col-lg-6 col-form-label font-weight-semibold">
                                Name <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-6">
                                <input
                                    name="name"
                                    value="{{ $class_type->name }}"
                                    required
                                    type="text"
                                    class="form-control"
                                    placeholder="Name of Class Type"
                                />
                            </div>
                        </div>
                        {{-- Code --}}
                        <div class="form-group row">
                            <label class="col-lg-6 col-form-label font-weight-semibold">
                                Code <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-6">
                                <input
                                    name="code"
                                    value="{{ $class_type->code }}"
                                    required
                                    type="text"
                                    class="form-control"
                                    placeholder="Code of Class Type"
                                />
                            </div>
                        </div>
                        {{-- Minimum Subjects Required --}}
                        <div class="form-group row">
                            <label class="col-lg-6 col-form-label font-weight-semibold">
                                Minimum Subjects Required <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-6">
                                <input
                                    type="number"
                                    name="minimum_subjects_required"
                                    min="0"
                                    value="{{ $class_type->minimum_subjects_required }}"
                                    required
                                    class="form-control"
                                />
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

    {{--Section Edit Ends--}}

@endsection
