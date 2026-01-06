@extends('layouts.master')

@section('page_title', 'Edit Role')

@section('content')
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Edit Role {{ $role->name }}</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('roles.update', $role->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold" for="name">Role Name <span class="text-danger">*</span></label>
                <div class="col-lg-5">
                    <input type="text" name="name" class="form-control text-capitalize" id="name" value="{{ old('name', $role->name) }}" required>
                </div>
                <div class="col-lg-4 m-auto">
                    <span class="font-weight-bold font-italic text-info-800">capitalize words separated by single spaces</span>
                </div>
            </div>

            <div class="form-group">
                <label for="permissions">Permissions</label>
                <select name="permissions[]" id="permissions" class="form-control select" multiple data-placeholder="Select permissions">
                    @foreach ($permissions as $permission)
                    <option value="{{ $permission->name }}" @selected($role->permissions->contains($permission))>
                        {{ $permission->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">Update <i class="material-symbols-rounded ml-2">save</i></button>
            </div>
        </form>
    </div>
</div>
@endsection
