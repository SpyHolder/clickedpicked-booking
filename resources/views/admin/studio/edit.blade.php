@extends('layouts.admin')
@section('title', 'Edit Studio — ' . $room->nama_room)
@section('content')
@php
    $currentCabang = old('cabang', $room->cabang ?? 'batu_lima');
    $currentJenis = old('jenis_room', $room->isPhotoBooth() ? 'photo_booth' : 'photo_studio');
@endphp
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-[#172033]">Edit {{ $room->nama_room }}</h1>
            <p class="mt-0.5 text-xs text-slate-500">
                Cabang <strong class="text-[#7b2631]">{{ $room->cabang_label }}</strong> · {{ $room->jenis_room_label }}.
            </p>
        </div>
        <div class="flex gap-2">
            @if (!$room->isPhotoBooth())
                <a href="{{ route('admin.studio.packages', $room->id_room) }}"
                    class="rounded-md bg-[#cbbb9e] px-3.5 py-2 text-xs font-bold text-[#172033] hover:bg-[#b9a688]">
                    <i class="fa-solid fa-tags mr-1"></i> Kelola Paket
                </a>
            @endif
            <a href="{{ route('admin.studio.index', ['tab' => $currentCabang]) }}"
                class="rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-[#172033] hover:bg-slate-50">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[1.15fr_0.85fr]">
        {{-- Kolom Kiri: Form Informasi Studio --}}
        <form method="POST" action="{{ route('admin.studio.update', $room->id_room) }}"
            class="h-fit space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-2xs">
            @csrf
            @method('PUT')
            <h2 class="border-b border-slate-100 pb-3 text-base font-black text-[#172033]">Informasi Ruangan</h2>

            {{-- Pilihan Cabang Studio --}}
            <div>
                <label class="field-label text-xs">Pilih Cabang Studio *</label>
                <div class="grid gap-2.5 sm:grid-cols-2">
                    <label class="relative flex cursor-pointer rounded-xl border-2 p-3 transition hover:border-[#172033] has-checked:border-[#172033] has-checked:bg-[#f3f0e8]/50">
                        <input type="radio" name="cabang" value="batu_lima" class="mt-0.5 text-[#172033] focus:ring-[#172033]"
                            @checked($currentCabang === 'batu_lima') required>
                        <div class="ml-2.5">
                            <span class="block text-xs font-black text-[#172033]">Cabang Batu 5</span>
                            <span class="text-[11px] text-slate-500">Jl. Merdeka, BT 5 Atas</span>
                        </div>
                    </label>

                    <label class="relative flex cursor-pointer rounded-xl border-2 p-3 transition hover:border-[#7b2631] has-checked:border-[#7b2631] has-checked:bg-[#f3f0e8]/50">
                        <input type="radio" name="cabang" value="jalan_merdeka" class="mt-0.5 text-[#7b2631] focus:ring-[#7b2631]"
                            @checked($currentCabang === 'jalan_merdeka') required>
                        <div class="ml-2.5">
                            <span class="block text-xs font-black text-[#7b2631]">Cabang Jalan Merdeka</span>
                            <span class="text-[11px] text-slate-500">Jl. Merdeka</span>
                        </div>
                    </label>
                </div>
                @error('cabang')
                    <p class="field-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Pilihan Jenis Ruangan --}}
            <div>
                <label class="field-label text-xs">Jenis Ruangan *</label>
                <div class="grid gap-2.5 sm:grid-cols-2">
                    <label class="relative flex cursor-pointer rounded-xl border-2 p-3 transition hover:border-[#163b71] has-checked:border-[#163b71] has-checked:bg-sky-50/50">
                        <input type="radio" name="jenis_room" value="photo_studio" class="mt-0.5 text-[#163b71] focus:ring-[#163b71]"
                            @checked($currentJenis === 'photo_studio') required>
                        <div class="ml-2.5">
                            <span class="block text-xs font-black text-sky-900">
                                <i class="fa-solid fa-camera mr-1 text-sky-700"></i> Photo Studio
                            </span>
                            <span class="text-[11px] text-slate-500">Bisa booking online & pilih frame.</span>
                        </div>
                    </label>

                    <label class="relative flex cursor-pointer rounded-xl border-2 p-3 transition hover:border-amber-600 has-checked:border-amber-600 has-checked:bg-amber-50/50">
                        <input type="radio" name="jenis_room" value="photo_booth" class="mt-0.5 text-amber-700 focus:ring-amber-600"
                            @checked($currentJenis === 'photo_booth') required>
                        <div class="ml-2.5">
                            <span class="block text-xs font-black text-amber-950">
                                <i class="fa-solid fa-cubes mr-1 text-amber-700"></i> Photo Booth
                            </span>
                            <span class="text-[11px] text-slate-500">Khusus Walk-In & pilih di PC booth.</span>
                        </div>
                    </label>
                </div>
                @error('jenis_room')
                    <p class="field-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Ruangan --}}
            <div>
                <label for="nama_room" class="field-label text-xs">Nama / Nomor Ruangan *</label>
                <input id="nama_room" class="field-input text-sm font-bold" name="nama_room" value="{{ old('nama_room', $room->nama_room) }}" required>
                <p class="mt-1 flex items-center gap-1 text-[11px] text-slate-500">
                    <i class="fa-solid fa-circle-info text-slate-400"></i>
                    Nama ruangan sederhana (contoh: <code>Room 1</code>). Nama cabang dan jenis ruangan dikelola otomatis oleh sistem.
                </p>
                @error('nama_room')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kapasitas" class="field-label text-xs">Kapasitas Maksimal (Orang) *</label>
                <input id="kapasitas" class="field-input text-sm" type="number" name="kapasitas" value="{{ old('kapasitas', $room->kapasitas) }}" min="1" max="50" required>
                @error('kapasitas')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="deskripsi" class="field-label text-xs">Deskripsi Ruangan</label>
                <textarea id="deskripsi" class="field-input text-sm" name="deskripsi" rows="3">{{ old('deskripsi', $room->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="w-full rounded-xl bg-[#172033] px-5 py-3.5 text-xs font-black text-white shadow-sm transition hover:bg-[#7b2631]" type="submit">
                Simpan Perubahan Ruangan
            </button>
        </form>

        {{-- Kolom Kanan: Galeri Foto Studio --}}
        <div class="space-y-5 rounded-lg border border-slate-200 bg-white p-6">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base font-black text-[#172033]">Foto Galeri Studio</h2>
                <p class="mt-0.5 text-xs text-slate-500">Unggah foto ruangan studio (JPG, PNG, WEBP maks. 5 MB).</p>
            </div>

            <form method="POST" action="{{ route('admin.studio.images.store', $room->id_room) }}"
                enctype="multipart/form-data" class="space-y-3 rounded-md border border-slate-200 bg-slate-50 p-4">
                @csrf
                <div>
                    <label for="image" class="field-label text-xs">Pilih Gambar *</label>
                    <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" required
                        class="field-input bg-white text-xs file:mr-3 file:rounded file:border-0 file:bg-slate-200 file:px-3 file:py-1 file:text-xs file:font-bold">
                    @error('image')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="keterangan" class="field-label text-xs">Keterangan Singkat (opsional)</label>
                    <input id="keterangan" type="text" name="keterangan" maxlength="100" class="field-input bg-white text-sm" placeholder="Contoh: Tampak sudut backdrop utama">
                </div>
                <button type="submit" class="w-full rounded-md bg-[#172033] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-800">
                    + Unggah Foto Studio
                </button>
            </form>

            {{-- Daftar Gambar Studio --}}
            <div class="space-y-2.5">
                @forelse ($room->images as $img)
                    <div class="flex items-center justify-between gap-3 rounded-md border border-slate-200 p-2.5">
                        <div class="flex items-center gap-3">
                            <div class="room-placeholder h-12 w-16 shrink-0 overflow-hidden rounded">
                                <img src="{{ str_starts_with($img->image_url, 'http') ? $img->image_url : asset('storage/' . $img->image_url) }}"
                                    alt="{{ $img->keterangan ?: $room->nama_room }}" class="h-full w-full object-cover">
                            </div>
                            <div>
                                <p class="text-xs font-bold text-[#172033]">{{ $img->keterangan ?: 'Foto Studio' }}</p>
                                <p class="font-mono text-[11px] text-slate-400">{{ basename($img->image_url) }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.studio.images.destroy', [$room->id_room, $img->id_image]) }}"
                            onsubmit="return confirm('Hapus foto studio ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 hover:bg-red-100">
                                Hapus
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="room-placeholder flex h-32 items-center justify-center rounded-md p-4 text-center text-xs font-semibold text-white/90">
                        Belum ada foto studio yang diunggah (menggunakan Placeholder Visual).
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
