@extends('layouts.master')

@section('page_title', 'Student Payments')

@section('content')
    @php
        $canViewProfile = auth()->user()->can($tenant_permissions['student_records']['show']['permission']);
        $canManagePayments = auth()->user()->can($tenant_permissions['payments']['invoice']['permission']);
        $hasActions = $canViewProfile || $canManagePayments;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="material-symbols-rounded mr-2">payments</i> Student Payments</h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            @can($tenant_permissions['payments']['select_class']['permission'])
                <form method="post" action="{{ route('payments.select_class') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 offset-md-3">
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <label for="my_class_id" class="col-form-label font-weight-bold">Class:</label>
                                        <select
                                            required
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

                                <div class="col-md-2 mt-4">
                                    <div class="mt-1 text-right">
                                        <button type="submit" class="btn btn-primary">
                                            Submit <i class="material-symbols-rounded ml-2">send</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @else
                <p class="text-muted text-center">You do not have permission to select a class.</p>
            @endcan
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
                            <th>ADM No</th>
                            @if ($hasActions)
                                <th class="text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $s)
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
                                <td>{{ $s->admission_number }}</td>
                                @if ($hasActions)
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <a href="javascript:;" data-toggle="dropdown">
                                                <i class="material-symbols-rounded">lists</i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-left">
                                                @if ($canViewProfile)
                                                    <a
                                                        href="{{ route('students.show', Qs::hash($s->id)) }}"
                                                        class="dropdown-item"
                                                    >
                                                        <i class="material-symbols-rounded">visibility</i>View Profile
                                                    </a>
                                                    <hr class="m-0" />
                                                @endif

                                                @if ($canManagePayments)
                                                    <span class="dropdown-item disabled opacity-75">Manage Payments</span>
                                                    <a
                                                        href="{{ route('payments.invoice', [Qs::hash($s->user_id)]) }}"
                                                        class="dropdown-item"
                                                    >All Payments</a>
                                                    @foreach (Pay::getDistinctPaymentYears($s->user_id) as $py)
                                                        @if ($py)
                                                            <a
                                                                href="{{ route('payments.invoice', [Qs::hash($s->user_id), $py]) }}"
                                                                class="dropdown-item"
                                                            >
                                                                {{ $py }}
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection
