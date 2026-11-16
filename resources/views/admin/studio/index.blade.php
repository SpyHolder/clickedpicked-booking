@extends('layouts.admin')
@section('title', 'Kelola Studio')
@section('content')
<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-[#172033]">Kelola Studio & Cabang</h1>
            <p class="mt-0.5 text-xs text-slate-500">
                Pemisahan studio berdasarkan cabang <strong>Batu 5</strong> dan <strong>Jalan Merdeka</strong> tanpa perlu mengetik nama cabang dan jenis ruangan secara manual.
            </p>
        </div>
        <a href="{{ route('admin.studio.create') }}"
            class="rounded-md bg-[#172033] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#7b2631]">
            <i class="fa-solid fa-plus mr-1.5"></i> Tambah Ruangan Baru
        </a>
    </div>

    {{-- Ringkasan Per Cabang --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#172033] text-white">
                        <i class="fa-solid fa-location-dot"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-black text-[#172033]">Cabang Batu 5</h2>
                        <p class="text-xs text-slate-500">Jl. Merdeka, BT 5 Atas</p>
                    </div>
                </div>
                <span class="rounded-full bg-[#f3f0e8] px-3 py-1 font-mono text-xs font-black text-[#7b2631]">
                    {{ $batuLimaRooms->count() }} Ruangan
                </span>
            </div>
            <div class="mt-3 flex flex-wrap gap-2 text-[11px] text-slate-600">
                <span class="rounded bg-sky-50 px-2 py-0.5 font-bold text-sky-800">
                    {{ $batuLimaRooms->filter(fn($r) => !$r->isPhotoBooth())->count() }} Photo Studio (Booking Online)
                </span>
                <span class="rounded bg-amber-50 px-2 py-0.5 font-bold text-amber-800">
                    {{ $batuLimaRooms->filter(fn($r) => $r->isPhotoBooth())->count() }} Photo Booth (Walk-In)
                </span>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#7b2631] text-white">
                        <i class="fa-solid fa-store"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-black text-[#172033]">Cabang Jalan Merdeka</h2>
                        <p class="text-xs text-slate-500">Jl. Merdeka</p>
                    </div>
                </div>
                <span class="rounded-full bg-[#f3f0e8] px-3 py-1 font-mono text-xs font-black text-[#7b2631]">
                    {{ $merdekaRooms->count() }} Ruangan
                </span>
            </div>
            <div class="mt-3 flex flex-wrap gap-2 text-[11px] text-slate-600">
                <span class="rounded bg-amber-50 px-2 py-0.5 font-bold text-amber-800">
                    {{ $merdekaRooms->count() }} Photo Booth (Semua Khusus Walk-In)
                </span>
            </div>
        </div>
    </div>

    {{-- Tab Navigation Batu 5 vs Jalan Merdeka --}}
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs Cabang Studio">
            <button type="button"
                data-tab-target="tab-batu-lima"
                class="tab-btn inline-flex items-center gap-2 border-b-2 pb-3.5 text-sm font-black transition {{ $activeTab === 'batu_lima' ? 'border-[#172033] text-[#172033]' : 'border-transparent text-slate-400 hover:border-slate-300 hover:text-slate-700' }}">
                <i class="fa-solid fa-location-dot"></i>
                <span>Cabang Batu 5</span>
                <span class="rounded-full {{ $activeTab === 'batu_lima' ? 'bg-[#172033] text-white' : 'bg-slate-100 text-slate-600' }} px-2 py-0.5 text-xs font-bold">
                    {{ $batuLimaRooms->count() }}
                </span>
            </button>

            <button type="button"
                data-tab-target="tab-jalan-merdeka"
                class="tab-btn inline-flex items-center gap-2 border-b-2 pb-3.5 text-sm font-black transition {{ $activeTab === 'jalan_merdeka' ? 'border-[#7b2631] text-[#7b2631]' : 'border-transparent text-slate-400 hover:border-slate-300 hover:text-slate-700' }}">
                <i class="fa-solid fa-store"></i>
                <span>Cabang Jalan Merdeka</span>
                <span class="rounded-full {{ $activeTab === 'jalan_merdeka' ? 'bg-[#7b2631] text-white' : 'bg-slate-100 text-slate-600' }} px-2 py-0.5 text-xs font-bold">
                    {{ $merdekaRooms->count() }}
                </span>
            </button>
        </nav>
    </div>

    {{-- TAB 1: Cabang Batu 5 --}}
    <div id="tab-batu-lima" class="tab-content {{ $activeTab === 'batu_lima' ? '' : 'hidden' }} space-y-4">
        <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-[#f8f7f2] p-4 text-xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-[#163b71]"></i>
                <span class="text-slate-700">
                    Cabang <strong>Batu 5</strong> memiliki 4 ruangan: <strong>Room 2 & Room 3</strong> adalah Photo Studio (bisa booking online), sedangkan <strong>Room 1 & Room 4</strong> adalah Photo Booth (walk-in).
                </span>
            </div>
            <a href="{{ route('admin.studio.create', ['cabang' => 'batu_lima']) }}" class="shrink-0 font-bold text-[#172033] hover:underline">
                + Tambah di Batu 5
            </a>
        </div>

        @include('admin.studio.partials.table', ['roomList' => $batuLimaRooms, 'cabangName' => 'Batu 5'])
    </div>

    {{-- TAB 2: Cabang Jalan Merdeka --}}
    <div id="tab-jalan-merdeka" class="tab-content {{ $activeTab === 'jalan_merdeka' ? '' : 'hidden' }} space-y-4">
        <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-[#f8f7f2] p-4 text-xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-[#7b2631]"></i>
                <span class="text-slate-700">
                    Cabang <strong>Jalan Merdeka</strong> memiliki 3 ruangan dan <strong>seluruhnya adalah Photo Booth</strong> (khusus walk-in langsung datang ke studio, pemilihan frame di layar PC booth).
                </span>
            </div>
            <a href="{{ route('admin.studio.create', ['cabang' => 'jalan_merdeka']) }}" class="shrink-0 font-bold text-[#7b2631] hover:underline">
                + Tambah di Jalan Merdeka
            </a>
        </div>

        @include('admin.studio.partials.table', ['roomList' => $merdekaRooms, 'cabangName' => 'Jalan Merdeka'])
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-tab-target');
            const isMerdeka = targetId === 'tab-jalan-merdeka';

            // Reset tabs styling
            tabBtns.forEach(b => {
                b.className = 'tab-btn inline-flex items-center gap-2 border-b-2 border-transparent pb-3.5 text-sm font-black text-slate-400 hover:border-slate-300 hover:text-slate-700 transition';
                const badge = b.querySelector('span:last-child');
                if (badge) badge.className = 'rounded-full bg-slate-100 text-slate-600 px-2 py-0.5 text-xs font-bold';
            });

            // Active button styling
            if (isMerdeka) {
                this.className = 'tab-btn inline-flex items-center gap-2 border-b-2 border-[#7b2631] pb-3.5 text-sm font-black text-[#7b2631] transition';
                const badge = this.querySelector('span:last-child');
                if (badge) badge.className = 'rounded-full bg-[#7b2631] text-white px-2 py-0.5 text-xs font-bold';
            } else {
                this.className = 'tab-btn inline-flex items-center gap-2 border-b-2 border-[#172033] pb-3.5 text-sm font-black text-[#172033] transition';
                const badge = this.querySelector('span:last-child');
                if (badge) badge.className = 'rounded-full bg-[#172033] text-white px-2 py-0.5 text-xs font-bold';
            }

            // Show matching content
            tabContents.forEach(c => {
                if (c.id === targetId) {
                    c.classList.remove('hidden');
                } else {
                    c.classList.add('hidden');
                }
            });

            // Update URL without full refresh
            const cabangParam = isMerdeka ? 'jalan_merdeka' : 'batu_lima';
            const url = new URL(window.location);
            url.searchParams.set('tab', cabangParam);
            window.history.replaceState({}, '', url);
        });
    });
})();
</script>
@endpush
