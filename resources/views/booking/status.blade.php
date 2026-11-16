@extends('layouts.app')
@section('title', 'Status Booking — ' . $booking->kode_booking)
@section('content')
@php
    $statusValue = $booking->status_booking->value;
    $bookingBadgeClass = match ($statusValue) {
        'pending' => 'bg-amber-100 text-amber-950 border border-amber-700/25',
        'confirmed' => 'bg-emerald-800 text-white',
        'done' => 'bg-[#1e1b16] text-[#f4efe5]',
        'cancelled' => 'bg-red-800 text-white',
        default => 'bg-[#e7dfcf] text-[#1e1b16]',
    };

    $latestPayment = $booking->payments->sortByDesc('created_at')->first();
    $paymentStatusValue = $latestPayment?->status_pembayaran?->value;
    $paymentBadgeClass = match ($paymentStatusValue) {
        'pending' => 'bg-amber-100 text-amber-950 border border-amber-700/25',
        'paid' => 'bg-emerald-800 text-white',
        'failed' => 'bg-red-800 text-white',
        'refunded' => 'bg-[#e7dfcf] text-[#1e1b16]',
        default => 'bg-[#e7dfcf] text-[#1e1b16]',
    };
    $firstDetail = $booking->details->first();
@endphp

<section class="py-12 sm:py-16">
    <div class="t-wrap">
        <div class="mx-auto max-w-2xl border border-[var(--line)] bg-[#faf7f1] p-7 sm:p-10">

            {{-- Header Kode & Badge --}}
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-[var(--line)] pb-5">
                <div>
                    <p class="t-label text-[#7b2631] font-medium">Status Reservasi Studio</p>
                    <div class="mt-1 flex items-center gap-2.5">
                        <h1 id="booking-code-status" class="font-mono text-2xl sm:text-3xl font-semibold text-[#1e1b16] tabular-nums">{{ $booking->kode_booking }}</h1>
                        <button type="button" id="copy-status-code-btn" title="Salin Kode Booking" aria-label="Salin Kode Booking"
                            class="group inline-flex h-9 w-9 items-center justify-center rounded-[2px] border border-[var(--line-strong)] bg-white text-[#1e1b16] transition hover:border-[#1e1b16] hover:bg-[#1e1b16] hover:text-[#faf7f1] cursor-pointer">
                            <svg id="status-copy-icon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <svg id="status-copied-icon" xmlns="http://www.w3.org/2000/svg" class="hidden h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <span id="status-copy-msg" class="text-xs font-semibold text-emerald-800 opacity-0 transition-opacity">Tersalin</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="status-badge {{ $bookingBadgeClass }}">
                        Booking: {{ $booking->status_booking->label() }}
                    </span>
                    @if ($latestPayment)
                        <span class="status-badge {{ $paymentBadgeClass }}">
                            Bayar: {{ $latestPayment->status_pembayaran->label() }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Pesan Status Kontekstual --}}
            <div class="mt-6 border border-[var(--line)] ground-surface p-5 text-sm">
                @if ($statusValue === 'confirmed')
                    <p class="font-semibold text-emerald-900"><i class="fa-solid fa-check mr-1.5" aria-hidden="true"></i> Pembayaran Terverifikasi &amp; Jadwal Dikonfirmasi</p>
                    <p class="mt-1 text-[#5c5549]">Silakan hadir di studio 10 menit sebelum sesi dimulai dan tunjukkan kode booking ini kepada staf kami.</p>
                @elseif ($statusValue === 'done')
                    <p class="font-semibold text-[#1e1b16]">Sesi Foto Telah Selesai</p>
                    <p class="mt-1 text-[#5c5549]">Terima kasih telah berkunjung ke Clicked Picked Studios.</p>
                @elseif ($statusValue === 'cancelled')
                    <p class="font-semibold text-red-800">Booking Dibatalkan</p>
                    <p class="mt-1 text-[#5c5549]">Reservasi ini telah dibatalkan. Silakan buat reservasi baru jika ingin memesan ulang.</p>
                @elseif ($paymentStatusValue === 'failed')
                    <p class="font-semibold text-red-800"><i class="fa-solid fa-triangle-exclamation mr-1.5" aria-hidden="true"></i> Bukti Pembayaran Ditolak</p>
                    <p class="mt-1 text-[#5c5549]">Bukti pembayaran Anda sebelumnya belum sesuai. Silakan unggah ulang bukti pembayaran yang valid di bawah ini.</p>
                @elseif (!$latestPayment)
                    <p class="font-semibold text-amber-950">Menunggu Unggah Bukti Pembayaran</p>
                    <p class="mt-1 text-[#5c5549]">Anda belum mengunggah bukti pembayaran untuk reservasi ini.</p>
                    <a href="{{ route('booking.payment', $booking->kode_booking) }}"
                        class="t-btn t-btn-primary mt-4 text-sm min-h-[42px]">
                        <span>Lanjutkan ke Halaman Pembayaran</span>
                        <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                    </a>
                @else
                    <p class="font-semibold text-amber-950">Menunggu Verifikasi Admin</p>
                    <p class="mt-1 text-[#5c5549]">Bukti pembayaran Anda sedang diperiksa oleh admin studio. Halaman ini akan diperbarui setelah diverifikasi.</p>
                @endif
            </div>

            {{-- Form Upload Ulang Bukti Pembayaran (Jika Ditolak oleh Admin) --}}
            @if ($statusValue === 'pending' && $paymentStatusValue === 'failed')
                <form method="POST" action="{{ route('booking.payment.reupload', $booking->kode_booking) }}"
                    enctype="multipart/form-data" class="mt-6 border border-red-700/30 bg-red-50/50 p-5 space-y-4">
                    @csrf
                    <h2 class="t-h3">Unggah ulang bukti pembayaran</h2>
                    <div>
                        <label for="bukti_pembayaran" class="field-label">Pilih file bukti baru (JPG, PNG, atau PDF, maks. 5 MB)</label>
                        <input id="bukti_pembayaran" type="file" name="bukti_pembayaran" accept="image/*,application/pdf" required class="field-input bg-white text-sm">
                        @error('bukti_pembayaran')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="t-btn t-btn-primary w-full text-sm min-h-[44px]">
                        <span>Kirim Bukti Pembayaran Baru</span>
                    </button>
                </form>
            @endif

            {{-- Detail Sesi & Tagihan (.t-rows) --}}
            <div class="t-rows mt-6 tabular-nums">
                <div class="t-row">
                    <span class="text-[#5c5549]">Nama Pelanggan</span>
                    <strong class="font-medium text-[#1e1b16]">{{ $booking->nama_pelanggan }}</strong>
                </div>
                @if ($firstDetail)
                    <div class="t-row">
                        <span class="text-[#5c5549]">Ruang Studio</span>
                        <strong class="font-medium text-[#1e1b16]">{{ $firstDetail->room?->nama_room ?? '-' }}</strong>
                    </div>
                    <div class="t-row">
                        <span class="text-[#5c5549]">Paket &amp; Durasi</span>
                        <strong class="font-medium text-[#1e1b16]">
                            {{ $firstDetail->package?->tipe_harga ?? '-' }} ({{ $firstDetail->package?->durasi_menit ?? '-' }} menit)
                        </strong>
                    </div>
                    <div class="t-row">
                        <span class="text-[#5c5549]">Tanggal &amp; Jam Sesi</span>
                        <strong class="font-medium text-[#1e1b16]">
                            {{ $firstDetail->tanggal_sesi?->translatedFormat('d M Y') }} · {{ $firstDetail->jam_mulai }} – {{ $firstDetail->jam_selesai }} WIB
                        </strong>
                    </div>
                    <div class="t-row">
                        <span class="text-[#5c5549]">Jumlah Peserta</span>
                        <strong class="font-medium text-[#1e1b16]">{{ $firstDetail->jumlah_orang }} orang</strong>
                    </div>
                @else
                    <div class="t-row">
                        <span class="text-[#5c5549]">Tanggal Booking</span>
                        <strong class="font-medium text-[#1e1b16]">{{ $booking->tanggal_booking?->translatedFormat('d M Y') }}</strong>
                    </div>
                @endif
                <div class="t-row">
                    <span class="text-[#5c5549]">Total Pembayaran</span>
                    <strong class="text-lg font-semibold text-[#7b2631]">
                        Rp {{ number_format($booking->total_harga, 0, ',', '.') }}
                    </strong>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap justify-between gap-3">
                <a href="{{ route('check-status') }}" class="t-btn t-btn-secondary text-sm min-h-[44px]">
                    <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                    <span>Cek Kode Lain</span>
                </a>
                <a href="{{ route('home') }}" class="t-btn t-btn-primary text-sm min-h-[44px]">
                    <span>Kembali ke Beranda</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const btn = document.getElementById('copy-status-code-btn');
    const code = document.getElementById('booking-code-status')?.textContent?.trim();
    const copyIcon = document.getElementById('status-copy-icon');
    const copiedIcon = document.getElementById('status-copied-icon');
    const msg = document.getElementById('status-copy-msg');

    btn?.addEventListener('click', function () {
        if (!code) return;
        navigator.clipboard?.writeText(code).then(() => {
            if (copyIcon && copiedIcon) {
                copyIcon.classList.add('hidden');
                copiedIcon.classList.remove('hidden');
            }
            if (msg) msg.classList.remove('opacity-0');

            setTimeout(() => {
                if (copyIcon && copiedIcon) {
                    copyIcon.classList.remove('hidden');
                    copiedIcon.classList.add('hidden');
                }
                if (msg) msg.classList.add('opacity-0');
            }, 2000);
        });
    });
})();
</script>
@endpush
