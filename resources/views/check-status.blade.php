@extends('layouts.app')
@section('title', 'Cek Status')
@section('content')
<section class="mx-auto max-w-xl px-5 py-16 sm:px-8"><div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm sm:p-10"><h1 class="text-3xl font-black">Cek Status</h1><p class="mt-2 text-slate-500">Masukkan kode booking atau request edit foto kamu.</p>@if(session('error'))<div class="mt-5 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div>@endif<form method="POST" action="{{ route('check-status.process') }}" class="mt-7">@csrf<label class="field-label">Kode</label><input class="field-input" name="kode" value="{{ old('kode') }}" placeholder="BK-XXXXXXXX atau ED-XXXXXXXX" required>@error('kode')<p class="field-error">{{ $message }}</p>@enderror<button class="mt-5 w-full rounded-xl bg-black px-5 py-4 font-bold text-white" type="submit">Cek status</button></form></div></section>
@endsection
