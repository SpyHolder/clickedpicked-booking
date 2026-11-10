@extends('layouts.app')
@section('title', 'Edit Foto - '.$room->nama_room)
@section('content')
<div class="mx-auto max-w-2xl px-5 py-12 sm:px-8">
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">{{ $room->nama_room }}</p>
        <h1 class="mt-2 text-3xl font-bold text-gray-900">Request Edit Foto</h1>
        <p class="mt-2 text-gray-600">Form ini dapat diakses melalui QR code studio.</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @if ($errors->any())
            <div class="rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif
        <form class="space-y-5" method="POST" action="{{ route('edit-foto.store', $room->id_room) }}">
            @csrf
            <div>
                <label for="nama" class="mb-1 block text-sm font-medium text-gray-700">Nama</label>
                <input id="nama" name="nama" type="text" required class="w-full rounded-lg border-gray-300 px-3 py-2" placeholder="Nama lengkap">
            </div>
            <div>
                <label for="nomor_wa" class="mb-1 block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                <input id="nomor_wa" name="nomor_wa" type="tel" required class="w-full rounded-lg border-gray-300 px-3 py-2" placeholder="081234567890">
            </div>
            <div>
                <label for="catatan" class="mb-1 block text-sm font-medium text-gray-700">Catatan umum</label>
                <textarea id="catatan" name="catatan" rows="3" class="w-full rounded-lg border-gray-300 px-3 py-2" placeholder="Catatan tambahan untuk tim editor">{{ old('catatan') }}</textarea>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700">Daftar foto</label>
                    <button type="button" id="add-photo" class="text-sm font-semibold text-indigo-600">+ Tambah foto</button>
                </div>
                <div id="photo-items" class="space-y-3">
                    <div class="photo-item rounded-lg bg-gray-50 p-3">
                        <input name="foto[0][nama_file]" type="text" required class="w-full rounded-lg border-gray-300 px-3 py-2" placeholder="Nama file, contoh: IMG_1234.jpg">
                        <textarea name="foto[0][instruksi_edit]" rows="2" class="mt-2 w-full rounded-lg border-gray-300 px-3 py-2" placeholder="Instruksi edit foto"></textarea>
                    </div>
                </div>
            </div>
            <label class="flex items-start gap-3 text-sm text-gray-600">
                <input name="persetujuan_data" type="checkbox" required class="mt-1 rounded border-gray-300">
                <span>Saya menyetujui data ini digunakan untuk memproses request edit foto.</span>
            </label>
            <button type="submit" class="w-full rounded-lg bg-black px-4 py-3 font-semibold text-white">Kirim request edit foto</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let photoIndex = 1;
    document.querySelector('#add-photo')?.addEventListener('click', () => {
        const item = document.createElement('div');
        item.className = 'photo-item rounded-lg bg-gray-50 p-3';
        item.innerHTML = `<div class="flex gap-2"><input name="foto[${photoIndex}][nama_file]" type="text" required class="w-full rounded-lg border-gray-300 px-3 py-2" placeholder="Nama file foto"><button type="button" class="remove-photo text-sm text-red-600">Hapus</button></div><textarea name="foto[${photoIndex}][instruksi_edit]" rows="2" class="mt-2 w-full rounded-lg border-gray-300 px-3 py-2" placeholder="Instruksi edit foto"></textarea>`;
        document.querySelector('#photo-items').appendChild(item);
        photoIndex++;
    });
    document.querySelector('#photo-items')?.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-photo')) event.target.closest('.photo-item').remove();
    });
</script>
@endpush