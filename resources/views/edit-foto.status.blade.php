@extends('layouts.app')
@section('title', 'Status Edit Foto')
@section('content')
<div class="mx-auto max-w-2xl px-5 py-12 sm:px-8">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold text-indigo-600">{{ $request->room->nama_room }}</p>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Request berhasil dikirim</h1>
        <p class="mt-2 text-gray-600">Simpan kode berikut untuk mengecek perkembangan request.</p>
        <div class="my-6 rounded-lg bg-gray-100 p-4 text-center text-xl font-bold tracking-wider">{{ $request->kode_request }}</div>
        <div class="space-y-2 text-sm text-gray-700">
            <p><strong>Nama:</strong> {{ $request->nama_pelanggan }}</p>
            <p><strong>Status:</strong> {{ $request->status_edit->label() }}</p>
            <p><strong>Jumlah foto:</strong> {{ $request->items->count() }}</p>
        </div>
        @if ($request->alasan_penolakan)
            <div class="mt-5 rounded-lg bg-red-50 p-4 text-sm text-red-700"><strong>Alasan penolakan:</strong> {{ $request->alasan_penolakan }}</div>
        @endif
        @if ($request->editedPhotos->isNotEmpty())
            <div class="mt-6 border-t border-gray-200 pt-5">
                <h2 class="font-bold text-gray-900">Hasil edit tersedia</h2>
                <div class="mt-3 space-y-2">
                    @foreach ($request->editedPhotos as $photo)
                        <a href="{{ route('edit-foto.download', [$request->kode_request, $photo->id_photo]) }}" class="flex items-center justify-between rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 hover:bg-emerald-100">
                            <span>{{ $photo->nama_file }}</span><span class="font-bold">Download →</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
        <a href="{{ route('check-status') }}" class="mt-6 inline-block rounded-lg bg-black px-4 py-2 font-semibold text-white">Cek status lain</a>
    </div>
</div>
@endsection