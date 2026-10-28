@extends('layouts.app')
@section('title', 'Booking Berhasil')
@section('content')
<section class="mx-auto max-w-2xl px-5 py-16 text-center sm:px-8"><div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm sm:p-12"><p class="text-sm font-bold uppercase tracking-widest text-[#7b2631]">Booking berhasil</p><h1 class="mt-4 text-3xl font-black">Simpan kode booking kamu</h1><p class="my-8 text-4xl font-black tracking-wider text-[#7b2631]">{{ $booking->kode_booking }}</p><p class="text-slate-500">Bukti pembayaran sudah diterima dan sedang menunggu verifikasi admin.</p><div class="mt-8 rounded-xl bg-[#f3f0e8] p-5 text-left"><p><strong>Nama:</strong> {{ $booking->nama_pelanggan }}</p><p class="mt-2"><strong>Total:</strong> Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</p></div><a href="{{ route('booking.status', $booking->kode_booking) }}" class="mt-7 block rounded-xl bg-black px-5 py-4 font-bold text-white">Lihat status booking</a></div></section>
@endsection
