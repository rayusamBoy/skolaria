@extends('layouts.master')

@section('page_title', 'Edit Permission')

@section('content')
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Edit Permission</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('permissions.update', $permission->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group row">
                <label class="col-lg-3 col-form-label font-weight-semibold" for="name">Permission Name <span class="text-danger">*</span></label>
                <div class="col-lg-5">
                    <input type="text" name="name" class="form-control text-lowercase" id="name" value="{{ old('name', $permission->name) }}" required>
                </div>
                <div class="col-lg-4 m-auto">
                    <span class="font-weight-bold font-italic text-info-800">lowercase words separated by single underscores</span>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">Update <i class="material-symbols-rounded ml-2">save</i></button>
            </div>
        </form>
    </div>
</div>
@endsection
