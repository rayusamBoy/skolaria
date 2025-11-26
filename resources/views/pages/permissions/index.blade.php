@extends('layouts.master')

@section('page_title', 'Manage Permissions')

@section('content')
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Manage Permissions</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item"><a href="#create-permission" class="nav-link active" data-toggle="tab">Create Permission</a></li>
            <li class="nav-item"><a href="#manage-permissions" class="nav-link" data-toggle="tab">Manage Permissions</a></li>
        </ul>

        <div class="tab-content">
            {{-- Create Permission Tab --}}
            <div class="tab-pane fade show active" id="create-permission">
                <form method="POST" action="{{ route('permissions.store') }}" class="ajax-store">
                    @csrf
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label font-weight-semibold" for="name">Permission Name <span class="text-danger">*</span></label>
                        <div class="col-lg-5">
                            <input type="text" name="name" class="form-control text-lowercase" id="name" placeholder="e.g., view_students" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-lg-4 m-auto">
                            <span class="font-weight-bold font-italic text-info-800">lowercase words separated by single underscores</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Submit <i class="material-symbols-rounded ml-2">send</i></button>
                    </div>
                </form>
            </div>

            {{-- Manage Permissions Tab --}}
            <div class="tab-pane fade" id="manage-permissions">
                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ ucwords(str_replace("_", " ", $permission->name)) }}</td>
                            <td>{{ $permission->created_at->diffForHumans() }}</td>
                            <td>{{ $permission->updated_at->diffForHumans() }}</td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="material-symbols-rounded" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            <a href="{{ route('permissions.edit', $permission->id) }}" class="dropdown-item"><i class="material-symbols-rounded">edit</i> Edit</a>

                                            <a href="javascript:;" id="{{ $permission->id }}" class="dropdown-item text-danger" onclick="confirmDelete(this.id)">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form method="POST" action="{{ route('permissions.destroy', $permission->id) }}" id="item-delete-{{ $permission->id }}" class="d-none">
                                                @csrf @method('DELETE')
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
