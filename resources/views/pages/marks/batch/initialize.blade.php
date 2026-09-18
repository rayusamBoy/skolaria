<div class="card card-collapsed">
    <div class="card-header header-elements-inline">
        <h6 class="card-title"><i class="material-symbols-rounded mr-2">playlist_add</i> Initialize Marks</h6>
        {!! Qs::getPanelOptionsHtml() !!}
    </div>

    <div class="row">
        <div class="col-md-12">
            <div
                class="alert alert-warning alert-dismissible has-do-not-show-again-button border-0"
                id="advance-level-info"
            >
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <span>
                    <strong>Initialize Marks</strong> creates an empty mark, exam-record, and (where applicable)
                    assessment row for every student in the selected class and exam — one per subject — so that marks
                    can be typed in manually or uploaded in bulk from the batch template. This step is
                    <strong>idempotent</strong>: running it again only adds whatever is missing and never overwrites
                    existing marks, so it is safe to run at any time.
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div
                class="alert alert-info alert-dismissible has-do-not-show-again-button border-0"
                id="section-change-info"
            >
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <span>
                    If a student has moved to a different section since their records were first created, stale rows may
                    still exist under the old section. Choose <strong>Yes</strong> under
                    <em>Delete old student section record</em> to remove those stale rows for every student whose
                    section has changed. Choose <strong>No</strong> to only top up missing rows and leave everything
                    else untouched.
                </span>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form class="ajax-update ajax-not-global" method="post" action="{{ route('marks.batch_initialize') }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-9">
                    <fieldset>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="exam_id" class="col-form-label font-weight-bold"
                                        >Exam: <span class="text-danger">*</span></label>
                                    <select
                                        required
                                        id="exam_id"
                                        name="exam_id"
                                        data-placeholder="Select Exam"
                                        class="form-control select-search"
                                    >
                                        <option selected disabled value="">Select exam</option>
                                        @foreach ($exams as $ex)
                                            <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="my_class_id" class="col-form-label font-weight-bold"
                                        >Class: <span class="text-danger">*</span></label>
                                    <select
                                        required
                                        data-placeholder="Select Class"
                                        id="my_class_id"
                                        name="my_class_id"
                                        class="form-control select"
                                    >
                                        <option selected disabled value="">Select Class</option>
                                        @foreach ($my_classes as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label
                                    for="delete-old-section-exam-recs"
                                    class="col-form-label font-weight-semibold w-100"
                                    >Delete old student section record
                                    <i
                                        class="material-symbols-rounded text-info float-right"
                                        data-toggle="tooltip"
                                        title="Only affects students whose section has changed."
                                    >info</i>
                                </label>
                                <div class="form-group text-center">
                                    <select
                                        class="form-control select"
                                        name="delete_old_section_record"
                                        id="delete-old-section-exam-recs"
                                    >
                                        <option value="1">Yes</option>
                                        <option selected value="0">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="col-md-3 mt-4">
                    <div class="mt-1 text-right">
                        <button type="submit" class="btn btn-sm btn-primary" data-text="Initializing...">
                            Initialize Marks <i class="material-symbols-rounded ml-2">check_circle</i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
