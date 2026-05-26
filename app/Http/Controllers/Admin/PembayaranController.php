<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with(['mahasiswa.user', 'tahunAkademik', 'mahasiswa.prodi'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('tahun_akademik_id')) {
            $query->where('tahun_akademik_id', $request->tahun_akademik_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('fakultas_id')) {
            $query->whereHas('mahasiswa.prodi', function($q) use ($request) {
                $q->where('fakultas_id', $request->fakultas_id);
            });
        }

        if ($request->filled('prodi_id')) {
            $query->whereHas('mahasiswa', function($q) use ($request) {
                $q->where('prodi_id', $request->prodi_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('mahasiswa', function($q) use ($search) {
                $q->where('nim', 'like', "%$search%")
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('name', 'like', "%$search%");
                  });
            });
        }

        $pembayaran = $query->paginate(config('siakad.pagination', 15))->withQueryString();
        $tahunAkademik = TahunAkademik::orderBy('tahun', 'desc')->get();
        $fakultasList = \App\Models\Fakultas::orderBy('nama')->get();
        $prodiList = \App\Models\Prodi::orderBy('nama')->get();

        return view('admin.pembayaran.index', compact('pembayaran', 'tahunAkademik', 'fakultasList', 'prodiList'));
    }

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load(['mahasiswa.user', 'tahunAkademik', 'mahasiswa.prodi.fakultas']);
        return view('admin.pembayaran.show', compact('pembayaran'));
    }

    public function verify(Pembayaran $pembayaran)
    {
        $pembayaran->update([
            'status' => 'success',
            'payment_type' => 'manual_verification'
        ]);

        return back()->with('success', 'Pembayaran berhasil diverifikasi secara manual.');
    }
}
