@extends('layouts.admin')
@section('title', 'Paket Studio — ' . $room->nama_room)
@section('content')
@php
    $editPackageId = request('edit');
    $editingPackage = $editPackageId ? $packages->firstWhere('id_package', $editPackageId) : null;
@endphp

<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-black tracking-tight text-[#172033]">Paket {{ $room->nama_room }}</h1>
                <span class="rounded bg-[#f3f0e8] px-2 py-0.5 text-xs font-bold text-[#7b2631]">
                    {{ $room->cabang_label }}
                </span>
                <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">
                    {{ $room->jenis_room_label }}
                </span>
            </div>
            <p class="mt-0.5 text-xs text-slate-500">Kelola variasi durasi sesi, harga, benefit, dan status aktif/nonaktif paket untuk studio ini.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.studio.edit', $room->id_room) }}"
                class="rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-[#172033] hover:bg-slate-50">
                Edit Studio
            </a>
            <a href="{{ route('admin.studio.index') }}"
                class="rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-[#172033] hover:bg-slate-50">
                <i class="fa-solid fa-arrow-left mr-1"></i> Daftar Studio
            </a>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[1fr_360px]">
        {{-- Kolom Kiri: Daftar Paket --}}
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-4 py-3">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600">Daftar Paket Sesi ({{ $packages->count() }})</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/40 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="whitespace-nowrap px-4 py-3">Nama / Tipe Paket</th>
                            <th class="whitespace-nowrap px-4 py-3">Durasi</th>
                            <th class="whitespace-nowrap px-4 py-3">Harga</th>
                            <th class="whitespace-nowrap px-4 py-3">Status</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($packages as $package)
                            <tr class="hover:bg-slate-50/70 {{ $editingPackage && $editingPackage->id_package === $package->id_package ? 'bg-amber-50/60' : '' }}">
                                <td class="px-4 py-3.5 align-middle">
                                    <p class="whitespace-nowrap font-bold text-[#172033]">{{ $package->tipe_harga }}</p>
                                    @if($package->deskripsi_benefit)
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $package->deskripsi_benefit }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 align-middle font-semibold">{{ $package->durasi_menit }} menit</td>
                                <td class="whitespace-nowrap px-4 py-3.5 align-middle font-black text-[#7b2631]">Rp {{ number_format($package->harga, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                                    <span class="status-badge {{ $package->is_active ? 'border border-emerald-200 bg-emerald-100 text-emerald-900' : 'border border-slate-200 bg-slate-100 text-slate-600' }}">
                                        {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-right align-middle">
                                    <div class="inline-flex items-center gap-1.5">
                                        {{-- Tombol Edit Paket --}}
                                        <button type="button"
                                            class="btn-edit-package rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50"
                                            data-id="{{ $package->id_package }}"
                                            data-name="{{ $package->tipe_harga }}"
                                            data-price="{{ (int) $package->harga }}"
                                            data-duration="{{ $package->durasi_menit }}"
                                            data-benefit="{{ $package->deskripsi_benefit }}"
                                            data-active="{{ $package->is_active ? '1' : '0' }}"
                                            data-action="{{ route('admin.studio.packages.update', [$room->id_room, $package->id_package]) }}">
                                            <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                        </button>

                                        {{-- Tombol Toggle Status Aktif/Nonaktif --}}
                                        <form method="POST" action="{{ route('admin.studio.packages.update', [$room->id_room, $package->id_package]) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="tipe_harga" value="{{ $package->tipe_harga }}">
                                            <input type="hidden" name="harga" value="{{ $package->harga }}">
                                            <input type="hidden" name="durasi_menit" value="{{ $package->durasi_menit }}">
                                            <input type="hidden" name="deskripsi_benefit" value="{{ $package->deskripsi_benefit }}">
                                            <input type="hidden" name="is_active" value="{{ $package->is_active ? 0 : 1 }}">
                                            <button type="submit"
                                                class="rounded-md px-2.5 py-1.5 text-xs font-bold {{ $package->is_active ? 'border border-amber-300 bg-amber-50 text-amber-900 hover:bg-amber-100' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                                                {{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Belum ada paket pada studio ini. Tambahkan paket di formulir sebelah kanan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kolom Kanan: Form Tambah / Edit Paket --}}
        <div class="h-fit space-y-4 rounded-lg border border-slate-200 bg-white p-5">
            @if($editingPackage)
                {{-- Mode Edit Form --}}
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-amber-900">Mode Edit</span>
                        <h2 class="mt-1 text-base font-black text-[#172033]">Edit Paket Sesi</h2>
                    </div>
                    <a href="{{ route('admin.studio.packages', $room->id_room) }}"
                        class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </a>
                </div>

                <form method="POST" action="{{ route('admin.studio.packages.update', [$room->id_room, $editingPackage->id_package]) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="field-label text-xs">Nama / Tipe Paket *</label>
                        <input class="field-input text-sm" name="tipe_harga" value="{{ old('tipe_harga', $editingPackage->tipe_harga) }}" placeholder="Contoh: Paket Reguler / Per Sesi" required>
                        @error('tipe_harga')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="field-label text-xs">Harga (Rp) *</label>
                            <input class="field-input text-sm" type="number" name="harga" value="{{ old('harga', (int)$editingPackage->harga) }}" placeholder="150000" min="0" required>
                            @error('harga')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="field-label text-xs">Durasi (Menit) *</label>
                            <input class="field-input text-sm" type="number" name="durasi_menit" value="{{ old('durasi_menit', $editingPackage->durasi_menit) }}" placeholder="60" min="1" required>
                            @error('durasi_menit')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="field-label text-xs">Status Paket *</label>
                        <select name="is_active" class="field-input text-sm">
                            <option value="1" @selected(old('is_active', $editingPackage->is_active ? '1' : '0') == '1')>Aktif (Tampil ke Pelanggan)</option>
                            <option value="0" @selected(old('is_active', $editingPackage->is_active ? '1' : '0') == '0')>Nonaktif (Disembunyikan)</option>
                        </select>
                        @error('is_active')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="field-label text-xs">Deskripsi Benefit</label>
                        <textarea class="field-input text-sm" name="deskripsi_benefit" rows="3" placeholder="Contoh: Termasuk seluruh file softcopy & properti studio">{{ old('deskripsi_benefit', $editingPackage->deskripsi_benefit) }}</textarea>
                    </div>

                    <div class="flex gap-2">
                        <button class="flex-1 rounded-md bg-[#172033] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#7b2631]" type="submit">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.studio.packages', $room->id_room) }}"
                            class="rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-center text-xs font-bold text-slate-700 hover:bg-slate-50">
                            Batal
                        </a>
                    </div>
                </form>
            @else
                {{-- Mode Tambah Paket Baru --}}
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-black text-[#172033]">Tambah Paket Sesi</h2>
                    <p class="text-xs text-slate-500">Buat paket durasi atau benefit baru untuk studio ini.</p>
                </div>

                <form method="POST" action="{{ route('admin.studio.packages.store', $room->id_room) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="field-label text-xs">Nama / Tipe Paket *</label>
                        <input class="field-input text-sm" name="tipe_harga" value="{{ old('tipe_harga') }}" placeholder="Contoh: Paket Reguler / Per Sesi" required>
                        @error('tipe_harga')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="field-label text-xs">Harga (Rp) *</label>
                            <input class="field-input text-sm" type="number" name="harga" value="{{ old('harga') }}" placeholder="150000" min="0" required>
                            @error('harga')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="field-label text-xs">Durasi (Menit) *</label>
                            <input class="field-input text-sm" type="number" name="durasi_menit" value="{{ old('durasi_menit', 60) }}" placeholder="60" min="1" required>
                            @error('durasi_menit')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="field-label text-xs">Deskripsi Benefit</label>
                        <textarea class="field-input text-sm" name="deskripsi_benefit" rows="3" placeholder="Contoh: Termasuk seluruh file softcopy & properti studio">{{ old('deskripsi_benefit') }}</textarea>
                    </div>

                    <button class="w-full rounded-md bg-[#172033] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-slate-800" type="submit">
                        + Simpan Paket Baru
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

{{-- Modal Edit Paket Cepat (Popup) --}}
<div id="modal-edit-package" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
    <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-5 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-amber-900">Ubah Paket</span>
                <h3 class="mt-0.5 text-base font-black text-[#172033]" id="modal-title">Edit Paket Sesi</h3>
            </div>
            <button type="button" id="btn-close-modal" class="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="form-edit-package" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="field-label text-xs">Nama / Tipe Paket *</label>
                <input id="edit-tipe-harga" class="field-input text-sm" name="tipe_harga" required>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="field-label text-xs">Harga (Rp) *</label>
                    <input id="edit-harga" class="field-input text-sm" type="number" name="harga" min="0" required>
                </div>
                <div>
                    <label class="field-label text-xs">Durasi (Menit) *</label>
                    <input id="edit-durasi" class="field-input text-sm" type="number" name="durasi_menit" min="1" required>
                </div>
            </div>

            <div>
                <label class="field-label text-xs">Status Paket *</label>
                <select id="edit-is-active" name="is_active" class="field-input text-sm">
                    <option value="1">Aktif (Tampil ke Pelanggan)</option>
                    <option value="0">Nonaktif (Disembunyikan)</option>
                </select>
            </div>

            <div>
                <label class="field-label text-xs">Deskripsi Benefit</label>
                <textarea id="edit-benefit" class="field-input text-sm" name="deskripsi_benefit" rows="3"></textarea>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="submit" class="flex-1 rounded-md bg-[#172033] px-4 py-2.5 text-xs font-bold text-white hover:bg-[#7b2631]">
                    Simpan Perubahan
                </button>
                <button type="button" id="btn-cancel-modal" class="rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const modal = document.getElementById('modal-edit-package');
    const form = document.getElementById('form-edit-package');
    const inputName = document.getElementById('edit-tipe-harga');
    const inputPrice = document.getElementById('edit-harga');
    const inputDuration = document.getElementById('edit-durasi');
    const selectActive = document.getElementById('edit-is-active');
    const textareaBenefit = document.getElementById('edit-benefit');
    const modalTitle = document.getElementById('modal-title');
    const closeBtn = document.getElementById('btn-close-modal');
    const cancelBtn = document.getElementById('btn-cancel-modal');

    function openModal(btn) {
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        const price = btn.getAttribute('data-price');
        const duration = btn.getAttribute('data-duration');
        const benefit = btn.getAttribute('data-benefit') || '';
        const active = btn.getAttribute('data-active');
        const action = btn.getAttribute('data-action');

        form.action = action;
        inputName.value = name;
        inputPrice.value = price;
        inputDuration.value = duration;
        selectActive.value = active;
        textareaBenefit.value = benefit;
        modalTitle.textContent = 'Edit Paket: ' + name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.querySelectorAll('.btn-edit-package').forEach(btn => {
        btn.addEventListener('click', function () {
            openModal(this);
        });
    });

    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
})();
</script>
@endpush
@endsection
