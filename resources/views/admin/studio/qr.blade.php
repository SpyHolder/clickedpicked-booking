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
        <img src="{{ $qrUrl }}" alt="QR Code {{ $room->nama_room }}" class="mx-auto h-64 w-64 rounded-lg">
        <p class="mt-4 text-sm text-gray-500">Scan QR ini untuk membuka form request edit foto.</p>
        <div class="mt-4 break-all rounded-lg bg-gray-50 p-3 text-left text-sm text-gray-700">
            <span class="font-medium">URL:</span> {{ $editUrl }}
        </div>
        <a href="{{ $qrUrl }}" target="_blank" rel="noreferrer" class="mt-5 inline-block rounded-lg bg-black px-4 py-2 text-sm font-bold text-white">Buka QR ukuran penuh</a>
    </div>
</div>
@endsection
