<!doctype html>
<html lang="id" data-type="place" data-radius="soft" data-buttons="pill">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — Clicked Picked Studios</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/img/clicked-picked-logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wdth,wght@12..96,75..100,500..800&family=Figtree:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
@php
    $pendingEditCount = 0;
    $pendingPaymentCount = 0;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('photo_edit_requests')) {
            $pendingEditCount = \App\Models\PhotoEditRequest::where('status_edit', 'menunggu_verifikasi')->count();
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
            $pendingPaymentCount = \App\Models\Payment::where('status_pembayaran', 'pending')->count();
        }
    } catch (\Throwable $e) {}
    $currentAdmin = auth('admin')->user();
@endphp
<body class="min-h-screen ground-main antialiased bg-[#fbf8f2] text-[#2b2723]">

    {{-- Top Admin Header --}}
    <header class="sticky top-0 z-30 border-b border-[var(--line)] bg-[#fbf8f2]/95 backdrop-blur-xs">
        <div class="mx-auto flex h-14 max-w-[1440px] items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('assets/img/clicked-picked-logo.png') }}" alt="Clicked Picked" class="h-7 w-auto object-contain">
                    <span class="rounded-full border border-[#7b2631] bg-[#fbf8f2] px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-[#7b2631]">
                        Admin
                    </span>
                </a>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('home') }}" target="_blank"
                    class="hidden rounded-full border border-[var(--line-strong)] bg-white px-3.5 py-1 text-xs font-semibold text-[#2b2723] hover:bg-[#f4ede3] sm:inline-block">
                    Lihat Website <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-[10px]" aria-hidden="true"></i>
                </a>
                @if ($currentAdmin)
                    <span class="hidden rounded-full bg-[#f4ede3] px-3.5 py-1 text-xs font-semibold text-[#2b2723] md:inline-block">
                        <i class="fa-solid fa-user-check text-[10px] mr-1 text-[#7b2631]" aria-hidden="true"></i>
                        {{ $currentAdmin->nama }}
                    </span>
                @endif
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="rounded-full bg-[#7b2631] px-4 py-1 text-xs font-semibold text-white transition hover:bg-[#9c2e3c] cursor-pointer">
                        Keluar
                    </button>
                </form>
            </div>
        </div>

        {{-- Mobile Horizontal Nav --}}
        <nav class="mx-auto flex max-w-[1440px] gap-1.5 overflow-x-auto border-t border-[var(--line)] bg-[#f4ede3] px-4 py-2 lg:hidden">
            <a class="whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#7b2631] text-white' : 'text-[#6b635b] hover:bg-white' }}"
                href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-chart-pie mr-1" aria-hidden="true"></i> Dashboard
            </a>
            <a class="whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('admin.booking.*') ? 'bg-[#7b2631] text-white' : 'text-[#6b635b] hover:bg-white' }}"
                href="{{ route('admin.booking.index') }}">
                <i class="fa-solid fa-receipt mr-1" aria-hidden="true"></i> Booking
                @if($pendingPaymentCount > 0)
                    <span class="tabular-nums ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[11px] font-extrabold rounded-full {{ request()->routeIs('admin.booking.*') ? 'bg-white text-[#7b2631]' : 'bg-amber-100 text-amber-900 border border-amber-300' }}">{{ $pendingPaymentCount }}</span>
                @endif
            </a>
            <a class="whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('admin.edit-foto.*') ? 'bg-[#7b2631] text-white' : 'text-[#6b635b] hover:bg-white' }}"
                href="{{ route('admin.edit-foto.index') }}">
                <i class="fa-solid fa-wand-magic-sparkles mr-1" aria-hidden="true"></i> Edit Foto
                @if($pendingEditCount > 0)
                    <span class="tabular-nums ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[11px] font-extrabold rounded-full {{ request()->routeIs('admin.edit-foto.*') ? 'bg-white text-[#7b2631]' : 'bg-amber-100 text-amber-900 border border-amber-300' }}">{{ $pendingEditCount }}</span>
                @endif
            </a>
            <a class="whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('admin.frames.*') ? 'bg-[#7b2631] text-white' : 'text-[#6b635b] hover:bg-white' }}"
                href="{{ route('admin.frames.index') }}">
                <i class="fa-solid fa-crop-simple mr-1" aria-hidden="true"></i> Katalog Frame
            </a>
            <a class="whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold transition {{ request()->routeIs('admin.studio.*') ? 'bg-[#7b2631] text-white' : 'text-[#6b635b] hover:bg-white' }}"
                href="{{ route('admin.studio.index') }}">
                <i class="fa-solid fa-store mr-1" aria-hidden="true"></i> Studio &amp; Paket
            </a>
        </nav>
    </header>

    <div class="mx-auto flex max-w-[1440px] gap-6 px-4 py-6 sm:px-6">
        {{-- Desktop Sidebar --}}
        <aside class="hidden w-64 shrink-0 lg:block">
            <div class="sticky top-20 rounded-2xl border border-[var(--line-strong)] bg-white p-3 space-y-1 shadow-2xs">
                <p class="px-3 pb-1 pt-2 t-label text-[#7b2631]">Operasional</p>
                <a class="admin-link flex items-center justify-between w-full px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.dashboard') ? 'admin-link-active bg-[#7b2631] text-white' : 'text-[#2b2723] hover:bg-[#f4ede3]' }}"
                    href="{{ route('admin.dashboard') }}">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-chart-pie text-xs {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-[#7b2631]' }}" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </span>
                </a>
                <a class="admin-link flex items-center justify-between w-full px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.booking.*') ? 'admin-link-active bg-[#7b2631] text-white' : 'text-[#2b2723] hover:bg-[#f4ede3]' }}"
                    href="{{ route('admin.booking.index') }}">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-receipt text-xs {{ request()->routeIs('admin.booking.*') ? 'text-white' : 'text-[#7b2631]' }}" aria-hidden="true"></i>
                        <span>Booking &amp; Bayar</span>
                    </span>
                    @if($pendingPaymentCount > 0)
                        <span class="tabular-nums inline-flex items-center justify-center px-2 py-0.5 text-[11px] font-extrabold rounded-full {{ request()->routeIs('admin.booking.*') ? 'bg-white text-[#7b2631] shadow-xs' : 'bg-amber-100 text-amber-900 border border-amber-300' }}">{{ $pendingPaymentCount }}</span>
                    @endif
                </a>
                <a class="admin-link flex items-center justify-between w-full px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.edit-foto.*') ? 'admin-link-active bg-[#7b2631] text-white' : 'text-[#2b2723] hover:bg-[#f4ede3]' }}"
                    href="{{ route('admin.edit-foto.index') }}">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-wand-magic-sparkles text-xs {{ request()->routeIs('admin.edit-foto.*') ? 'text-white' : 'text-[#7b2631]' }}" aria-hidden="true"></i>
                        <span>Antrian Edit Foto</span>
                    </span>
                    @if($pendingEditCount > 0)
                        <span class="tabular-nums inline-flex items-center justify-center px-2 py-0.5 text-[11px] font-extrabold rounded-full {{ request()->routeIs('admin.edit-foto.*') ? 'bg-white text-[#7b2631] shadow-xs' : 'bg-amber-100 text-amber-900 border border-amber-300' }}">{{ $pendingEditCount }}</span>
                    @endif
                </a>

                <div class="my-2 border-t border-[var(--line)]"></div>
                <p class="px-3 pb-1 pt-1 t-label text-[#7b2631]">Master Data</p>
                <a class="admin-link flex items-center justify-between w-full px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.frames.*') ? 'admin-link-active bg-[#7b2631] text-white' : 'text-[#2b2723] hover:bg-[#f4ede3]' }}"
                    href="{{ route('admin.frames.index') }}">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-crop-simple text-xs {{ request()->routeIs('admin.frames.*') ? 'text-white' : 'text-[#7b2631]' }}" aria-hidden="true"></i>
                        <span>Katalog Frame</span>
                    </span>
                </a>
                <a class="admin-link flex items-center justify-between w-full px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.studio.*') ? 'admin-link-active bg-[#7b2631] text-white' : 'text-[#2b2723] hover:bg-[#f4ede3]' }}"
                    href="{{ route('admin.studio.index') }}">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-store text-xs {{ request()->routeIs('admin.studio.*') ? 'text-white' : 'text-[#7b2631]' }}" aria-hidden="true"></i>
                        <span>Studio &amp; Paket</span>
                    </span>
                </a>
            </div>
        </aside>

        {{-- Main Content Area --}}
        <main class="min-w-0 flex-1">
            @foreach(['success','error','info'] as $type)
                @if(session($type))
                    <div class="mb-5 flex items-center gap-2.5 rounded-xl border px-4 py-3 text-sm font-medium {{ $type === 'success' ? 'border-emerald-700/30 bg-emerald-50 text-emerald-950' : ($type === 'error' ? 'border-red-700/30 bg-red-50 text-red-950' : 'border-[var(--line-strong)] bg-white text-[#2b2723]') }}">
                        <i class="fa-solid {{ $type === 'success' ? 'fa-check' : ($type === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-info') }}" aria-hidden="true"></i>
                        <span>{{ session($type) }}</span>
                    </div>
                @endif
            @endforeach

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
