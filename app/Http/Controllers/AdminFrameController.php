<?php

namespace App\Http\Controllers;

use App\Models\Frame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminFrameController extends Controller
{
    public function index(): View
    {
        $frames = Frame::orderBy('jumlah_slot')->orderBy('kode_frame')->get();

        return view('admin.frame.index', [
            'frames' => $frames,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_frame' => 'required|string|max:50|unique:frames,kode_frame',
            'nama_frame' => 'required|string|max:100',
            'jumlah_slot' => 'required|integer|min:1|max:12',
            'warna_aksen' => 'nullable|string|max:30',
            'deskripsi' => 'nullable|string|max:255',
            'preview' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg|max:5120',
        ], [
            'kode_frame.required' => 'Kode frame wajib diisi.',
            'kode_frame.unique' => 'Kode frame sudah digunakan.',
            'nama_frame.required' => 'Nama frame wajib diisi.',
            'jumlah_slot.required' => 'Jumlah slot foto wajib diisi.',
        ]);

        $previewPath = null;
        if ($request->hasFile('preview')) {
            $previewPath = $request->file('preview')->store('frames', 'public');
        }

        Frame::create([
            'kode_frame' => strtoupper(trim($validated['kode_frame'])),
            'nama_frame' => trim($validated['nama_frame']),
            'jumlah_slot' => (int) $validated['jumlah_slot'],
            'warna_aksen' => $validated['warna_aksen'] ?: '#172033',
            'preview_path' => $previewPath,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Frame baru berhasil ditambahkan ke katalog.');
    }

    public function update(int $frame, Request $request): RedirectResponse
    {
        $model = Frame::findOrFail($frame);

        $validated = $request->validate([
            'kode_frame' => 'required|string|max:50|unique:frames,kode_frame,' . $model->id_frame . ',id_frame',
            'nama_frame' => 'required|string|max:100',
            'jumlah_slot' => 'required|integer|min:1|max:12',
            'warna_aksen' => 'nullable|string|max:30',
            'deskripsi' => 'nullable|string|max:255',
            'preview' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg|max:5120',
        ]);

        $previewPath = $model->preview_path;
        if ($request->hasFile('preview')) {
            $previewPath = $request->file('preview')->store('frames', 'public');
        }

        $model->update([
            'kode_frame' => strtoupper(trim($validated['kode_frame'])),
            'nama_frame' => trim($validated['nama_frame']),
            'jumlah_slot' => (int) $validated['jumlah_slot'],
            'warna_aksen' => $validated['warna_aksen'] ?: $model->warna_aksen,
            'preview_path' => $previewPath,
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        return back()->with('success', "Template frame {$model->kode_frame} berhasil diperbarui.");
    }

    public function toggle(int $frame): RedirectResponse
    {
        $model = Frame::findOrFail($frame);
        $model->update([
            'is_active' => !$model->is_active,
        ]);

        return back()->with('success', "Status frame {$model->kode_frame} diperbarui.");
    }

    public function destroy(int $frame): RedirectResponse
    {
        $model = Frame::findOrFail($frame);

        if ($model->preview_path && !str_starts_with($model->preview_path, 'frames/fr-0')) {
            Storage::disk('public')->delete($model->preview_path);
        }

        $model->delete();

        return back()->with('success', 'Frame berhasil dihapus dari katalog.');
    }
}
