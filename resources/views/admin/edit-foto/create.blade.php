@extends('layouts.admin')
@section('title', 'Input Request Edit Foto Manual')
@section('content')
<div class="mx-auto max-w-3xl">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-[#7b2631]">Antrian Edit Foto</p>
            <h1 class="mt-0.5 text-2xl font-black text-[#172033]">Input Request Manual</h1>
        </div>
        <a href="{{ route('admin.edit-foto.index') }}" class="rounded-md border border-[#d6d3c9] bg-white px-3.5 py-2 text-xs font-bold text-[#172033] hover:bg-[#f8f7f2]">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="mt-5 rounded-lg border border-[#dedad0] bg-white p-6">
        <form method="POST" action="{{ route('admin.edit-foto.store') }}" class="space-y-5">
            @csrf

            <div class="rounded-md border border-[#e3e1db] bg-[#f8f7f2] p-4">
                <label for="kode_booking" class="field-label">Kode Booking Terkait <span class="font-normal text-slate-500">(opsional — isi otomatis data pelanggan)</span></label>
                <div class="flex gap-2">
                    <input type="text" id="kode_booking" name="kode_booking" value="{{ old('kode_booking') }}"
                        class="field-input font-mono text-sm uppercase" placeholder="Contoh: BK-1A2B3C4D">
                    <button type="button" id="btn-lookup-booking"
                        class="shrink-0 rounded-md bg-[#172033] px-4 py-2.5 text-xs font-bold text-white hover:bg-[#7b2631]">
                        Cari Booking
                    </button>
                </div>
                <p id="lookup-status-msg" class="mt-1.5 text-xs text-slate-500"></p>
                @error('kode_booking')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="id_room" class="field-label">Studio *</label>
                    <select id="id_room" name="id_room" class="field-input" required>
                        <option value="">-- Pilih Studio --</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id_room }}" @selected(old('id_room') == $room->id_room)>
                                {{ $room->nama_room }} ({{ $room->jenis_room }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_room')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nomor_wa" class="field-label">Nomor WhatsApp *</label>
                    <input type="tel" id="nomor_wa" name="nomor_wa" value="{{ old('nomor_wa') }}"
                        class="field-input" placeholder="081234567890" required>
                    @error('nomor_wa')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="nama_pelanggan" class="field-label">Nama Pelanggan *</label>
                <input type="text" id="nama_pelanggan" name="nama_pelanggan" maxlength="100"
                    value="{{ old('nama_pelanggan') }}" class="field-input" placeholder="Masukkan nama pelanggan" required>
                @error('nama_pelanggan')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="jenis_layanan" class="field-label">Jenis Layanan Frame *</label>
                    <select id="jenis_layanan" name="jenis_layanan" class="field-input">
                        <option value="foto_frame" @selected(old('jenis_layanan', 'foto_frame') === 'foto_frame')>Foto + Frame (Katalog Studio)</option>
                        <option value="custom_frame" @selected(old('jenis_layanan') === 'custom_frame')>Custom Frame (Request Desain Khusus)</option>
                    </select>
                </div>

                <div id="admin-frame-select-wrap">
                    <label for="id_frame" class="field-label">Pilih Frame Katalog</label>
                    <select id="id_frame" name="id_frame" class="field-input">
                        <option value="">-- Pilih Frame --</option>
                        @foreach ($frames ?? [] as $frame)
                            <option value="{{ $frame->id_frame }}" data-slots="{{ $frame->jumlah_slot }}" @selected(old('id_frame') == $frame->id_frame)>
                                {{ $frame->kode_frame }} — {{ $frame->nama_frame }} ({{ $frame->jumlah_slot }} Slot)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between">
                    <label class="field-label mb-0">Daftar Kode Foto per Slot (Dari Google Drive) *</label>
                    <button type="button" id="admin-add-photo-row"
                        class="rounded-md border border-[#d6d3c9] bg-[#f8f7f2] px-3 py-1.5 text-xs font-bold text-[#172033] hover:bg-[#f3f0e8]">
                        + Tambah Slot
                    </button>
                </div>

                @php
                    $oldItems = old('items', [['nama_file' => '', 'instruksi_edit' => ''], ['nama_file' => '', 'instruksi_edit' => '']]);
                @endphp

                <div id="admin-photo-items" class="space-y-2.5">
                    @foreach ($oldItems as $idx => $item)
                        <div class="photo-item-row flex items-start gap-2 rounded-md border border-[#e3e1db] bg-[#f8f7f2] p-3">
                            <span class="slot-label mt-2.5 w-16 shrink-0 font-mono text-xs font-black text-[#7b2631]">Slot #{{ $idx + 1 }}</span>
                            <div class="flex-1 space-y-2 sm:flex sm:gap-2 sm:space-y-0">
                                <div class="sm:w-5/12">
                                    <input type="text" name="items[{{ $idx }}][nama_file]" value="{{ $item['nama_file'] ?? '' }}"
                                        class="field-input text-sm" placeholder="Kode Foto di Drive (misal: Kode 1)" required maxlength="255">
                                </div>
                                <div class="sm:w-7/12">
                                    <input type="text" name="items[{{ $idx }}][instruksi_edit]" value="{{ $item['instruksi_edit'] ?? '' }}"
                                        class="field-input text-sm" placeholder="Instruksi slot (opsional)" maxlength="500">
                                </div>
                            </div>
                            <button type="button" class="remove-photo-row mt-1 rounded-md px-2.5 py-2 text-xs font-bold text-red-600 hover:bg-red-50 {{ count($oldItems) <= 1 ? 'hidden' : '' }}">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
                @error('items')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="catatan" class="field-label">Catatan Tambahan / Konsep Custom Frame <span class="font-normal text-slate-400">(opsional)</span></label>
                <textarea id="catatan" name="catatan" rows="3" maxlength="1000" class="field-input"
                    placeholder="Catatan umum pengeditan atau detail desain Custom Frame">{{ old('catatan') }}</textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full rounded-md bg-[#172033] px-5 py-3 text-sm font-bold text-white hover:bg-[#7b2631]">
                    Simpan Request Edit Foto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const container = document.getElementById('admin-photo-items');
    const addBtn = document.getElementById('admin-add-photo-row');
    const lookupBtn = document.getElementById('btn-lookup-booking');
    const kodeInput = document.getElementById('kode_booking');
    const statusMsg = document.getElementById('lookup-status-msg');
    const frameSelect = document.getElementById('id_frame');

    function createRow(idx) {
        const div = document.createElement('div');
        div.className = 'photo-item-row flex items-start gap-2 rounded-md border border-[#e3e1db] bg-[#f8f7f2] p-3';
        div.innerHTML = `
            <span class="slot-label mt-2.5 w-16 shrink-0 font-mono text-xs font-black text-[#7b2631]">Slot #${idx + 1}</span>
            <div class="flex-1 space-y-2 sm:flex sm:gap-2 sm:space-y-0">
                <div class="sm:w-5/12">
                    <input type="text" name="items[${idx}][nama_file]" class="field-input text-sm" placeholder="Kode Foto di Drive (misal: Kode ${idx + 1})" required maxlength="255">
                </div>
                <div class="sm:w-7/12">
                    <input type="text" name="items[${idx}][instruksi_edit]" class="field-input text-sm" placeholder="Instruksi slot (opsional)" maxlength="500">
                </div>
            </div>
            <button type="button" class="remove-photo-row mt-1 rounded-md px-2.5 py-2 text-xs font-bold text-red-600 hover:bg-red-50"><i class="fa-solid fa-xmark"></i></button>
        `;
        return div;
    }

    function reindex() {
        const rows = container.querySelectorAll('.photo-item-row');
        rows.forEach((row, idx) => {
            const lbl = row.querySelector('.slot-label');
            if (lbl) lbl.textContent = `Slot #${idx + 1}`;
            row.querySelector('.remove-photo-row')?.classList.toggle('hidden', rows.length <= 1);
            const f = row.querySelector('input[name*="[nama_file]"]');
            const i = row.querySelector('input[name*="[instruksi_edit]"]');
            if (f) f.name = `items[${idx}][nama_file]`;
            if (i) i.name = `items[${idx}][instruksi_edit]`;
        });
    }

    frameSelect?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const slots = parseInt(opt?.getAttribute('data-slots') || '0', 10);
        if (!slots) return;
        let rows = container.querySelectorAll('.photo-item-row');
        while (rows.length < slots) {
            container.appendChild(createRow(rows.length));
            rows = container.querySelectorAll('.photo-item-row');
        }
        while (rows.length > slots) {
            rows[rows.length - 1].remove();
            rows = container.querySelectorAll('.photo-item-row');
        }
        reindex();
    });

    addBtn?.addEventListener('click', function () {
        const rows = container.querySelectorAll('.photo-item-row');
        if (rows.length >= 50) return;
        container.appendChild(createRow(rows.length));
        reindex();
    });

    container?.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-photo-row');
        if (!btn) return;
        if (container.querySelectorAll('.photo-item-row').length > 1) {
            btn.closest('.photo-item-row').remove();
            reindex();
        }
    });

    lookupBtn?.addEventListener('click', async function () {
        const kode = kodeInput.value.trim();
        if (!kode) return;
        statusMsg.textContent = 'Mencari kode booking...';
        statusMsg.className = 'mt-1.5 text-xs text-slate-500';
        try {
            const res = await fetch(`{{ route('admin.edit-foto.lookup-booking') }}?kode=${encodeURIComponent(kode)}`);
            const data = await res.json();
            if (data.found) {
                document.getElementById('nama_pelanggan').value = data.nama_pelanggan || '';
                document.getElementById('nomor_wa').value = data.nomor_wa || '';
                if (data.id_room) {
                    document.getElementById('id_room').value = data.id_room;
                }
                statusMsg.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Data pelanggan & studio berhasil diisi otomatis dari booking.';
                statusMsg.className = 'mt-1.5 text-xs font-bold text-emerald-700';
            } else {
                statusMsg.innerHTML = '<i class="fa-solid fa-xmark mr-1"></i> Kode booking tidak ditemukan.';
                statusMsg.className = 'mt-1.5 text-xs font-bold text-red-600';
            }
        } catch (err) {
            statusMsg.textContent = 'Gagal memeriksa kode booking.';
        }
    });
})();
</script>
@endpush
