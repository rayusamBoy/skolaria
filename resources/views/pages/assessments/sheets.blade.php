@extends('layouts.master')

@section('page_title', 'Select Student Assessmentsheet')

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="material-symbols-rounded">two_pager</i> Select Student Assessmentsheet</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('assessments.sheets_select') }}">
                @csrf
                <div class="row">
                    <div class="col-md-10">
                        <fieldset>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="my_class_id" class="col-form-label font-weight-bold">Class:</label>
                                        <select
                                            required
                                            onchange="getClassSections(this.value, '#section_id')"
                                            id="my_class_id"
                                            name="my_class_id"
                                            class="form-control select"
                                        >
                                            <option value="">Select Class</option>
                                            @foreach ($my_classes as $c)
                                                <option
                                                    @selected($selected && $my_class_id == $c->id)
                                                    value="{{ $c->id }}"
                                                >
                                                    {{ $c->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
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
                                                @foreach ($sections as $s)
                                                    <option @selected($section_id == $s->id) value="{{ $s->id }}">
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
                                View Assessmentsheet <i class="material-symbols-rounded ml-2">send</i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($selected)
        <div class="card">
            <div class="card-body">
                <table class="datatable-button-html5-columns table">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>ADM No</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Exclude soft deleted student --}}
                        @foreach ($students->whereNotNull('user') as $s)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <img
                                        class="rounded-circle"
                                        style="height: 40px; width: 40px"
                                        src="{{ Usr::getTenancyAwarePhotoUrl($s->user->photo_path) }}"
                                        alt="photo"
                                    />
                                </td>
                                <td>{{ $s->user->name }}</td>
                                <td>{{ $s->user->gender }}</td>
                                <td>{{ $s->admission_number }}</td>
                                <td>
                                    <a
                                        class="btn btn-link"
                                        href="{{ route('assessments.year_selector', Qs::hash($s->user_id)) }}"
                                    >View Assessmentsheet</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection
