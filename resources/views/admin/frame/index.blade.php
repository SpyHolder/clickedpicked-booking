@extends('layouts.admin')
@section('title', 'Katalog Frame Photo Studio')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-[#7b2631]">Master Data · Photo Studio (Room 2 & Room 3 Batu Lima)</p>
            <h1 class="mt-0.5 text-2xl font-black tracking-tight text-[#172033]">Katalog Frame & Contoh Template Foto</h1>
            <p class="mt-1 text-xs text-slate-500">
                Kelola contoh gambar template frame dan kapasitas slot foto. Anda dapat mengubah gambar contoh template kapan saja.
            </p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.35fr_0.65fr]">
        {{-- Daftar Frame --}}
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-5 py-3.5">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600">Daftar Template Frame ({{ $frames->count() }})</h2>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse ($frames as $frame)
                    <div class="p-4 hover:bg-slate-50/60">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                {{-- Gambar Contoh Template Frame --}}
                                <div class="shrink-0">
                                    @if ($frame->preview_path)
                                        <a href="{{ asset('storage/' . $frame->preview_path) }}" target="_blank" title="Klik untuk lihat ukuran penuh">
                                            <img src="{{ asset('storage/' . $frame->preview_path) }}" alt="{{ $frame->nama_frame }}"
                                                class="h-24 w-16 rounded-md border border-slate-200 bg-slate-100 object-contain shadow-2xs transition hover:scale-105">
                                        </a>
                                    @else
                                        <div class="flex h-24 w-16 flex-col items-center justify-center gap-1 rounded-md border border-slate-300 p-1.5"
                                            style="background-color: {{ $frame->warna_aksen ?: '#172033' }}">
                                            @for ($s = 1; $s <= min($frame->jumlah_slot, 4); $s++)
                                                <div class="w-full flex-1 rounded-[2px] bg-white/85"></div>
                                            @endfor
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs font-black text-[#172033]">{{ $frame->kode_frame }}</span>
                                        <span class="font-bold text-[#172033]">{{ $frame->nama_frame }}</span>
                                        <span class="status-badge border border-sky-200 bg-sky-50 text-sky-800">
                                            {{ $frame->jumlah_slot }} Slot Foto
                                        </span>
                                        <span class="status-badge {{ $frame->is_active ? 'border border-emerald-200 bg-emerald-50 text-emerald-800' : 'border border-slate-200 bg-slate-100 text-slate-600' }}">
                                            {{ $frame->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    @if ($frame->deskripsi)
                                        <p class="mt-1 text-xs text-slate-500">{{ $frame->deskripsi }}</p>
                                    @endif
                                    @if ($frame->preview_path)
                                        <a href="{{ asset('storage/' . $frame->preview_path) }}" target="_blank"
                                            class="mt-1.5 inline-block text-xs font-bold text-[#163b71] hover:underline">
                                            Lihat Gambar Contoh Template <i class="fa-solid fa-arrow-up-right-from-square ml-0.5 text-[10px]"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <form method="POST" action="{{ route('admin.frames.toggle', $frame->id_frame) }}">
                                    @csrf
                                    <button type="submit" class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                        {{ $frame->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.frames.destroy', $frame->id_frame) }}"
                                    onsubmit="return confirm('Hapus frame {{ $frame->kode_frame }} dari katalog?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-bold text-red-700 hover:bg-red-100">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Form Ubah / Ganti Gambar Contoh Template Frame --}}
                        <details class="mt-3 rounded-md border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                            <summary class="cursor-pointer text-xs font-bold text-[#172033]">
                                <i class="fa-solid fa-pen-to-square mr-1 text-slate-500"></i> Ubah Data / Ganti Gambar Contoh Template ({{ $frame->kode_frame }})
                            </summary>
                            <form method="POST" action="{{ route('admin.frames.update', $frame->id_frame) }}" enctype="multipart/form-data" class="mt-3 space-y-3">
                                @csrf
                                @method('PUT')
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <div>
                                        <label class="field-label text-xs">Kode Frame *</label>
                                        <input type="text" name="kode_frame" value="{{ $frame->kode_frame }}" class="field-input bg-white font-mono text-xs uppercase" required>
                                    </div>
                                    <div>
                                        <label class="field-label text-xs">Nama Frame *</label>
                                        <input type="text" name="nama_frame" value="{{ $frame->nama_frame }}" class="field-input bg-white text-xs" required>
                                    </div>
                                    <div>
                                        <label class="field-label text-xs">Jumlah Slot Foto *</label>
                                        <select name="jumlah_slot" class="field-input bg-white text-xs" required>
                                            @foreach ([1, 2, 3, 4, 6, 8] as $slot)
                                                <option value="{{ $slot }}" @selected($frame->jumlah_slot === $slot)>{{ $slot }} Slot Foto</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="field-label text-xs">Ganti Gambar Contoh Template (JPG/PNG/WEBP/SVG)</label>
                                        <input type="file" name="preview" accept=".jpg,.jpeg,.png,.webp,.svg" class="field-input bg-white text-xs">
                                    </div>
                                    <div>
                                        <label class="field-label text-xs">Deskripsi</label>
                                        <input type="text" name="deskripsi" value="{{ $frame->deskripsi }}" class="field-input bg-white text-xs">
                                    </div>
                                </div>
                                <button type="submit" class="rounded-md bg-[#172033] px-3.5 py-2 text-xs font-bold text-white hover:bg-[#7b2631]">
                                    Simpan Perubahan Template
                                </button>
                            </form>
                        </details>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-sm text-slate-500">
                        Belum ada template frame di katalog. Tambahkan melalui form di samping.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Form Tambah Frame Baru --}}
        <div class="rounded-lg border border-slate-200 bg-white p-5 h-fit">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Tambah Template Frame Baru</h2>
            <p class="mt-1 text-xs text-slate-500">
                Upload contoh gambar frame beserta jumlah slot fotonya agar pelanggan dapat melihat contoh hasil jadinya.
            </p>

            <form method="POST" action="{{ route('admin.frames.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="field-label">Kode Frame *</label>
                        <input type="text" name="kode_frame" value="{{ old('kode_frame') }}" class="field-input font-mono uppercase"
                            placeholder="Contoh: FR-07" required maxlength="50">
                        @error('kode_frame')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="field-label">Jumlah Slot Foto *</label>
                        <select name="jumlah_slot" class="field-input" required>
                            @foreach ([1, 2, 3, 4, 6, 8] as $slot)
                                <option value="{{ $slot }}" @selected(old('jumlah_slot', 2) == $slot)>
                                    {{ $slot }} Slot Foto
                                </option>
                            @endforeach
                        </select>
                        @error('jumlah_slot')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="field-label">Nama Desain Frame *</label>
                    <input type="text" name="nama_frame" value="{{ old('nama_frame') }}" class="field-input"
                        placeholder="Contoh: Korean Pastel Strip" required maxlength="100">
                    @error('nama_frame')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="field-label">Upload Gambar Contoh Template Frame (JPG/PNG/WEBP/SVG)</label>
                    <input type="file" name="preview" accept=".jpg,.jpeg,.png,.webp,.svg" class="field-input text-xs">
                    @error('preview')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="field-label">Deskripsi Singkat</label>
                    <input type="text" name="deskripsi" value="{{ old('deskripsi') }}" class="field-input"
                        placeholder="Contoh: Strip 3 foto vertikal dengan border minimalis" maxlength="255">
                </div>

                <button type="submit" class="w-full rounded-md bg-[#172033] px-4 py-2.5 text-xs font-bold text-white hover:bg-[#7b2631]">
                    + Simpan ke Katalog Frame
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
