@extends('layouts.app')
@section('title', 'Pembayaran — Langkah 3')
@section('content')
@php
    $firstDetail = $booking->details->first();
    $qrisImagePath = null;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $qrisImagePath = \App\Models\Setting::getValue('qris_image');
        }
    } catch (\Throwable $e) {
        $qrisImagePath = null;
    }
@endphp

<section class="py-12 sm:py-16">
    <div class="t-wrap">
        <div class="mx-auto max-w-2xl">

            {{-- 4-Step Animated Line Stepper --}}
            @include('booking.partials.stepper', ['currentStep' => 3])

            <div class="border border-[var(--line)] bg-[#faf7f1] p-7 sm:p-10">
                <div>
                    <p class="t-label text-[#7b2631] font-medium">Langkah 3 dari 4 · Pembayaran QRIS</p>
                    <h1 class="t-h2 mt-1">Pembayaran sesi foto</h1>
                    <p class="mt-2 t-body text-[#5c5549]">
                        Slot jadwal Anda telah dikunci. Silakan lakukan pembayaran sesuai nominal di bawah lalu unggah bukti transaksi.
                    </p>
                </div>

                {{-- Total Tagihan & Ringkasan (Ruled Architectural Panel) --}}
                <div class="mt-7 border border-[var(--line)] ground-surface p-6 tabular-nums">
                    <div class="flex flex-wrap items-baseline justify-between gap-4 border-b border-[var(--line)] pb-4">
                        <span class="t-label text-[#1e1b16]">Total tagihan pembayaran</span>
                        <span class="font-serif text-3xl sm:text-4xl text-[#7b2631]">
                            Rp {{ number_format($booking->total_harga, 0, ',', '.') }}
                        </span>
                    </div>
                    @if ($firstDetail)
                        <div class="mt-3 flex flex-wrap justify-between gap-2 text-sm text-[#5c5549]">
                            <span>{{ $firstDetail->room?->nama_room ?? 'Studio' }}</span>
                            <span class="font-medium text-[#1e1b16]">
                                {{ $firstDetail->tanggal_sesi?->translatedFormat('d M Y') }} · {{ $firstDetail->jam_mulai }} – {{ $firstDetail->jam_selesai }} WIB
                            </span>
                        </div>
                    @endif
                </div>

                {{-- QRIS Visual --}}
                <div class="my-8 flex flex-col items-center">
                    <div class="flex h-60 w-60 flex-col items-center justify-center border border-[var(--line-strong)] bg-white p-4">
                        @if ($qrisImagePath)
                            <img src="{{ str_starts_with($qrisImagePath, 'http') ? $qrisImagePath : asset('storage/' . $qrisImagePath) }}"
                                alt="QRIS Clicked Picked Studios" class="h-full w-full object-contain">
                        @else
                            <div class="flex h-full w-full flex-col items-center justify-center border border-dashed border-[var(--line-strong)] ground-surface p-6 text-center">
                                <i class="fa-solid fa-qrcode text-4xl text-[#1e1b16]" aria-hidden="true"></i>
                                <span class="mt-3 block text-sm font-semibold text-[#1e1b16]">QRIS Clicked Picked</span>
                                <span class="mt-1 block text-xs text-[#5c5549]">Scan via m-Banking / e-Wallet</span>
                            </div>
                        @endif
                    </div>
                    <p class="mt-3 text-center text-xs text-[#5c5549]">
                        Menerima pembayaran dari seluruh aplikasi m-Banking dan e-Wallet (GoPay, OVO, Dana, ShopeePay).
                    </p>
                </div>

                {{-- Form Upload Bukti --}}
                <form method="POST" action="{{ route('booking.payment.upload', $booking->kode_booking) }}"
                    enctype="multipart/form-data" class="space-y-5 border-t border-[var(--line)] pt-6" id="payment-upload-form">
                    @csrf

                    <div>
                        <label for="bukti_pembayaran" class="field-label">Unggah bukti pembayaran (JPG, PNG, atau PDF, maks. 5 MB)</label>
                        <input id="bukti_pembayaran" class="field-input file:mr-4 file:rounded-[2px] file:border-0 file:bg-[#1e1b16] file:px-4 file:py-2 file:text-xs file:font-medium file:text-[#f4efe5] hover:file:bg-[#7b2631]"
                            type="file" name="bukti_pembayaran" accept="image/jpeg,image/png,image/jpg,application/pdf" required>
                        <p id="file-preview-info" class="mt-1.5 text-xs text-[#5c5549]"></p>
                        @error('bukti_pembayaran')
                            <p class="field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <button id="payment-submit-btn" class="t-btn t-btn-primary w-full" type="submit">
                        <span>Kirim Bukti Pembayaran &amp; Lihat Kode Booking</span>
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
document.getElementById('bukti_pembayaran')?.addEventListener('change', function () {
    const info = document.getElementById('file-preview-info');
    const file = this.files?.[0];
    if (!file || !info) return;
    const sizeKB = (file.size / 1024).toFixed(1);
    info.textContent = `File terpilih: ${file.name} (${sizeKB} KB)`;
});

document.getElementById('payment-upload-form')?.addEventListener('submit', function () {
    const btn = document.getElementById('payment-submit-btn');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Mengunggah bukti pembayaran...';
    }
});
</script>
@endpush
