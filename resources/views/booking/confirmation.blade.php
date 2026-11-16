@extends('layouts.app')
@section('title', 'Booking Berhasil — Langkah 4')
@section('content')
@php
    $firstDetail = $booking->details->first();
@endphp

<section class="py-12 sm:py-16">
    <div class="t-wrap">
        <div class="mx-auto max-w-2xl">

            {{-- 4-Step Animated Line Stepper --}}
            @include('booking.partials.stepper', ['currentStep' => 4])

            <div class="border border-[var(--line)] bg-[#faf7f1] p-7 sm:p-10">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-[2px] bg-emerald-800 text-base text-white">
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                    </span>
                    <div>
                        <p class="t-label text-emerald-900 font-medium">Langkah 4 dari 4 · Booking Berhasil Dibuat</p>
                        <h1 class="t-h2">Simpan kode booking Anda</h1>
                    </div>
                </div>

                <p class="mt-3 t-body text-[#5c5549]">
                    Bukti pembayaran Anda telah diterima dan sedang menunggu verifikasi admin studio.
                </p>

                {{-- Kode Booking Card dengan Tombol Ikon Salin di Samping Nomor --}}
                <div class="my-7 border border-[var(--line-strong)] ground-surface p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="t-label text-[#1e1b16]">Kode booking resmi</span>
                        <span class="status-badge bg-amber-100 text-amber-950 border border-amber-700/25">
                            Menunggu Verifikasi Pembayaran
                        </span>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <span id="booking-code-display" class="font-mono text-3xl sm:text-4xl font-semibold tracking-wider text-[#7b2631] tabular-nums">
                            {{ $booking->kode_booking }}
                        </span>

                        {{-- Tombol Salin di Samping Nomor (motion.md Copy button) --}}
                        <button type="button" id="copy-booking-code" title="Salin Kode Booking" aria-label="Salin Kode Booking"
                            class="group inline-flex h-11 w-11 items-center justify-center rounded-[2px] border border-[var(--line-strong)] bg-[#faf7f1] text-[#1e1b16] transition hover:border-[#1e1b16] hover:bg-[#1e1b16] hover:text-[#faf7f1] cursor-pointer">
                            <svg id="copy-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <svg id="copied-icon" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>

                    <p id="copy-feedback-msg" class="mt-2 text-xs font-semibold text-emerald-800 opacity-0 transition-opacity duration-200" role="status">
                        <i class="fa-solid fa-check mr-1" aria-hidden="true"></i> Kode booking berhasil disalin.
                    </p>
                </div>

                {{-- Ringkasan Sesi (.t-rows) --}}
                <div class="t-rows tabular-nums">
                    <div class="t-row">
                        <span class="text-[#5c5549]">Nama Pelanggan</span>
                        <strong class="font-medium text-[#1e1b16]">{{ $booking->nama_pelanggan }}</strong>
                    </div>
                    @if ($firstDetail)
                        <div class="t-row">
                            <span class="text-[#5c5549]">Studio &amp; Paket</span>
                            <strong class="font-medium text-[#1e1b16]">
                                {{ $firstDetail->room?->nama_room }} ({{ $firstDetail->package?->tipe_harga }})
                            </strong>
                        </div>
                        <div class="t-row">
                            <span class="text-[#5c5549]">Jadwal Sesi</span>
                            <strong class="font-medium text-[#1e1b16]">
                                {{ $firstDetail->tanggal_sesi?->translatedFormat('d M Y') }} · {{ $firstDetail->jam_mulai }} – {{ $firstDetail->jam_selesai }} WIB
                            </strong>
                        </div>
                    @endif
                    <div class="t-row">
                        <span class="text-[#5c5549]">Total Tagihan</span>
                        <strong class="text-lg font-semibold text-[#7b2631]">
                            Rp {{ number_format($booking->total_harga, 0, ',', '.') }}
                        </strong>
                    </div>
                </div>

                <a href="{{ route('booking.status', $booking->kode_booking) }}"
                    class="t-btn t-btn-primary w-full mt-8">
                    <span>Pantau Status Booking</span>
                    <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const codeDisplay = document.getElementById('booking-code-display');
    const copyBtn = document.getElementById('copy-booking-code');
    const copyIcon = document.getElementById('copy-icon');
    const copiedIcon = document.getElementById('copied-icon');
    const feedbackMsg = document.getElementById('copy-feedback-msg');

    copyBtn?.addEventListener('click', function () {
        const code = codeDisplay?.textContent?.trim();
        if (!code) return;

        navigator.clipboard?.writeText(code).then(() => {
            if (copyIcon && copiedIcon) {
                copyIcon.classList.add('hidden');
                copiedIcon.classList.remove('hidden');
            }
            if (feedbackMsg) feedbackMsg.classList.remove('opacity-0');

            setTimeout(() => {
                if (copyIcon && copiedIcon) {
                    copyIcon.classList.remove('hidden');
                    copiedIcon.classList.add('hidden');
                }
                if (feedbackMsg) feedbackMsg.classList.add('opacity-0');
            }, 2000);
        });
    });
})();
</script>
@endpush
