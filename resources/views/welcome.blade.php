@extends('layouts.app')
@section('title', 'Self-Photo Studio & Photobooth di Tanjungpinang')
@section('content')
@php
    $rooms = collect();
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('rooms')) {
            $rooms = \App\Models\Room::where('status', 'aktif')
                ->with(['images', 'packages' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('cabang')
                ->orderBy('nama_room')
                ->get();
        }
    } catch (\Throwable $e) {
        $rooms = collect();
    }

    $batuRooms = $rooms->filter(fn ($r) => $r->cabang === 'batu_lima' || str_contains(strtolower((string) $r->cabang), 'batu'))->values();
    $merdekaRooms = $rooms->filter(fn ($r) => $r->cabang === 'jalan_merdeka' || str_contains(strtolower((string) $r->cabang), 'merdeka'))->values();

    $standInPhotos = [
        ['src' => 'assets/img/photo-f39702528-1280.jpg', 'alt' => 'Ruang studio foto mandiri dengan lampu softbox'],
        ['src' => 'assets/img/photostrip-1.jpg', 'alt' => 'Pencahayaan studio potret mandiri'],
        ['src' => 'assets/img/photostrip-2.jpg', 'alt' => 'Diffuser octabox pencahayaan studio potret'],
        ['src' => 'assets/img/photostrip-3.jpg', 'alt' => 'Cahaya hangat di ruang studio foto'],
        ['src' => 'assets/img/photostrip-4.jpg', 'alt' => 'Lembaran cetak photostrip arsip studio'],
        ['src' => 'assets/img/photo-f77363553-960.jpg', 'alt' => 'Lampu studio potret tripod terkalibrasi'],
    ];
@endphp

{{-- =========================================================================
     HERO: THE PHOTOBOOTH DISPENSER & PLAYFUL EDITORIAL MOMENT
     Animasi lembut: foto keluar seperti dari mesin photobooth, strip berputar halus saat hover
     ========================================================================= --}}
<section class="hero pt-6 pb-14 sm:pt-10 sm:pb-20 border-b border-[var(--line)]" data-enter aria-labelledby="hero-title">
    <div class="t-wrap">
        <div class="grid gap-10 lg:grid-cols-12 items-center">

            {{-- LEFT (Cols 1-7): Brand Voice, Heading, CTAs & Micro-facts --}}
            <div class="lg:col-span-7">
                {{-- Floating Sparkle Badge --}}
                <div class="inline-flex items-center gap-2 rounded-full border border-[var(--line-strong)] bg-white px-3.5 py-1 text-xs font-semibold text-[#181715] shadow-2xs">
                    <span class="floating-star text-[#d94b38] text-sm leading-none">✦</span>
                    <span>Your private photobooth &amp; studio in Tanjungpinang</span>
                    <span class="floating-star-rev text-[#d94b38] text-sm leading-none">✦</span>
                </div>

                <h1 id="hero-title" class="t-h1 mt-4">
                    Bebas berekspresi.<br>
                    <span class="text-[#d94b38]">No awkward poses,</span> just good vibes.
                </h1>

                <p class="t-body mt-4 max-w-xl text-[#6b6255]">
                    Self-photo studio &amp; authentic photobooth mandiri tanpa operator. Ambil wireless clicker, atur pose sesukamu bareng bestie or solo, dan bawa pulang cetak photostrip + puluhan file resolusi tinggi di Google Drive.
                </p>

                {{-- Playful Handwritten Note --}}
                <p class="font-hand text-xl text-[#d94b38] mt-2.5 font-bold">
                    <i class="fa-solid fa-arrow-turn-down text-xs mr-1 text-[#d94b38]" aria-hidden="true"></i>
                    kamu yang pegang remote sendiri, full private tanpa dilihat orang!
                </p>

                {{-- CTAs --}}
                <div class="mt-7 flex flex-wrap items-center gap-3.5">
                    <a href="{{ route('booking.wizard') }}" class="t-btn t-btn-primary">
                        <span>Pesan Studio Batu 5</span>
                        <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                    </a>
                    <a href="#pilihan-cabang" class="t-btn t-btn-secondary">
                        <span>Lihat 2 Lokasi Cabang</span>
                        <i class="fa-solid fa-location-dot text-xs text-[#d94b38]" aria-hidden="true"></i>
                    </a>
                </div>

                {{-- Micro-facts / Trust Pills --}}
                <div class="mt-8 flex flex-wrap items-center gap-2.5 pt-4 border-t border-[var(--line)] text-xs text-[#6b6255]">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#f3ede1] px-3 py-1 font-medium text-[#181715]">
                        <i class="fa-solid fa-cloud-arrow-down text-[11px] text-[#d94b38]" aria-hidden="true"></i>
                        ~80 File di Google Drive
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#f3ede1] px-3 py-1 font-medium text-[#181715]">
                        <i class="fa-solid fa-lock text-[11px] text-[#d94b38]" aria-hidden="true"></i>
                        100% Private Studio
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#f3ede1] px-3 py-1 font-medium text-[#181715]">
                        <i class="fa-solid fa-print text-[11px] text-[#d94b38]" aria-hidden="true"></i>
                        Cetak Photostrip Instan
                    </span>
                </div>
            </div>

            {{-- RIGHT (Cols 8-12): DUO PHOTOSTRIP STACK (AUTHENTIC STUDIO TABLETOP SPREAD) --}}
            <div class="lg:col-span-5 flex justify-center items-center">
                <div class="relative w-full max-w-[360px] sm:max-w-[400px]">

                    {{-- Floating Decorative Star Doodles --}}
                    <span class="floating-star absolute -top-4 -left-3 text-2xl text-[#d94b38] select-none pointer-events-none">✦</span>
                    <span class="floating-star-rev absolute -bottom-3 -right-3 text-xl text-[#d94b38] select-none pointer-events-none">✧</span>

                    {{-- Mini Playful Sticky Note Badge --}}
                    <div class="absolute -top-3 right-4 z-10 inline-flex items-center gap-1.5 rounded-full bg-[#f4ede3] border border-[#d8cfc0] px-3 py-1 text-[11px] font-bold text-[#7b2631] shadow-2xs rotate-2">
                        <i class="fa-solid fa-camera text-[10px]" aria-hidden="true"></i>
                        <span>Double Print · 2 Lembar</span>
                    </div>

                    {{-- Photostrip Stack Container --}}
                    <div class="photostrip-stack" aria-label="Contoh hasil cetak photostrip studio">

                        {{-- BACK PHOTOSTRIP (Strip 2 - Peeking at +5deg) --}}
                        <div class="strip-back w-[160px] sm:w-[185px] bg-[#faf6ee] text-[#2b2723] p-2.5 sm:p-3 rounded-sm shadow-md border border-[#e4dcce]"
                             tabindex="0"
                             role="region"
                             aria-label="Photostrip Arsip Studio">
                            {{-- Washi Tape --}}
                            <div class="washi-tape" style="top: -9px; width: 54px; height: 16px;"></div>

                            {{-- Strip Header --}}
                            <div class="pb-1.5 mb-1.5 border-b border-dashed border-[#d8cfc0] flex items-center justify-between text-[8px] font-mono text-[#6b635b]">
                                <span>SERIES #02</span>
                                <span>WALK-IN BOOTH</span>
                            </div>

                            {{-- 3 Photo Cuts --}}
                            <div class="space-y-1.5">
                                <div class="aspect-[4/3] w-full overflow-hidden rounded-[2px] bg-slate-200">
                                    <img src="{{ asset('assets/img/photostrip-4.jpg') }}" alt="Photobooth pose 1" class="h-full w-full object-cover grayscale contrast-110">
                                </div>
                                <div class="aspect-[4/3] w-full overflow-hidden rounded-[2px] bg-slate-200">
                                    <img src="{{ asset('assets/img/photostrip-2.jpg') }}" alt="Photobooth pose 2" class="h-full w-full object-cover grayscale contrast-110">
                                </div>
                                <div class="aspect-[4/3] w-full overflow-hidden rounded-[2px] bg-slate-200">
                                    <img src="{{ asset('assets/img/photostrip-3.jpg') }}" alt="Photobooth pose 3" class="h-full w-full object-cover grayscale contrast-110">
                                </div>
                            </div>

                            {{-- Strip Footer --}}
                            <div class="pt-2 mt-2 border-t border-dashed border-[#d8cfc0] text-center">
                                <p class="font-mono text-[8px] font-bold tracking-wider uppercase text-[#7b2631]">
                                    JL. MERDEKA · KOTA LAMA
                                </p>
                            </div>
                        </div>

                        {{-- FRONT PHOTOSTRIP (Strip 1 - Primary at -4deg) --}}
                        <div class="strip-front w-[170px] sm:w-[195px] bg-white text-[#2b2723] p-2.5 sm:p-3 rounded-sm shadow-xl border border-slate-200"
                             tabindex="0"
                             role="region"
                             aria-label="Photostrip Utama Studio">
                            {{-- Washi Tape --}}
                            <div class="washi-tape" style="top: -9px;"></div>

                            {{-- Strip Header --}}
                            <div class="pb-1.5 mb-1.5 border-b border-dashed border-slate-200 flex items-center justify-between text-[8px] font-mono text-slate-500">
                                <span>SERIES #01</span>
                                <span class="text-[#7b2631] font-bold">PRIVATE STUDIO</span>
                            </div>

                            {{-- 3 Photo Cuts --}}
                            <div class="space-y-1.5">
                                <div class="aspect-[4/3] w-full overflow-hidden rounded-[2px] bg-slate-100">
                                    <img src="{{ asset('assets/img/photostrip-1.jpg') }}" alt="Photobooth snapshot 1" class="h-full w-full object-cover grayscale contrast-110">
                                </div>
                                <div class="aspect-[4/3] w-full overflow-hidden rounded-[2px] bg-slate-100">
                                    <img src="{{ asset('assets/img/photostrip-2.jpg') }}" alt="Photobooth snapshot 2" class="h-full w-full object-cover grayscale contrast-110">
                                </div>
                                <div class="aspect-[4/3] w-full overflow-hidden rounded-[2px] bg-slate-100">
                                    <img src="{{ asset('assets/img/photostrip-3.jpg') }}" alt="Photobooth snapshot 3" class="h-full w-full object-cover grayscale contrast-110">
                                </div>
                            </div>

                            {{-- Strip Footer with Authentic Clicked Picked Logo --}}
                            <div class="pt-2 mt-2 border-t border-dashed border-slate-300 text-center">
                                <img src="{{ asset('assets/img/clicked-picked-logo.png') }}"
                                     alt="Clicked Picked Studios"
                                     class="h-4 sm:h-5 mx-auto object-contain">
                                <p class="font-mono text-[7.5px] sm:text-[8px] tracking-wider uppercase text-slate-400 mt-0.5">
                                    BATU 5 ATAS · TANJUNGPINANG
                                </p>
                            </div>
                        </div>

                    </div>

                    {{-- Subtle hint under the stack --}}
                    <p class="text-center font-hand text-base text-[#6b635b] -mt-2">
                        <span class="text-[#7b2631] font-bold">tap atau hover</span> untuk intip lembaran strip foto!
                    </p>

                </div>
            </div>

        </div>
    </div>
</section>

{{-- =========================================================================
     DUAL BRANCH ARCHITECTURE: CABANG 1 (BATU 5) vs CABANG 2 (JALAN MERDEKA)
     Kejelasan UX: mana yang studio booking online & mana yang photobooth walk-in
     ========================================================================= --}}
<section id="pilihan-cabang" class="py-16 sm:py-24 border-b border-[var(--line)]">
    <div class="t-wrap">
        {{-- Section Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
            <div>
                <div class="inline-flex items-center gap-1.5 text-[#d94b38] text-xs font-semibold uppercase tracking-wider font-mono">
                    <span class="floating-star text-xs">✦</span>
                    <span>Two Spots, Two Vibes</span>
                </div>
                <h2 class="t-h2 mt-1">Dua spot di Tanjungpinang, dua vibe seru.</h2>
            </div>
        </div>

        {{-- 6/6 Split Comparison --}}
        <div class="mt-10 sm:mt-12 grid gap-8 lg:grid-cols-12 items-stretch">

            {{-- CABANG 1: BATU 5 ATAS (Photo Studio — Wajib Booking Online) --}}
            <div class="lg:col-span-6 rounded-3xl border border-[var(--line-strong)] bg-white p-7 sm:p-9 flex flex-col justify-between shadow-xs transition hover:shadow-md">
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-4 border-b border-[var(--line)]">
                        <span class="t-label font-bold text-[#2b2723]">Cabang 1 · Jl. Merdeka, Batu 5 Atas</span>
                        <span class="status-badge bg-[#7b2631] text-white">
                            <i class="fa-solid fa-calendar-check text-[10px]" aria-hidden="true"></i>
                            <span>Bisa Booking Online</span>
                        </span>
                    </div>

                    <h3 class="t-h2 mt-5 text-[#181715]">Cabang Batu 5</h3>
                    <p class="mt-2 t-body text-[#6b6255]">
                        Sesi <strong>Photo Studio</strong> privat dengan lighting terkalibrasi + <strong>Photo Booth</strong> walk-in. Pas banget buat wisuda, couple, besties, or family.
                    </p>

                    {{-- Ruled Rows --}}
                    <div class="t-rows mt-6 tabular-nums">
                        <div class="t-row">
                            <span class="font-medium text-[#181715]">Room 2 &amp; Room 3 (Photo Studio)</span>
                            <span class="font-bold text-[#d94b38]">Wajib Booking Online</span>
                        </div>
                        <div class="t-row">
                            <span class="font-medium text-[#181715]">Room 1 &amp; Room 4 (Photo Booth)</span>
                            <span class="text-[#6b6255]">Langsung Datang (Walk-In)</span>
                        </div>
                        <div class="t-row">
                            <span class="text-[#6b6255]">Kapasitas Studio</span>
                            <span class="font-medium text-[#181715]">Hingga 10 orang</span>
                        </div>
                        <div class="t-row">
                            <span class="text-[#6b6255]">File Digital &amp; Cetak Frame</span>
                            <span class="font-medium text-[#181715]">~80 file Drive + Pilihan Frame</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-4 flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('booking.wizard') }}" class="t-btn t-btn-primary w-full sm:w-auto">
                        <span>Pesan Studio Batu 5</span>
                        <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                    </a>
                    <button type="button" onclick="filterCabang('batu_lima')" class="t-link text-sm cursor-pointer bg-transparent border-0">
                        <span>Lihat ruangan Batu 5 ({{ $batuRooms->count() }})</span>
                        <i class="fa-solid fa-arrow-down text-xs m-arrow" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            {{-- CABANG 2: JALAN MERDEKA (Korean Photobooth — Just Walk In) --}}
            <div class="lg:col-span-6 rounded-3xl border border-[var(--line)] bg-[#f3ede1] p-7 sm:p-9 flex flex-col justify-between transition hover:shadow-md">
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-4 border-b border-[var(--line)]">
                        <span class="t-label font-bold text-[#2b2723]">Cabang 2 · Jl. Merdeka, Kota Lama</span>
                        <span class="status-badge border border-[#7b2631]/30 bg-white text-[#7b2631]">
                            <i class="fa-solid fa-person-walking text-[10px]" aria-hidden="true"></i>
                            <span>Khusus Walk-In</span>
                        </span>
                    </div>

                    <h3 class="t-h2 mt-5 text-[#181715]">Cabang Jalan Merdeka</h3>
                    <p class="mt-2 t-body text-[#6b6255]">
                        Pusat <strong>Korean Photobooth Mandiri</strong>. Lagi nongkrong sore di Kota Lama? Mampir langsung tanpa reservasi. Masuk booth, tap layar PC, and grab your physical strip!
                    </p>

                    {{-- Ruled Rows --}}
                    <div class="t-rows mt-6 tabular-nums">
                        <div class="t-row">
                            <span class="font-medium text-[#181715]">Room 1, Room 2 &amp; Room 3</span>
                            <span class="font-bold text-[#181715]">3 Photo Booth Mandiri</span>
                        </div>
                        <div class="t-row">
                            <span class="text-[#6b6255]">Sistem Masuk</span>
                            <span class="font-medium text-[#181715]">Langsung datang (Tanpa Booking)</span>
                        </div>
                        <div class="t-row">
                            <span class="text-[#6b6255]">Kapasitas per Booth</span>
                            <span class="font-medium text-[#181715]">1 – 5 orang</span>
                        </div>
                        <div class="t-row">
                            <span class="text-[#6b6255]">Pilihan Frame di Komputer</span>
                            <span class="font-medium text-[#181715]">Langsung di layar PC booth</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-4 flex flex-wrap items-center justify-between gap-4">
                    <button type="button" onclick="filterCabang('jalan_merdeka')" class="t-btn t-btn-secondary w-full sm:w-auto">
                        <span>Lihat Booth Merdeka ({{ $merdekaRooms->count() }})</span>
                        <i class="fa-solid fa-arrow-down text-xs m-arrow" aria-hidden="true"></i>
                    </button>
                    <a href="{{ route('studio.index') }}" class="t-link text-sm">
                        <span>Buka katalog lengkap</span>
                        <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- =========================================================================
     RUANGAN & HARGA: KATALOG FOTOSTRIP STYLE
     ========================================================================= --}}
@if ($rooms->isNotEmpty())
<section id="katalog-ruangan" class="py-16 sm:py-24 border-b border-[var(--line)]">
    <div class="t-wrap">
        {{-- Section Head Row --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 pb-6 border-b border-[var(--line)]">
            <div>
                <span class="text-xs font-mono uppercase tracking-wider text-[#d94b38] font-semibold">✦ KATALOG ROOM &amp; BOOTH ✦</span>
                <h2 class="t-h2 mt-1">Pilih spot favoritmu di Batu 5 atau Merdeka.</h2>
            </div>

            {{-- Branch Filter Tabs --}}
            <div class="inline-flex rounded-full border border-[var(--line-strong)] bg-white p-1 self-start md:self-auto" role="group" aria-label="Filter Cabang">
                <button type="button" data-filter-btn="all"
                    onclick="filterCabang('all')"
                    class="filter-tab-btn px-4 py-1.5 text-xs font-semibold rounded-full transition cursor-pointer bg-[#7b2631] text-white">
                    Semua ({{ $rooms->count() }})
                </button>
                <button type="button" data-filter-btn="batu_lima"
                    onclick="filterCabang('batu_lima')"
                    class="filter-tab-btn px-4 py-1.5 text-xs font-medium rounded-full transition cursor-pointer text-[#6b6255] hover:text-[#181715]">
                    Batu 5 ({{ $batuRooms->count() }})
                </button>
                <button type="button" data-filter-btn="jalan_merdeka"
                    onclick="filterCabang('jalan_merdeka')"
                    class="filter-tab-btn px-4 py-1.5 text-xs font-medium rounded-full transition cursor-pointer text-[#6b6255] hover:text-[#181715]">
                    Jalan Merdeka ({{ $merdekaRooms->count() }})
                </button>
            </div>
        </div>

        {{-- Room Grid --}}
        <div id="room-grid" class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($rooms as $idx => $room)
                @php
                    $isBooth = $room->isPhotoBooth();
                    $minPrice = $room->packages->where('is_active', true)->min('harga');
                    $firstImage = $room->images->first()->image_url ?? null;
                    $standIn = $standInPhotos[$idx % count($standInPhotos)];
                    $photoUrl = $firstImage
                        ? (str_starts_with($firstImage, 'http') ? $firstImage : asset('storage/' . $firstImage))
                        : asset($standIn['src']);
                    $photoAlt = $firstImage
                        ? ($room->cabang_label . ' — ' . $room->nama_room)
                        : $standIn['alt'];
                    $targetUrl = $isBooth
                        ? route('studio.show', $room->id_room)
                        : route('booking.wizard', ['room' => $room->id_room]);
                @endphp
                <article class="room-card photostrip-card rounded-3xl bg-white border border-[var(--line)] p-4 flex flex-col justify-between"
                    data-room-cabang="{{ $room->cabang }}"
                    style="--strip-rot: {{ $idx % 2 === 0 ? '-1deg' : '1deg' }};">
                    <div>
                        {{-- Photo Frame --}}
                        <a href="{{ $targetUrl }}" class="m-zoom block aspect-[4/3] w-full rounded-2xl overflow-hidden bg-slate-100 border border-[var(--line)]">
                            <img src="{{ $photoUrl }}"
                                alt="{{ $photoAlt }}"
                                width="960" height="720"
                                loading="lazy" decoding="async">
                        </a>

                        {{-- Details --}}
                        <div class="pt-4 px-2 pb-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="t-label text-[#d94b38]">
                                    {{ $room->cabang_label }} · {{ $room->jenis_room_label }}
                                </span>
                                <span class="status-badge {{ $isBooth ? 'border border-[#7b2631]/30 bg-white text-[#7b2631]' : 'bg-[#7b2631] text-white' }}">
                                    {{ $isBooth ? 'Walk-In' : 'Bisa Booking' }}
                                </span>
                            </div>

                            <h3 class="t-h3 mt-2">
                                <a href="{{ $targetUrl }}" class="hover:text-[#d94b38] transition-colors">
                                    {{ $room->cabang_label }} — {{ $room->nama_room }}
                                </a>
                            </h3>

                            <p class="mt-1.5 line-clamp-2 text-xs leading-relaxed text-[#6b6255]">
                                {{ $room->deskripsi ?: 'Ruang foto privat dengan pencahayaan studio terkalibrasi dan monitor live-view.' }}
                            </p>

                            <div class="t-rows mt-3 tabular-nums text-xs">
                                <div class="t-row py-1.5 min-h-[34px]">
                                    <span class="text-[#6b6255]">Kapasitas</span>
                                    <span class="font-medium text-[#181715]">Maks. {{ $room->kapasitas }} orang</span>
                                </div>
                                <div class="t-row py-1.5 min-h-[34px]">
                                    <span class="text-[#6b6255]">{{ $isBooth ? 'Sistem' : 'Tarif Sesi' }}</span>
                                    <span class="font-bold {{ $isBooth ? 'text-[#181715]' : 'text-[#d94b38]' }}">
                                        {{ $isBooth ? 'Pilih frame di PC booth' : ($minPrice ? 'Mulai Rp ' . number_format($minPrice, 0, ',', '.') : 'Tersedia paket') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 px-2">
                        @if ($isBooth)
                            <a href="{{ route('studio.show', $room->id_room) }}" class="t-btn t-btn-secondary w-full text-xs py-2.5">
                                <span>Lihat Info Booth</span>
                                <i class="fa-solid fa-arrow-right text-[10px] m-arrow" aria-hidden="true"></i>
                            </a>
                        @else
                            <a href="{{ route('booking.wizard', ['room' => $room->id_room]) }}" class="t-btn t-btn-primary w-full text-xs py-2.5">
                                <span>Pesan {{ $room->nama_room }}</span>
                                <i class="fa-solid fa-arrow-right text-[10px] m-arrow" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- =========================================================================
     HOW IT WORKS: 3 LANGKAH SANTAI & NYAMAN
     ========================================================================= --}}
<section class="py-16 sm:py-24">
    <div class="t-wrap">
        <div class="grid gap-10 lg:grid-cols-12 items-center">

            {{-- Left (Cols 1-6): Steps --}}
            <div class="lg:col-span-6">
                <span class="t-label text-[#d94b38]">✦ SIMPLE &amp; ZERO AWKWARDNESS ✦</span>
                <h2 class="t-h2 mt-1">Tiga langkah mudah dari masuk studio hingga bawa pulang cetak.</h2>
                <p class="mt-3 t-body text-[#6b6255]">
                    Tanpa perlu operator foto yang memperhatikan. Kamu dan orang terdekatmu punya kendali penuh atas pose, momen, dan waktu.
                </p>

                <div class="t-rows mt-8">
                    <div class="py-4 border-b border-[var(--line)]">
                        <div class="flex items-baseline justify-between gap-4">
                            <h3 class="t-h3 text-base">01 · Pick Your Spot</h3>
                            <span class="t-label tabular-nums">Online / Walk-In</span>
                        </div>
                        <p class="mt-1 text-sm text-[#6b6255]">
                            Pesan slot online untuk Photo Studio di Cabang Batu 5, atau mampir langsung ke Photobooth Jalan Merdeka kapan saja.
                        </p>
                    </div>

                    <div class="py-4 border-b border-[var(--line)]">
                        <div class="flex items-baseline justify-between gap-4">
                            <h3 class="t-h3 text-base">02 · Grab The Wireless Remote</h3>
                            <span class="t-label tabular-nums">Free Pose</span>
                        </div>
                        <p class="mt-1 text-sm text-[#6b6255]">
                            Kunci pintu ruang privat, putar lagu favoritmu, lihat layar monitor live-view, dan jepret sesukamu dengan remote di tangan.
                        </p>
                    </div>

                    <div class="py-4 border-b border-[var(--line)]">
                        <div class="flex items-baseline justify-between gap-4">
                            <h3 class="t-h3 text-base">03 · Print &amp; Save All Files</h3>
                            <span class="t-label tabular-nums">~80 Digital Files</span>
                        </div>
                        <p class="mt-1 text-sm text-[#6b6255]">
                            Bawa pulang photostrip cetak dan simpan puluhan foto mentah beresolusi tinggi langsung di folder Google Drive Anda.
                        </p>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="{{ route('booking.wizard') }}" class="t-btn t-btn-primary">
                        <span>Pesan Sesi Studio Sekarang</span>
                        <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('check-status') }}" class="t-link text-sm">
                        <span>Lacak booking / request edit</span>
                        <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

            {{-- Right (Cols 7-12): Photostrip Collage Visual with data-moment="picture-develops" --}}
            <div class="lg:col-span-6 flex justify-center" data-moment="picture-develops">
                <div class="relative w-full max-w-[420px] rounded-3xl bg-[#f3ede1] p-6 border border-[var(--line-strong)]">
                    <div class="flex items-center justify-between pb-3 border-b border-[var(--line)]">
                        <span class="photobooth-stamp">
                            <i class="fa-solid fa-heart text-[10px] mr-1" aria-hidden="true"></i> REAL PHOTOSTRIP
                        </span>
                        <span class="font-mono text-xs text-[#6b6255]">EST. 2024 · TANJUNGPINANG</span>
                    </div>

                    {{-- Overlapping Photostrip Layout --}}
                    <div class="mt-6 flex justify-center gap-4">
                        {{-- Strip 1 --}}
                        <div class="photostrip-card w-[150px] bg-white p-2.5 rounded-sm shadow-md" style="--strip-rot: -3deg;">
                            <div class="washi-tape"></div>
                            <div class="space-y-1.5 pt-1">
                                <div class="aspect-square w-full bg-slate-100 overflow-hidden">
                                    <img src="{{ asset('assets/img/photostrip-1.jpg') }}" alt="Shot 1" class="h-full w-full object-cover grayscale">
                                </div>
                                <div class="aspect-square w-full bg-slate-100 overflow-hidden">
                                    <img src="{{ asset('assets/img/photostrip-2.jpg') }}" alt="Shot 2" class="h-full w-full object-cover grayscale">
                                </div>
                            </div>
                            <p class="font-mono text-[8px] text-center text-slate-400 mt-2">CLICKED · PICKED</p>
                        </div>

                        {{-- Strip 2 --}}
                        <div class="photostrip-card w-[150px] bg-white p-2.5 rounded-sm shadow-md" style="--strip-rot: 3deg;">
                            <div class="washi-tape"></div>
                            <div class="space-y-1.5 pt-1">
                                <div class="aspect-square w-full bg-slate-100 overflow-hidden">
                                    <img src="{{ asset('assets/img/photostrip-3.jpg') }}" alt="Shot 3" class="h-full w-full object-cover grayscale">
                                </div>
                                <div class="aspect-square w-full bg-slate-100 overflow-hidden">
                                    <img src="{{ asset('assets/img/photostrip-4.jpg') }}" alt="Shot 4" class="h-full w-full object-cover grayscale">
                                </div>
                            </div>
                            <p class="font-mono text-[8px] text-center text-slate-400 mt-2">BATU 5 &amp; MERDEKA</p>
                        </div>
                    </div>

                    <p class="font-hand text-center text-lg text-[#d94b38] font-bold mt-6">
                        ✦ collect your memories, frame by frame ✦
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function filterCabang(cabang) {
    const cards = document.querySelectorAll('.room-card');
    const buttons = document.querySelectorAll('.filter-tab-btn');

    buttons.forEach(btn => {
        const filterType = btn.getAttribute('data-filter-btn');
        if (filterType === cabang) {
            btn.className = 'filter-tab-btn px-4 py-1.5 text-xs font-semibold rounded-full transition cursor-pointer bg-[#7b2631] text-white';
        } else {
            btn.className = 'filter-tab-btn px-4 py-1.5 text-xs font-medium rounded-full transition cursor-pointer text-[#6b635b] hover:text-[#2b2723]';
        }
    });

    cards.forEach(card => {
        const roomCabang = card.getAttribute('data-room-cabang');
        if (cabang === 'all' || roomCabang === cabang) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });

    const targetSection = document.getElementById('katalog-ruangan');
    if (targetSection && window.scrollY < targetSection.offsetTop - 220) {
        targetSection.scrollIntoView({ behavior: 'smooth' });
    }
}
</script>
@endpush
