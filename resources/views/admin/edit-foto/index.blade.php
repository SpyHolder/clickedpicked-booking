@extends('layouts.admin')
@section('title', 'Antrian Edit Foto')
@section('content')
@php
    $currentStatus = $filters['status'] ?? '';
    $statusTabs = [
        '' => 'Semua',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'pending' => 'Antre',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'dikirim' => 'Terkirim',
        'ditolak' => 'Ditolak',
    ];
@endphp

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-[#172033]">Antrian Edit Foto & Frame</h1>
        <p class="mt-0.5 text-xs text-slate-500">Kelola verifikasi kode foto Google Drive, pengerjaan Foto + Frame / Custom Frame, dan pengiriman hasil edit.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.frames.index') }}"
            class="rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-[#172033] hover:bg-slate-50">
            Kelola Katalog Frame
        </a>
        <a href="{{ route('admin.edit-foto.create') }}"
            class="rounded-md bg-[#172033] px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-800">
            + Input Request Manual
        </a>
    </div>
</div>

{{-- Unified Table Card: Tabs + Filter Toolbar + Data Table --}}
<div class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-white">

    {{-- Underline Status Tabs --}}
    <div class="flex gap-1 overflow-x-auto border-b border-slate-200 bg-white px-4 pt-2">
        @foreach ($statusTabs as $key => $label)
            <a href="{{ route('admin.edit-foto.index', array_merge($filters, ['status' => $key])) }}"
                class="whitespace-nowrap border-b-2 px-3.5 py-2.5 text-xs font-bold transition
                {{ $currentStatus === $key
                    ? 'border-[#172033] text-[#172033]'
                    : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Compact Filter Toolbar --}}
    <form method="GET" action="{{ route('admin.edit-foto.index') }}"
        class="flex flex-wrap items-center gap-2.5 border-b border-slate-200 bg-slate-50/80 p-3">
        <input type="hidden" name="status" value="{{ $currentStatus }}">

        <div class="min-w-[220px] flex-1">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-xs text-[#172033] placeholder:text-slate-400 focus:border-[#172033] focus:outline-none"
                placeholder="Cari kode ED-xxx, nama, atau nomor WA...">
        </div>

        <select name="id_room"
            class="h-9 rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-[#172033] focus:border-[#172033] focus:outline-none">
            <option value="">Semua Studio</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id_room }}" @selected(($filters['id_room'] ?? '') == $room->id_room)>
                    {{ $room->nama_room }}
                </option>
            @endforeach
        </select>

        <select name="sumber"
            class="h-9 rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-[#172033] focus:border-[#172033] focus:outline-none">
            <option value="">Semua Sumber</option>
            <option value="qr" @selected(($filters['sumber'] ?? '') === 'qr')>QR Studio</option>
            <option value="admin" @selected(($filters['sumber'] ?? '') === 'admin')>Input Manual</option>
        </select>

        <input type="date" name="tanggal_dari" value="{{ $filters['tanggal_dari'] ?? '' }}"
            class="h-9 rounded-md border border-slate-300 bg-white px-3 text-xs text-[#172033] focus:border-[#172033] focus:outline-none"
            title="Dari tanggal">

        <button type="submit"
            class="h-9 rounded-md bg-[#172033] px-4 text-xs font-bold text-white transition hover:bg-slate-800">
            Terapkan Filter
        </button>

        @if (!empty($filters['q']) || !empty($filters['id_room']) || !empty($filters['sumber']) || !empty($filters['tanggal_dari']) || !empty($filters['status']))
            <a href="{{ route('admin.edit-foto.index') }}"
                class="inline-flex h-9 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                Reset
            </a>
        @endif
    </form>

    {{-- Data Table --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="whitespace-nowrap px-4 py-3">Kode Request</th>
                    <th class="whitespace-nowrap px-4 py-3">Pelanggan</th>
                    <th class="whitespace-nowrap px-4 py-3">Studio</th>
                    <th class="whitespace-nowrap px-4 py-3">Layanan & Frame</th>
                    <th class="whitespace-nowrap px-4 py-3">Status</th>
                    <th class="whitespace-nowrap px-4 py-3">Sumber</th>
                    <th class="whitespace-nowrap px-4 py-3">Admin</th>
                    <th class="whitespace-nowrap px-4 py-3">Waktu Dibuat</th>
                    <th class="whitespace-nowrap px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($requests as $req)
                    @php
                        $st = $req->status_edit->value;
                        $badgeColor = match ($st) {
                            'menunggu_verifikasi' => 'bg-amber-100 text-amber-900 border border-amber-200',
                            'pending' => 'bg-slate-100 text-slate-800 border border-slate-200',
                            'diproses' => 'bg-sky-100 text-sky-900 border border-sky-200',
                            'selesai' => 'bg-teal-100 text-teal-900 border border-teal-200',
                            'dikirim' => 'bg-emerald-100 text-emerald-900 border border-emerald-200',
                            'ditolak' => 'bg-red-100 text-red-900 border border-red-200',
                            default => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/70">
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-black text-[#172033]">{{ $req->kode_request }}</span>
                                @if ($req->perlu_dicek)
                                    <span class="inline-flex items-center whitespace-nowrap rounded border border-amber-300 bg-amber-50 px-1.5 py-0.5 text-[11px] font-bold text-amber-800" title="Tidak ada booking cocok otomatis">
                                        <i class="fa-solid fa-triangle-exclamation mr-1 text-amber-700"></i> Perlu dicek
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            <span class="block font-bold text-[#172033]">{{ $req->nama_pelanggan }}</span>
                            <span class="block font-mono text-xs text-slate-500">{{ $req->nomor_wa }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle font-semibold text-[#172033]">
                            {{ $req->room?->nama_room ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            @if ($req->jenis_layanan === 'custom_frame')
                                <span class="inline-flex items-center rounded border border-[#7b2631]/30 bg-[#7b2631]/10 px-2 py-0.5 text-[11px] font-bold text-[#7b2631]">
                                    Custom Frame ({{ $req->jumlah_slot ?? 2 }} Slot)
                                </span>
                            @else
                                <span class="inline-flex items-center rounded border border-slate-200 bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-[#172033]">
                                    Foto + Frame {{ $req->frame ? '· ' . $req->frame->kode_frame . ' (' . $req->frame->jumlah_slot . ' Slot)' : '' }}
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            <span class="status-badge {{ $badgeColor }}">
                                {{ $req->status_edit->label() }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            <span class="inline-flex items-center rounded border border-slate-200 bg-slate-100 px-2 py-0.5 text-[11px] font-bold uppercase text-slate-700">
                                {{ $req->sumber->value }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle text-xs text-slate-600">
                            {{ $req->handler?->nama ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle text-xs text-slate-500">
                            {{ $req->created_at?->translatedFormat('d M Y, H:i') }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-right align-middle">
                            <a href="{{ route('admin.edit-foto.show', $req->id_request) }}"
                                class="inline-flex items-center rounded-md bg-[#172033] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-slate-800">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-sm text-slate-500">
                            Belum ada permintaan edit foto yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($requests->hasPages())
        <div class="border-t border-slate-200 px-4 py-3">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
