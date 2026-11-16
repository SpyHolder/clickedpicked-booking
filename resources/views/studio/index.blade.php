@extends('layouts.app')
@section('title', 'Pilih Studio & Photo Booth')
@section('content')
@php
    $batuRooms = $rooms->filter(fn ($r) => $r->cabang === 'batu_lima' || str_contains(strtolower((string) $r->cabang), 'batu'))->values();
    $merdekaRooms = $rooms->filter(fn ($r) => $r->cabang === 'jalan_merdeka' || str_contains(strtolower((string) $r->cabang), 'merdeka'))->values();

    $standInPhotos = [
        ['src' => 'assets/img/photo-f19490668-960.jpg', 'alt' => 'Pencahayaan softbox studio fotografi terkalibrasi'],
        ['src' => 'assets/img/photo-f78164006-960.jpg', 'alt' => 'Diffuser octabox pencahayaan potret studio'],
        ['src' => 'assets/img/photo-f77363553-960.jpg', 'alt' => 'Panel lampu studio di atas tripod'],
        ['src' => 'assets/img/photo-d88-960.jpg', 'alt' => 'Cahaya hangat di ruang tunggu studio'],
        ['src' => 'assets/img/photo-c823-960.jpg', 'alt' => 'Lembaran kertas cetak foto matte arsip studio'],
        ['src' => 'assets/img/photo-f39702528-960.jpg', 'alt' => 'Tata ruang kamera dan layar monitor self-photo studio'],
    ];
@endphp

<section class="py-12 sm:py-16">
    <div class="t-wrap">
        {{-- Left-Aligned Editorial Header (layout-v4.md Section 4: Two text lines) --}}
        <div class="grid gap-6 lg:grid-cols-12 items-end pb-8 border-b border-[var(--line)]">
            <div class="lg:col-span-7">
                <p class="t-label">Katalog Ruangan · Clicked Picked Studios</p>
                <h1 class="t-h1 mt-2">
                    Daftar photo studio dan photo booth.
                </h1>
            </div>
            <div class="lg:col-span-5">
                <p class="t-body text-[#5c5549]">
                    Reservasi online berlaku untuk <strong>Photo Studio Cabang Batu 5</strong>. Seluruh <strong>Photo Booth</strong> di Batu 5 dan Jalan Merdeka melayani kunjungan langsung (<em>Walk-In</em>).
                </p>
            </div>
        </div>

        {{-- Filter Bar by Branch --}}
        <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
            <div class="inline-flex border border-[var(--line-strong)] bg-[#faf7f1] p-1" role="group" aria-label="Filter Cabang Studio">
                <button type="button" data-filter-btn="all"
                    onclick="filterStudioCabang('all')"
                    class="studio-filter-btn px-4 py-2 text-sm font-semibold transition cursor-pointer bg-[#1e1b16] text-[#f4efe5]">
                    Semua Ruangan ({{ $rooms->count() }})
                </button>
                <button type="button" data-filter-btn="batu_lima"
                    onclick="filterStudioCabang('batu_lima')"
                    class="studio-filter-btn px-4 py-2 text-sm font-medium transition cursor-pointer text-[#5c5549] hover:text-[#1e1b16]">
                    Cabang Batu 5 ({{ $batuRooms->count() }})
                </button>
                <button type="button" data-filter-btn="jalan_merdeka"
                    onclick="filterStudioCabang('jalan_merdeka')"
                    class="studio-filter-btn px-4 py-2 text-sm font-medium transition cursor-pointer text-[#5c5549] hover:text-[#1e1b16]">
                    Cabang Jalan Merdeka ({{ $merdekaRooms->count() }})
                </button>
            </div>

            <p class="t-label tabular-nums">
                Senin – Kamis 13.00–22.00 · Jumat – Minggu 11.00–22.00 WIB
            </p>
        </div>

        {{-- Room Tiles Grid --}}
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($rooms as $idx => $room)
                @php
                    $isBooth = $room->isPhotoBooth();
                    $activePackages = $room->packages ?? collect();
                    $minPrice = $activePackages->min('harga');
                    $firstImage = $room->images->first()->image_url ?? null;
                    $standIn = $standInPhotos[$idx % count($standInPhotos)];
                    $photoUrl = $firstImage
                        ? (str_starts_with($firstImage, 'http') ? $firstImage : asset('storage/' . $firstImage))
                        : asset($standIn['src']);
                    $photoAlt = $firstImage
                        ? ($room->cabang_label . ' — ' . $room->nama_room)
                        : $standIn['alt'];
                @endphp
                <article class="studio-room-card t-card-link flex flex-col justify-between"
                    data-room-cabang="{{ $room->cabang }}">
                    <div>
                        {{-- 4:3 Room Photograph Link (No text or scrim over image) --}}
                        <a href="{{ route('studio.show', $room->id_room) }}" class="m-zoom block aspect-[4/3] w-full border-b border-[var(--line)]">
                            <img src="{{ $photoUrl }}"
                                alt="{{ $photoAlt }}"
                                width="960" height="720"
                                loading="lazy" decoding="async"
                                data-source="{{ $firstImage ? 'client' : 'stand-in' }}"
                                data-subject="{{ $firstImage ? 'room' : 'stand-in' }}">
                        </a>

                        <div class="p-6 pb-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="t-label font-medium text-[#7b2631]">
                                    <i class="fa-solid fa-location-dot text-[11px] mr-1" aria-hidden="true"></i>
                                    {{ $room->cabang_label }} · {{ $room->jenis_room_label }}
                                </span>
                                <span class="status-badge {{ $isBooth ? 'bg-[#e7dfcf] text-[#1e1b16]' : 'bg-[#1e1b16] text-[#f4efe5]' }}">
                                    {{ $isBooth ? 'Walk-In Only' : 'Bisa Booking' }}
                                </span>
                            </div>

                            <h2 class="t-h3 mt-2.5">
                                <a href="{{ route('studio.show', $room->id_room) }}" class="hover:text-[#7b2631] transition-colors">
                                    {{ $room->cabang_label }} — {{ $room->nama_room }}
                                </a>
                            </h2>

                            <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-[#5c5549]">
                                {{ $room->deskripsi ?: 'Studio self-photo nyaman dengan peralatan kamera dan pencahayaan profesional.' }}
                            </p>

                            <div class="t-rows mt-4 tabular-nums">
                                <div class="t-row text-sm min-h-[38px] py-2">
                                    <span class="text-[#5c5549]">Kapasitas</span>
                                    <span class="font-medium text-[#1e1b16]">Maks. {{ $room->kapasitas }} orang</span>
                                </div>
                                @if ($isBooth)
                                    <div class="t-row text-sm min-h-[38px] py-2">
                                        <span class="text-[#5c5549]">Sistem Kunjungan</span>
                                        <span class="font-medium text-[#1e1b16]">Langsung datang (Pilih frame di PC)</span>
                                    </div>
                                @else
                                    <div class="t-row text-sm min-h-[38px] py-2">
                                        <span class="text-[#5c5549]">Tarif Sesi ({{ $activePackages->count() }} paket)</span>
                                        <span class="font-semibold text-[#7b2631]">
                                            {{ $minPrice ? 'Mulai Rp ' . number_format($minPrice, 0, ',', '.') : 'Hubungi Admin' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="px-6 pb-6 pt-2">
                        @if ($isBooth)
                            <a href="{{ route('studio.show', $room->id_room) }}"
                                class="t-btn t-btn-secondary w-full text-sm min-h-[44px]">
                                <span>Lihat Info Photo Booth</span>
                                <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                            </a>
                        @else
                            <div class="grid grid-cols-[1fr_auto] gap-2.5">
                                <a href="{{ route('studio.show', $room->id_room) }}"
                                    class="t-btn t-btn-secondary text-sm min-h-[44px] px-4">
                                    <span>Lihat Detail &amp; Paket</span>
                                </a>
                                <a href="{{ route('booking.wizard', ['room' => $room->id_room]) }}"
                                    class="t-btn t-btn-primary text-sm min-h-[44px] px-5">
                                    <span>Pesan</span>
                                    <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="col-span-full border border-[var(--line)] bg-[#faf7f1] p-12 text-center">
                    <h2 class="t-h2">Belum ada ruangan studio aktif.</h2>
                    <p class="mt-2 text-base text-[#5c5549]">Silakan kembali lagi nanti atau hubungi admin studio kami.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function filterStudioCabang(cabang) {
    const cards = document.querySelectorAll('.studio-room-card');
    const buttons = document.querySelectorAll('.studio-filter-btn');

    buttons.forEach(btn => {
        const filterType = btn.getAttribute('data-filter-btn');
        if (filterType === cabang) {
            btn.className = 'studio-filter-btn px-4 py-2 text-sm font-semibold transition cursor-pointer bg-[#1e1b16] text-[#f4efe5]';
        } else {
            btn.className = 'studio-filter-btn px-4 py-2 text-sm font-medium transition cursor-pointer text-[#5c5549] hover:text-[#1e1b16]';
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
}
</script>
@endpush
