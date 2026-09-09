@extends('layouts.master')

@section('page_title', 'View TimeTable')

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="font-weight-bold"><strong>Name: </strong> {{ $ttr->name }}</h6>
            <h6 class="card-title"><strong>Class: </strong> {{ $my_class->name }} {{ $ttr->section->name ?? '' }}</h6>
            <h6 class="card-title">
                <strong>Year: </strong>
                {{ $ttr->exam_id ? 'Exam TimeTable' : 'Class TimeTable' }} {{ "({$ttr->year})" }}
            </h6>

            <div class="header-elements pt-0">
                <div class="list-icons">
                    @can($tenant_permissions['time_tables']['print_record']['permission'])
                        <a
                            class="list-icons-item"
                            target="_blank"
                            href="{{ route('ttr.print', $ttr->id) }}"
                            data-action="print"
                        ></a>
                    @endcan
                    <a class="list-icons-item" data-action="fullscreen"></a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table-responsive table-striped table">
                <thead>
                    <tr>
                        <th rowspan="2">
                            Time <i class="icon-arrow-right7 ml-2"></i> <br />
                            Date<i class="icon-arrow-down7 ml-2"></i>
                        </th>
                        @foreach ($time_slots as $tms)
                            <th rowspan="2">
                                {{ $tms->time_from }}
                                <br />
                                {{ $tms->time_to }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($days as $day)
                        <tr>
                            @if ($ttr->exam_id)
                                <td>
                                    <strong
                                        >{{ date('l', strtotime($day)) }} <br />
                                        {{ date('d/m/Y', strtotime($day)) }}
                                    </strong>
                                </td>
                            @else
                                <td><strong>{{ $day }}</strong></td>
                            @endif

                            @foreach ($d_time->where('day', $day) as $dt)
                                <td>{{ $dt['subject'] }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
