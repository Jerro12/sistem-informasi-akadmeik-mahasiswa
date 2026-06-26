<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KRS_{{ $mahasiswa->nim }}_{{ $krs->tahunAkademik->tahun }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 9pt; color: #000; margin: 0; padding: 0; }
        .container { width: 21cm; min-height: 29.7cm; padding: 0.5cm 1cm; margin: auto; background: #fff; box-sizing: border-box; }
        
        /* Header / Kop Surat */
        .header { display: flex; align-items: center; border-bottom: 2px solid #ccc; padding-bottom: 2px; margin-bottom: 5px; }
        .header-logo { width: 50px; height: 50px; margin-right: 15px; display: flex; align-items: center; justify-content: center; }
        .header-logo img { width: 100%; height: auto; object-fit: contain; }
        .header-text h1 { font-size: 14pt; margin: 0; color: #0055A5; text-transform: uppercase; letter-spacing: 1px; }
        .header-text h2 { font-size: 10pt; margin: 0; font-weight: normal; color: #555; text-transform: uppercase; }
        
        .title-bar { background-color: #d1d5db; text-align: center; font-weight: bold; font-size: 11pt; padding: 3px 0; margin-bottom: 10px; text-transform: uppercase; border: 1px solid #9ca3af; }
        
        /* Info Table */
        .info-container { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 8pt; font-weight: bold; text-transform: uppercase; }
        .info-col { width: 48%; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 1px 0; vertical-align: top; }
        .info-table td.label { width: 120px; }
        .info-table td.separator { width: 10px; text-align: center; }
        
        /* Course Table */
        .course-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 7.5pt; }
        .course-table th, .course-table td { border: 1px solid #9ca3af; padding: 2px 4px; }
        .course-table th { background-color: #e5e7eb; text-transform: uppercase; font-weight: bold; text-align: left;}
        .course-table .center { text-align: center; }
        .course-table .right { text-align: right; }
        
        .oval { width: 14px; height: 8px; border: 1px solid #000; border-radius: 50%; display: inline-block; vertical-align: middle; }
        .oval.filled { background-color: #000; }
        
        .total-row td { background-color: #f3f4f6; font-weight: bold; }
        
        /* Summary Section */
        .summary-section { margin-top: 10px; font-size: 8pt; line-height: 1.3; }
        
        /* Signature Area */
        .signature-container { width: 100%; margin-top: 15px; font-size: 8pt; }
        .signature-row { display: flex; justify-content: space-between; }
        .signature-box { width: 30%; text-align: left; }
        .signature-box.right-box { text-align: center; }
        .signature-box p { margin: 2px 0; }
        .signature-space { height: 40px; }
        
        /* Footer Lines */
        .footer-lines { margin-top: 15px; border: 1px solid #000; padding: 3px 8px; font-size: 7pt; display: flex; justify-content: space-between; }
        
        @media print {
            body { background: none; }
            .container { width: 100%; padding: 0; margin: 0; border: none; }
            .no-print { display: none; }
            @page { margin: 0.5cm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-logo">
                <img src="{{ asset('images/logo-umpar.png') }}" alt="Logo UMPAR" onerror="this.outerHTML='<div style=\'width:60px;height:60px;background:#ccc;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;\'>LOGO</div>'">
            </div>
            <div class="header-text">
                <h1>{{ str_starts_with(strtolower($mahasiswa->prodi->fakultas->nama ?? ''), 'fakultas') ? strtoupper($mahasiswa->prodi->fakultas->nama) : 'FAKULTAS ' . strtoupper($mahasiswa->prodi->fakultas->nama ?? 'TEKNIK') }}</h1>
                <h2>UNIVERSITAS MUHAMMADIYAH PAREPARE</h2>
            </div>
        </div>

        <div class="title-bar">KARTU RENCANA STUDI (KRS)</div>

        <!-- Student Info -->
        <div class="info-container">
            <div class="info-col">
                <table class="info-table">
                    <tr><td class="label">PROGRAM STUDI</td><td class="separator">:</td><td>{{ strtoupper($mahasiswa->prodi->nama ?? '-') }}</td></tr>
                    <tr><td class="label">JENJANG PROGRAM</td><td class="separator">:</td><td>STRATA SATU (S1)</td></tr>
                    <tr><td class="label">TAHUN AKADEMIK</td><td class="separator">:</td><td>{{ $krs->tahunAkademik->tahun }}</td></tr>
                    <tr><td class="label">SEMESTER</td><td class="separator">:</td><td>{{ strtoupper($krs->tahunAkademik->semester) }}</td></tr>
                </table>
            </div>
            <div class="info-col">
                <table class="info-table">
                    <tr><td class="label">NAMA MAHASISWA</td><td class="separator">:</td><td>{{ strtoupper($mahasiswa->user->name) }}</td></tr>
                    <tr><td class="label">NIM</td><td class="separator">:</td><td>{{ $mahasiswa->nim }}</td></tr>
                    <tr><td class="label">TEMPAT, TGL. LAHIR</td><td class="separator">:</td><td>{{ strtoupper($mahasiswa->tempat_lahir ?? '-') }}, {{ $mahasiswa->tanggal_lahir ? $mahasiswa->tanggal_lahir->format('d/m/Y') : '-' }}</td></tr>
                    <tr><td class="label">KELAS</td><td class="separator">:</td><td>-</td></tr>
                    <tr><td class="label">KONSENTRASI</td><td class="separator">:</td><td>-</td></tr>
                </table>
            </div>
        </div>

        <!-- Courses Table -->
        <table class="course-table">
            @php 
                $grandTotalSks = 0;
                $romanNumerals = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X'];
            @endphp
            
            <tbody>
            @forelse($groupedMataKuliah as $semester => $mkGroup)
                @php $semesterSks = 0; @endphp
                <tr>
                    <th style="width: 10%;">SMT {{ $romanNumerals[$semester] ?? $semester }}</th>
                    <th style="width: 15%;">KODE MK</th>
                    <th>MATAKULIAH</th>
                    <th style="width: 20%;">DOSEN</th>
                    <th style="width: 5%; text-align: center;">SKS</th>
                </tr>
                @foreach($mkGroup as $index => $mk)
                    @php 
                        // Cek apakah mata kuliah ini diambil oleh mahasiswa di KRS ini
                        $takenDetail = $krs->krsDetail->first(fn($d) => $d->kelas && $d->kelas->mata_kuliah_id === $mk->id);
                        $isTaken = !empty($takenDetail);
                        if ($isTaken) {
                            $semesterSks += $mk->sks;
                            $grandTotalSks += $mk->sks;
                        }
                        
                        // Tentukan nama dosen (kosong karena diambil langsung dari data matkul)
                        $dosenName = '';
                    @endphp
                    <tr>
                        <td>
                            <span style="display:inline-block; width: 15px;">{{ $index + 1 }}</span>
                            <div class="oval {{ $isTaken ? 'filled' : '' }}"></div>
                        </td>
                        <td>{{ $mk->kode_mk }}</td>
                        <td>{{ $mk->nama_mk }}</td>
                        <td>{{ $dosenName }}</td>
                        <td class="center">{{ $mk->sks }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" class="right">JUMLAH SKS</td>
                    <td class="center">{{ $semesterSks }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="center">Tidak ada mata kuliah yang ditawarkan pada semester ini.</td></tr>
            @endforelse
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary-section">
            <table style="border:none; width: 300px;">
                <tr><td>- Indeks Prestasi (IP) Semester Lalu</td><td style="width:10px;">:</td><td>-</td></tr>
                <tr><td>- Jumlah SKS yang Diprogramkan Semester Ini</td><td>:</td><td>{{ $grandTotalSks }}</td></tr>
            </table>
        </div>

        <!-- Date & Signatures -->
        @php
            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $tglSekarang = now();
        @endphp
        
        <div style="text-align: right; font-size: 9pt; margin-top: 10px;">
            Parepare, {{ $tglSekarang->format('d/m/Y') }}
        </div>

        <div class="signature-container">
            <div class="signature-row">
                <!-- Ketua Prodi -->
                <div class="signature-box">
                    <p>Ketua Program Studi</p>
                    <div class="signature-space"></div>
                    <p style="text-decoration: underline; font-weight: bold;">{{ $mahasiswa->prodi->nama_ketua_prodi ?? '........................................' }}</p>
                    <p>NBM : {{ $mahasiswa->prodi->nidn_ketua_prodi ?? '....................' }}</p>
                </div>

                <!-- Dosen PA -->
                <div class="signature-box">
                    <p>Penasehat Akademik</p>
                    <div class="signature-space"></div>
                    <p style="text-decoration: underline; font-weight: bold;">{{ $mahasiswa->dosenPa->user->name ?? '........................................' }}</p>
                    <p>NBM : {{ $mahasiswa->dosenPa->nidn ?? '....................' }}</p>
                </div>

                <!-- Mahasiswa -->
                <div class="signature-box right-box">
                    <p>Mahasiswa</p>
                    <div class="signature-space"></div>
                    <p style="text-decoration: underline; font-weight: bold;">{{ strtoupper($mahasiswa->user->name) }}</p>
                    <p>NIM : {{ $mahasiswa->nim }}</p>
                </div>
            </div>
        </div>

        <div class="footer-lines">
            <span>1. Kuning : BAAK</span>
            <span>2. Hijau : Penasehat Akademik</span>
            <span>4. Putih : Program Studi</span>
            <span>5. Merah : Mahasiswa</span>
        </div>
        
    </div>
    
    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px; background: white; padding: 10px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Cetak Sekarang</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: #fff; border: none; border-radius: 6px; cursor: pointer; margin-left: 8px;">Tutup</button>
    </div>
</body>
</html>
