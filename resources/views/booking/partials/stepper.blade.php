@php
    $currentStep = $currentStep ?? 1;

    $steps = [
        1 => ['number' => '01', 'label' => 'Data Diri', 'url' => route('booking.wizard')],
        2 => ['number' => '02', 'label' => 'Waktu', 'url' => session()->has('booking_draft') ? route('booking.step2') : null],
        3 => ['number' => '03', 'label' => 'Pembayaran', 'url' => null],
        4 => ['number' => '04', 'label' => 'Kode', 'url' => null],
    ];

    $targetPercentages = [
        1 => 25,
        2 => 50,
        3 => 75,
        4 => 100,
    ];

    $targetPercent = $targetPercentages[$currentStep] ?? 25;

    $startPercent = match($currentStep) {
        1 => 0,
        2 => 25,
        3 => 50,
        4 => 75,
        default => 0,
    };
@endphp

<div class="stepper-container mb-8 sm:mb-10 w-full" aria-label="Tahapan Booking">
    <div class="mx-auto max-w-2xl">
        {{-- 4 Card Pills Grid (Sesuai referensi visual card + typography) --}}
        <ol class="grid grid-cols-4 gap-2 sm:gap-3.5">
            @foreach ($steps as $stepNum => $step)
                @php
                    $isPassed = $stepNum < $currentStep;
                    $isActive = $stepNum === $currentStep;
                @endphp

                <li>
                    @if ($isPassed && $step['url'])
                        <a href="{{ $step['url'] }}" title="Kembali ke {{ $step['label'] }}"
                            class="group block rounded-2xl border border-[var(--line-strong)] bg-white p-2.5 sm:p-3.5 text-center transition-all hover:border-[#181715] hover:bg-[#f3ede1]">
                            <span class="font-display text-sm sm:text-base italic text-[#d94b38] block tabular-nums">
                                <i class="fa-solid fa-check text-[10px] mr-0.5" aria-hidden="true"></i>{{ $step['number'] }}
                            </span>
                            <span class="mt-0.5 block text-[11px] sm:text-xs font-semibold text-[#181715] truncate">
                                {{ $step['label'] }}
                            </span>
                        </a>
                    @elseif ($isPassed)
                        <div class="block rounded-2xl border border-[var(--line-strong)] bg-white p-2.5 sm:p-3.5 text-center">
                            <span class="font-display text-sm sm:text-base italic text-[#d94b38] block tabular-nums">
                                <i class="fa-solid fa-check text-[10px] mr-0.5" aria-hidden="true"></i>{{ $step['number'] }}
                            </span>
                            <span class="mt-0.5 block text-[11px] sm:text-xs font-semibold text-[#181715] truncate">
                                {{ $step['label'] }}
                            </span>
                        </div>
                    @elseif ($isActive)
                        <div class="stepper-card-active block rounded-2xl border-2 border-[#7b2631] bg-[#7b2631] p-2.5 sm:p-3.5 text-center text-white shadow-xs">
                            <span class="stepper-active-number font-display text-sm sm:text-base italic text-white block tabular-nums">
                                {{ $step['number'] }}
                            </span>
                            <span class="mt-0.5 block text-[11px] sm:text-xs font-bold text-white truncate">
                                {{ $step['label'] }}
                            </span>
                        </div>
                    @else
                        <div class="block rounded-2xl border border-[var(--line)] bg-white/70 p-2.5 sm:p-3.5 text-center text-[#6b6255]">
                            <span class="font-display text-sm sm:text-base italic text-[#6b6255] block tabular-nums">
                                {{ $step['number'] }}
                            </span>
                            <span class="mt-0.5 block text-[11px] sm:text-xs font-medium text-[#6b6255] truncate">
                                {{ $step['label'] }}
                            </span>
                        </div>
                    @endif
                </li>
            @endforeach
        </ol>

        {{-- Garis Rel Progres Horizontal Beranimasi (Line Progress Indicator) --}}
        <div class="mt-3 relative h-1.5 w-full overflow-hidden rounded-full bg-[#e4dcce]">
            <div id="animated-progress-line"
                class="h-full rounded-full bg-[#d94b38] transition-all duration-700"
                style="width: {{ $startPercent }}%; transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1);"
                data-target-width="{{ $targetPercent }}%">
            </div>
        </div>
    </div>
</div>

<style>
@keyframes stepperCardEntrance {
    0% {
        transform: scale(0.95);
        opacity: 0.85;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes stepperNumberEntrance {
    0% {
        opacity: 0;
        transform: translateY(4px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.stepper-card-active {
    animation: stepperCardEntrance 0.4s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.stepper-active-number {
    display: inline-block;
    animation: stepperNumberEntrance 0.35s cubic-bezier(0.22, 1, 0.36, 1) 0.15s both;
}
</style>

<script>
(function () {
    const line = document.getElementById('animated-progress-line');
    if (line) {
        requestAnimationFrame(() => {
            setTimeout(() => {
                const target = line.getAttribute('data-target-width');
                if (target) {
                    line.style.width = target;
                }
            }, 60);
        });
    }
})();
</script>
