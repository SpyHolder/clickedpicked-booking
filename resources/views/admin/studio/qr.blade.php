@extends('layouts.admin')
@section('title', 'QR Studio')
@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <p class="text-sm font-medium text-indigo-600">Studio</p>
        <h1 class="text-2xl font-bold text-gray-900">QR Code Edit Foto</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $room->nama_room }}</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
        <div class="mx-auto flex h-64 w-64 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 text-sm text-gray-500">
            QR code akan tersedia setelah fitur edit foto diaktifkan.
        </div>
        <p class="mt-4 text-sm text-gray-500">Bagikan halaman ini kepada pelanggan untuk akses edit foto studio.</p>
        <div class="mt-4 rounded-lg bg-gray-50 p-3 text-left text-sm text-gray-700">
            <span class="font-medium">Studio:</span> {{ $room->nama_room }}
        </div>
    </div>
</div>
@endsection
