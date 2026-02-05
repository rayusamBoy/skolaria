@extends('layouts.master')

@section('page_title', 'Query Builder')

@section('content')

{{-- Query Print --}}
<div class="card card-collapsed">
    <div class="card-header header-elements-inline">
        <h6 class="card-title"><i class="material-symbols-rounded mr-2">print</i> Query Print</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <form method="POST" target="_blank" action="{{ route('query_builder.print_users_data') }}">
            @csrf
            <div class="row">
                <div class="col-md-3 form-group">
                    <label for="user-roles">Select user role(s) to print</label>
                    <select required multiple name="user_roles[]" id="user-roles" class="form-control select">
                        @foreach ($user_roles as $ur)
                        <option value="{{ $ur->name }}">{{ Str::plural($ur->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label for="data-per-page">Enter a number (3–15)</label>
                    <input required type="number" min="3" max="15" name="per_page" id="data-per-page" class="form-control" placeholder="Eg. 8">
                </div>
                <div class="col-md-2 form-group">
                    <label for="paper-size">Select Paper</label>
                    <select name="paper_size" id="paper-size" class="form-control select select-search">
                        @foreach(Qs::getDompdfSupportedPaperSizes() as $name => $size)
                        <option value="{{ $name }}" @selected($name === 'a4')>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Orientation (portrait default)</label>
                    <div class="form-check">
                        <label class="form-check-label">
                            Landscape
                            <input type="checkbox" name="paper_orientation" value="landscape" class="form-input-styled" data-fouc>
                        </label>
                    </div>
                </div>
                <div class="col-md-2 form-group text-right">
                    <button type="submit" class="btn btn-primary">Print <i class="material-symbols-rounded ml-1">print</i></button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Query Select --}}
<div class="card card-collapsed" id="query-box">
    <div class="card-header header-elements-inline">
        <h6 class="card-title"><i class="material-symbols-rounded mr-2">lasso_select</i> Query Select</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <form method="get" action="{{ route('query_builder.select') }}">
            @csrf

            {{-- First row --}}
            <div class="row">
                <div class="col-md-1 form-group">
                    <input type="text" disabled class="form-control" value="select">
                    <input type="hidden" name="type" value="select">
                </div>

                <div class="col-md-2 form-group">
                    <select name="select[]" id="select" multiple required class="form-control select"></select>
                </div>

                <div class="col-md-2 form-group">
                    <select name="from" id="from" required onchange="getTableColumns(this.value)" class="form-control select">
                        <option disabled selected>from *</option>
                        @foreach(Qs::getTableNames() as $table => $label)
                        <option value="{{ $table }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 form-group">
                    <select name="where" required class="form-control select where" onchange="toggleElDisableState(this.value,'#condition,#input')">
                        <option disabled selected>where</option>
                    </select>
                </div>

                <div class="col-md-2 form-group">
                    <select id="condition" name="condition" disabled required class="form-control select">
                        <option disabled selected>condition</option>
                        <option value="=">equals</option>
                        <option value="<>">not equal</option>
                        <option value=">">greater than</option>
                        <option value="<">less than</option>
                        <option value="like">like</option>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <input type="text" name="input" id="input" disabled class="form-control" placeholder="input">
                </div>
            </div>

            {{-- Second row --}}
            <div class="row">
                <div class="col-md-1 text-center form-group">
                    <input type="text" disabled class="form-control" value="AND">
                </div>
                <div class="col-md-2 form-group">
                    <select name="where_two" required class="form-control select where" onchange="toggleElDisableState(this.value,'#condition_two,#input_two')">
                        <option disabled selected>where</option>
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <select id="condition_two" name="condition_two" disabled required class="form-control select">
                        <option disabled selected>condition</option>
                        <option value="=">equals</option>
                        <option value="<>">not equal</option>
                        <option value=">">greater than</option>
                        <option value="<">less than</option>
                        <option value="like">like</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <input type="text" name="input_two" id="input_two" disabled class="form-control" placeholder="input">
                </div>
                <div class="col-md-3 form-group">
                    <div class="input-group">
                        <label class="input-group-text">Limit/Take</label>
                        <input type="number" name="limit" id="limit" class="form-control" placeholder="All">
                    </div>
                </div>
            </div>

            {{-- Third row --}}
            <div class="row">
                <div class="col-md-6 form-group">
                    <div class="row">
                        <div class="col-2">
                            <label for="orderby_column" class="input-group-text">Order by</label>
                        </div>
                        <div class="col-6">
                            <select name="orderby_column" class="form-control select orderby_column" id="orderby_column">
                                <option>default</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <select name="orderby_direction" class="form-control select orderby_direction">
                                <option value="asc" selected>ascending</option>
                                <option value="desc">descending</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 offset-md-5 text-right form-group">
                    <button type="submit" class="btn btn-primary" @disabled(true)>Go <i class="material-symbols-rounded ml-2">send</i></button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Query Results --}}
@if($queryed)
<div class="card w-fit wmin-100-pcnt">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Query Results</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="alert alert-info border-0 alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Query —</strong> <span class="text-muted">{{ $query }}</span>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item">
                <a href="#results" class="nav-link active" data-toggle="tab">Results</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="results">
                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            @foreach($placeholder as $ph)
                            <th>{{ ucwords(str_replace('_',' ',$ph)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                        <tr>
                            @foreach(explode(',',$select) as $s)
                            @php
                            $field = trim(collect(explode(' ',$s))->last(), '[]"' );
                            @endphp

                            @if($field==='photo')
                            <td><img src="{{ asset($record->$field) }}" width="60" height="60"></td>
                            @else
                            <td>{{ Str::of($record->$field)->replace('"','') ?? '-' }}</td>
                            @endif
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Inline JS for select validation --}}
<script>
    $("select#select").on("change", function() {
        let vals = $(this).val() || [];
        let btn = $(this).closest('form').find("button[type=submit]");
        if (vals.includes('*') && vals.length > 1) {
            $(this).siblings('.select2').find('.select2-selection').addClass('border-danger');
            btn.prop('disabled', true);
            return alert('Cannot combine default (*) with specific columns.');
        }
        $(this).siblings('.select2').find('.select2-selection').removeClass('border-danger');
        btn.prop('disabled', false);
    });

</script>

@endsection
