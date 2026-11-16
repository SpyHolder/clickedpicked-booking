@extends('layouts.app')
@section('title', 'Pilih Jadwal — Langkah 2')
@section('content')
@php
    $selectedPackage = $room?->packages?->firstWhere('id_package', $draft['id_package'] ?? null);
    $maxCapacity = $room?->kapasitas ?? 10;
@endphp

<section class="py-12 sm:py-16">
    <div class="t-wrap">
        <div class="mx-auto max-w-3xl">

            {{-- 4-Step Animated Line Stepper --}}
            @include('booking.partials.stepper', ['currentStep' => 2])

            <div class="border border-[var(--line)] bg-[#faf7f1] p-7 sm:p-10">
                <div class="flex flex-wrap items-start justify-between gap-4 pb-6 border-b border-[var(--line)]">
                    <div>
                        <p class="t-label text-[#7b2631] font-medium">Langkah 2 dari 4 · Jadwal Sesi</p>
                        <h1 class="t-h2 mt-1">Pilih tanggal dan jam mulai</h1>
                        <p class="mt-1 t-body text-[#5c5549]">
                            Pilih slot waktu yang tersedia. Jadwal akan langsung dikunci untuk sesi Anda.
                        </p>
                    </div>
                    <a href="{{ route('booking.wizard') }}" class="t-btn t-btn-secondary text-sm min-h-[42px] px-4">
                        <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                        <span>Ubah Data</span>
                    </a>
                </div>

                {{-- Ringkasan Sementara (Ruled Architectural Panel) --}}
                <div class="mt-6 grid gap-4 border border-[var(--line)] ground-surface p-5 text-sm sm:grid-cols-3 tabular-nums">
                    <div>
                        <span class="t-label">Pemesan</span>
                        <p class="mt-0.5 font-semibold text-[#1e1b16]">{{ $draft['nama_pelanggan'] ?? '-' }}</p>
                        <p class="font-mono text-xs text-[#5c5549]">{{ $draft['no_hp_pelanggan'] ?? '' }}</p>
                    </div>
                    <div>
                        <span class="t-label">Ruang &amp; Paket</span>
                        <p class="mt-0.5 font-semibold text-[#1e1b16]">{{ $room?->nama_room ?? 'Studio' }}</p>
                        <p class="text-xs font-medium text-[#7b2631]">
                            {{ $selectedPackage ? $selectedPackage->tipe_harga . ' (' . $selectedPackage->durasi_menit . ' menit)' : '-' }}
                        </p>
                    </div>
                    <div class="sm:text-right">
                        <span class="t-label">Estimasi Tagihan</span>
                        <p class="mt-0.5 text-lg font-semibold text-[#1e1b16]">
                            {{ $selectedPackage ? 'Rp ' . number_format($selectedPackage->harga, 0, ',', '.') : '-' }}
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('booking.step2.process') }}" class="mt-7 space-y-6" id="step2-form">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="tanggal_sesi" class="field-label">Tanggal sesi foto</label>
                            <input class="field-input tabular-nums" type="date" name="tanggal_sesi" id="tanggal_sesi"
                                min="{{ now()->format('Y-m-d') }}"
                                max="{{ now()->addDays(90)->format('Y-m-d') }}"
                                value="{{ old('tanggal_sesi') }}" required>
                            @error('tanggal_sesi')
                                <p class="field-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jumlah_orang" class="field-label">Jumlah peserta <span class="text-[#5c5549] font-normal">(Maks. {{ $maxCapacity }} orang)</span></label>
                            <input class="field-input tabular-nums" type="number" inputmode="numeric" name="jumlah_orang" id="jumlah_orang"
                                min="1" max="{{ $maxCapacity }}" value="{{ old('jumlah_orang', 2) }}" required>
                            @error('jumlah_orang')
                                <p class="field-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label class="field-label mb-0">Jam mulai sesi</label>
                            <span id="selected-slot-badge" class="hidden status-badge bg-[#1e1b16] text-[#f4efe5] tabular-nums"></span>
                        </div>

                        <div id="slots" class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4 tabular-nums">
                            <p class="col-span-full border border-dashed border-[var(--line-strong)] ground-surface p-6 text-center text-sm text-[#5c5549]">
                                Pilih tanggal sesi terlebih dahulu untuk menampilkan jam yang tersedia.
                            </p>
                        </div>
                        <input type="hidden" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai') }}">
                        @error('jam_mulai')
                            <p class="field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <button id="step2-submit" class="t-btn t-btn-primary w-full" type="submit">
                        <span>Pilih Slot Ini &amp; Kunci Jadwal</span>
                        <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const dateInput = document.querySelector('#tanggal_sesi');
    const slotsContainer = document.querySelector('#slots');
    const selectedInput = document.querySelector('#jam_mulai');
    const slotBadge = document.querySelector('#selected-slot-badge');
    const draft = @json($draft);

    async function loadSlots() {
        if (!dateInput.value) return;
        slotsContainer.innerHTML = '<p class="col-span-full ground-surface p-6 text-center text-sm font-medium text-[#5c5549]">Memeriksa jadwal tersedia...</p>';

        try {
            const url = `{{ route('booking.slots') }}?id_room=${draft.id_room}&id_package=${draft.id_package}&tanggal_sesi=${dateInput.value}`;
            const response = await fetch(url);
            const result = await response.json();
            slotsContainer.innerHTML = '';

            if (!result.success || !result.data?.length) {
                slotsContainer.innerHTML = '<p class="col-span-full border border-amber-700/30 bg-amber-50 p-5 text-center text-sm font-medium text-amber-950">Tidak ada slot kosong pada tanggal ini. Silakan pilih tanggal lain.</p>';
                return;
            }

            result.data.forEach(slot => {
                const time = typeof slot === 'string' ? slot : (slot.jam_mulai ?? slot.time);
                const isAvailable = typeof slot === 'object' && 'available' in slot ? slot.available : true;

                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = time + ' WIB';

                if (!isAvailable) {
                    button.disabled = true;
                    button.className = 'cursor-not-allowed rounded-[2px] border border-[var(--line)] bg-[#e7dfcf]/60 p-3.5 text-sm font-medium text-[#5c5549]/60 line-through';
                } else {
                    const isSelected = selectedInput.value === time;
                    button.className = isSelected
                        ? 'rounded-[2px] border border-[#1e1b16] bg-[#1e1b16] p-3.5 text-sm font-semibold text-[#f4efe5] transition cursor-pointer'
                        : 'rounded-[2px] border border-[var(--line-strong)] bg-white p-3.5 text-sm font-medium text-[#1e1b16] transition hover:border-[#1e1b16] hover:bg-[#e7dfcf] cursor-pointer';

                    button.onclick = () => {
                        selectedInput.value = time;
                        if (slotBadge) {
                            slotBadge.textContent = 'Terpilih: ' + time + ' WIB';
                            slotBadge.classList.remove('hidden');
                        }
                        slotsContainer.querySelectorAll('button:not(:disabled)').forEach(item => {
                            item.className = 'rounded-[2px] border border-[var(--line-strong)] bg-white p-3.5 text-sm font-medium text-[#1e1b16] transition hover:border-[#1e1b16] hover:bg-[#e7dfcf] cursor-pointer';
                        });
                        button.className = 'rounded-[2px] border border-[#1e1b16] bg-[#1e1b16] p-3.5 text-sm font-semibold text-[#f4efe5] transition cursor-pointer';
                    };
                }
                slotsContainer.appendChild(button);
            });
        } catch (err) {
            slotsContainer.innerHTML = '<p class="col-span-full bg-red-50 p-5 text-center text-sm text-red-800">Gagal memuat jadwal. Silakan coba lagi.</p>';
        }
    }

    dateInput?.addEventListener('change', () => {
        selectedInput.value = '';
        slotBadge?.classList.add('hidden');
        loadSlots();
    });

    if (dateInput?.value) {
        loadSlots();
    }
})();
</script>
@endpush
