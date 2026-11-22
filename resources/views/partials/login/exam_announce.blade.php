<!-- Exam announce -->
@if ($is_tenant_context && request()->routeIs('login') && !app()->runningUnitTests())
    @php $exam_announces = Qs::getValidExamAnnounces() @endphp

    @if ($exam_announces?->isNotEmpty())
        <div class="bg-dark exam-announce-wrapper">
            <div class="pb-2px mx-3 overflow-hidden" id="exam-announce">
                @foreach ($exam_announces as $exm_ann)
                    <span class="badge badge-default badge-warning p-1 text-white">{{ $loop->iteration }}</span>
                    <span class="pr-2 align-middle text-white"
                        >{{ ucwords(strtolower($exm_ann->message)) }} &#183;
                        <small>{{ $exm_ann->created_at->diffForHumans() }}</small>.</span>
                @endforeach

                &nbsp;
            </div>
        </div>
    @endif

@endif
