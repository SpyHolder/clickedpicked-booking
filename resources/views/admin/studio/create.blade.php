@extends('layouts.admin')
@section('title', 'Tambah Ruangan Studio Baru')
@section('content')
@php
    $preselectedCabang = old('cabang', request('cabang', 'batu_lima'));
    $preselectedJenis = old('jenis_room', 'photo_studio');
@endphp
<div class="mx-auto max-w-2xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-[#172033]">Tambah Ruangan Baru</h1>
            <p class="mt-0.5 text-xs text-slate-500">
                Pilih cabang dan jenis ruangan secara terpisah tanpa perlu mengetik manual di nama ruangan.
            </p>
        </div>
        <a href="{{ route('admin.studio.index', ['tab' => $preselectedCabang]) }}" class="rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-[#172033] hover:bg-slate-50">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('admin.studio.store') }}" class="mt-5 space-y-5 rounded-lg border border-slate-200 bg-white p-6 shadow-2xs">
        @csrf

        {{-- 1. Pilihan Cabang Studio --}}
        <div>
            <label class="field-label text-xs">Pilih Cabang Studio *</label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="relative flex cursor-pointer rounded-xl border-2 p-3.5 transition hover:border-[#172033] has-checked:border-[#172033] has-checked:bg-[#f3f0e8]/50">
                    <input type="radio" name="cabang" value="batu_lima" class="mt-0.5 text-[#172033] focus:ring-[#172033]"
                        @checked($preselectedCabang === 'batu_lima') required>
                    <div class="ml-3">
                        <span class="block text-sm font-black text-[#172033]">Cabang Batu 5</span>
                        <span class="mt-0.5 block text-xs text-slate-500">Jl. Merdeka, BT 5 Atas (Tersedia Studio & Booth)</span>
                    </div>
                </label>

                <label class="relative flex cursor-pointer rounded-xl border-2 p-3.5 transition hover:border-[#7b2631] has-checked:border-[#7b2631] has-checked:bg-[#f3f0e8]/50">
                    <input type="radio" name="cabang" value="jalan_merdeka" class="mt-0.5 text-[#7b2631] focus:ring-[#7b2631]"
                        @checked($preselectedCabang === 'jalan_merdeka') required>
                    <div class="ml-3">
                        <span class="block text-sm font-black text-[#7b2631]">Cabang Jalan Merdeka</span>
                        <span class="mt-0.5 block text-xs text-slate-500">Jl. Merdeka (Khusus Photo Booth Walk-In)</span>
                    </div>
                </label>
            </div>
            @error('cabang')
                <p class="field-error mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 2. Pilihan Jenis Ruangan --}}
        <div>
            <label class="field-label text-xs">Jenis Ruangan *</label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="relative flex cursor-pointer rounded-xl border-2 p-3.5 transition hover:border-[#163b71] has-checked:border-[#163b71] has-checked:bg-sky-50/50">
                    <input type="radio" name="jenis_room" value="photo_studio" class="mt-0.5 text-[#163b71] focus:ring-[#163b71]"
                        @checked($preselectedJenis === 'photo_studio') required>
                    <div class="ml-3">
                        <span class="block text-sm font-black text-sky-900">
                            <i class="fa-solid fa-camera mr-1 text-sky-700"></i> Photo Studio
                        </span>
                        <span class="mt-0.5 block text-xs text-slate-500">Bisa di-booking online via website. Pelanggan dapat memilih Template Frame & slot foto dari Drive.</span>
                    </div>
                </label>

                <label class="relative flex cursor-pointer rounded-xl border-2 p-3.5 transition hover:border-amber-600 has-checked:border-amber-600 has-checked:bg-amber-50/50">
                    <input type="radio" name="jenis_room" value="photo_booth" class="mt-0.5 text-amber-700 focus:ring-amber-600"
                        @checked($preselectedJenis === 'photo_booth') required>
                    <div class="ml-3">
                        <span class="block text-sm font-black text-amber-950">
                            <i class="fa-solid fa-cubes mr-1 text-amber-700"></i> Photo Booth
                        </span>
                        <span class="mt-0.5 block text-xs text-slate-500">Khusus Walk-In (tanpa booking online). Pemilihan frame dilakukan langsung mandiri di layar PC booth.</span>
                    </div>
                </label>
            </div>
            @error('jenis_room')
                <p class="field-error mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 3. Nama Ruangan --}}
        <div>
            <label for="nama_room" class="field-label text-xs">Nama / Nomor Ruangan *</label>
            <input id="nama_room" class="field-input text-sm font-bold" name="nama_room" value="{{ old('nama_room') }}"
                placeholder="Contoh: Room 1 atau Room 2" required>
            <p class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-500">
                <i class="fa-solid fa-circle-info text-slate-400"></i>
                Cukup isi nomor/nama ruangan (misal: <code>Room 1</code>). Nama cabang dan jenis ruangan akan otomatis ditangani oleh sistem.
            </p>
            @error('nama_room')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 4. Kapasitas Maksimal --}}
        <div>
            <label for="kapasitas" class="field-label text-xs">Kapasitas Maksimal (Orang) *</label>
            <input id="kapasitas" class="field-input text-sm" type="number" name="kapasitas" value="{{ old('kapasitas', 4) }}" min="1" max="50" required>
            @error('kapasitas')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 5. Deskripsi & Fasilitas --}}
        <div>
            <label for="deskripsi" class="field-label text-xs">Deskripsi & Fasilitas Ruangan <span class="font-normal text-slate-400">(opsional)</span></label>
            <textarea id="deskripsi" class="field-input text-sm" name="deskripsi" rows="3"
                placeholder="Jelaskan jenis pencahayaan, backdrop, atau panduan dalam ruangan">{{ old('deskripsi') }}</textarea>
            @error('deskripsi')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <button class="w-full rounded-xl bg-[#172033] px-5 py-3.5 text-xs font-black text-white shadow-sm transition hover:bg-[#7b2631]" type="submit">
            Simpan Ruangan Baru
        </button>
    </form>
</div>
@endsection
