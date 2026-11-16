@extends('layouts.app')
@section('title', 'Cek Status Booking & Edit Foto')
@section('content')
<section class="py-14 sm:py-20">
    <div class="t-wrap">
        <div class="mx-auto max-w-xl border border-[var(--line)] bg-[#faf7f1] p-7 sm:p-10">
            <p class="t-label text-[#7b2631] font-medium">Pelacakan Pesanan &amp; Kurasi Frame</p>
            <h1 class="t-h2 mt-1">Cek status pesanan Anda</h1>
            <p class="mt-2 t-body text-[#5c5549]">
                Masukkan kode booking studio (<span class="font-mono text-sm">BK-...</span>) atau kode permintaan edit foto (<span class="font-mono text-sm">ED-...</span>).
            </p>

            <form method="POST" action="{{ route('check-status.process') }}" class="mt-7 space-y-5">
                @csrf
                <div>
                    <label for="kode" class="field-label">Kode booking atau kode request edit</label>
                    <input id="kode" class="field-input font-mono text-base uppercase tracking-wider"
                        name="kode" value="{{ old('kode') }}"
                        placeholder="Contoh: BK-1A2B3C4D atau ED-9F8E7D6C" required>
                    @error('kode')
                        <p class="field-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button class="t-btn t-btn-primary w-full" type="submit">
                    <span>Lacak Status Sekarang</span>
                    <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
