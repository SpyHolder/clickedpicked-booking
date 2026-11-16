@extends('layouts.app')
@section('title', 'Portal Admin — Login')
@section('content')
<section class="py-14 sm:py-20">
    <div class="t-wrap">
        <div class="mx-auto max-w-md rounded-3xl border border-[var(--line-strong)] bg-white p-7 sm:p-10 shadow-sm">
            <div class="mb-5 flex items-center gap-3">
                <img src="{{ asset('assets/img/clicked-picked-logo.png') }}"
                     alt="Clicked Picked Studios"
                     class="h-9 w-auto object-contain">
                <div>
                    <p class="t-label text-[#d94b38]">Backoffice Portal</p>
                    <h1 class="t-h3 text-xl">Login Admin Studio</h1>
                </div>
            </div>

            <p class="t-body text-[#6b6255] text-xs">
                Masuk menggunakan akun administrator untuk mengelola ruangan cabang Batu 5 &amp; Jalan Merdeka, verifikasi pembayaran, dan antrian edit foto.
            </p>

            <form method="POST" action="{{ route('admin.login.process') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="field-label">Alamat email</label>
                    <input id="email" class="field-input" type="email" autocomplete="email" name="email"
                        value="{{ old('email') }}" placeholder="admin@clicked.studio" required autofocus>
                    @error('email')
                        <p class="field-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="field-label">Kata sandi</label>
                    <input id="password" class="field-input" type="password" autocomplete="current-password" name="password"
                        placeholder="••••••••••••" required>
                    @error('password')
                        <p class="field-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button class="t-btn t-btn-primary w-full mt-2" type="submit">
                    <span>Masuk ke Dashboard</span>
                    <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
