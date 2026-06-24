<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KHS_{{ $mahasiswa->nim }}_{{ $tahunAkademik->tahun }}_{{ $tahunAkademik->semester }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 10pt; color: #000; margin: 0; padding: 0; }
        .container { width: 21cm; min-height: 29.7cm; padding: 1cm 1.5cm; margin: auto; background: #fff; box-sizing: border-box; }
        
        /* Header / Kop Surat */
        .header { display: flex; align-items: center; border-bottom: 2px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; }
        .header-logo { width: 65px; height: 65px; margin-right: 15px; display: flex; align-items: center; justify-content: center; }
        .header-logo img { width: 100%; height: auto; object-fit: contain; }
        .header-text h1 { font-size: 16pt; margin: 0; color: #003366; text-transform: uppercase; letter-spacing: 1px; }
        .header-text h2 { font-size: 11pt; margin: 0; font-weight: normal; color: #555; text-transform: uppercase; }
        
        .title-bar { background-color: #d1d5db; text-align: center; font-weight: bold; font-size: 12pt; padding: 5px 0; margin-bottom: 15px; text-transform: uppercase; border: 1px solid #9ca3af; }
        
        /* Info Table */
        .info-container { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 9pt; }
        .info-col { width: 48%; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        .info-table td.label { width: 140px; }
        .info-table td.separator { width: 15px; text-align: center; }
        
        /* Course Table */
        .course-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9pt; }
        .course-table th, .course-table td { border: 1px solid #9ca3af; padding: 5px 6px; }
        .course-table th { background-color: #e5e7eb; text-align: center; }
        .course-table .center { text-align: center; }
        
        /* Summary Table */
        .summary-container { width: 100%; font-size: 9pt; margin-bottom: 20px; }
        .summary-table { border-collapse: collapse; }
        .summary-table td { padding: 3px 0; }
        .summary-table td.label { width: 350px; }
        .summary-table td.separator { width: 20px; text-align: center; }
        
        /* Signature Area */
        .signature-container { width: 100%; margin-top: 30px; font-size: 9pt; display: flex; justify-content: flex-end; }
        .signature-box { width: 300px; text-align: left; }
        .signature-box p { margin: 2px 0; }
        .signature-space { height: 60px; }
        
        @media print {
            body { background: none; }
            .container { width: 100%; padding: 0; margin: 0; border: none; }
            .no-print { display: none; }
            @page { margin: 1cm; }
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
                <h1>FAKULTAS {{ strtoupper($mahasiswa->prodi->fakultas->nama ?? 'TEKNIK') }}</h1>
                <h2>UNIVERSITAS MUHAMMADIYAH PAREPARE</h2>
            </div>
        </div>

        <div class="title-bar">KARTU HASIL STUDI (KHS)</div>

        <!-- Student Info -->
        <div class="info-container">
            <div class="info-col">
                <table class="info-table">
                    <tr><td class="label">Nama Mahasiswa</td><td class="separator">:</td><td>{{ $mahasiswa->user->name }}</td></tr>
                    <tr><td class="label">NIM</td><td class="separator">:</td><td>{{ $mahasiswa->nim }}</td></tr>
                    <tr><td class="label">Program Studi</td><td class="separator">:</td><td>{{ $mahasiswa->prodi->nama ?? '-' }}</td></tr>
                    <tr><td class="label">Penasehat Akademik</td><td class="separator">:</td><td>{{ $mahasiswa->dosenPa->user->name ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="info-col">
                <table class="info-table">
                    <tr><td class="label">Semester</td><td class="separator">:</td><td>{{ strtoupper($tahunAkademik->semester) }}</td></tr>
                    <tr><td class="label">Tahun Akademik</td><td class="separator">:</td><td>{{ $tahunAkademik->tahun }}</td></tr>
                </table>
            </div>
        </div>

        <!-- Courses Table -->
        <table class="course-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 15%;">Kode MK</th>
                    <th>Matakuliah</th>
                    <th style="width: 8%;">SKS (K)</th>
                    <th style="width: 10%;">Nilai Huruf</th>
                    <th style="width: 12%;">Nilai Angka (N)</th>
                    <th style="width: 8%;">N x K</th>
                    <th style="width: 8%;">Ket.</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                    $totalSks = 0;
                    $totalMutu = 0;
                    $bobotMap = ['A' => 4.0, 'B+' => 3.5, 'B' => 3.0, 'C+' => 2.5, 'C' => 2.0, 'D' => 1.0, 'E' => 0];
                @endphp
                @forelse($nilaiList as $nilai)
                    @php
                        $mk = $nilai->kelas->mataKuliah;
                        // For display logic based on standard university
                        $nilaiHuruf = $nilai->nilai_huruf ?? '-';
                        $bobot = $bobotMap[$nilaiHuruf] ?? 0;
                        if ($nilaiHuruf === '-') $bobot = 0;
                        
                        $mutu = $bobot * $mk->sks;
                        $totalSks += $mk->sks;
                        $totalMutu += $mutu;
                    @endphp
                    <tr>
                        <td class="center">{{ $no++ }}</td>
                        <td class="center">{{ $mk->kode_mk }}</td>
                        <td>{{ $mk->nama_mk }}</td>
                        <td class="center">{{ $mk->sks }}</td>
                        <td class="center">{{ $nilaiHuruf }}</td>
                        <td class="center">{{ $nilaiHuruf !== '-' ? $bobot : '0' }}</td>
                        <td class="center">{{ $nilaiHuruf !== '-' ? $mutu : '0' }}</td>
                        <td></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="center">Belum ada data nilai.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary -->
        @php
            $ips = $totalSks > 0 ? ($totalMutu / $totalSks) : 0;
            // Prediksi max sks (standar umum)
            $maxSks = 12;
            if ($ips >= 3.00) $maxSks = 24;
            elseif ($ips >= 2.50) $maxSks = 21;
            elseif ($ips >= 2.00) $maxSks = 18;
            elseif ($ips >= 1.50) $maxSks = 15;
        @endphp
        <div class="summary-container">
            <table class="summary-table">
                <tr><td class="label">Jumlah Matakuliah yang diprogram</td><td class="separator">:</td><td>{{ $nilaiList->count() }}</td></tr>
                <tr><td class="label">Total Satuan Kredit Semester (SKS)</td><td class="separator">:</td><td>{{ $totalSks }}</td></tr>
                <tr><td class="label">Total Nilai</td><td class="separator">:</td><td>{{ $totalMutu }}</td></tr>
                <tr><td class="label">Indeks Prestasi (IP) semester ini</td><td class="separator">:</td><td>{{ number_format($ips, 2, ',', '.') }}</td></tr>
                <tr><td class="label">Jumlah kredit maksimum yang dapat diprogram semester depan</td><td class="separator">:</td><td>{{ $maxSks }}</td></tr>
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
        
        <div class="signature-container">
            <div class="signature-box">
                <p>Parepare, &nbsp; {{ $tglSekarang->day }} {{ $namaBulan[$tglSekarang->month] }} {{ $tglSekarang->year }}</p>
                <p>a.n. DEKAN</p>
                <p>WAKIL DEKAN I</p>
                <div class="signature-space"></div>
                <p style="text-decoration: underline; font-weight: bold;">{{ $mahasiswa->prodi->fakultas->nama_wakil_dekan1 ?? '........................................' }}</p>
                <p>NBM. &nbsp; {{ $mahasiswa->prodi->fakultas->nidn_wakil_dekan1 ?? '....................' }}</p>
            </div>
        </div>

    </div>
    
    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px; background: white; padding: 10px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Cetak Sekarang</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: #fff; border: none; border-radius: 6px; cursor: pointer; margin-left: 8px;">Tutup</button>
    </div>
</body>
</html>
