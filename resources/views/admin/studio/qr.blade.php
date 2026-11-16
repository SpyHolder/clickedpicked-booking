@extends('layouts.admin')
@section('title', 'QR Studio — ' . $room->nama_room)
@section('content')
@php
    $qrTargetUrl = route('edit-foto.form', $room->id_room);
@endphp
<div class="mx-auto max-w-2xl space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-[#172033]">QR Code Edit Foto</h1>
            <p class="mt-0.5 text-xs text-slate-500">Kartu QR khusus untuk meja {{ $room->nama_room }}.</p>
        </div>
        <a href="{{ route('admin.studio.index') }}" class="rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div id="printable-qr-card" class="rounded-lg border border-slate-200 bg-white p-8 text-center">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#7b2631]">Clicked Picked Studios · {{ $room->cabang_label }}</p>
        <h2 class="mt-1 text-2xl font-black text-[#172033]">{{ $room->nama_room }} ({{ $room->jenis_room_label }})</h2>
        <p class="mt-1 text-xs text-slate-500">Pindai QR di bawah ini untuk mengisi formulir pemilihan & edit foto</p>

        <div class="mx-auto my-6 flex h-60 w-60 items-center justify-center rounded-lg border border-slate-200 bg-white p-4">
            <svg viewBox="0 0 200 200" class="h-full w-full" role="img" aria-label="QR Code {{ $room->nama_room }}">
                <rect width="200" height="200" fill="#ffffff"/>
                {{-- Finder Patterns --}}
                <rect x="15" y="15" width="50" height="50" fill="none" stroke="#172033" stroke-width="8"/>
                <rect x="27" y="27" width="26" height="26" fill="#172033"/>
                <rect x="135" y="15" width="50" height="50" fill="none" stroke="#172033" stroke-width="8"/>
                <rect x="147" y="27" width="26" height="26" fill="#172033"/>
                <rect x="15" y="135" width="50" height="50" fill="none" stroke="#172033" stroke-width="8"/>
                <rect x="27" y="147" width="26" height="26" fill="#172033"/>
                {{-- Data Modules --}}
                <rect x="80" y="20" width="12" height="12" fill="#172033"/>
                <rect x="100" y="20" width="12" height="12" fill="#172033"/>
                <rect x="80" y="40" width="20" height="12" fill="#172033"/>
                <rect x="20" y="80" width="12" height="20" fill="#172033"/>
                <rect x="44" y="85" width="16" height="16" fill="#172033"/>
                <rect x="75" y="75" width="50" height="50" rx="4" fill="#f3f0e8" stroke="#172033" stroke-width="4"/>
                <text x="100" y="104" text-anchor="middle" font-size="14" font-weight="bold" fill="#172033">ID #{{ $room->id_room }}</text>
                <rect x="140" y="80" width="16" height="16" fill="#172033"/>
                <rect x="165" y="95" width="16" height="24" fill="#172033"/>
                <rect x="80" y="140" width="16" height="16" fill="#172033"/>
                <rect x="105" y="150" width="20" height="12" fill="#172033"/>
                <rect x="140" y="140" width="35" height="35" fill="none" stroke="#172033" stroke-width="6"/>
                <rect x="152" y="152" width="11" height="11" fill="#172033"/>
            </svg>
        </div>

        <div class="mx-auto max-w-md rounded-md border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700">
            <span class="block font-bold text-slate-500">URL Formulir Edit Foto:</span>
            <code id="qr-url-text" class="mt-1 block break-all font-mono text-xs font-bold text-[#172033]">{{ $qrTargetUrl }}</code>
        </div>

        <div class="mt-6 flex flex-wrap justify-center gap-2.5 print:hidden">
            <button type="button" onclick="window.print()" class="rounded-md bg-[#172033] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-800">
                Cetak QR Studio
            </button>
            <button type="button" id="copy-qr-url" class="rounded-md border border-slate-300 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                Salin URL
            </button>
            <a href="{{ $qrTargetUrl }}" target="_blank" class="rounded-md bg-[#cbbb9e] px-4 py-2.5 text-xs font-bold text-[#172033] hover:bg-[#b9a688]">
                Buka Form Edit Foto <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-[10px]"></i>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('copy-qr-url')?.addEventListener('click', function () {
    const url = document.getElementById('qr-url-text')?.textContent?.trim();
    if (!url) return;
    navigator.clipboard?.writeText(url);
    this.textContent = 'URL Tersalin!';
    setTimeout(() => { this.textContent = 'Salin URL'; }, 2000);
});
</script>
@endpush
