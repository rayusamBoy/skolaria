<form method="post" action="{{ route('assessments.selector') }}">
    @csrf
    <div class="row">
        <div class="col-md-10">
            <fieldset>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="assessment_id" class="col-form-label font-weight-bold">Assessments for:</label>
                            <select
                                required
                                id="assessment_id"
                                name="assessment_id"
                                data-placeholder="Select Assessment"
                                class="form-control select"
                            >
                                <option value="">Select Assessment</option>
                                @foreach ($assessments as $as)
                                    <option @selected($selected && $exam->id == $as->exam->id) value="{{ $as->id }}">
                                        {{ $as->exam->name }} assessment
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="my_class_id" class="col-form-label font-weight-bold">Class:</label>
                            <select
                                required
                                data-placeholder="Select Class"
                                onchange="
                                    getClassSubjects(this.value);
                                    getClassSections(this.value);
                                "
                                id="my_class_id"
                                name="my_class_id"
                                class="form-control select"
                            >
                                <option value="">Select Class</option>
                                @foreach ($my_classes as $c)
                                    <option @selected($selected && $my_class_id == $c->id) value="{{ $c->id }}">
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="section_id" class="col-form-label font-weight-bold">Section:</label>
                            <select
                                required
                                id="section_id"
                                name="section_id"
                                data-placeholder="Select Class First"
                                class="form-control select"
                            >
                                @if ($selected)
                                    @if (isset($section_id))
                                        @foreach ($sections->where('my_class_id', $my_class_id) as $s)
                                            <option @selected($section_id == $s->id) value="{{ $s->id }}">
                                                {{ $s->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="subject_id" class="col-form-label font-weight-bold">Subject:</label>
                            <select
                                required
                                id="subject_id"
                                name="subject_id"
                                data-placeholder="Select Class First"
                                class="form-control select-search"
                            >
                                @if ($selected)
                                    @foreach ($subjects as $s)
                                        <option @selected($subject_id == $s->id) value="{{ $s->id }}">
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>

        <div class="col-md-2 mt-4">
            <div class="mt-1 text-right">
                <button type="submit" class="btn btn-primary">
                    Manage <i class="material-symbols-rounded ml-2">send</i>
                </button>
            </div>
        </div>
    </div>
</form>
