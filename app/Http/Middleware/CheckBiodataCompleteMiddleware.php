<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBiodataCompleteMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Exclude routes that allow completing biodata or logging out
        if ($request->routeIs('dosen.biodata.*') || 
            $request->routeIs('mahasiswa.biodata.*') || 
            $request->routeIs('profile.*') || 
            $request->routeIs('logout')) {
            return $next($request);
        }

        if ($user->role === 'mahasiswa') {
            $mhs = $user->mahasiswa;
            if (!$mhs || empty($mhs->no_hp) || empty($mhs->jenis_kelamin) || empty($mhs->tempat_lahir) || empty($mhs->tanggal_lahir) || empty($mhs->alamat)) {
                return redirect()->route('mahasiswa.biodata.index')->with('warning', 'Biodata Anda belum lengkap. Silakan lengkapi biodata Anda terlebih dahulu untuk membuka akses fitur lainnya.');
            }
        } elseif ($user->role === 'dosen') {
            $dsn = $user->dosen;
            if (!$dsn || empty($dsn->no_hp) || empty($dsn->jenis_kelamin) || empty($dsn->tempat_lahir) || empty($dsn->tanggal_lahir) || empty($dsn->alamat)) {
                return redirect()->route('dosen.biodata.index')->with('warning', 'Biodata Anda belum lengkap. Silakan lengkapi biodata Anda terlebih dahulu untuk membuka akses fitur lainnya.');
            }
        }

        return $next($request);
    }
}
