@extends('layouts.master')

@section('page_title', 'Assign Exam Numbers')

@section('content')
    @php
        $canAssign = auth()->user()->can($tenant_permissions['subjects']['update_record']['permission']);
        $canGenerate = auth()->user()->can($tenant_permissions['exams']['store']['permission']);
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Assign Exam Numbers ({{ $year }})</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="exam_numbers_exam_id">Exam <span class="text-danger">*</span></label>
                        <select required class="form-control select" id="exam_numbers_exam_id" name="exam_id">
                            <option value="">Select Exam</option>
                            @foreach ($exams as $ex)
                                <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="exam_numbers_class_id">Class <span class="text-danger">*</span></label>
                        <select
                            required
                            class="form-control select"
                            id="exam_numbers_class_id"
                            name="my_class_id"
                            onchange="getClassSections(this.value, '#exam_numbers_section_id_add_not_applicable')"
                        >
                            <option value="">Select Class</option>
                            @foreach ($my_classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="exam_numbers_section_id_add_not_applicable">Section (optional)</label>
                        <select
                            class="form-control select"
                            id="exam_numbers_section_id_add_not_applicable"
                            name="section_id"
                            data-placeholder="Not Applicable"
                        >
                            <option value="">Not Applicable</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button type="button" id="load-exam-numbers-btn" class="btn btn-primary">
                    <i class="material-symbols-rounded mr-1">refresh</i> Load Students
                </button>
                <button type="button" id="generate-exam-numbers-btn" class="btn btn-success" disabled>
                    <i class="material-symbols-rounded mr-1">autorenew</i> Generate Numbers
                </button>
                <button type="button" id="save-exam-numbers-btn" class="btn btn-info" disabled>
                    <i class="material-symbols-rounded mr-1">save</i> Save Changes
                </button>
            </div>

            <div id="exam-numbers-container">
                <div class="alert alert-info text-center">
                    Select an exam and a class, then click <strong>Load Students</strong>.
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(function () {
            $('#load-assignments-btn').on('click', function () {
                var classId = $('#assign_class_id').val();
                var sectionId = $('#assign_section_id').val();

                if (!classId) {
                    toastrMessage(null, { type: 'warning', msg: 'Please select a class.' });
                    return;
                }

                var btn = $(this);
                var original = btn.html();
                btn.html('Loading...').prop('disabled', true);
            });

            $('#load-exam-numbers-btn').on('click', function () {
                var examId = $('#exam_numbers_exam_id').val();
                var classId = $('#exam_numbers_class_id').val();
                var sectionId = $('#exam_numbers_section_id').val();

                if (!examId || !classId) {
                    flash({ msg: 'Please select an exam and a class.', type: 'warning' });
                    return;
                }

                var btn = $(this);
                var original = btn.html();
                btn.html('Loading...').prop('disabled', true);

                $.get('{{ route("exams.exam_numbers.load") }}', {
                    exam_id: examId,
                    class_id: classId,
                    section_id: sectionId,
                })
                    .done(function (html) {
                        $('#exam-numbers-container').html(html);
                        $('#generate-exam-numbers-btn, #save-exam-numbers-btn').prop('disabled', false);
                    })
                    .fail(function () {
                        $('#exam-numbers-container').html(
                            '<div class="alert alert-danger">Failed to load students.</div>',
                        );
                    })
                    .always(function () {
                        btn.html(original).prop('disabled', false);
                    });
            });

            // Generate numbers via the existing exam controller endpoint
            $('#generate-exam-numbers-btn').on('click', function () {
                Modal.fire({
                    title: 'Do you want to proceed?',
                    icon: 'warning',
                    confirmButtonText: 'Sure, Proceed',
                    customClass: { confirmButton: 'bg-danger' },
                }).then((result) => {
                    if (result.isConfirmed) {
                        var btn = $(this);
                        var original = btn.html();
                        btn.html('Generating...').prop('disabled', true);

                        $.post('{{ route("exams.generate_numbers") }}', {
                            exam_id: $('#exam_numbers_exam_id').val(),
                            my_class_id: $('#exam_numbers_class_id').val(),
                            section_id: $('#exam_numbers_section_id').val(),
                        })
                            .done(function (resp) {
                                flash({ msg: resp.msg, type: 'success' });
                                if (resp.ok) {
                                    $('#load-exam-numbers-btn').click();
                                }
                            })
                            .fail(function () {
                                flash({ msg: 'Failed to generate numbers.', type: 'error' });
                            })
                            .always(function () {
                                btn.html(original).prop('disabled', false);
                            });
                    }
                });
            });

            // Save manually edited numbers
            $('#save-exam-numbers-btn').on('click', function () {
                var numbers = {};
                $('.exam-number-input').each(function () {
                    numbers[$(this).data('student-id')] = $(this).val();
                });

                var btn = $(this);
                var original = btn.html();
                btn.html('Saving...').prop('disabled', true);

                $.post('{{ route("exams.exam_numbers.save") }}', {
                    exam_id: $('#exam_numbers_exam_id').val(),
                    class_id: $('#exam_numbers_class_id').val(),
                    numbers: numbers,
                })
                    .done(function (resp) {
                        flash({ msg: resp.msg, type: 'success' });
                    })
                    .fail(function () {
                        flash({ msg: 'Failed to save numbers.', type: 'error' });
                    })
                    .always(function () {
                        btn.html(original).prop('disabled', false);
                    });
            });
        });
    </script>
@endpush
