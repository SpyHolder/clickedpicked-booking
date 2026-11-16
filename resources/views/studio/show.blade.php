@extends('layouts.app')
@section('title', $room->cabang_label . ' — ' . $room->nama_room)
@section('content')
@php
    $isBooth = $room->isPhotoBooth();
    $heroImg = $room->images->first()->image_url ?? null;
    $photoUrl = $heroImg
        ? (str_starts_with($heroImg, 'http') ? $heroImg : asset('storage/' . $heroImg))
        : asset($isBooth ? 'assets/img/photo-f78164006-960.jpg' : 'assets/img/photo-f39702528-1280.jpg');
@endphp

<section class="py-10 sm:py-16">
    <div class="t-wrap">
        <a href="{{ route('studio.index') }}" class="t-link text-sm">
            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
            <span>Kembali ke daftar studio</span>
        </a>

        {{-- Room Header --}}
        <div class="mt-6 flex flex-wrap items-baseline justify-between gap-4 pb-6 border-b border-[var(--line)]">
            <div>
                <p class="t-label text-[#7b2631] font-medium">
                    {{ $room->cabang_label }} · {{ $room->jenis_room_label }}
                </p>
                <h1 class="t-h1 mt-1">
                    {{ $room->cabang_label }} — {{ $room->nama_room }}
                </h1>
            </div>
            <div class="flex items-center gap-2.5 tabular-nums">
                <span class="status-badge {{ $isBooth ? 'bg-[#e7dfcf] text-[#1e1b16]' : 'bg-[#1e1b16] text-[#f4efe5]' }}">
                    {{ $isBooth ? 'Khusus Walk-In' : 'Bisa Booking Online' }}
                </span>
                <span class="status-badge border border-[var(--line-strong)] bg-[#faf7f1] text-[#1e1b16]">
                    Maks. {{ $room->kapasitas }} orang
                </span>
            </div>
        </div>

        {{-- 7/5 Architectural Split: Left Photograph & Specs, Right Packages / Walk-In Panel --}}
        <div class="mt-8 grid gap-10 lg:grid-cols-12 items-start">

            {{-- Left Column (Cols 1-7): Clean Photograph & Ruled Specifications --}}
            <div class="lg:col-span-7 space-y-8">
                <div class="m-zoom border border-[var(--line)] bg-[#e7dfcf]">
                    <img src="{{ $photoUrl }}"
                        alt="{{ $room->cabang_label }} — {{ $room->nama_room }}"
                        width="1200" height="800"
                        class="aspect-[4/3] w-full object-cover">
                </div>

                @if ($room->images->count() > 1)
                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                        @foreach ($room->images as $img)
                            <div class="m-zoom aspect-[4/3] border border-[var(--line)]">
                                <img src="{{ str_starts_with($img->image_url, 'http') ? $img->image_url : asset('storage/' . $img->image_url) }}"
                                    alt="{{ $img->keterangan ?: $room->nama_room }}"
                                    loading="lazy">
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="border-t border-[var(--line)] pt-6">
                    <h2 class="t-h2">Deskripsi dan spesifikasi ruangan</h2>
                    <p class="mt-3 t-body text-[#5c5549]">
                        {{ $room->deskripsi ?: 'Studio nyaman dengan tata cahaya terkalibrasi untuk mengabadikan momen terbaik bersama orang-orang terdekat Anda.' }}
                    </p>

                    <div class="t-rows mt-6 tabular-nums">
                        <div class="t-row">
                            <span class="text-[#5c5549]">Lokasi Cabang</span>
                            <span class="font-medium text-[#1e1b16]">{{ $room->cabang_label }} ({{ $room->cabang === 'jalan_merdeka' ? 'Jl. Merdeka, Kota Lama' : 'Jl. Merdeka, BT 5 Atas' }})</span>
                        </div>
                        <div class="t-row">
                            <span class="text-[#5c5549]">Kategori Ruangan</span>
                            <span class="font-medium text-[#1e1b16]">{{ $room->jenis_room_label }}</span>
                        </div>
                        <div class="t-row">
                            <span class="text-[#5c5549]">Kapasitas Peserta</span>
                            <span class="font-medium text-[#1e1b16]">Hingga {{ $room->kapasitas }} orang</span>
                        </div>
                        <div class="t-row">
                            <span class="text-[#5c5549]">Pemilihan Template Frame</span>
                            <span class="font-medium text-[#1e1b16]">{{ $isBooth ? 'Langsung di layar PC dalam booth' : 'Scan QR katalog frame & akses file Google Drive' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column (Cols 8-12): Packages List or Walk-In Notice on --g-alt surface --}}
            <div class="lg:col-span-5 ground-surface border border-[var(--line)] p-7 sm:p-9">
                @if ($isBooth)
                    <span class="status-badge bg-[#1e1b16] text-[#f4efe5]">
                        <i class="fa-solid fa-person-walking text-[11px]" aria-hidden="true"></i>
                        <span>Khusus Walk-In (Tanpa Booking Online)</span>
                    </span>

                    <h2 class="t-h2 mt-4">Langsung datang ke lokasi</h2>
                    <p class="mt-3 t-body text-[#5c5549]">
                        Untuk <strong>Photo Booth</strong>, sistem pemesanan tidak menggunakan reservasi jadwal online. Silakan langsung datang ke cabang <strong>{{ $room->cabang_label }}</strong> dan pilih frame secara mandiri di layar PC dalam ruangan booth.
                    </p>

                    <div class="t-rows mt-6 tabular-nums">
                        <div class="t-row">
                            <span class="text-[#5c5549]">Senin – Kamis</span>
                            <span class="font-medium text-[#1e1b16]">13.00 – 22.00 WIB</span>
                        </div>
                        <div class="t-row">
                            <span class="text-[#5c5549]">Jumat – Minggu</span>
                            <span class="font-medium text-[#1e1b16]">11.00 – 22.00 WIB</span>
                        </div>
                    </div>

                    <div class="mt-6 border border-[var(--line)] bg-[#faf7f1] p-4 text-sm text-[#5c5549]">
                        <p class="font-semibold text-[#1e1b16]">Ingin booking jadwal Photo Studio?</p>
                        <p class="mt-1">Sistem Booking Online tersedia khusus untuk <strong>Photo Studio (Room 2 &amp; Room 3 Cabang Batu 5)</strong>.</p>
                    </div>

                    <a href="{{ route('booking.wizard') }}" class="t-btn t-btn-primary w-full mt-6">
                        <span>Pesan Photo Studio Batu 5</span>
                        <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                    </a>
                @else
                    <p class="t-label text-[#7b2631] font-medium">Pilihan paket sesi · {{ $room->nama_room }}</p>
                    <h2 class="t-h2 mt-1">Paket dan durasi sesi</h2>
                    <p class="mt-2 text-sm text-[#5c5549]">
                        Pilih salah satu paket di bawah untuk melanjutkan ke penguncian jadwal sesi online.
                    </p>

                    <div class="mt-6 space-y-4">
                        @forelse ($room->packages->where('is_active', true) as $package)
                            <div class="t-card-link bg-[#faf7f1] p-5"
                                data-package-id="{{ $package->id_package }}"
                                data-package-name="{{ $package->tipe_harga }} ({{ $package->durasi_menit }} menit) - Rp {{ number_format($package->harga, 0, ',', '.') }}">
                                <div class="flex items-baseline justify-between gap-4 border-b border-[var(--line)] pb-3 tabular-nums">
                                    <div>
                                        <span class="t-label font-medium text-[#7b2631]">Durasi {{ $package->durasi_menit }} menit</span>
                                        <h3 class="t-h3 mt-0.5">{{ $package->tipe_harga }}</h3>
                                    </div>
                                    <div class="text-right">
                                        <span class="t-label">Tarif</span>
                                        <p class="text-xl font-semibold text-[#1e1b16]">
                                            Rp {{ number_format($package->harga, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                @if ($package->deskripsi_benefit)
                                    <p class="mt-3 text-sm leading-relaxed text-[#5c5549]">
                                        {{ $package->deskripsi_benefit }}
                                    </p>
                                @endif

                                <a href="{{ route('booking.wizard', ['room' => $room->id_room, 'package' => $package->id_package]) }}"
                                    class="t-btn t-btn-secondary w-full mt-4 text-sm min-h-[42px]">
                                    <span>Pilih Paket Ini</span>
                                    <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                                </a>
                            </div>
                        @empty
                            <div class="border border-[var(--line)] bg-[#faf7f1] p-6 text-center text-sm text-[#5c5549]">
                                Paket belum tersedia untuk studio ini.
                            </div>
                        @endforelse
                    </div>

                    <a href="{{ route('booking.wizard', ['room' => $room->id_room]) }}"
                        class="t-btn t-btn-primary w-full mt-6">
                        <span>Pesan {{ $room->nama_room }} Sekarang</span>
                        <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                    </a>
                @endif
            </div>

        </div>
    </div>
</section>
@endsection
