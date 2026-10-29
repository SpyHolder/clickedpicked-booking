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
        <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-5 sm:px-8">
            <div class="flex gap-2 text-xs font-semibold"><span>ID</span><span class="text-slate-400">|</span><span class="text-slate-400">EN</span></div>
            <a href="{{ route('home') }}" class="text-center text-xs font-black leading-[.8] text-[#163b71]">Clicked<br><span class="font-serif text-[15px] italic">Picked</span></a>
            <button type="button" id="menu-toggle" class="text-2xl leading-none text-slate-500" aria-label="Buka menu" aria-expanded="false">☰</button>
        </div>
        <nav id="mobile-menu" class="mx-auto hidden max-w-7xl border-t border-[#dfe3e9] px-5 py-3 sm:px-8">
            <div class="flex flex-col gap-3 text-sm font-semibold sm:flex-row sm:items-center">
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('studio.index') }}">Pilih studio</a>
                <a href="{{ route('booking.wizard') }}">Booking</a>
                <a href="{{ route('check-status') }}">Cek status</a>
                <a href="{{ route('admin.login') }}">Login admin</a>
            </div>
        </nav>
        </div>
    </header>
    @if (session('success'))<div class="mx-auto max-w-7xl px-5 pt-4 sm:px-8"><div class="rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div></div>@endif
    @if (session('error'))<div class="mx-auto max-w-7xl px-5 pt-4 sm:px-8"><div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div></div>@endif
    <main>@yield('content')</main>
    @stack('scripts')
    <script>
        document.querySelector('#menu-toggle')?.addEventListener('click', function () {
            const menu = document.querySelector('#mobile-menu');
            const expanded = this.getAttribute('aria-expanded') === 'true';
            menu.classList.toggle('hidden', expanded);
            this.setAttribute('aria-expanded', String(!expanded));
        });
    </script>
    <footer class="border-t border-[#e3e1db] px-5 py-8 text-center text-sm text-slate-500">© {{ date('Y') }} by Clicked Picked Studios</footer>
</body>
</html>
