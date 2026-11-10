@extends('layouts.app')
@section('title', 'Formulir Pemesanan')
@section('content')
<section class="booking-page mx-auto max-w-5xl px-5 py-12 sm:px-8 lg:py-20">
    <a href="{{ route('studio.index') }}" class="back-link">← Kembali</a>
    <div class="mx-auto mt-10 max-w-3xl text-center"><p class="eyebrow">Reservasi</p><h1 class="display-title mt-3">Formulir Pemesanan</h1><p class="mt-4 text-lg text-slate-500">Lengkapi data Anda untuk memesan sesi foto.</p></div>
    <div class="booking-progress mt-10"><span class="active">01 Data diri</span><i></i><span>02 Waktu</span><i></i><span>03 Pembayaran</span><i></i><span>04 Selesai</span></div>
    <div class="design-panel mx-auto mt-8 max-w-3xl"><form method="POST" action="{{ route('booking.step1') }}" class="space-y-6">@csrf
        <div><label class="design-label">Nama Lengkap *</label><input class="design-input" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}" placeholder="Masukkan nama" required>@error('nama_pelanggan')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div><label class="design-label">Email <span>(opsional)</span></label><input type="email" class="design-input" name="email_pelanggan" value="{{ old('email_pelanggan') }}" placeholder="Masukkan email">@error('email_pelanggan')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div><label class="design-label">No. Handphone / WA *</label><input type="tel" class="design-input" name="no_hp_pelanggan" value="{{ old('no_hp_pelanggan') }}" placeholder="Masukkan No. HP / WA" required>@error('no_hp_pelanggan')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div><label class="design-label">Room *</label><select class="design-input" name="id_room" id="id_room" required><option value="">-- Pilih Room --</option>@foreach($rooms as $room)<option value="{{ $room->id_room }}" @selected(old('id_room') == $room->id_room)>{{ $room->nama_room }}</option>@endforeach</select>@error('id_room')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div><label class="design-label">Paket *</label><select class="design-input" name="id_package" id="id_package" required><option value="">Pilih paket</option></select>@error('id_package')<p class="field-error">{{ $message }}</p>@enderror</div>
        <button class="design-button w-full" type="submit">Lanjutkan</button>
    </form></div>
</section>
@endsection
@push('scripts')<script>document.querySelector('#id_room')?.addEventListener('change',async function(){const select=document.querySelector('#id_package');select.innerHTML='<option>Memuat paket...</option>';if(!this.value){select.innerHTML='<option value="">Pilih paket</option>';return;}const response=await fetch(`/studio/${this.value}`);const html=await response.text();const doc=new DOMParser().parseFromString(html,'text/html');select.innerHTML='<option value="">Pilih paket</option>';doc.querySelectorAll('[data-package-id]').forEach(item=>select.add(new Option(item.dataset.packageName,item.dataset.packageId)));});</script>@endpush