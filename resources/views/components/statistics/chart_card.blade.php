@props([
'id',
'title' => '',
'labels' => [],
'data' => [],
'label' => 'Value',
'xAxisTitle' => '',
'yAxisTitle' => '',
'collapsed' => true,
])

<div class="row">
    <div class="col-12">
        <div class="card print-this {{ $collapsed ? 'card-collapsed' : '' }}">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">{{ $title }}</h6>
                <div class="header-elements pt-0 print-none">
                    <div class="list-icons">
                        <a class="list-icons-item print-this-btn" href="javascript:;" data-action="print"></a>
                        <a class="list-icons-item" data-action="collapse"></a>
                        <a class="list-icons-item" data-action="remove"></a>
                    </div>
                </div>
            </div>
            <div class="py-2 px-md-5 px-4 position-relative card-body">
                {{ $nav ?? '' }}
                <canvas id="{{ $id }}" data-chart data-title="{{ $title }}" data-labels="{{ json_encode($labels) }}" data-data="{{ json_encode($data) }}" data-label="{{ $label }}" data-x-axis-title="{{ $xAxisTitle }}" data-y-axis-title="{{ $yAxisTitle }}" role="img" aria-label="{{ $title }}">
                    <p>Your browser does not support the canvas element.</p>
                </canvas>
            </div>
        </div>
    </div>
</div>
