@extends('layouts.app')
@section('title', 'Pilih Lokasi')
@section('content')
<section class="user-page mx-auto max-w-7xl px-5 py-14 sm:px-8 lg:py-20">
    <div class="mx-auto max-w-3xl text-center">
        <p class="eyebrow">Clicked Picked Studios</p>
        <h1 class="display-title mt-3">Pilih Lokasi</h1>
        <select class="location-select mt-8 w-full" aria-label="Pilih lokasi"><option>Merdeka</option><option>BT 5 Atas</option></select>
    </div>
    <div class="mt-14 grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($rooms as $room)
            @php($image = $room->images->first())
            <article class="studio-card group">
                <div class="studio-card-image room-placeholder">@if($image?->image_url)<img src="{{ str_starts_with($image->image_url, 'http') ? $image->image_url : asset('storage/'.$image->image_url) }}" alt="{{ $room->nama_room }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">@endif</div>
                <div class="p-5"><h2 class="text-xl font-black uppercase">{{ $room->nama_room }}</h2><div class="mt-5 flex justify-between text-sm text-slate-500"><span>Harga</span><span>Max Person</span></div><div class="mt-2 flex justify-between font-bold"><span>Mulai dari</span><span>{{ $room->kapasitas }} Orang</span></div><a href="{{ route('studio.show', $room->id_room) }}" class="user-button mt-6 block text-center">Lihat Studio</a></div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl bg-white p-12 text-center text-slate-500">Belum ada studio aktif.</div>
        @endforelse
    </div>
</section>
@endsection