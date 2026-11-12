@extends('layouts.master')

@section('page_title', 'Manage Payments')

@section('content')
    @php
        $canReset = auth()->user()->can('payments.reset_record');
        $canReceipt = auth()->user()->can('payments.receipts');
        $hasActions = $canReset || $canReceipt;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">
                Manage Payment Records for {{ $sr->user->name }} ({{ $year ?? "All" }})
            </h6>
            {!! Qs::getPanelOptionsHtml() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item">
                    <a href="#all-uc" class="nav-link active" data-toggle="tab">Incomplete Payments</a>
                </li>
                <li class="nav-item"><a href="#all-cl" class="nav-link" data-toggle="tab">Completed Payments</a></li>
            </ul>

            <div class="tab-content">
                {{-- Incomplete Payments --}}
                <div class="tab-pane fade show active" id="all-uc">
                    <table class="datatable-button-html5-columns table-responsive table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Pay Ref</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Pay Now</th>
                                <th>Receipt No</th>
                                <th>Year</th>
                                @if ($hasActions)
                                    <th class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($uncleared as $uc)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $uc->payment->title }}</td>
                                    <td>{{ $uc->payment->reference_number }}</td>

                                    {{--Amount--}}
                                    <td
                                        class="font-weight-bold"
                                        id="amt-{{ Qs::hash($uc->id) }}"
                                        data-amount="{{ $uc->payment->amount }}"
                                    >
                                        {{ $uc->payment->amount }}
                                    </td>
                                    {{--Amount Paid--}}
                                    <td
                                        id="amount_paid-{{ Qs::hash($uc->id) }}"
                                        data-amount="{{ $uc->amount_paid ?: 0 }}"
                                        class="text-blue font-weight-bold"
                                    >
                                        {{ $uc->amount_paid ?: '0.00' }}
                                    </td>
                                    {{--Balance--}}
                                    <td id="bal-{{ Qs::hash($uc->id) }}" class="text-danger font-weight-bold">
                                        {{ $uc->balance ?: $uc->payment->amount }}
                                    </td>
                                    {{--Pay Now Form--}}
                                    <td>
                                        <form
                                            id="{{ Qs::hash($uc->id) }}"
                                            method="post"
                                            class="ajax-pay"
                                            action="{{ route('payments.pay_now', Qs::hash($uc->id)) }}"
                                        >
                                            @csrf
                                            <div class="row">
                                                <div class="input-group col-12">
                                                    <input
                                                        min="1"
                                                        max="{{ $uc->balance ?: $uc->payment->amount }}"
                                                        id="val-{{ Qs::hash($uc->id) }}"
                                                        class="form-control form-control-sm w-6rem"
                                                        required
                                                        placeholder="Pay Now"
                                                        title="Pay Now"
                                                        name="amount_paid"
                                                        type="number"
                                                    />
                                                    <button
                                                        class="btn input-group-text border-0"
                                                        data-text=" "
                                                        type="submit"
                                                    >
                                                        Pay
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                    {{--Receipt No--}}
                                    <td>{{ $uc->reference_number }}</td>
                                    {{-- Year --}}
                                    <td>{{ $uc->year }}</td>
                                    {{--Action--}}
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
                                                        @if ($canReset)
                                                            <a
                                                                id="{{ Qs::hash($uc->id) }}"
                                                                onclick="confirmResetTwice(this.id)"
                                                                href="javascript:;"
                                                                class="dropdown-item"
                                                                ><i class="material-symbols-rounded">restart_alt</i>
                                                                Reset Payment</a>
                                                            <form
                                                                method="post"
                                                                id="item-reset-{{ Qs::hash($uc->id) }}"
                                                                action="{{ route('payments.reset_record', Qs::hash($uc->id)) }}"
                                                                class="hidden"
                                                            >
                                                                @csrf
                                                                @method('delete')
                                                            </form>
                                                        @endif
                                                        @if ($canReceipt)
                                                            <a
                                                                target="_blank"
                                                                href="{{ route('payments.receipts', Qs::hash($uc->id)) }}"
                                                                class="dropdown-item"
                                                                ><i class="material-symbols-rounded">print</i> Print
                                                                Receipt</a>
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

                {{-- Completed Payments --}}
                <div class="tab-pane fade" id="all-cl">
                    <table class="datatable-button-html5-columns table-responsive table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Pay Ref</th>
                                <th>Amount</th>
                                <th>Receipt No</th>
                                <th>Year</th>
                                @if ($hasActions)
                                    <th class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cleared as $cl)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $cl->payment->title }}</td>
                                    <td>{{ $cl->payment->reference_number }}</td>
                                    {{--Amount--}}
                                    <td class="font-weight-bold">{{ $cl->payment->amount }}</td>
                                    {{--Receipt No--}}
                                    <td>{{ $cl->reference_number }}</td>
                                    {{-- Year --}}
                                    <td>{{ $cl->year }}</td>
                                    {{--Action--}}
                                    @if ($hasActions)
                                        <td class="text-center">
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a
                                                        href="javascript:;"
                                                        class="material-symbols-rounded"
                                                        data-toggle="dropdown"
                                                    >lists</a>

                                                    <div class="dropdown-menu dropdown-menu-left">
                                                        @if ($canReset)
                                                            <a
                                                                id="{{ Qs::hash($cl->id) }}"
                                                                onclick="confirmResetTwice(this.id)"
                                                                href="javascript:;"
                                                                class="dropdown-item"
                                                                ><i class="material-symbols-rounded">restart_alt</i>
                                                                Reset Payment</a>
                                                            <form
                                                                method="post"
                                                                id="item-reset-{{ Qs::hash($cl->id) }}"
                                                                action="{{ route('payments.reset_record', Qs::hash($cl->id)) }}"
                                                                class="hidden"
                                                            >
                                                                @csrf
                                                                @method('delete')
                                                            </form>
                                                        @endif
                                                        @if ($canReceipt)
                                                            <a
                                                                target="_blank"
                                                                href="{{ route('payments.receipts', Qs::hash($cl->id)) }}"
                                                                class="dropdown-item"
                                                                ><i class="material-symbols-rounded">print</i> Print
                                                                Receipt</a>
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
            </div>
        </div>
    </div>

    {{--Payments Invoice List Ends--}}

@endsection
