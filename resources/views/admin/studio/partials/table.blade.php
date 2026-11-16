<div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xs">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="whitespace-nowrap px-4 py-3">Nama Ruangan</th>
                    <th class="whitespace-nowrap px-4 py-3">Jenis Ruangan</th>
                    <th class="whitespace-nowrap px-4 py-3">Kapasitas</th>
                    <th class="whitespace-nowrap px-4 py-3">Paket Sesi</th>
                    <th class="whitespace-nowrap px-4 py-3">Status</th>
                    <th class="whitespace-nowrap px-4 py-3 text-right">Aksi Manajemen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($roomList as $room)
                    @php
                        $isActive = $room->status->value === 'aktif';
                        $isBooth = $room->isPhotoBooth();
                        $packageCount = $room->packages->where('is_active', true)->count();
                    @endphp
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3.5 align-middle">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#f3f0e8] font-mono text-xs font-black text-[#172033]">
                                    #{{ $room->id_room }}
                                </span>
                                <div>
                                    <p class="whitespace-nowrap font-black text-[#172033]">{{ $room->nama_room }}</p>
                                    @if($room->deskripsi)
                                        <p class="mt-0.5 line-clamp-1 max-w-xs text-xs text-slate-500" title="{{ $room->deskripsi }}">{{ $room->deskripsi }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            @if ($isBooth)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-900">
                                    <i class="fa-solid fa-cubes text-amber-700"></i> Photo Booth
                                </span>
                                <span class="block mt-1 text-[11px] font-semibold text-slate-500">
                                    <i class="fa-solid fa-person-walking mr-1 text-[10px]"></i> Khusus Walk-In
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-bold text-sky-900">
                                    <i class="fa-solid fa-camera text-sky-700"></i> Photo Studio
                                </span>
                                <span class="block mt-1 text-[11px] font-semibold text-emerald-700">
                                    <i class="fa-solid fa-calendar-check mr-1 text-[10px]"></i> Bisa Booking Online
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            <span class="font-bold text-[#172033]">{{ $room->kapasitas }} Orang</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            @if ($isBooth)
                                <span class="text-xs text-slate-400">Pilih di Layar PC</span>
                            @else
                                <a href="{{ route('admin.studio.packages', $room->id_room) }}"
                                    class="inline-flex items-center gap-1 rounded bg-[#f3f0e8] px-2.5 py-1 text-xs font-bold text-[#7b2631] hover:bg-[#cbbb9e]/50">
                                    <i class="fa-solid fa-tags text-[10px]"></i> {{ $packageCount }} Paket Aktif
                                </a>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                            <span class="status-badge {{ $isActive ? 'border border-emerald-200 bg-emerald-100 text-emerald-900' : 'border border-slate-200 bg-slate-100 text-slate-600' }}">
                                {{ $room->status->label() }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-right align-middle">
                            <div class="inline-flex items-center gap-1.5">
                                <a class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-bold text-[#172033] hover:bg-slate-50"
                                    href="{{ route('admin.studio.edit', $room->id_room) }}" title="Edit Info & Galeri Foto">
                                    <i class="fa-solid fa-pen-to-square mr-1 text-slate-500"></i> Edit
                                </a>
                                @if (!$isBooth)
                                    <a class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-bold text-[#172033] hover:bg-slate-50"
                                        href="{{ route('admin.studio.packages', $room->id_room) }}" title="Kelola Paket Harga">
                                        <i class="fa-solid fa-tags mr-1 text-slate-500"></i> Paket
                                    </a>
                                @endif
                                <a class="rounded-md bg-[#cbbb9e] px-2.5 py-1.5 text-xs font-bold text-[#172033] hover:bg-[#b9a688]"
                                    href="{{ route('admin.studio.qr', $room->id_room) }}" title="QR Code Edit Foto">
                                    <i class="fa-solid fa-qrcode mr-1 text-slate-700"></i> QR
                                </a>
                                <form method="POST" action="{{ route('admin.studio.status', $room->id_room) }}" class="inline"
                                    @if($isActive)
                                        onsubmit="return confirm('Nonaktifkan {{ $room->nama_room }}? Formulir edit foto QR untuk studio ini juga akan dinonaktifkan.');"
                                    @endif>
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="rounded-md px-2.5 py-1.5 text-xs font-bold {{ $isActive ? 'border border-amber-300 bg-amber-50 text-amber-900 hover:bg-amber-100' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                                        {{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">
                            <p class="font-bold text-slate-700">Belum ada ruangan untuk Cabang {{ $cabangName }}.</p>
                            <a href="{{ route('admin.studio.create', ['cabang' => $cabangName === 'Jalan Merdeka' ? 'jalan_merdeka' : 'batu_lima']) }}"
                                class="mt-3 inline-block rounded-md bg-[#172033] px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">
                                <i class="fa-solid fa-plus mr-1"></i> Tambah Ruangan di {{ $cabangName }}
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
