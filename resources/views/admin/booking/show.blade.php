@extends('layouts.admin')
@section('title', 'Detail Booking — ' . $booking->kode_booking)
@section('content')
@php
    $bStatus = $booking->status_booking->value;
    $bBadge = match ($bStatus) {
        'pending' => 'bg-amber-100 text-amber-900 border border-amber-200',
        'confirmed' => 'bg-emerald-100 text-emerald-900 border border-emerald-200',
        'done' => 'bg-slate-100 text-slate-800 border border-slate-200',
        'cancelled' => 'bg-red-100 text-red-900 border border-red-200',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <h1 class="font-mono text-2xl font-black tracking-tight text-[#172033]">{{ $booking->kode_booking }}</h1>
            <span class="status-badge {{ $bBadge }}">
                {{ $booking->status_booking->label() }}
            </span>
        </div>
        <a href="{{ route('admin.booking.index') }}"
            class="rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar Booking
        </a>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        {{-- Kolom Kiri: Data Pelanggan & Jadwal Sesi --}}
        <div class="space-y-5">
            <div class="rounded-lg border border-slate-200 bg-white p-5">
                <h2 class="border-b border-slate-100 pb-3 text-base font-black text-[#172033]">Identitas Pelanggan</h2>
                <dl class="mt-4 space-y-2.5 text-sm">
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Nama Lengkap</dt>
                        <dd class="font-bold text-[#172033]">{{ $booking->nama_pelanggan }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Nomor WhatsApp</dt>
                        <dd class="font-mono font-bold text-[#172033]">{{ $booking->no_hp_pelanggan }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Alamat Email</dt>
                        <dd class="text-[#172033]">{{ $booking->email_pelanggan ?: '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 pt-1">
                        <dt class="text-slate-500">Total Tagihan</dt>
                        <dd class="text-base font-black text-[#7b2631]">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5">
                <h2 class="border-b border-slate-100 pb-3 text-base font-black text-[#172033]">Rincian Sesi & Status Booking</h2>

                <div class="mt-4 space-y-3">
                    @foreach($booking->details as $detail)
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-3.5">
                            <div class="flex items-center justify-between">
                                <strong class="text-sm font-black text-[#172033]">{{ $detail->room->nama_room ?? 'Studio' }}</strong>
                                <span class="rounded border border-slate-200 bg-white px-2 py-0.5 text-xs font-bold text-[#7b2631]">
                                    {{ $detail->jumlah_orang }} Orang
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-600">
                                Paket: {{ $detail->package->tipe_harga ?? '-' }} ({{ $detail->package->durasi_menit ?? '-' }} menit)
                            </p>
                            <p class="mt-1 text-xs font-bold text-[#172033]">
                                {{ $detail->tanggal_sesi?->translatedFormat('d M Y') }} · {{ $detail->jam_mulai }} – {{ $detail->jam_selesai }} WIB
                            </p>
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('admin.booking.status', $booking->id_booking) }}"
                    class="mt-5 border-t border-slate-100 pt-4">
                    @csrf
                    @method('PATCH')
                    <label class="field-label text-xs">Ubah Status Booking Manual</label>
                    <div class="flex gap-2">
                        <select class="field-input text-sm" name="status">
                            <option value="confirmed" @selected($bStatus === 'confirmed')>Dikonfirmasi (Confirmed)</option>
                            <option value="done" @selected($bStatus === 'done')>Selesai (Done)</option>
                            <option value="cancelled" @selected($bStatus === 'cancelled')>Dibatalkan (Cancelled)</option>
                        </select>
                        <button type="submit" class="shrink-0 rounded-md bg-[#172033] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-800">
                            Simpan Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Kolom Kanan: Bukti Pembayaran & Verifikasi --}}
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="border-b border-slate-100 pb-3 text-base font-black text-[#172033]">Bukti Pembayaran QRIS</h2>
            <p class="mt-2 text-xs text-slate-500">Periksa kesesuaian nominal bukti transfer sebelum mengonfirmasi jadwal pelanggan.</p>

            <div class="mt-4 space-y-4">
                @forelse ($booking->payments->sortByDesc('created_at') as $payment)
                    @php
                        $pVal = $payment->status_pembayaran->value;
                        $pBadge = match ($pVal) {
                            'pending' => 'bg-amber-100 text-amber-900 border border-amber-200',
                            'paid' => 'bg-emerald-100 text-emerald-900 border border-emerald-200',
                            'failed' => 'bg-red-100 text-red-900 border border-red-200',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="text-[11px] font-bold uppercase text-slate-500">
                                    Metode: {{ strtoupper($payment->metode_pembayaran) }}
                                </span>
                                <p class="text-base font-black text-[#172033]">
                                    Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}
                                </p>
                            </div>
                            <span class="status-badge {{ $pBadge }}">
                                {{ $payment->status_pembayaran->label() }}
                            </span>
                        </div>

                        @if ($payment->bukti_pembayaran)
                            <div class="mt-3 overflow-hidden rounded-md border border-slate-200 bg-white p-2 text-center">
                                <a href="{{ route('admin.payment.proof', $payment->id_payment) }}" target="_blank" class="block">
                                    <img src="{{ route('admin.payment.proof', $payment->id_payment) }}"
                                        alt="Bukti Pembayaran {{ $booking->kode_booking }}"
                                        class="mx-auto max-h-72 rounded object-contain">
                                </a>
                                <a href="{{ route('admin.payment.proof', $payment->id_payment) }}" target="_blank"
                                    class="mt-2 inline-block text-xs font-bold text-[#163b71] hover:underline">
                                    Perbesar Bukti Pembayaran di Tab Baru <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-[11px]"></i>
                                </a>
                            </div>
                        @endif

                        @if ($payment->verifier)
                            <p class="mt-2.5 text-xs text-slate-500">
                                Diverifikasi oleh: <strong>{{ $payment->verifier->nama }}</strong>
                            </p>
                        @endif

                        @if ($pVal === 'pending')
                            <div class="mt-3.5 flex flex-wrap gap-2 border-t border-slate-200 pt-3.5">
                                <form method="POST" action="{{ route('admin.payment.verify', $payment->id_payment) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="w-full rounded-md bg-emerald-600 px-3.5 py-2.5 text-xs font-bold text-white hover:bg-emerald-700">
                                        <i class="fa-solid fa-check mr-1.5"></i> Terima & Konfirmasi Booking
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.payment.verify', $payment->id_payment) }}"
                                    onsubmit="return confirm('Tolak bukti pembayaran ini? Pelanggan dapat mengunggah ulang bukti baru.');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="rounded-md border border-red-300 bg-red-50 px-3.5 py-2.5 text-xs font-bold text-red-700 hover:bg-red-100">
                                        <i class="fa-solid fa-xmark mr-1.5"></i> Tolak Bukti
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-md border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                        Pelanggan belum mengunggah bukti pembayaran untuk reservasi ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
