@extends('layouts.app')
@section('title', 'Booking Studio — Langkah 1')
@section('content')
@php
    $selectedRoomId = old('id_room', request()->query('room'));
    $selectedPackageId = old('id_package', request()->query('package'));
    $packagesByRoom = $rooms->mapWithKeys(function ($r) {
        return [
            $r->id_room => ($r->packages ?? collect())->map(fn ($p) => [
                'id' => $p->id_package,
                'label' => $p->tipe_harga . ' (' . $p->durasi_menit . ' menit) — Rp ' . number_format($p->harga, 0, ',', '.'),
                'benefit' => $p->deskripsi_benefit,
            ])->values(),
        ];
    });
@endphp

<section class="py-12 sm:py-16">
    <div class="t-wrap">
        <div class="mx-auto max-w-2xl">

            {{-- 4-Step Animated Line Stepper --}}
            @include('booking.partials.stepper', ['currentStep' => 1])

            <div class="border border-[var(--line)] bg-[#faf7f1] p-7 sm:p-10">
                <p class="t-label text-[#7b2631] font-medium">Langkah 1 dari 4 · Reservasi Photo Studio</p>
                <h1 class="t-h2 mt-1">Formulir pemesanan sesi</h1>
                <p class="mt-2 t-body text-[#5c5549]">
                    Lengkapi identitas pemesan dan pilih ruangan studio beserta paket durasi sesi foto.
                </p>

                <form method="POST" action="{{ route('booking.step1') }}" class="mt-8 space-y-5" id="step1-form">
                    @csrf

                    <div>
                        <label for="nama_pelanggan" class="field-label">Nama lengkap pemesan</label>
                        <input id="nama_pelanggan" class="field-input" name="nama_pelanggan" maxlength="100"
                            autocomplete="name"
                            value="{{ old('nama_pelanggan') }}" placeholder="Contoh: Rina Maharani" required>
                        @error('nama_pelanggan')
                            <p class="field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="no_hp_pelanggan" class="field-label">Nomor WhatsApp aktif</label>
                            <input id="no_hp_pelanggan" type="tel" inputmode="numeric" class="field-input tabular-nums"
                                name="no_hp_pelanggan" value="{{ old('no_hp_pelanggan') }}"
                                placeholder="081234567890" required>
                            <p class="mt-1.5 text-xs text-[#5c5549]">Digunakan untuk pencocokan sesi edit foto.</p>
                            @error('no_hp_pelanggan')
                                <p class="field-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email_pelanggan" class="field-label">Alamat email <span class="text-[#5c5549] font-normal">(opsional)</span></label>
                            <input id="email_pelanggan" type="email" autocomplete="email" class="field-input"
                                name="email_pelanggan" value="{{ old('email_pelanggan') }}"
                                placeholder="nama@email.com">
                            @error('email_pelanggan')
                                <p class="field-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="border-t border-[var(--line)] pt-5">
                        <label for="id_room" class="field-label">Ruangan Photo Studio</label>
                        <select class="field-input" name="id_room" id="id_room" required>
                            <option value="">Pilih ruangan studio</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id_room }}" @selected((string) $selectedRoomId === (string) $room->id_room)>
                                    {{ $room->cabang_label }} — {{ $room->nama_room }} ({{ $room->jenis_room_label }} · Maks. {{ $room->kapasitas }} orang)
                                </option>
                            @endforeach
                        </select>
                        @error('id_room')
                            <p class="field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_package" class="field-label">Paket durasi sesi</label>
                        <select class="field-input tabular-nums" name="id_package" id="id_package" required>
                            <option value="">Pilih ruangan studio terlebih dahulu</option>
                        </select>
                        <p id="package-benefit-hint" class="mt-2 text-xs font-medium text-[#7b2631]"></p>
                        @error('id_package')
                            <p class="field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-3">
                        <button id="step1-submit" class="t-btn t-btn-primary w-full" type="submit">
                            <span>Lanjutkan ke Pemilihan Jadwal</span>
                            <i class="fa-solid fa-arrow-right text-xs m-arrow" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const packagesMap = @json($packagesByRoom);
    const initialPackageId = @json((string) $selectedPackageId);
    const roomSelect = document.querySelector('#id_room');
    const packageSelect = document.querySelector('#id_package');
    const benefitHint = document.querySelector('#package-benefit-hint');

    async function loadPackagesForRoom(roomId, preselectId = '') {
        packageSelect.innerHTML = '<option value="">Memuat paket...</option>';
        benefitHint.textContent = '';

        if (!roomId) {
            packageSelect.innerHTML = '<option value="">Pilih ruangan studio terlebih dahulu</option>';
            return;
        }

        const localList = packagesMap[roomId];
        if (Array.isArray(localList) && localList.length > 0) {
            packageSelect.innerHTML = '<option value="">Pilih paket sesi</option>';
            localList.forEach(pkg => {
                const opt = new Option(pkg.label, pkg.id);
                opt.dataset.benefit = pkg.benefit || '';
                if (String(pkg.id) === String(preselectId)) {
                    opt.selected = true;
                    if (pkg.benefit) benefitHint.textContent = 'Fasilitas paket: ' + pkg.benefit;
                }
                packageSelect.add(opt);
            });
            return;
        }

        try {
            const response = await fetch(`/studio/${roomId}`);
            packageSelect.innerHTML = '<option value="">Pilih paket sesi</option>';
            if (response.ok) {
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                doc.querySelectorAll('[data-package-id]').forEach(item => {
                    const opt = new Option(item.dataset.packageName, item.dataset.packageId);
                    if (String(item.dataset.packageId) === String(preselectId)) {
                        opt.selected = true;
                    }
                    packageSelect.add(opt);
                });
            }
        } catch (e) {
            packageSelect.innerHTML = '<option value="">Gagal memuat paket</option>';
        }
    }

    roomSelect?.addEventListener('change', function () {
        loadPackagesForRoom(this.value, '');
    });

    packageSelect?.addEventListener('change', function () {
        const selectedOpt = this.options[this.selectedIndex];
        benefitHint.textContent = selectedOpt?.dataset?.benefit ? 'Fasilitas paket: ' + selectedOpt.dataset.benefit : '';
    });

    if (roomSelect?.value) {
        loadPackagesForRoom(roomSelect.value, initialPackageId);
    }
})();
</script>
@endpush
