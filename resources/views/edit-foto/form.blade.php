@extends('layouts.app')
@section('title', 'Pilih Frame & Edit Foto — ' . $room->nama_room)
@section('content')
<div class="mx-auto max-w-2xl">
    {{-- Header Identitas Studio --}}
    <div class="border border-[var(--line)] bg-[#faf7f1] p-6 sm:p-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="t-label text-[#7b2631]">Layanan Pasca-Sesi Photo Studio</p>
                <h1 class="t-h2 mt-1">{{ $room->cabang_label }} — {{ $room->nama_room }}</h1>
                <p class="mt-1 text-sm text-[#5c5549]">{{ $room->jenis_room_label }} · Kapasitas {{ $room->kapasitas }} orang</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center border border-[var(--line)] bg-[#e7dfcf] font-serif text-lg font-normal italic text-[#7b2631]">
                CP
            </div>
        </div>

        @if (!$isInactive)
            <div class="mt-5 border border-[var(--line)] bg-[#f4efe5] p-4 text-xs leading-relaxed text-[#5c5549]">
                <p class="font-semibold text-[#1e1b16]">Alur Pemilihan Foto &amp; Template Frame:</p>
                <ol class="mt-1.5 list-inside list-decimal space-y-1">
                    <li>Buka tautan <strong>Google Drive</strong> hasil sesi foto Anda (~80 file) dan catat <strong>Kode / Nomor Foto</strong> pilihan Anda.</li>
                    <li>Klik salah satu <strong>Contoh Template Frame</strong> di bawah (atau pilih <strong>Custom Frame</strong>).</li>
                    <li>Masukkan <strong>Kode Foto</strong> pada setiap <strong>Slot Foto</strong> (jumlah slot otomatis mengikuti template frame yang Anda pilih).</li>
                </ol>
            </div>
        @endif
    </div>

    @if ($isInactive)
        <div class="mt-6 border border-[var(--line)] bg-[#faf7f1] p-6 text-center sm:p-8">
            <div class="mx-auto flex h-12 w-12 items-center justify-center border border-[#7b2631] bg-[#e7dfcf] text-[#7b2631] font-serif italic text-lg">!</div>
            <h2 class="mt-4 t-h3">Formulir Tidak Tersedia</h2>
            <p class="mt-2 text-sm text-[#5c5549]">
                Formulir untuk studio ini sedang tidak aktif. Silakan hubungi admin studio kami.
            </p>
            <a href="{{ route('home') }}" class="mt-5 inline-block t-btn t-btn-primary">
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    @else
        @php
            $framesList = $frames ?? collect();
            $defaultFrame = $framesList->first();
            $oldJenis = old('jenis_layanan', 'foto_frame');
            $oldFrameId = old('id_frame', $defaultFrame?->id_frame);
            $selectedFrameObj = $framesList->firstWhere('id_frame', $oldFrameId) ?? $defaultFrame;
            $defaultSlotCount = $oldJenis === 'foto_frame' && $selectedFrameObj ? $selectedFrameObj->jumlah_slot : (int) old('jumlah_slot', 2);
            $oldItems = old('items');
            if (!is_array($oldItems) || count($oldItems) === 0) {
                $oldItems = [];
                for ($i = 0; $i < max(1, $defaultSlotCount); $i++) {
                    $oldItems[] = ['nama_file' => '', 'instruksi_edit' => ''];
                }
            }
        @endphp

        <form method="POST" action="{{ route('edit-foto.store', $room->id_room) }}" class="mt-6 space-y-6" id="edit-foto-form">
            @csrf

            {{-- Honeypot anti-spam --}}
            <div class="hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            {{-- 1. Data Pelanggan --}}
            <div class="border border-[var(--line)] bg-[#faf7f1] p-6 sm:p-8">
                <h2 class="t-h3">1. Data Pelanggan</h2>
                <p class="mt-1 text-xs text-[#5c5549]">Gunakan nomor WhatsApp yang sama dengan saat booking agar sesi Anda otomatis terhubung.</p>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="nama_pelanggan" class="field-label">Nama Lengkap *</label>
                        <input type="text" id="nama_pelanggan" name="nama_pelanggan" maxlength="100"
                            value="{{ old('nama_pelanggan') }}"
                            class="field-input" placeholder="Masukkan nama lengkap Anda" required>
                        @error('nama_pelanggan')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="nomor_wa" class="field-label">Nomor WhatsApp *</label>
                            <input type="tel" id="nomor_wa" name="nomor_wa"
                                value="{{ old('nomor_wa') }}"
                                class="field-input" placeholder="081234567890" required>
                            @error('nomor_wa')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nomor_wa_confirmation" class="field-label">Ulangi Nomor WhatsApp *</label>
                            <input type="tel" id="nomor_wa_confirmation" name="nomor_wa_confirmation"
                                value="{{ old('nomor_wa_confirmation') }}"
                                class="field-input" placeholder="Ketik ulang nomor WhatsApp" required>
                            @error('nomor_wa_confirmation')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Pilih Jenis Layanan & Contoh Template Frame --}}
            <div class="border border-[var(--line)] bg-[#faf7f1] p-6 sm:p-8">
                <h2 class="t-h3">2. Pilih Contoh Template Frame</h2>
                <p class="mt-1 text-xs text-[#5c5549]">Klik salah satu contoh template frame di bawah atau pilih Custom Frame untuk desain khusus.</p>

                <input type="hidden" name="jenis_layanan" id="jenis_layanan" value="{{ $oldJenis }}">
                <input type="hidden" name="id_frame" id="id_frame" value="{{ $oldFrameId }}">
                <input type="hidden" name="jumlah_slot" id="jumlah_slot" value="{{ $defaultSlotCount }}">

                {{-- Tab Pilihan: Foto + Frame vs Custom Frame --}}
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <button type="button" data-mode="foto_frame"
                        class="mode-tab-btn flex flex-col items-start border p-4 text-left transition cursor-pointer {{ $oldJenis === 'foto_frame' ? 'border-[#1e1b16] bg-[#1e1b16] text-[#f4efe5]' : 'border-[var(--line)] bg-[#f4efe5] text-[#1e1b16] hover:border-[#1e1b16]' }}">
                        <span class="text-xs font-bold uppercase tracking-wider">Foto + Frame</span>
                        <span class="mt-1 text-[11px] {{ $oldJenis === 'foto_frame' ? 'text-[#e7dfcf]' : 'text-[#5c5549]' }}">
                            Pilih dari Contoh Template Frame Studio
                        </span>
                    </button>

                    <button type="button" data-mode="custom_frame"
                        class="mode-tab-btn flex flex-col items-start border p-4 text-left transition cursor-pointer {{ $oldJenis === 'custom_frame' ? 'border-[#7b2631] bg-[#7b2631] text-[#f4efe5]' : 'border-[var(--line)] bg-[#f4efe5] text-[#1e1b16] hover:border-[#7b2631]' }}">
                        <span class="text-xs font-bold uppercase tracking-wider">Custom Frame</span>
                        <span class="mt-1 text-[11px] {{ $oldJenis === 'custom_frame' ? 'text-[#e7dfcf]' : 'text-[#5c5549]' }}">
                            Request desain frame khusus (warna, tema, tulisan)
                        </span>
                    </button>
                </div>

                {{-- Panel A: Galeri Contoh Template Frame --}}
                <div id="panel-katalog-frame" class="mt-5 {{ $oldJenis === 'custom_frame' ? 'hidden' : '' }}">
                    @if ($framesList->isEmpty())
                        <p class="border border-[var(--line)] bg-[#f4efe5] p-4 text-xs text-[#5c5549]">
                            Katalog frame belum tersedia. Silakan tuliskan kode frame yang Anda inginkan pada kolom catatan.
                        </p>
                    @else
                        <label class="field-label mb-2.5">Klik Contoh Template Frame Pilihan Anda:</label>
                        <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3">
                            @foreach ($framesList as $frame)
                                @php
                                    $isSelected = (int) $oldFrameId === (int) $frame->id_frame;
                                    $previewUrl = $frame->preview_path ? asset('storage/' . $frame->preview_path) : null;
                                @endphp
                                <div data-frame-id="{{ $frame->id_frame }}"
                                    data-frame-slots="{{ $frame->jumlah_slot }}"
                                    data-frame-code="{{ $frame->kode_frame }}"
                                    data-frame-name="{{ $frame->nama_frame }}"
                                    data-selected="{{ $isSelected ? '1' : '0' }}"
                                    class="frame-card cursor-pointer border p-3 transition {{ $isSelected ? 'border-[#7b2631] bg-[#e7dfcf] ring-2 ring-[#7b2631]' : 'border-[var(--line)] bg-[#faf7f1] hover:border-[#1e1b16]' }}">

                                    {{-- Gambar Contoh Template Frame --}}
                                    <div class="relative mx-auto flex h-44 w-full items-center justify-center overflow-hidden border border-[var(--line)] bg-[#f4efe5] p-2">
                                        @if ($previewUrl)
                                            <img src="{{ $previewUrl }}" alt="Contoh {{ $frame->nama_frame }}"
                                                class="h-full w-full object-contain">
                                        @else
                                            <div class="flex h-full w-16 flex-col gap-1 p-1.5" style="background-color: {{ $frame->warna_aksen ?: '#1e1b16' }}">
                                                @for ($s = 1; $s <= min($frame->jumlah_slot, 4); $s++)
                                                    <div class="flex flex-1 items-center justify-center bg-white/90 text-[9px] font-bold text-[#1e1b16]">
                                                        Slot {{ $s }}
                                                    </div>
                                                @endfor
                                            </div>
                                        @endif
                                        <span class="absolute right-2 top-2 bg-[#1e1b16] px-2 py-0.5 text-[10px] font-semibold text-[#f4efe5]">
                                            {{ $frame->jumlah_slot }} Slot
                                        </span>
                                    </div>

                                    <div class="mt-2.5">
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="font-mono text-xs font-bold text-[#7b2631]">{{ $frame->kode_frame }}</span>
                                            @if ($previewUrl)
                                                <button type="button" data-preview-src="{{ $previewUrl }}" data-preview-title="{{ $frame->kode_frame }} — {{ $frame->nama_frame }} ({{ $frame->jumlah_slot }} Slot Foto)"
                                                    class="btn-zoom-frame text-[11px] font-semibold text-[#7b2631] hover:underline cursor-pointer">
                                                    Perbesar <i class="fa-solid fa-arrow-up-right-from-square ml-0.5 text-[9px]" aria-hidden="true"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <p class="mt-0.5 truncate text-xs font-semibold text-[#1e1b16]">{{ $frame->nama_frame }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Panel B: Pengaturan Jumlah Slot untuk Custom Frame --}}
                <div id="panel-custom-frame" class="mt-5 space-y-4 {{ $oldJenis === 'custom_frame' ? '' : 'hidden' }}">
                    <div class="border border-[var(--line)] bg-[#f4efe5] p-4">
                        <label class="field-label text-[#1e1b16]">Berapa Slot Foto yang Diinginkan pada Custom Frame?</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ([1, 2, 3, 4, 6] as $slotOption)
                                <button type="button" data-custom-slot="{{ $slotOption }}"
                                    class="custom-slot-btn border px-3.5 py-2 text-xs font-semibold transition cursor-pointer {{ (int) $defaultSlotCount === $slotOption ? 'border-[#7b2631] bg-[#7b2631] text-[#f4efe5]' : 'border-[var(--line)] bg-[#faf7f1] text-[#1e1b16] hover:bg-[#e7dfcf]' }}">
                                    {{ $slotOption }} Slot Foto
                                </button>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-[#5c5549]">
                            Tuliskan detail warna, konsep, atau teks/ucapan untuk Custom Frame Anda pada bagian Catatan di bawah.
                        </p>
                    </div>
                </div>
            </div>

            {{-- 3. Daftar Kode Foto Sesuai Slot Frame --}}
            <div class="border border-[var(--line)] bg-[#faf7f1] p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="t-h3">3. Masukkan Kode Foto per Slot</h2>
                            <span id="slot-badge-indicator" class="border border-[var(--line)] bg-[#e7dfcf] px-2.5 py-0.5 text-xs font-semibold text-[#7b2631]">
                                {{ count($oldItems) }} Slot Foto
                            </span>
                        </div>
                        <p id="selected-frame-summary" class="mt-1 text-xs text-[#5c5549]">
                            Masukkan kode/nomor file foto dari Google Drive (contoh: <code>Kode 1</code>, <code>Kode 2</code>, atau <code>IMG_0012</code>) untuk setiap slot pada contoh frame.
                        </p>
                    </div>
                    <button type="button" id="add-photo-row"
                        class="border border-[var(--line)] bg-[#f4efe5] px-3.5 py-2 text-xs font-semibold text-[#1e1b16] transition hover:bg-[#e7dfcf] cursor-pointer">
                        <i class="fa-solid fa-plus mr-1" aria-hidden="true"></i> Tambah Slot
                    </button>
                </div>

                @error('items')
                    <p class="field-error mt-2">{{ $message }}</p>
                @enderror

                <div id="photo-items-container" class="mt-5 space-y-3">
                    @foreach ($oldItems as $idx => $item)
                        <div class="photo-item-row border border-[var(--line)] bg-[#f4efe5] p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="row-number text-xs font-bold uppercase tracking-wider text-[#7b2631]">
                                    Slot Foto #{{ $idx + 1 }}
                                </span>
                                <button type="button" class="remove-photo-row text-xs font-semibold text-[#7b2631] hover:underline cursor-pointer {{ count($oldItems) <= 1 ? 'hidden' : '' }}">
                                    <i class="fa-solid fa-trash-can mr-1" aria-hidden="true"></i> Hapus
                                </button>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-12">
                                <div class="sm:col-span-5">
                                    <label class="field-label text-xs">Kode / Nomor Foto di Drive *</label>
                                    <input type="text" name="items[{{ $idx }}][nama_file]"
                                        value="{{ $item['nama_file'] ?? '' }}"
                                        class="field-input font-mono text-sm font-medium"
                                        placeholder="Contoh: Kode {{ $idx + 1 }} / IMG_00{{ $idx + 1 }}" required maxlength="255">
                                    @error("items.{$idx}.nama_file")
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="sm:col-span-7">
                                    <label class="field-label text-xs">Catatan Slot / Instruksi Edit <span class="font-normal text-[#5c5549]">(opsional)</span></label>
                                    <input type="text" name="items[{{ $idx }}][instruksi_edit]"
                                        value="{{ $item['instruksi_edit'] ?? '' }}"
                                        class="field-input text-sm"
                                        placeholder="Contoh: Posisi atas, cerahkan sedikit" maxlength="500">
                                    @error("items.{$idx}.instruksi_edit")
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 4. Catatan Desain & Persetujuan --}}
            <div class="border border-[var(--line)] bg-[#faf7f1] p-6 sm:p-8 space-y-5">
                <div>
                    <label for="catatan" id="catatan-label" class="field-label">
                        Catatan Tambahan / Detail Desain Custom Frame <span class="font-normal text-[#5c5549]">(opsional)</span>
                    </label>
                    <textarea id="catatan" name="catatan" rows="3" maxlength="1000"
                        class="field-input"
                        placeholder="Jika memilih Custom Frame, tuliskan warna/tema/tulisan yang diinginkan di sini (Contoh: Tema ulang tahun maroon, tulisan 'Happy Birthday')...">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="border border-[var(--line)] bg-[#f4efe5] p-4">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" name="persetujuan_data" value="1"
                            @checked(old('persetujuan_data'))
                            class="mt-1 h-4 w-4 border-[var(--line-strong)] text-[#7b2631] focus:ring-[#7b2631]" required>
                        <span class="text-xs leading-relaxed text-[#5c5549]">
                            Saya menyetujui penggunaan nomor WhatsApp di atas untuk menerima tautan hasil edit foto &amp; cetak frame dari <strong>Clicked Picked Studios</strong>. *
                        </span>
                    </label>
                    @error('persetujuan_data')
                        <p class="field-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="t-btn t-btn-primary w-full text-base">
                    <span>Kirim Pilihan Frame &amp; Kode Foto</span>
                    <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                </button>
            </div>
        </form>

        {{-- Modal Preview Contoh Template Frame --}}
        <div id="frame-preview-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
            <div class="relative max-w-md w-full border border-[var(--line)] bg-[#faf7f1] p-5 shadow-lg">
                <div class="flex items-center justify-between border-b border-[var(--line)] pb-3">
                    <h3 id="modal-frame-title" class="t-h3 text-sm">Contoh Template Frame</h3>
                    <button type="button" id="close-frame-modal" class="border border-[var(--line)] bg-[#e7dfcf] px-2.5 py-1 text-xs font-semibold text-[#1e1b16] hover:bg-[#d5cbb8] cursor-pointer">
                        <i class="fa-solid fa-xmark mr-1" aria-hidden="true"></i> Tutup
                    </button>
                </div>
                <div class="mt-4 flex justify-center border border-[var(--line)] bg-[#f4efe5] p-3">
                    <img id="modal-frame-img" src="" alt="Preview Frame" class="max-h-[65vh] w-auto object-contain">
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const container = document.getElementById('photo-items-container');
    const addBtn = document.getElementById('add-photo-row');
    const jenisInput = document.getElementById('jenis_layanan');
    const frameInput = document.getElementById('id_frame');
    const slotInput = document.getElementById('jumlah_slot');
    const slotBadge = document.getElementById('slot-badge-indicator');
    const panelKatalog = document.getElementById('panel-katalog-frame');
    const panelCustom = document.getElementById('panel-custom-frame');
    const modeBtns = document.querySelectorAll('.mode-tab-btn');
    const frameCards = document.querySelectorAll('.frame-card');
    const customSlotBtns = document.querySelectorAll('.custom-slot-btn');
    const modal = document.getElementById('frame-preview-modal');
    const modalImg = document.getElementById('modal-frame-img');
    const modalTitle = document.getElementById('modal-frame-title');
    const closeModalBtn = document.getElementById('close-frame-modal');
    const maxRows = 50;

    if (!container || !addBtn) return;

    function createRowElement(idx) {
        const div = document.createElement('div');
        div.className = 'photo-item-row border border-[var(--line)] bg-[#f4efe5] p-4';
        div.innerHTML = `
            <div class="mb-2 flex items-center justify-between">
                <span class="row-number text-xs font-bold uppercase tracking-wider text-[#7b2631]">Slot Foto #${idx + 1}</span>
                <button type="button" class="remove-photo-row text-xs font-semibold text-[#7b2631] hover:underline cursor-pointer">
                    <i class="fa-solid fa-trash-can mr-1" aria-hidden="true"></i> Hapus
                </button>
            </div>
            <div class="grid gap-3 sm:grid-cols-12">
                <div class="sm:col-span-5">
                    <label class="field-label text-xs">Kode / Nomor Foto di Drive *</label>
                    <input type="text" name="items[${idx}][nama_file]" class="field-input font-mono text-sm font-medium" placeholder="Contoh: Kode ${idx + 1} / IMG_00${idx + 1}" required maxlength="255">
                </div>
                <div class="sm:col-span-7">
                    <label class="field-label text-xs">Catatan Slot / Instruksi Edit <span class="font-normal text-[#5c5549]">(opsional)</span></label>
                    <input type="text" name="items[${idx}][instruksi_edit]" class="field-input text-sm" placeholder="Contoh: Posisi slot #${idx + 1}, cerahkan sedikit" maxlength="500">
                </div>
            </div>
        `;
        return div;
    }

    function updateRowIndices() {
        const rows = container.querySelectorAll('.photo-item-row');
        rows.forEach((row, index) => {
            const label = row.querySelector('.row-number');
            if (label) label.textContent = 'Slot Foto #' + (index + 1);

            const fileInput = row.querySelector('input[name*="[nama_file]"]');
            if (fileInput) fileInput.name = `items[${index}][nama_file]`;

            const instInput = row.querySelector('input[name*="[instruksi_edit]"]');
            if (instInput) instInput.name = `items[${index}][instruksi_edit]`;

            const removeBtn = row.querySelector('.remove-photo-row');
            if (removeBtn) {
                removeBtn.classList.toggle('hidden', rows.length <= 1);
            }
        });
        if (slotBadge) {
            slotBadge.textContent = rows.length + ' Slot Foto';
        }
        if (slotInput) {
            slotInput.value = rows.length;
        }
    }

    function setSlotCount(targetCount) {
        const count = Math.max(1, Math.min(maxRows, parseInt(targetCount, 10) || 2));
        let rows = container.querySelectorAll('.photo-item-row');

        while (rows.length < count) {
            container.appendChild(createRowElement(rows.length));
            rows = container.querySelectorAll('.photo-item-row');
        }

        while (rows.length > count) {
            rows[rows.length - 1].remove();
            rows = container.querySelectorAll('.photo-item-row');
        }

        updateRowIndices();
    }

    // Mode toggle: Foto + Frame vs Custom Frame
    modeBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const mode = this.getAttribute('data-mode');
            jenisInput.value = mode;

            modeBtns.forEach(b => {
                const isCurrent = b.getAttribute('data-mode') === mode;
                if (b.getAttribute('data-mode') === 'foto_frame') {
                    b.className = 'mode-tab-btn flex flex-col items-start border p-4 text-left transition cursor-pointer ' +
                        (isCurrent ? 'border-[#1e1b16] bg-[#1e1b16] text-[#f4efe5]' : 'border-[var(--line)] bg-[#f4efe5] text-[#1e1b16] hover:border-[#1e1b16]');
                } else {
                    b.className = 'mode-tab-btn flex flex-col items-start border p-4 text-left transition cursor-pointer ' +
                        (isCurrent ? 'border-[#7b2631] bg-[#7b2631] text-[#f4efe5]' : 'border-[var(--line)] bg-[#f4efe5] text-[#1e1b16] hover:border-[#7b2631]');
                }
            });

            if (mode === 'foto_frame') {
                panelKatalog?.classList.remove('hidden');
                panelCustom?.classList.add('hidden');
                const activeCard = document.querySelector('.frame-card[data-selected="1"]') || frameCards[0];
                if (activeCard) {
                    activeCard.click();
                }
            } else {
                panelKatalog?.classList.add('hidden');
                panelCustom?.classList.remove('hidden');
                frameInput.value = '';
            }
        });
    });

    // Klik kartu Frame di katalog -> otomatis sesuaikan jumlah slot foto!
    frameCards.forEach(card => {
        card.addEventListener('click', function (e) {
            if (e.target.closest('.btn-zoom-frame')) return;
            const id = this.getAttribute('data-frame-id');
            const slots = parseInt(this.getAttribute('data-frame-slots'), 10) || 2;
            frameInput.value = id;

            frameCards.forEach(c => {
                const selected = c.getAttribute('data-frame-id') === id;
                c.setAttribute('data-selected', selected ? '1' : '0');
                c.className = 'frame-card cursor-pointer border p-3 transition ' +
                    (selected ? 'border-[#7b2631] bg-[#e7dfcf] ring-2 ring-[#7b2631]' : 'border-[var(--line)] bg-[#faf7f1] hover:border-[#1e1b16]');
            });

            setSlotCount(slots);
        });
    });

    // Zoom contoh gambar template frame
    document.querySelectorAll('.btn-zoom-frame').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const src = this.getAttribute('data-preview-src');
            const title = this.getAttribute('data-preview-title');
            if (modal && modalImg && src) {
                modalImg.src = src;
                if (modalTitle) modalTitle.textContent = title || 'Contoh Template Frame';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        });
    });

    closeModalBtn?.addEventListener('click', function () {
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
    });

    modal?.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });

    // Klik jumlah slot pada mode Custom Frame
    customSlotBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const slots = parseInt(this.getAttribute('data-custom-slot'), 10) || 2;
            customSlotBtns.forEach(b => {
                const active = parseInt(b.getAttribute('data-custom-slot'), 10) === slots;
                b.className = 'custom-slot-btn border px-3.5 py-2 text-xs font-semibold transition cursor-pointer ' +
                    (active ? 'border-[#7b2631] bg-[#7b2631] text-[#f4efe5]' : 'border-[var(--line)] bg-[#faf7f1] text-[#1e1b16] hover:bg-[#e7dfcf]');
            });
            setSlotCount(slots);
        });
    });

    addBtn.addEventListener('click', function () {
        const rows = container.querySelectorAll('.photo-item-row');
        if (rows.length >= maxRows) return;
        container.appendChild(createRowElement(rows.length));
        updateRowIndices();
    });

    container.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-photo-row');
        if (!btn) return;
        const rows = container.querySelectorAll('.photo-item-row');
        if (rows.length > 1) {
            btn.closest('.photo-item-row').remove();
            updateRowIndices();
        }
    });
})();
</script>
@endpush
