@extends('layouts.admin')
@section('title', 'Detail Request ' . $editRequest->kode_request)
@section('content')
@php
    $st = $editRequest->status_edit->value;
    $badgeColor = match ($st) {
        'menunggu_verifikasi' => 'bg-amber-100 text-amber-900 border border-amber-200',
        'pending' => 'bg-slate-100 text-slate-800 border border-slate-200',
        'diproses' => 'bg-sky-100 text-sky-900 border border-sky-200',
        'selesai' => 'bg-teal-100 text-teal-900 border border-teal-200',
        'dikirim' => 'bg-emerald-100 text-emerald-900 border border-emerald-200',
        'ditolak' => 'bg-red-100 text-red-900 border border-red-200',
        default => 'bg-slate-100 text-slate-700',
    };
    $uploadedPhotos = $editRequest->editedPhotos ?? collect();
    $fotoFramePhotos = $uploadedPhotos->where('kategori_hasil', 'foto_frame');
    $customFramePhotos = $uploadedPhotos->where('kategori_hasil', 'custom_frame');
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="font-mono text-2xl font-black text-[#172033]">{{ $editRequest->kode_request }}</h1>
                <span class="status-badge {{ $badgeColor }}">
                    {{ $editRequest->status_edit->label() }}
                </span>
                @if ($editRequest->jenis_layanan === 'custom_frame')
                    <span class="status-badge border border-[#7b2631]/30 bg-[#7b2631]/10 text-[#7b2631]">
                        Custom Frame ({{ $editRequest->jumlah_slot ?? $editRequest->items->count() }} Slot)
                    </span>
                @else
                    <span class="status-badge border border-slate-300 bg-slate-100 text-[#172033]">
                        Foto + Frame {{ $editRequest->frame ? '· ' . $editRequest->frame->kode_frame : '' }}
                    </span>
                @endif
                @if ($editRequest->perlu_dicek)
                    <span class="status-badge border border-amber-300 bg-amber-50 text-amber-800">
                        <i class="fa-solid fa-triangle-exclamation mr-1 text-amber-700"></i> Perlu dicek (belum terhubung booking)
                    </span>
                @endif
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Dibuat {{ $editRequest->created_at?->translatedFormat('d M Y, H:i') }} · Sumber: <strong class="uppercase">{{ $editRequest->sumber->value }}</strong>
            </p>
        </div>

        <a href="{{ route('admin.edit-foto.index') }}" class="rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Antrian
        </a>
    </div>

    {{-- Notifikasi Sukses Kirim WA + Tombol Buka WhatsApp Langsung --}}
    @if (session('wa_redirect_url'))
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-emerald-300 bg-emerald-50 p-4">
            <div>
                <p class="text-sm font-bold text-emerald-950"><i class="fa-solid fa-check mr-1.5 text-emerald-700"></i> Pengiriman tercatat! Klik tombol di samping untuk membuka WhatsApp pelanggan dengan pesan & link siap kirim.</p>
                <p class="text-xs text-emerald-800">Pesan otomatis sudah terisi dengan nama pelanggan dan tautan hasil edit.</p>
            </div>
            <a href="{{ session('wa_redirect_url') }}" target="_blank" rel="noopener"
                class="rounded-md bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-800">
                Buka WhatsApp Pelanggan <i class="fa-brands fa-whatsapp ml-1"></i>
            </a>
        </div>
    @endif

    {{-- Banner Pelanggan Kembali (PRD 7.4) --}}
    @if (!empty($returningCustomer['is_returning']))
        <div class="rounded-lg border border-indigo-200 bg-indigo-50/70 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs font-bold text-indigo-950">
                    <i class="fa-solid fa-star text-amber-500 mr-1"></i> Pelanggan kembali: {{ $returningCustomer['request_count'] }} request dan {{ $returningCustomer['booking_count'] }} booking sebelumnya
                </p>
            </div>
            @if (!empty($returningCustomer['recent_items']))
                <div class="mt-2.5 flex flex-wrap gap-1.5">
                    @foreach ($returningCustomer['recent_items'] as $ri)
                        <a href="{{ $ri['url'] }}" class="inline-flex items-center gap-1.5 rounded border border-indigo-200 bg-white px-2.5 py-1 text-xs font-semibold text-indigo-900 hover:bg-indigo-100">
                            <span class="font-mono font-bold">{{ $ri['kode'] }}</span>
                            <span class="text-slate-400">·</span>
                            <span>{{ $ri['status'] }}</span>
                            <span class="text-slate-400">({{ $ri['tanggal']?->format('d/m/y') }})</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Kolom Kiri (2/3): Data Pelanggan, Detail Frame & Slot Foto, dan Panel Upload Hasil Edit --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Card 1: Informasi Pelanggan & Sesi Booking --}}
            <div class="rounded-lg border border-slate-200 bg-white p-5">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Informasi Pelanggan & Studio</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-slate-400">Nama Pelanggan</p>
                        <p class="mt-0.5 text-sm font-bold text-[#172033]">{{ $editRequest->nama_pelanggan }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Nomor WhatsApp</p>
                        <p class="mt-0.5 font-mono text-sm font-bold text-[#172033]">{{ $editRequest->nomor_wa }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Studio</p>
                        <p class="mt-0.5 text-sm font-bold text-[#172033]">{{ $editRequest->room?->nama_room ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Booking Terkait</p>
                        @if ($editRequest->booking)
                            <a href="{{ route('admin.booking.show', $editRequest->booking->id_booking) }}"
                                class="mt-0.5 inline-block font-mono text-sm font-bold text-[#163b71] underline">
                                {{ $editRequest->booking->kode_booking }}
                            </a>
                            @if ($editRequest->detail)
                                <p class="text-xs text-slate-500">
                                    Sesi: {{ $editRequest->detail->tanggal_sesi?->format('d M Y') }} ({{ $editRequest->detail->jam_mulai }} - {{ $editRequest->detail->jam_selesai }})
                                </p>
                            @endif
                        @else
                            <p class="mt-0.5 text-xs font-semibold text-amber-700">Tidak terhubung ke booking otomatis</p>
                        @endif
                    </div>
                </div>

                {{-- Form Koreksi Data (Hanya saat menunggu_verifikasi) --}}
                @if ($st === 'menunggu_verifikasi')
                    <details class="mt-5 rounded-md border border-slate-200 bg-slate-50 p-4">
                        <summary class="cursor-pointer text-xs font-bold text-slate-700">
                            <i class="fa-solid fa-pen-to-square mr-1"></i> Koreksi Data Pelanggan / Kaitkan Kode Booking Manual
                        </summary>
                        <form method="POST" action="{{ route('admin.edit-foto.update', $editRequest->id_request) }}" class="mt-4 space-y-3">
                            @csrf
                            @method('PATCH')
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="field-label text-xs">Nama Pelanggan</label>
                                    <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan', $editRequest->nama_pelanggan) }}" class="field-input bg-white text-sm" required>
                                </div>
                                <div>
                                    <label class="field-label text-xs">Nomor WhatsApp</label>
                                    <input type="text" name="nomor_wa" value="{{ old('nomor_wa', $editRequest->nomor_wa) }}" class="field-input bg-white text-sm" required>
                                </div>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="field-label text-xs">Kode Booking (opsional)</label>
                                    <input type="text" name="kode_booking" value="{{ old('kode_booking', $editRequest->booking?->kode_booking) }}"
                                        class="field-input bg-white font-mono text-sm uppercase" placeholder="BK-XXXXXXXX">
                                </div>
                                <div class="flex items-end pb-1">
                                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                                        <input type="checkbox" name="mark_checked" value="1" class="rounded border-slate-300">
                                        Hapus penanda "Perlu dicek"
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="field-label text-xs">Catatan</label>
                                <textarea name="catatan" rows="2" class="field-input bg-white text-sm">{{ old('catatan', $editRequest->catatan) }}</textarea>
                            </div>
                            <button type="submit" class="rounded-md bg-[#172033] px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">
                                Simpan Koreksi
                            </button>
                        </form>
                    </details>
                @endif
            </div>

            {{-- Card 2: Detail Pilihan Frame & Kode Foto per Slot (Dari Google Drive) --}}
            <div class="rounded-lg border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Pilihan Frame & Daftar Kode Foto (Google Drive)</h2>
                    </div>
                    <span class="rounded bg-slate-100 px-2.5 py-1 text-xs font-bold text-[#172033]">
                        Total {{ $editRequest->items->count() }} Slot Foto
                    </span>
                </div>

                {{-- Ringkasan Frame yang Dipilih Pelanggan --}}
                <div class="mt-3 flex flex-wrap items-center justify-between gap-4 rounded-md border border-slate-200 bg-[#f8f7f2] p-3.5">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jenis Layanan & Frame</p>
                        @if ($editRequest->jenis_layanan === 'custom_frame')
                            <p class="mt-0.5 text-sm font-black text-[#7b2631]">
                                Custom Frame (Request Desain Khusus · {{ $editRequest->jumlah_slot ?? $editRequest->items->count() }} Slot Foto)
                            </p>
                        @else
                            <p class="mt-0.5 text-sm font-black text-[#172033]">
                                Foto + Frame Katalog:
                                @if ($editRequest->frame)
                                    <span class="font-mono text-[#7b2631]">{{ $editRequest->frame->kode_frame }}</span> — {{ $editRequest->frame->nama_frame }} ({{ $editRequest->frame->jumlah_slot }} Slot)
                                @else
                                    Template Studio ({{ $editRequest->jumlah_slot ?? $editRequest->items->count() }} Slot)
                                @endif
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Daftar Kode Foto per Slot --}}
                <div class="mt-4 divide-y divide-slate-200 rounded-md border border-slate-200">
                    @foreach ($editRequest->items as $idx => $item)
                        <div class="flex flex-wrap items-center justify-between gap-2 p-3.5">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-6 w-16 shrink-0 items-center justify-center rounded bg-[#172033] text-[11px] font-bold text-white">
                                    Slot #{{ $idx + 1 }}
                                </span>
                                <div>
                                    <span class="font-mono text-sm font-black text-[#172033]">{{ $item->nama_file }}</span>
                                    @if ($item->instruksi_edit)
                                        <p class="text-xs text-slate-600">Instruksi: {{ $item->instruksi_edit }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($editRequest->catatan)
                    <div class="mt-4 rounded-md border border-amber-200 bg-amber-50/70 p-3.5">
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-900">Catatan Pelanggan / Konsep Custom Frame</p>
                        <p class="mt-1 text-sm text-slate-800">{{ $editRequest->catatan }}</p>
                    </div>
                @endif
            </div>

            {{-- Card 3: PANEL UPLOAD HASIL EDIT (Foto + Frame & Custom Frame) --}}
            <div class="rounded-lg border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#7b2631]">Upload Hasil Edit (Foto + Frame / Custom Frame)</h2>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Unggah file hasil penggabungan Foto + Frame maupun desain Custom Frame untuk pelanggan ini.
                        </p>
                    </div>
                    <span class="status-badge border border-slate-200 bg-slate-100 text-[#172033]">
                        {{ $uploadedPhotos->count() }} File Terunggah
                    </span>
                </div>

                @if (in_array($st, ['diproses', 'selesai', 'dikirim'], true))
                    {{-- Form Upload File Hasil Edit --}}
                    <form method="POST" action="{{ route('admin.edit-foto.upload', $editRequest->id_request) }}"
                        enctype="multipart/form-data"
                        class="mt-4 space-y-4 rounded-md border border-slate-200 bg-[#f8f7f2] p-4">
                        @csrf

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="field-label text-xs">Kategori Hasil yang Diupload *</label>
                                <select name="kategori_hasil" class="field-input bg-white text-xs font-bold" required>
                                    <option value="foto_frame" @selected($editRequest->jenis_layanan !== 'custom_frame')>
                                        Hasil Foto + Frame (Gabungan Foto & Frame)
                                    </option>
                                    <option value="custom_frame" @selected($editRequest->jenis_layanan === 'custom_frame')>
                                        Hasil Custom Frame (Desain Frame Khusus + Foto)
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="field-label text-xs">Kaitkan ke Slot Tertentu (Opsional)</label>
                                <select name="id_item" class="field-input bg-white text-xs">
                                    <option value="">— Hasil Gabungan Semua Slot Frame —</option>
                                    @foreach ($editRequest->items as $idx => $item)
                                        <option value="{{ $item->id_item }}">
                                            Slot #{{ $idx + 1 }} ({{ $item->nama_file }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="field-label text-xs">Pilih File Gambar Hasil Edit (JPG, PNG, WEBP — Bisa pilih beberapa file sekaligus) *</label>
                            <input type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png,.webp"
                                class="field-input bg-white text-xs" required>
                            @error('photos')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                            @error('photos.*')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="rounded-md bg-[#172033] px-4 py-2.5 text-xs font-bold text-white hover:bg-[#7b2631]">
                            <i class="fa-solid fa-upload mr-1"></i> Upload File Hasil Edit Sekarang
                        </button>
                    </form>

                    {{-- Opsional: Simpan Link Folder Google Drive Eksternal --}}
                    <form method="POST" action="{{ route('admin.edit-foto.drive-link', $editRequest->id_request) }}"
                        class="mt-3 flex flex-wrap items-end gap-2 rounded-md border border-slate-200 bg-slate-50 p-3">
                        @csrf
                        <div class="min-w-[240px] flex-1">
                            <label class="field-label text-xs">Link Folder Google Drive Hasil Edit (Opsional — jika ingin menyertakan link Drive)</label>
                            <input type="url" name="drive_folder_url" value="{{ old('drive_folder_url', $editRequest->drive_folder_url) }}"
                                class="field-input bg-white text-xs" placeholder="https://drive.google.com/drive/folders/...">
                        </div>
                        <button type="submit" class="h-9 rounded-md border border-slate-300 bg-white px-3.5 text-xs font-bold text-[#172033] hover:bg-slate-100">
                            Simpan Link
                        </button>
                    </form>
                @else
                    <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3.5 text-xs text-amber-900">
                        Klik tombol <strong>Terima & Masukkan Antrian</strong> lalu <strong>Ambil & Kerjakan</strong> di panel kanan terlebih dahulu untuk mulai meng-upload hasil edit.
                    </div>
                @endif

                {{-- Daftar File Hasil Edit yang Sudah Diupload --}}
                @if ($uploadedPhotos->isNotEmpty())
                    <div class="mt-5 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Galeri Hasil Edit yang Sudah Terunggah</h3>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($uploadedPhotos as $photo)
                                <div class="flex flex-col justify-between rounded-md border border-slate-200 bg-white p-3">
                                    <div>
                                        <div class="relative aspect-4/3 w-full overflow-hidden rounded border border-slate-100 bg-slate-100">
                                            <img src="{{ $photo->drive_url }}" alt="{{ $photo->nama_file }}"
                                                class="h-full w-full object-contain">
                                            <span class="absolute left-2 top-2 rounded px-2 py-0.5 text-[10px] font-bold {{ $photo->kategori_hasil === 'custom_frame' ? 'bg-[#7b2631] text-white' : 'bg-[#172033] text-white' }}">
                                                {{ $photo->kategori_hasil === 'custom_frame' ? 'Custom Frame' : 'Foto + Frame' }}
                                            </span>
                                        </div>
                                        <p class="mt-2 truncate font-mono text-xs font-bold text-[#172033]" title="{{ $photo->nama_file }}">
                                            {{ $photo->nama_file }}
                                        </p>
                                        <p class="text-[11px] text-slate-500">
                                            {{ number_format(($photo->ukuran_file ?? 0) / 1024, 0) }} KB
                                            @if ($photo->item)
                                                · Slot: {{ $photo->item->nama_file }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between gap-2 border-t border-slate-100 pt-2">
                                        <a href="{{ $photo->drive_url }}" target="_blank"
                                            class="text-xs font-bold text-[#163b71] hover:underline">
                                            Lihat / Unduh <i class="fa-solid fa-arrow-up-right-from-square ml-0.5 text-[10px]"></i>
                                        </a>
                                        @if (in_array($st, ['diproses', 'selesai', 'dikirim'], true))
                                            <form method="POST" action="{{ route('admin.edit-foto.photo.destroy', [$editRequest->id_request, $photo->id_photo]) }}"
                                                onsubmit="return confirm('Hapus file hasil edit ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-600 hover:underline">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Kolom Kanan (1/3): Panel Tindakan Status & Kirim WhatsApp --}}
        <div class="space-y-6">
            <div class="rounded-lg border border-slate-200 bg-white p-5">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Tindakan Operasional</h2>

                <div class="mt-4 space-y-3">
                    @if ($st === 'menunggu_verifikasi')
                        <form method="POST" action="{{ route('admin.edit-foto.accept', $editRequest->id_request) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-md bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-800">
                                <i class="fa-solid fa-check mr-1"></i> Terima & Masukkan Antrian
                            </button>
                        </form>
                    @endif

                    @if ($st === 'pending')
                        <form method="POST" action="{{ route('admin.edit-foto.claim', $editRequest->id_request) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-md bg-[#172033] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-800">
                                Ambil & Mulai Kerjakan
                            </button>
                        </form>
                    @endif

                    @if (in_array($st, ['menunggu_verifikasi', 'pending'], true))
                        <details class="rounded-md border border-red-200 bg-red-50/50 p-3">
                            <summary class="cursor-pointer text-xs font-bold text-red-700">
                                <i class="fa-solid fa-xmark mr-1"></i> Tolak Request Ini
                            </summary>
                            <form method="POST" action="{{ route('admin.edit-foto.reject', $editRequest->id_request) }}" class="mt-3 space-y-2">
                                @csrf
                                <label class="block text-xs font-semibold text-red-900">Alasan Penolakan *</label>
                                <textarea name="alasan_penolakan" rows="2" maxlength="500" required
                                    class="field-input bg-white text-xs" placeholder="Contoh: Nomor file foto tidak ditemukan di folder Drive sesi"></textarea>
                                <button type="submit" class="w-full rounded-md bg-red-600 px-3 py-2 text-xs font-bold text-white hover:bg-red-700">
                                    Konfirmasi Tolak
                                </button>
                            </form>
                        </details>
                    @endif

                    @if ($st === 'diproses')
                        <div class="rounded-md border border-sky-200 bg-sky-50 p-3.5 text-xs text-sky-900">
                            <p class="font-bold">Sedang Dikerjakan oleh: {{ $editRequest->handler?->nama ?? 'Admin' }}</p>
                            <p class="mt-1">
                                1. Upload file hasil <strong>Foto + Frame</strong> atau <strong>Custom Frame</strong> pada panel di sebelah kiri.<br>
                                2. Setelah terupload ({{ $uploadedPhotos->count() }} file saat ini), klik tombol <strong>Tandai Selesai</strong> di bawah.
                            </p>
                        </div>
                        <form method="POST" action="{{ route('admin.edit-foto.complete', $editRequest->id_request) }}">
                            @csrf
                            <button type="submit"
                                @disabled($uploadedPhotos->isEmpty() && empty($editRequest->drive_folder_url))
                                class="w-full rounded-md bg-teal-700 px-4 py-2.5 text-xs font-bold text-white hover:bg-teal-800 disabled:cursor-not-allowed disabled:opacity-50">
                                <i class="fa-solid fa-check mr-1"></i> Tandai Selesai Edit
                            </button>
                        </form>
                    @endif

                    @if (in_array($st, ['selesai', 'dikirim'], true))
                        {{-- Form Kirim Hasil ke WhatsApp Pelanggan --}}
                        <form method="POST" action="{{ route('admin.edit-foto.send-wa', $editRequest->id_request) }}"
                            class="space-y-3 rounded-md border border-teal-200 bg-teal-50/60 p-3.5">
                            @csrf
                            <p class="text-xs font-bold text-teal-950">Kirim Hasil Edit ke WhatsApp Pelanggan</p>
                            <div>
                                <label class="field-label text-xs">Pesan WhatsApp *</label>
                                <textarea name="pesan" rows="4" class="field-input bg-white text-xs" required>{{ old('pesan', $defaultWaMessage) }}</textarea>
                            </div>
                            <button type="submit" class="w-full rounded-md bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-800">
                                {{ $st === 'dikirim' ? 'Kirim Ulang ke WhatsApp' : 'Kirim Hasil via WhatsApp' }}
                            </button>
                        </form>
                    @endif

                    @if ($st === 'dikirim')
                        <form method="POST" action="{{ route('admin.edit-foto.reopen', $editRequest->id_request) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Buka Kembali untuk Revisi
                            </button>
                        </form>
                    @endif

                    @if ($st === 'ditolak')
                        <div class="rounded-md border border-red-200 bg-red-50 p-3.5 text-xs text-red-900">
                            <p class="font-bold">Alasan Penolakan:</p>
                            <p class="mt-1">{{ $editRequest->alasan_penolakan }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Log Riwayat Status --}}
            <div class="rounded-lg border border-slate-200 bg-white p-5">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Riwayat Perubahan Status</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($editRequest->statusLogs as $log)
                        <div class="border-l-2 border-slate-200 pl-3 text-xs">
                            <p class="font-bold text-slate-800">
                                @if ($log->status_lama)
                                    {{ $log->status_lama }} <i class="fa-solid fa-arrow-right text-[10px] text-slate-400"></i>
                                @endif
                                {{ $log->status_baru }}
                            </p>
                            <p class="text-slate-500">
                                {{ $log->changed_at?->translatedFormat('d M Y, H:i') }}
                                @if ($log->admin)
                                    · oleh {{ $log->admin->nama }}
                                @endif
                            </p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada log status.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
