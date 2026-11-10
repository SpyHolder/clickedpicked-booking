<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Clicked Picked Studios')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f8f7f2] text-[#172033] antialiased">
    <header class="sticky top-0 z-20 border-b border-[#dfe3e9] bg-[#f8f7f2]/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 sm:px-8">
            <div class="language-switcher flex items-center gap-1 text-xs font-bold" aria-label="Pilih bahasa">
                <button type="button" data-language="id" class="language-button rounded-full px-2 py-1">ID</button>
                <span class="text-slate-300">|</span>
                <button type="button" data-language="en" class="language-button rounded-full px-2 py-1 text-slate-400">EN</button>
            </div>
            <a href="{{ route('home') }}" class="text-center text-xs font-black leading-[.8] text-[#163b71]">Clicked<br><span class="font-serif text-[15px] italic">Picked</span></a>
            <button type="button" id="menu-toggle" class="menu-toggle group relative h-9 w-9 rounded-full text-2xl leading-none text-slate-500 transition hover:bg-white" aria-label="Buka menu" aria-expanded="false">
                <span class="menu-line menu-line-one"></span><span class="menu-line menu-line-two"></span><span class="menu-line menu-line-three"></span>
            </button>
        </div>
        <nav id="mobile-menu" class="mx-auto hidden max-w-7xl border-t border-[#dfe3e9] px-5 py-4 sm:px-8">
            <div class="flex flex-col gap-1 text-sm font-semibold sm:flex-row sm:items-center sm:gap-6">
                <a class="nav-link" href="{{ route('home') }}" data-id="Beranda" data-en="Home">Beranda</a>
                <a class="nav-link" href="{{ route('studio.index') }}" data-id="Pilih studio" data-en="Choose studio">Pilih studio</a>
                <a class="nav-link" href="{{ route('booking.wizard') }}" data-id="Booking" data-en="Booking">Booking</a>
                <a class="nav-link" href="{{ route('check-status') }}" data-id="Cek status" data-en="Check status">Cek status</a>
                <a class="nav-link" href="{{ route('admin.login') }}" data-id="Login admin" data-en="Admin login">Login admin</a>
            </div>
        </nav>
    </header>
    @if (session('success'))<div class="mx-auto max-w-7xl px-5 pt-4 sm:px-8"><div class="rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div></div>@endif
    @if (session('error'))<div class="mx-auto max-w-7xl px-5 pt-4 sm:px-8"><div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div></div>@endif
    <main>@yield('content')</main>
    @stack('scripts')
    <script>
        const menuToggle = document.querySelector('#menu-toggle');
        const mobileMenu = document.querySelector('#mobile-menu');
        menuToggle?.addEventListener('click', () => {
            const open = menuToggle.getAttribute('aria-expanded') === 'true';
            mobileMenu.classList.toggle('hidden', open);
            menuToggle.classList.toggle('is-open', !open);
            menuToggle.setAttribute('aria-expanded', String(!open));
        });
    </script>
    <footer class="border-t border-[#e3e1db] px-5 py-8 text-center text-sm text-slate-500">© {{ date('Y') }} by Clicked Picked Studios</footer>
</body>
</html>