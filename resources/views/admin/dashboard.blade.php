@extends('layouts.admin')
@section('title', 'Dashboard Operasional')
@section('content')
@php
    $kpi = [
        'edit_menunggu' => 0,
        'edit_diproses' => 0,
        'edit_selesai' => 0,
        'delivery_failed' => 0,
        'total_requests' => 0,
        'booking_pending' => 0,
        'payment_pending' => 0,
        'booking_today' => 0,
        'studio_aktif' => 0,
        'total_pelanggan' => 0,
    ];
    $actionItems = collect();

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('photo_edit_requests')) {
            $kpi['edit_menunggu'] = \App\Models\PhotoEditRequest::where('status_edit', 'menunggu_verifikasi')->count();
            $kpi['edit_diproses'] = \App\Models\PhotoEditRequest::where('status_edit', 'diproses')->count();
            $kpi['edit_selesai'] = \App\Models\PhotoEditRequest::where('status_edit', 'selesai')->count();
            $kpi['total_requests'] = \App\Models\PhotoEditRequest::count();

            $needActionRequests = \App\Models\PhotoEditRequest::with('room')
                ->whereIn('status_edit', ['menunggu_verifikasi', 'selesai'])
                ->orderBy('created_at', 'asc')
                ->take(8)
                ->get();

            foreach ($needActionRequests as $req) {
                $actionItems->push([
                    'kode' => $req->kode_request,
                    'label' => $req->status_edit->value === 'menunggu_verifikasi' ? 'Perlu Verifikasi Edit Foto' : 'Edit Selesai — Belum Dikirim WA',
                    'sub' => $req->nama_pelanggan . ' · ' . ($req->room?->nama_room ?? 'Studio'),
                    'waktu' => $req->created_at,
                    'badge_class' => $req->status_edit->value === 'menunggu_verifikasi'
                        ? 'bg-amber-50 text-amber-800 border border-amber-200'
                        : 'bg-teal-50 text-teal-800 border border-teal-200',
                    'url' => route('admin.edit-foto.show', $req->id_request),
                ]);
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('photo_deliveries')) {
            $kpi['delivery_failed'] = \App\Models\PhotoDelivery::where('status_pengiriman', 'failed')->count();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('bookings')) {
            $kpi['booking_pending'] = \App\Models\Booking::where('status_booking', 'pending')->count();
            $kpi['total_pelanggan'] = \App\Models\Booking::distinct('no_hp_pelanggan')->count('no_hp_pelanggan');
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
            $kpi['payment_pending'] = \App\Models\Payment::where('status_pembayaran', 'pending')->count();

            $pendingPayments = \App\Models\Payment::with('booking')
                ->where('status_pembayaran', 'pending')
                ->orderBy('created_at', 'asc')
                ->take(5)
                ->get();

            foreach ($pendingPayments as $pmt) {
                if ($pmt->booking) {
                    $actionItems->push([
                        'kode' => $pmt->booking->kode_booking,
                        'label' => 'Verifikasi Bukti Pembayaran',
                        'sub' => $pmt->booking->nama_pelanggan . ' · Rp ' . number_format($pmt->jumlah_bayar, 0, ',', '.'),
                        'waktu' => $pmt->created_at,
                        'badge_class' => 'bg-amber-50 text-amber-800 border border-amber-200',
                        'url' => route('admin.booking.show', $pmt->booking->id_booking),
                    ]);
                }
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('booking_details')) {
            $kpi['booking_today'] = \App\Models\BookingDetail::whereDate('tanggal_sesi', now()->toDateString())
                ->whereHas('booking', fn ($q) => $q->where('status_booking', 'confirmed'))
                ->count();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('rooms')) {
            $kpi['studio_aktif'] = \App\Models\Room::where('status', 'aktif')->count();
        }

        $actionItems = $actionItems->sortBy('waktu')->take(10);
    } catch (\Throwable $e) {}
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-[#7b2631]">Ringkasan Operasional</p>
            <h1 class="mt-0.5 text-2xl font-black tracking-tight text-[#172033]">Dashboard Studio</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.edit-foto.create') }}"
                class="rounded-md border border-[#d6d3c9] bg-white px-3.5 py-2 text-xs font-bold text-[#172033] hover:bg-[#f8f7f2]">
                + Input Request Edit Manual
            </a>
            <a href="{{ route('admin.studio.create') }}"
                class="rounded-md bg-[#172033] px-3.5 py-2 text-xs font-bold text-white hover:bg-[#7b2631]">
                + Tambah Studio
            </a>
        </div>
    </div>

    {{-- Baris 1: KPI Layanan Edit Foto --}}
    <div>
        <h2 class="mb-2.5 text-[11px] font-bold uppercase tracking-wider text-slate-500">Metrik Layanan Edit Foto</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="stat-card">
                <span>Menunggu Verifikasi</span>
                <strong class="text-amber-700">{{ $kpi['edit_menunggu'] }}</strong>
            </div>
            <div class="stat-card">
                <span>Sedang Diproses</span>
                <strong class="text-sky-700">{{ $kpi['edit_diproses'] }}</strong>
            </div>
            <div class="stat-card">
                <span>Selesai (Belum Dikirim)</span>
                <strong class="text-teal-700">{{ $kpi['edit_selesai'] }}</strong>
            </div>
            <div class="stat-card">
                <span>Pengiriman WA Gagal</span>
                <strong class="text-red-600">{{ $kpi['delivery_failed'] }}</strong>
            </div>
            <div class="stat-card">
                <span>Total Request Edit</span>
                <strong>{{ $kpi['total_requests'] }}</strong>
            </div>
        </div>
    </div>

    {{-- Baris 2: KPI Booking & Studio --}}
    <div>
        <h2 class="mb-2.5 text-[11px] font-bold uppercase tracking-wider text-slate-500">Metrik Booking & Studio</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="stat-card">
                <span>Pembayaran Perlu Verifikasi</span>
                <strong class="text-amber-700">{{ $kpi['payment_pending'] }}</strong>
            </div>
            <div class="stat-card">
                <span>Booking Menunggu Bayar</span>
                <strong>{{ $kpi['booking_pending'] }}</strong>
            </div>
            <div class="stat-card">
                <span>Sesi Hari Ini (Confirmed)</span>
                <strong class="text-emerald-700">{{ $kpi['booking_today'] }}</strong>
            </div>
            <div class="stat-card">
                <span>Studio Aktif</span>
                <strong>{{ $kpi['studio_aktif'] }}</strong>
            </div>
            <div class="stat-card">
                <span>Jumlah Pelanggan Unik</span>
                <strong>{{ $kpi['total_pelanggan'] }}</strong>
            </div>
        </div>
    </div>

    {{-- Pintasan Operasional (Tanpa Tombol Buat Booking sesuai PRD) --}}
    <div class="rounded-lg border border-[#dedad0] bg-white p-5">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Pintasan Operasional</h2>
        <div class="mt-3 flex flex-wrap gap-2">
            <a class="rounded-md border border-[#d6d3c9] bg-[#f8f7f2] px-3.5 py-2 text-xs font-bold text-[#172033] transition hover:bg-[#f3f0e8]"
                href="{{ route('admin.edit-foto.index', ['status' => 'menunggu_verifikasi']) }}">
                Request Menunggu Verifikasi ({{ $kpi['edit_menunggu'] }})
            </a>
            <a class="rounded-md border border-[#d6d3c9] bg-[#f8f7f2] px-3.5 py-2 text-xs font-bold text-[#172033] transition hover:bg-[#f3f0e8]"
                href="{{ route('admin.booking.index', ['status' => 'pending']) }}">
                Pembayaran Perlu Verifikasi ({{ $kpi['payment_pending'] }})
            </a>
            <a class="rounded-md border border-[#d6d3c9] bg-[#f8f7f2] px-3.5 py-2 text-xs font-bold text-[#172033] transition hover:bg-[#f3f0e8]"
                href="{{ route('admin.edit-foto.create') }}">
                Input Request Edit Manual
            </a>
            <a class="rounded-md border border-[#d6d3c9] bg-[#f8f7f2] px-3.5 py-2 text-xs font-bold text-[#172033] transition hover:bg-[#f3f0e8]"
                href="{{ route('admin.studio.index') }}">
                Kelola Studio & Unduh QR
            </a>
        </div>
    </div>

    {{-- Daftar Perlu Tindakan (Diurutkan dari yang Paling Lama) --}}
    <div class="overflow-hidden rounded-lg border border-[#dedad0] bg-white">
        <div class="border-b border-[#e3e1db] bg-[#f8f7f2]/70 px-5 py-3.5">
            <h2 class="text-sm font-black text-[#172033]">Daftar Perlu Tindakan</h2>
            <p class="text-xs text-slate-500">Diurutkan dari antrian yang paling lama menunggu respons admin.</p>
        </div>

        @if ($actionItems->isEmpty())
            <div class="p-8 text-center text-sm text-slate-500">
                Semua antrian verifikasi pembayaran dan edit foto telah tertangani.
            </div>
        @else
            <div class="divide-y divide-[#e3e1db]">
                @foreach ($actionItems as $item)
                    <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-3.5 hover:bg-[#f8f7f2]/60">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-sm font-black text-[#172033]">{{ $item['kode'] }}</span>
                                <span class="status-badge {{ $item['badge_class'] }}">
                                    {{ $item['label'] }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-600">{{ $item['sub'] }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="whitespace-nowrap text-xs text-slate-400">{{ $item['waktu']?->diffForHumans() }}</span>
                            <a href="{{ $item['url'] }}"
                                class="whitespace-nowrap rounded-md bg-[#172033] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#7b2631]">
                                Tindak Lanjuti <i class="fa-solid fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
