<!doctype html>
<html lang="id" data-type="place" data-radius="soft" data-buttons="pill" data-motion>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="page-type" content="place">
    <meta name="site-kind" content="other">
    <meta name="page-kind" content="place">
    <meta name="theme-color" content="#fbf8f2">
    <meta name="description" content="@yield('meta_description', 'Clicked Picked Studios — Self-photo studio & photobooth mandiri di Tanjungpinang. Sesi privat di Batu 5 Atas & photobooth seru di Jalan Merdeka.')">
    <meta property="og:title" content="@yield('title', 'Clicked Picked Studios') — Self-Photo Studio Tanjungpinang">
    <meta property="og:description" content="Private self-photo studio & authentic photobooth di Tanjungpinang. Bebas berekspresi, no awkward poses.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('assets/img/clicked-picked-logo.png') }}">
    <meta name="twitter:card" content="summary_large_image">

    <title>@yield('title', 'Clicked Picked Studios') — Self-Photo Studio & Photobooth</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/img/clicked-picked-logo.png') }}">

    {{-- Google Fonts: Bricolage Grotesque (Playful Display) + Caveat (Handmade) + Figtree (Sans) + JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wdth,wght@12..96,75..100,500..800&family=Caveat:wght@600;700&family=Figtree:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col ground-main antialiased selection:bg-[#d94b38]/20 selection:text-[#181715]">

    {{-- Top Header --}}
    <header class="sticky top-0 z-30 border-b border-[var(--line)] bg-[#fbf8f2]/95 backdrop-blur-xs">
        <div class="t-wrap flex h-[62px] sm:h-[74px] items-center justify-between gap-4">
            {{-- Skip Link --}}
            <a class="skip-link" href="#top">Langsung ke konten</a>

            {{-- Authentic Brand Mark with Clicked Picked Logo --}}
            <a href="{{ route('home') }}" class="group inline-flex items-center gap-2.5 text-decoration-none focus-visible:outline-2 focus-visible:outline-[#d94b38]">
                <img src="{{ asset('assets/img/clicked-picked-logo.png') }}"
                     alt="Clicked Picked Studios"
                     class="h-8 sm:h-10 w-auto object-contain transition-transform duration-200 group-hover:scale-105">
            </a>

            {{-- Desktop Navigation --}}
            <nav class="hidden items-center gap-7 md:flex" aria-label="Navigasi Utama">
                <a href="{{ route('home') }}"
                    class="t-nav-link"
                    @if(request()->routeIs('home')) aria-current="page" @endif>
                    Beranda
                </a>
                <a href="{{ route('home') }}#pilihan-cabang"
                    class="t-nav-link">
                    2 Lokasi Cabang
                </a>
                <a href="{{ route('studio.index') }}"
                    class="t-nav-link"
                    @if(request()->routeIs('studio.*')) aria-current="page" @endif>
                    Pilihan Studio
                </a>
                <a href="{{ route('check-status') }}"
                    class="t-nav-link"
                    @if(request()->routeIs('check-status*') || request()->routeIs('edit-foto.status')) aria-current="page" @endif>
                    Lacak Booking
                </a>
            </nav>

            {{-- Right CTA Button + Mobile Menu Toggle --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('booking.wizard') }}"
                    class="t-btn t-btn-primary hidden sm:inline-flex text-sm py-2.5 px-5">
                    <span>Pesan Studio Batu 5</span>
                    <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                </a>

                <button type="button" id="menu-toggle"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--line-strong)] bg-white text-base text-[#181715] md:hidden cursor-pointer"
                    aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="mobile-menu">
                    <i class="fa-solid fa-bars" id="menu-icon-bars" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        {{-- Mobile Full-Screen Sheet --}}
        <nav id="mobile-menu" class="hidden border-t border-[var(--line)] bg-[#fbf8f2] md:hidden" aria-label="Navigasi Seluler">
            <div class="t-wrap py-5">
                <div class="flex flex-col divide-y divide-[var(--line)]">
                    <a href="{{ route('home') }}" class="flex h-[52px] items-center justify-between text-base font-semibold {{ request()->routeIs('home') ? 'text-[#d94b38]' : 'text-[#181715]' }}">
                        <span>Beranda</span>
                        <i class="fa-solid fa-arrow-right text-xs text-[#6b6255]" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('home') }}#pilihan-cabang" class="flex h-[52px] items-center justify-between text-base font-medium text-[#181715]">
                        <span>2 Lokasi Cabang</span>
                        <i class="fa-solid fa-arrow-right text-xs text-[#6b6255]" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('studio.index') }}" class="flex h-[52px] items-center justify-between text-base font-medium {{ request()->routeIs('studio.*') ? 'text-[#d94b38]' : 'text-[#181715]' }}">
                        <span>Pilihan Studio &amp; Booth</span>
                        <i class="fa-solid fa-arrow-right text-xs text-[#6b6255]" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('booking.wizard') }}" class="flex h-[52px] items-center justify-between text-base font-medium text-[#181715]">
                        <span>Booking Sesi Studio</span>
                        <i class="fa-solid fa-arrow-right text-xs text-[#6b6255]" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('check-status') }}" class="flex h-[52px] items-center justify-between text-base font-medium {{ request()->routeIs('check-status*') ? 'text-[#d94b38]' : 'text-[#181715]' }}">
                        <span>Lacak Pesanan / Kode</span>
                        <i class="fa-solid fa-arrow-right text-xs text-[#6b6255]" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="mt-5 border-t border-[var(--line)] pt-4 text-xs text-[#6b6255] space-y-1.5">
                    <p class="font-semibold text-[#181715]">Clicked Picked Studios Tanjungpinang</p>
                    <p class="tabular-nums">Batu 5 Atas (Booking Online) &amp; Jalan Merdeka (Walk-In Only)</p>
                    <p class="tabular-nums">Buka Setiap Hari · 13.00 – 22.00 WIB (Jumat–Minggu: 11.00 – 22.00 WIB)</p>
                    <div class="pt-2">
                        <a href="{{ route('admin.login') }}" class="t-link text-xs">Portal Admin Studio</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    {{-- Flash Alerts --}}
    @if (session('success') || session('error') || session('info'))
        <div class="t-wrap pt-5" aria-live="polite">
            @if (session('success'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-600/30 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-950">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-700 text-xs text-white"><i class="fa-solid fa-check"></i></span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="flex items-center gap-3 rounded-2xl border border-red-600/30 bg-red-50 px-4 py-3 text-sm font-medium text-red-950">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-700 text-xs font-bold text-white">!</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if (session('info'))
                <div class="flex items-center gap-3 rounded-2xl border border-[var(--line-strong)] bg-white px-4 py-3 text-sm font-medium text-[#181715]">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#181715] text-xs font-bold text-white">i</span>
                    <span>{{ session('info') }}</span>
                </div>
            @endif
        </div>
    @endif

    {{-- Main Page Content --}}
    <main id="top" class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-20 border-t border-[var(--line)] bg-[#f3ede1] pb-[calc(3rem+var(--bar))] sm:pb-14">
        <div class="t-wrap">
            {{-- Closing Banner --}}
            <div class="flex flex-col items-start justify-between gap-6 border-b border-[var(--line)] py-12 lg:flex-row lg:items-end lg:py-14">
                <div class="max-w-xl">
                    <div class="inline-flex items-center gap-2">
                        <span class="floating-star text-[#d94b38] text-sm">✦</span>
                        <p class="t-label text-[#d94b38]">Snap &amp; Print Your Memories</p>
                    </div>
                    <h2 class="t-h2 mt-2">
                        Ruang privat untuk bebas berekspresi bareng orang tersayang.
                    </h2>
                    <p class="mt-2.5 text-base text-[#6b6255] tabular-nums">
                        Studio Batu 5 wajib booking online slot. Seluruh Photobooth di Merdeka &amp; Batu 5 buka langsung datang (walk-in).
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('booking.wizard') }}" class="t-btn t-btn-primary w-full sm:w-auto">
                        <span>Pesan Studio Batu 5</span>
                        <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('studio.index') }}" class="t-btn t-btn-secondary w-full sm:w-auto">
                        <span>Lihat Ruangan</span>
                    </a>
                </div>
            </div>

            {{-- Directory --}}
            <div class="grid gap-10 py-10 md:grid-cols-12">
                <div class="md:col-span-5">
                    <img src="{{ asset('assets/img/clicked-picked-logo.png') }}"
                         alt="Clicked Picked Studios"
                         class="h-9 sm:h-11 w-auto object-contain">
                    <p class="mt-3 max-w-sm text-sm leading-relaxed text-[#6b6255]">
                        <strong>Cabang 1:</strong> Jl. Merdeka, Batu 5 Atas (Photo Studio &amp; Walk-In Booth)<br>
                        <strong>Cabang 2:</strong> Jl. Merdeka, Kota Lama (Khusus Korean Photobooth Walk-In)
                    </p>
                    <p class="mt-2.5">
                        <a href="https://www.openstreetmap.org/search?query=Jalan+Merdeka+Tanjungpinang"
                           target="_blank" rel="noopener noreferrer"
                           class="t-link text-xs">
                            <span>Buka di peta Tanjungpinang</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px] m-arrow" aria-hidden="true"></i>
                        </a>
                    </p>
                </div>

                <div class="md:col-span-4">
                    <h3 class="t-label text-[#181715]">Jam Buka Studio</h3>
                    <dl class="mt-3 space-y-2 text-sm tabular-nums">
                        <div class="flex justify-between gap-4 border-b border-[var(--line)] pb-2">
                            <dt class="text-[#6b6255]">Senin – Kamis</dt>
                            <dd class="font-semibold text-[#181715]">13.00 – 22.00 WIB</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-[var(--line)] pb-2">
                            <dt class="text-[#6b6255]">Jumat – Minggu</dt>
                            <dd class="font-semibold text-[#181715]">11.00 – 22.00 WIB</dd>
                        </div>
                    </dl>
                </div>

                <div class="md:col-span-3">
                    <h3 class="t-label text-[#181715]">Menu Cepat</h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="t-link">Beranda</a></li>
                        <li><a href="{{ route('studio.index') }}" class="t-link">Pilihan Studio</a></li>
                        <li><a href="{{ route('booking.wizard') }}" class="t-link">Booking Sesi</a></li>
                        <li><a href="{{ route('check-status') }}" class="t-link">Cek Kode Booking</a></li>
                        <li class="pt-1">
                            <a href="{{ route('admin.login') }}" class="t-link text-xs text-[#6b6255]">
                                <span>Portal Admin</span>
                                <i class="fa-solid fa-arrow-right text-[10px] m-arrow" aria-hidden="true"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Bottom Note --}}
            <div class="flex flex-col justify-between gap-3 border-t border-[var(--line)] pt-5 text-xs text-[#6b6255] sm:flex-row sm:items-center">
                <p>© {{ date('Y') }} Clicked Picked Studios · Tanjungpinang. All rights reserved.</p>
                <details class="text-[11px] text-[#6b6255]">
                    <summary class="cursor-pointer hover:text-[#181715]">Foto stand-in fotografi berlisensi</summary>
                    <p class="mt-1 max-w-md leading-relaxed">
                        Foto studio berlisensi CC0 / CC BY-SA 4.0 via Wikimedia Commons.
                    </p>
                </details>
            </div>
        </div>
    </footer>

    {{-- Mobile Bottom Action Bar --}}
    <nav id="mobile-actionbar" class="fixed bottom-0 left-0 right-0 z-20 border-t border-[var(--line-strong)] bg-[#fbf8f2] px-4 py-2.5 sm:hidden transition-transform duration-200" aria-label="Aksi Cepat">
        <div class="grid grid-cols-[1fr_auto] gap-2">
            <a href="{{ route('booking.wizard') }}" class="t-btn t-btn-primary text-xs min-h-[42px] py-2">
                <span>Pesan Studio Batu 5</span>
                <i class="fa-solid fa-arrow-right text-[10px] m-arrow" aria-hidden="true"></i>
            </a>
            <a href="{{ route('check-status') }}" class="t-btn t-btn-secondary text-xs min-h-[42px] px-3 py-2">
                <span>Cek Kode</span>
            </a>
        </div>
    </nav>

    {{-- Mobile Menu Toggle Script --}}
    <script>
    (function () {
        const toggle = document.getElementById('menu-toggle');
        const menu = document.getElementById('mobile-menu');
        const icon = document.getElementById('menu-icon-bars');

        if (!toggle || !menu) return;

        toggle.addEventListener('click', function () {
            const isOpen = !menu.classList.contains('hidden');
            if (isOpen) {
                menu.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
                if (icon) icon.className = 'fa-solid fa-bars';
            } else {
                menu.classList.remove('hidden');
                toggle.setAttribute('aria-expanded', 'true');
                if (icon) icon.className = 'fa-solid fa-xmark';
            }
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>
