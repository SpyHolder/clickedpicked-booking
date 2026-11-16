@extends('layouts.admin')
@section('title', 'Kelola Booking')
@section('content')
@php
    $statusTabs = [
        '' => 'Semua Booking',
        'pending' => 'Menunggu',
        'confirmed' => 'Dikonfirmasi',
        'done' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
@endphp

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-[#172033]">Kelola Booking Studio</h1>
        <p class="mt-0.5 text-xs text-slate-500">Daftar reservasi jadwal studio dan verifikasi bukti pembayaran pelanggan.</p>
    </div>
</div>

{{-- Unified Table Card --}}
<div class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-white">

    {{-- Underline Status Tabs --}}
    <div class="flex gap-1 overflow-x-auto border-b border-slate-200 bg-white px-4 pt-2">
        @foreach ($statusTabs as $key => $label)
            <a href="{{ route('admin.booking.index', $key ? ['status' => $key] : []) }}"
                class="whitespace-nowrap border-b-2 px-3.5 py-2.5 text-xs font-bold transition
                {{ ($currentStatus ?? '') === $key
                    ? 'border-[#172033] text-[#172033]'
                    : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="whitespace-nowrap px-4 py-3">Kode Booking</th>
                    <th class="whitespace-nowrap px-4 py-3">Pelanggan</th>
                    <th class="whitespace-nowrap px-4 py-3">Studio & Jadwal Sesi</th>
                    <th class="whitespace-nowrap px-4 py-3">Total Tagihan</th>
                    <th class="whitespace-nowrap px-4 py-3">Status Booking</th>
                    <th class="whitespace-nowrap px-4 py-3">Pembayaran</th>
                    <th class="whitespace-nowrap px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($bookings as $booking)
                    @php
                        $bStatus = $booking->status_booking->value;
                        $bBadge = match ($bStatus) {
                            'pending' => 'bg-amber-100 text-amber-900 border border-amber-200',
                            'confirmed' => 'bg-emerald-100 text-emerald-900 border border-emerald-200',
                            'done' => 'bg-slate-100 text-slate-800 border border-slate-200',
                            'cancelled' => 'bg-red-100 text-red-900 border border-red-200',
                            default => 'bg-slate-100 text-slate-700',
                        };
                        $firstDetail = $booking->details->first();
                        $latestPayment = $booking->payments->sortByDesc('created_at')->first();
                        $pStatus = $latestPayment?->status_pembayaran?->value;
                        $pBadge = match ($pStatus) {
                            'pending' => 'bg-amber-100 text-amber-900 border border-amber-200',
                            'paid' => 'bg-emerald-100 text-emerald-900 border border-emerald-200',
                            'failed' => 'bg-red-100 text-red-900 border border-red-200',
                            default => 'bg-slate-100 text-slate-500',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/70">
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle font-mono text-xs font-black text-[#172033]">
                            {{ $booking->kode_booking }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            <span class="block font-bold text-[#172033]">{{ $booking->nama_pelanggan }}</span>
                            <span class="block font-mono text-xs text-slate-500">{{ $booking->no_hp_pelanggan }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            @if ($firstDetail)
                                <span class="block font-bold text-[#172033]">{{ $firstDetail->room?->nama_room ?? 'Studio' }}</span>
                                <span class="block text-xs text-slate-500">
                                    {{ $firstDetail->tanggal_sesi?->format('d M Y') }} · {{ $firstDetail->jam_mulai }} - {{ $firstDetail->jam_selesai }}
                                </span>
                            @else
                                <span class="text-xs text-slate-500">{{ $booking->tanggal_booking?->format('d M Y') }}</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle font-bold text-[#7b2631]">
                            Rp {{ number_format($booking->total_harga, 0, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            <span class="status-badge {{ $bBadge }}">
                                {{ $booking->status_booking->label() }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            @if ($latestPayment)
                                <span class="status-badge {{ $pBadge }}">
                                    {{ $latestPayment->status_pembayaran->label() }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400">Belum Unggah</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-right align-middle">
                            <a class="inline-flex items-center rounded-md bg-[#172033] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-slate-800"
                                href="{{ route('admin.booking.show', $booking->id_booking) }}">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                            Belum ada data reservasi studio.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if (method_exists($bookings, 'hasPages') && $bookings->hasPages())
        <div class="border-t border-slate-200 px-4 py-3">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection
