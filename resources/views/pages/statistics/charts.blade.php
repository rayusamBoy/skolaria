@if (isset($last_exam))
    @php
        $chart_options = [
            'line' => 'Line Chart',
            'pie' => 'Pie Chart',
            'doughnut' => 'Doughnut Chart',
            'polarArea' => 'PolarArea',
            'bar' => 'Bar Chart',
            'radar' => 'Radar Chart',
            'horizontal_bar' => 'Horizontal Bar Chart',
            'horizontal_line' => 'Horizontal Line Chart',
        ];
    @endphp

    {{-- Chart Type Switcher --}}
    <span class="dropdown bottom-0 z-100">
        <button
            type="button"
            class="btn btn-info btn-sm position-fixed bottom-p5em right-3"
            data-toggle="dropdown"
            aria-expanded="false"
        >
            <span>Change Chart Type</span><i class="material-symbols-rounded ml-1">keyboard_arrow_up</i>
        </button>
        <div class="bg-dark-alpha dropdown-menu">
            @foreach ($chart_options as $key => $value)
                <a
                    href="javascript:;"
                    class="dropdown-item chart-type-option"
                    data-chart-type="{{ $key }}"
                >{{ $value }}</a>
            @endforeach
        </div>
    </span>

    {{-- PARENT / STUDENT VIEW --}}
    @if ($user_is_parent || $user_is_student)
        {{-- Overall Exams Performance Charts --}}
        @foreach ($exams_recs->groupBy('student_id') as $student_id => $exam_recs)
            @php
                $student_name = $exam_recs->first()->student->name;
                $year = $exam_recs->first()->year;
                $chart_id = "exam-performances-{$loop->iteration}";
            @endphp
            <x-statistics.chart_card
                :id="$chart_id"
                :title="'Overall Exams Performance Chart of '.$student_name.' ('.$year.')'"
                :labels="$exam_recs->pluck('exam.name')"
                :data="$exam_recs->pluck('total')"
                label="Total Marks Obtained"
            />
        @endforeach

        {{-- Last Exam Subjects Performance Charts --}}
        @if (isset($last_exm_marks))
            @foreach ($last_exm_marks->groupBy('student_id') as $student_id => $marks)
                @php
                    $student_name = $marks->first()->user->name;
                    $exam_title = "{$last_exam->name} {$last_exam->year}";
                    $chart_id = "subjects-performances-{$loop->iteration}";
                    $tex = "tex{$last_exam->term}";
                @endphp
                <x-statistics.chart_card
                    :id="$chart_id"
                    :title="'Subjects Performance Chart of '.$student_name.' ('.$exam_title.')'"
                    :labels="$marks->pluck('subject.name')"
                    :data="$marks->pluck($tex)"
                    label="Mark Obtained"
                />
            @endforeach
        @endif
    @endif

    {{-- STAFF / ADMIN VIEW --}}
    @if ($user_is_staff)
        {{-- Classes Performance Chart --}}
        <x-statistics.chart_card
            id="classes-performance"
            :title="'Classes Performance Chart for '.$last_exam->name.' ('.$last_exam->year.')'"
            :labels="$classes_names"
            :data="$averages"
            label="Class Average"
            :collapsed="($prev_exam_id === null || $next_exam_id === null)"
        >
            <x-slot:nav>
                <span class="nav-btn position-absolute left-0" data-toggle="tooltip" title="Previous">
                    <a
                        class="{{ $prev_exam_id === null ? 'disabled pr-2 btn btn-sm' : 'pr-2 btn btn-sm' }}"
                        href="{{ route('statistics.index', Qs::hash($prev_exam_id)) }}"
                    >
                        <span class="material-symbols-rounded">arrow_back_ios</span>
                    </a>
                </span>
                <span class="nav-btn position-absolute right-0" data-toggle="tooltip" title="Next">
                    <a
                        class="{{ $next_exam_id === null ? 'disabled pr-2 btn btn-sm' : 'pr-2 btn btn-sm' }}"
                        href="{{ route('statistics.index', Qs::hash($next_exam_id)) }}"
                    >
                        <span class="material-symbols-rounded">arrow_forward_ios</span>
                    </a>
                </span>
            </x-slot:nav>
        </x-statistics.chart_card>

        {{-- Exams GPA Performance Chart --}}
        @if ($exams_names && $exams_gpas)
            <x-statistics.chart_card
                id="exams-gpa-performance"
                :title="'Exams GPA Performance Chart ('.$year.')'"
                :labels="$exams_names"
                :data="$exams_gpas"
                label="Exam GPA"
                :collapsed="true"
            />
        @endif
    @endif

@endif

{{-- JavaScript --}}
@pushOnce('scripts')
    <script>
        // Global Chart.js configuration
        const CHART_COLORS = {
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 205, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(201, 203, 207, 0.2)',
            ],
            borderColor: [
                'rgb(255, 99, 132)',
                'rgb(255, 159, 64)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)',
                'rgb(54, 162, 235)',
                'rgb(153, 102, 255)',
                'rgb(201, 203, 207)',
            ],
        };

        // Chart options factory
        function getChartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: true,
                indexAxis: getIndexAxis(),
                animation: false,
                scales: {
                    x: { title: { display: true } },
                    y: { title: { display: true }, beginAtZero: true },
                },
                plugins: {
                    tooltip: {
                        backgroundColor: '#303030',
                        titleColor: 'lightgray',
                        bodyColor: 'lightgray',
                        boxHeight: 8,
                        boxPadding: 2,
                    },
                },
            };
        }

        function getIndexAxis() {
            const chartType = localStorage.chart_type || 'bar';
            const chartTypeLabel = localStorage.chart_type_label || 'bar';
            if (chartType === 'horizontal_bar' || chartTypeLabel === 'horizontal_bar') {
                localStorage.chart_type = 'bar';
                return 'y';
            }
            if (chartType === 'horizontal_line' || chartTypeLabel === 'horizontal_line') {
                localStorage.chart_type = 'line';
                return 'y';
            }

            localStorage.chart_type_label = localStorage.chart_type;
            return 'x';
        }

        // Initialize all charts on the page
        function initCharts() {
            document.querySelectorAll('[data-chart]').forEach((canvas) => {
                const id = canvas.id;
                const labels = JSON.parse(canvas.dataset.labels || '[]');
                const data = JSON.parse(canvas.dataset.data || '[]');
                const label = canvas.dataset.label || 'Value';
                const xTitle = canvas.dataset.xAxisTitle || '';
                const yTitle = canvas.dataset.yAxisTitle || '';

                const options = getChartOptions();
                if (xTitle) options.scales.x.title.text = xTitle;
                if (yTitle) options.scales.y.title.text = yTitle;

                // Set card title
                const card = canvas.closest('.card');
                if (card) {
                    const titleEl = card.querySelector('.card-title');
                    if (titleEl) titleEl.textContent = canvas.dataset.title || '';
                }

                new Chart(canvas, {
                    type: localStorage.chart_type || 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: label,
                                data: data,
                                backgroundColor: CHART_COLORS.backgroundColor,
                                borderColor: CHART_COLORS.borderColor,
                                borderWidth: 1,
                            },
                        ],
                    },
                    options: options,
                });
            });
        }

        // Chart type switcher
        document.querySelectorAll('.chart-type-option').forEach((option) => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                const chartType = this.dataset.chartType;
                localStorage.chart_type = chartType;
                localStorage.chart_type_label = chartType;
                window.location.reload();
            });
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initCharts);
        window.addEventListener('resize', () => {
            Chart.helpers.each(Chart.instances, (instance) => instance.resize());
        });
    </script>
@endpushOnce
