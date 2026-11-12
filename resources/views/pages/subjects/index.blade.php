@extends('layouts.master')

@section('page_title', 'Manage Subjects')

@section('content')
    @php
        $canEdit = auth()->user()->can('subjects.edit');
        $canDelete = auth()->user()->can('subjects.destroy');
        $hasActions = $canEdit || $canDelete;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Subjects</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                {{-- Manage Subjects --}}
                <li class="nav-item dropdown">
                    <a href="javascript:;" class="nav-link active dropdown-toggle" data-toggle="dropdown"
                        >Manage Subjects</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach ($my_classes as $c)
                            <a href="#c{{ $c->id }}" class="dropdown-item" data-toggle="tab">{{ $c->name }}</a>
                        @endforeach
                    </div>
                </li>
                {{-- Create Subject --}}
                @can('subjects.store')
                    <li class="nav-item"><a href="#new-subject" class="nav-link" data-toggle="tab">Add Subject</a></li>
                @endcan
            </ul>

            {{-- Create Subject Tab --}}
            <div class="tab-content">
                @can('subjects.store')
                    <div class="tab-pane fade" id="new-subject">
                        <div class="row">
                            <div class="col-md-8">
                                <form class="ajax-store" method="post" action="{{ route('subjects.store') }}">
                                    @csrf
                                    {{-- Class --}}
                                    <div class="form-group row">
                                        <label for="my_class_id" class="col-lg-3 col-form-label font-weight-semibold"
                                            >Select Class(es) <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <select
                                                required
                                                data-placeholder="Select Class"
                                                class="form-control select"
                                                multiple="multiple"
                                                name="my_class_ids[]"
                                                id="my_class_id"
                                            >
                                                <option value=""></option>
                                                @foreach ($my_classes as $c)
                                                    <option
                                                        data-class_type_id="{{ $c->class_type_id }}"
                                                        @selected(old('my_class_id') == $c->id)
                                                        value="{{ $c->id }}"
                                                    >
                                                        {{ $c->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Name --}}
                                    <div class="form-group row">
                                        <label for="name" class="col-lg-3 col-form-label font-weight-semibold"
                                            >Name <span class="text-danger">*</span>
                                            <i
                                                class="material-symbols-rounded text-info float-right"
                                                data-toggle="tooltip"
                                                title="Choose option, search option, or type to add option"
                                            >info</i>
                                        </label>
                                        <div class="col-lg-9">
                                            <select
                                                required
                                                data-placeholder="Select class first"
                                                class="form-control select-tags"
                                                name="name"
                                                id="name"
                                            >
                                                @foreach (Usr::getPredefinedSubjects() as $subject)
                                                    <option value="{{ $subject }}">{{ $subject }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Slug --}}
                                    <div class="form-group row">
                                        <label for="slug" class="col-lg-3 col-form-label font-weight-semibold"
                                            >Short Name <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <input
                                                id="slug"
                                                required
                                                name="slug"
                                                value="{{ old('slug') }}"
                                                type="text"
                                                class="form-control"
                                                placeholder="Subject short name"
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
                                                required
                                                type="number"
                                                class="form-control"
                                                placeholder="E.g., 10"
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
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
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
                @endcan

                @foreach ($my_classes as $c)
                    <div class="tab-pane fade show" id="c{{ $c->id }}">
                        <table class="datatable-button-html5-columns table">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Name</th>
                                    <th>Short Name</th>
                                    <th>Is Core?</th>
                                    <th>Credits</th>
                                    <th>Class</th>
                                    @if ($hasActions)
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subjects->where('my_class_id', $c->id) as $s)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $s->name }}</td>
                                        <td>{{ $s->slug }}</td>
                                        <td>{{ $s->is_core ? 'Yes' : 'No' }}</td>
                                        <td>{{ $s->credits }}</td>
                                        <td>{{ $s->my_class->name }}</td>
                                        @if ($hasActions)
                                            <td class="text-center">
                                                <div class="list-icons">
                                                    <div class="dropdown">
                                                        <a
                                                            class="material-symbols-rounded"
                                                            href="javascript:;"
                                                            data-toggle="dropdown"
                                                        >lists</a>
                                                        <div class="dropdown-menu dropdown-menu-left">
                                                            @if ($canEdit)
                                                                <a
                                                                    href="{{ route('subjects.edit', $s->id) }}"
                                                                    class="dropdown-item"
                                                                >
                                                                    <i class="material-symbols-rounded">edit</i> Edit
                                                                </a>
                                                            @endif
                                                            @if ($canDelete)
                                                                @php $inUse = $s->record->isNotEmpty(); @endphp
                                                                <a
                                                                    id="{{ $s->id }}"
                                                                    @if ($inUse)
                                                                        class="dropdown-item text-danger disabled"
                                                                        data-toggle="tooltip"
                                                                        title="{{ __('msg.subject_in_use') }}"
                                                                    @else
                                                                        class="dropdown-item text-danger"
                                                                        onclick="confirmDelete(this.id)"
                                                                    @endif
                                                                    href="javascript:;"
                                                                >
                                                                    <i class="material-symbols-rounded">delete</i>
                                                                    Delete
                                                                </a>
                                                                @if (! $inUse)
                                                                    <form
                                                                        method="post"
                                                                        id="item-delete-{{ $s->id }}"
                                                                        action="{{ route('subjects.destroy', $s->id) }}"
                                                                        class="hidden"
                                                                    >
                                                                        @csrf
                                                                        @method('delete')
                                                                    </form>
                                                                @endif
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
                @endforeach
            </div>
        </div>
    </div>

@endsection
