@extends('layouts.app')
@section('title', 'Status Edit Foto — ' . $editRequest->kode_request)
@section('content')
@php
    $statusValue = $editRequest->status_edit->value;
    $badgeClasses = match ($statusValue) {
        'menunggu_verifikasi' => 'bg-amber-100 text-amber-950 border border-amber-700/25',
        'pending' => 'bg-[#e7dfcf] text-[#1e1b16]',
        'diproses' => 'bg-[#1e1b16] text-[#f4efe5]',
        'selesai' => 'bg-emerald-800 text-white',
        'dikirim' => 'bg-emerald-800 text-white',
        'ditolak' => 'bg-red-800 text-white',
        default => 'bg-[#e7dfcf] text-[#1e1b16]',
    };

    $customerStatusText = match ($statusValue) {
        'menunggu_verifikasi' => 'Permintaan diterima, sedang diperiksa admin',
        'pending' => 'Menunggu giliran diedit',
        'diproses' => 'Sedang diedit & dipasang ke frame',
        'selesai' => 'Edit selesai, hasil segera dikirim',
        'dikirim' => 'Hasil sudah dikirim ke WhatsApp Anda',
        'ditolak' => 'Permintaan tidak dapat diproses',
        default => $editRequest->status_edit->label(),
    };

    $steps = [
        ['key' => 'menunggu_verifikasi', 'title' => 'Permintaan diterima, sedang diperiksa admin'],
        ['key' => 'pending', 'title' => 'Menunggu giliran diedit'],
        ['key' => 'diproses', 'title' => 'Sedang diedit & dipasang ke frame'],
        ['key' => 'selesai', 'title' => 'Edit selesai, hasil segera dikirim'],
        ['key' => 'dikirim', 'title' => 'Hasil sudah dikirim ke WhatsApp Anda'],
    ];
    $orderMap = array_flip(array_column($steps, 'key'));
    $currentOrder = $orderMap[$statusValue] ?? -1;
@endphp

<section class="py-12 sm:py-16">
    <div class="t-wrap">
        <div class="mx-auto max-w-2xl border border-[var(--line)] bg-[#faf7f1] p-7 sm:p-10">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-[var(--line)] pb-5">
                <div>
                    <p class="t-label text-[#7b2631] font-medium">Kode Request Edit Foto &amp; Frame</p>
                    <h1 class="mt-1 font-mono text-2xl sm:text-3xl font-semibold text-[#1e1b16] tabular-nums">{{ $editRequest->kode_request }}</h1>
                </div>
                <span class="status-badge {{ $badgeClasses }}">
                    {{ $editRequest->status_edit->label() }}
                </span>
            </div>

            <div class="mt-6 border border-[var(--line)] ground-surface p-5">
                <p class="t-label text-[#7b2631] font-medium">Status saat ini</p>
                <p class="mt-1 text-lg font-semibold text-[#1e1b16]">{{ $customerStatusText }}</p>

                @if ($statusValue === 'ditolak' && $editRequest->alasan_penolakan)
                    <div class="mt-3 border border-red-700/30 bg-red-50 p-3.5 text-sm text-red-900">
                        <strong>Alasan penolakan:</strong> {{ $editRequest->alasan_penolakan }}
                    </div>
                @endif
            </div>

            {{-- Garis Waktu Status --}}
            @if ($statusValue !== 'ditolak')
                <div class="mt-7">
                    <h2 class="t-label text-[#1e1b16] font-semibold">Progres pengerjaan</h2>
                    <ol class="mt-4 space-y-3 tabular-nums">
                        @foreach ($steps as $idx => $step)
                            @php
                                $isPassed = $idx < $currentOrder;
                                $isCurrent = $idx === $currentOrder;
                            @endphp
                            <li class="flex items-center gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold
                                    {{ $isPassed ? 'bg-emerald-800 text-white' : ($isCurrent ? 'bg-[#1e1b16] text-[#f4efe5] ring-2 ring-[#7b2631]' : 'border border-[var(--line-strong)] bg-white text-[#5c5549]') }}">
                                    @if ($isPassed)
                                        <i class="fa-solid fa-check text-[10px]" aria-hidden="true"></i>
                                    @else
                                        {{ $idx + 1 }}
                                    @endif
                                </span>
                                <span class="text-sm {{ $isCurrent ? 'font-semibold text-[#1e1b16]' : ($isPassed ? 'font-medium text-[#1e1b16]' : 'text-[#5c5549]') }}">
                                    {{ $step['title'] }}
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            {{-- Detail Ringkas (Tanpa Link Drive Eksternal, Nomor WA Disamarkan) --}}
            <div class="t-rows mt-7 tabular-nums">
                <div class="t-row">
                    <span class="text-[#5c5549]">Studio</span>
                    <strong class="font-medium text-[#1e1b16]">{{ $editRequest->room?->nama_room }}</strong>
                </div>
                <div class="t-row">
                    <span class="text-[#5c5549]">Jenis Layanan &amp; Frame</span>
                    <strong class="font-medium text-[#7b2631]">
                        @if ($editRequest->jenis_layanan === 'custom_frame')
                            Custom Frame ({{ $editRequest->jumlah_slot ?? $editRequest->items->count() }} Slot)
                        @else
                            Foto + Frame {{ $editRequest->frame ? '· ' . $editRequest->frame->kode_frame . ' (' . $editRequest->frame->nama_frame . ')' : '' }}
                        @endif
                    </strong>
                </div>
                <div class="t-row">
                    <span class="text-[#5c5549]">Nama Pelanggan</span>
                    <strong class="font-medium text-[#1e1b16]">{{ $editRequest->nama_pelanggan }}</strong>
                </div>
                <div class="t-row">
                    <span class="text-[#5c5549]">Nomor WhatsApp</span>
                    <strong class="font-mono font-medium text-[#1e1b16]">{{ $maskedPhone }}</strong>
                </div>
                <div class="t-row">
                    <span class="text-[#5c5549]">Waktu Pengajuan</span>
                    <strong class="font-medium text-[#1e1b16]">{{ $editRequest->created_at?->translatedFormat('d M Y, H:i') }} WIB</strong>
                </div>
            </div>

            {{-- Daftar Kode Foto per Slot yang Diminta --}}
            <div class="mt-7">
                <h2 class="t-label text-[#1e1b16] font-semibold">Daftar kode foto per slot ({{ $editRequest->items->count() }} slot)</h2>
                <div class="mt-3 divide-y divide-[var(--line)] border border-[var(--line)] bg-white tabular-nums">
                    @foreach ($editRequest->items as $idx => $item)
                        <div class="flex items-center gap-3 p-3.5 text-sm">
                            <span class="rounded-[2px] bg-[#1e1b16] px-2 py-0.5 text-xs font-medium text-[#f4efe5]">Slot #{{ $idx + 1 }}</span>
                            <div>
                                <p class="font-semibold text-[#1e1b16]">{{ $item->nama_file }}</p>
                                @if ($item->instruksi_edit)
                                    <p class="mt-0.5 text-xs text-[#5c5549]">Instruksi: {{ $item->instruksi_edit }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="mt-6 text-xs text-[#5c5549]">
                Demi privasi Anda, tautan unduhan hasil edit foto hanya dikirimkan langsung ke nomor WhatsApp terdaftar.
            </p>
        </div>
    </div>
</section>
@endsection
