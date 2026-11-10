@extends('layouts.app')
@section('title', $room->nama_room)
@section('content')
<section class="user-page mx-auto max-w-6xl px-5 py-12 sm:px-8 lg:py-16">
    <a href="{{ route('studio.index') }}" class="back-link">← Kembali ke lokasi</a>
    @php($images = $room->images)
    <div class="mt-8 grid gap-10 lg:grid-cols-[1.1fr_.9fr]">
        <div>
            <div class="studio-feature room-detail-placeholder">@if($images->first()?->image_url)<img src="{{ str_starts_with($images->first()->image_url, 'http') ? $images->first()->image_url : asset('storage/'.$images->first()->image_url) }}" alt="{{ $room->nama_room }}" class="h-full w-full object-cover">@endif</div>
            <div class="mt-4 grid grid-cols-3 gap-3">@foreach($images->take(3) as $index => $image)<div class="studio-thumb"><img src="{{ str_starts_with($image->image_url, 'http') ? $image->image_url : asset('storage/'.$image->image_url) }}" alt="{{ $room->nama_room }} {{ $index + 1 }}" class="h-full w-full object-cover"><span>[0{{ $index + 1 }}]</span></div>@endforeach</div>
            <h1 class="mt-8 text-4xl font-black uppercase">{{ $room->nama_room }}</h1><p class="mt-4 leading-7 text-slate-600">{{ $room->deskripsi ?: 'Studio nyaman untuk mengabadikan momen bersama orang-orang terdekat.' }}</p>
        </div>
        <div class="studio-panel"><p class="eyebrow">Paket layanan</p><h2 class="mt-2 text-3xl font-black">Reservasi studio</h2><div class="mt-7 space-y-3">@forelse($room->packages->where('is_active', true) as $package)<div class="package-row" data-package-id="{{ $package->id_package }}" data-package-name="{{ $package->tipe_harga }} - Rp {{ number_format($package->harga, 0, ',', '.') }}"><div><strong>{{ $package->tipe_harga }}</strong><p class="text-sm text-slate-500">{{ $package->durasi_menit }} menit</p></div><strong>Rp {{ number_format($package->harga, 0, ',', '.') }}</strong></div>@empty<p class="text-slate-500">Paket belum tersedia.</p>@endforelse</div><a href="{{ route('booking.wizard') }}" class="user-button mt-8 block text-center">Pesan Sekarang</a></div>
    </div>
</section>
@endsection