@extends('layouts.app')
@section('title', 'Permintaan Edit Foto Terkirim')
@section('content')
<section class="py-14 sm:py-20">
    <div class="t-wrap">
        <div class="mx-auto max-w-xl border border-[var(--line)] bg-[#faf7f1] p-7 sm:p-10">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-[2px] bg-emerald-800 text-base text-white">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                </span>
                <div>
                    <p class="t-label text-emerald-900 font-medium">Layanan Pasca-Sesi Studio</p>
                    <h1 class="t-h2">Permintaan diterima!</h1>
                </div>
            </div>

            <p class="mt-3 t-body text-[#5c5549]">
                Simpan kode di bawah ini untuk memantau progres pengeditan foto &amp; frame Anda.
            </p>

            <div class="mt-6 border border-[var(--line-strong)] ground-surface p-6">
                <p class="t-label text-[#1e1b16]">Kode request edit foto</p>
                <div class="mt-3 flex items-center gap-3">
                    <span id="kode-request-text" class="font-mono text-3xl sm:text-4xl font-semibold tracking-wider text-[#1e1b16] tabular-nums">{{ $editRequest->kode_request }}</span>
                    <button type="button" id="copy-kode-btn" title="Salin Kode Request" aria-label="Salin Kode Request"
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
                    <i class="fa-solid fa-check mr-1" aria-hidden="true"></i> Kode request berhasil disalin!
                </p>
            </div>

            <div class="t-rows mt-6 tabular-nums">
                <div class="t-row">
                    <span class="text-[#5c5549]">Studio</span>
                    <strong class="font-medium text-[#1e1b16]">{{ $editRequest->room?->nama_room }}</strong>
                </div>
                <div class="t-row">
                    <span class="text-[#5c5549]">Jenis Frame</span>
                    <strong class="font-medium text-[#7b2631]">
                        @if ($editRequest->jenis_layanan === 'custom_frame')
                            Custom Frame ({{ $editRequest->jumlah_slot ?? $editRequest->items->count() }} Slot)
                        @else
                            Foto + Frame {{ $editRequest->frame ? '· ' . $editRequest->frame->kode_frame : '' }}
                        @endif
                    </strong>
                </div>
                <div class="t-row">
                    <span class="text-[#5c5549]">Nama Pelanggan</span>
                    <strong class="font-medium text-[#1e1b16]">{{ $editRequest->nama_pelanggan }}</strong>
                </div>
                <div class="t-row">
                    <span class="text-[#5c5549]">Jumlah Slot Foto</span>
                    <strong class="font-medium text-[#1e1b16]">{{ $editRequest->items->count() }} foto</strong>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('edit-foto.status', $editRequest->kode_request) }}"
                    class="t-btn t-btn-primary flex-1">
                    <span>Pantau Status Edit Foto</span>
                    <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                </a>
                <a href="{{ route('edit-foto.form', $editRequest->id_room) }}"
                    class="t-btn t-btn-secondary flex-1">
                    <span>Ajukan Request Lain</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const btn = document.getElementById('copy-kode-btn');
    const code = document.getElementById('kode-request-text')?.textContent?.trim();
    const copyIcon = document.getElementById('copy-icon');
    const copiedIcon = document.getElementById('copied-icon');
    const feedbackMsg = document.getElementById('copy-feedback-msg');

    btn?.addEventListener('click', function () {
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
