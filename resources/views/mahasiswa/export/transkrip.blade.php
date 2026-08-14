<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRANSKRIP_{{ $mahasiswa->nim }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 10pt; color: #000; margin: 0; padding: 0; }
        .container { width: 21cm; min-height: 29.7cm; padding: 1cm 1.5cm; margin: auto; background: #fff; box-sizing: border-box; }
        
        /* Header / Kop Surat UMPAR */
        .header { display: flex; align-items: center; border-bottom: 2px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; }
        .header-logo { width: 65px; height: 65px; margin-right: 15px; display: flex; align-items: center; justify-content: center; }
        .header-logo img { width: 100%; height: auto; object-fit: contain; }
        .header-text h1 { font-size: 15pt; margin: 0; color: #003366; text-transform: uppercase; letter-spacing: 1px; }
        .header-text h2 { font-size: 11pt; margin: 0; font-weight: normal; color: #333; text-transform: uppercase; }
        
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
        .course-table th { background-color: #e5e7eb; text-align: center; font-weight: bold; }
        .course-table .center { text-align: center; }
        .course-table .right { text-align: right; }
        
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
        
        .print-btn { position: fixed; bottom: 20px; right: 20px; padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .print-btn:hover { background: #1d4ed8; }
        
        @media print {
            body { background: none; }
            .container { width: 100%; padding: 0; margin: 0; border: none; }
            .print-btn { display: none; }
            @page { margin: 1cm; size: A4 portrait; }
        }
    </style>
</head>
<body onload="window.print()">
    <button class="print-btn" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

    <div class="container">
        <!-- Header Kop Surat UMPAR -->
        <div class="header">
            <div class="header-logo">
                @php
                    $logoPath = public_path('images/logo-umpar.png');
                    $logoSrc = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : asset('images/logo-umpar.png');
                @endphp
                <img src="{{ $logoSrc }}" alt="Logo UMPAR">
            </div>
            <div class="header-text">
                <h2>{{ str_starts_with(strtolower($mahasiswa->prodi?->fakultas?->nama ?? ''), 'fakultas') ? strtoupper($mahasiswa->prodi?->fakultas?->nama) : 'FAKULTAS ' . strtoupper($mahasiswa->prodi?->fakultas?->nama ?? 'TEKNIK') }}</h2>
                <h1>UNIVERSITAS MUHAMMADIYAH PAREPARE</h1>
            </div>
        </div>

        <div class="title-bar">TRANSKRIP AKADEMIK</div>

        <!-- Student Info -->
        <div class="info-container">
            <div class="info-col">
                <table class="info-table">
                    <tr><td class="label">Nama Mahasiswa</td><td class="separator">:</td><td><strong>{{ $mahasiswa->user->name }}</strong></td></tr>
                    <tr><td class="label">NIM</td><td class="separator">:</td><td><strong>{{ $mahasiswa->nim }}</strong></td></tr>
                    <tr><td class="label">Program Studi</td><td class="separator">:</td><td>{{ $mahasiswa->prodi?->nama ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="info-col">
                <table class="info-table">
                    <tr><td class="label">Fakultas</td><td class="separator">:</td><td>{{ $mahasiswa->prodi?->fakultas?->nama ?? '-' }}</td></tr>
                    <tr><td class="label">Jenjang Program</td><td class="separator">:</td><td>Strata-1 (S1)</td></tr>
                    <tr><td class="label">Tanggal Cetak</td><td class="separator">:</td><td>{{ now()->format('d F Y') }}</td></tr>
                </table>
            </div>
        </div>

        <!-- Course Table -->
        <table class="course-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 14%;">Kode MK</th>
                    <th>Matakuliah</th>
                    <th style="width: 8%;">SKS (K)</th>
                    <th style="width: 12%;">Nilai Huruf (HM)</th>
                    <th style="width: 12%;">Nilai Angka (AM)</th>
                    <th style="width: 12%;">Bobot (N x K)</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $totalSks = 0;
                    $totalSksDinilai = 0;
                    $totalSksLulus = 0;
                    $totalBobot = 0; 
                    $courses = $transcript['all_courses'] ?? collect();
                    $bobotMap = ['A' => 4.0, 'B+' => 3.5, 'B' => 3.0, 'C+' => 2.5, 'C' => 2.0, 'D' => 1.0, 'E' => 0.0, 'T' => 0.0, '-' => 0.0];
                @endphp
                @forelse($courses as $index => $course)
                @php
                    $huruf = isset($course['nilai_huruf']) ? strtoupper(trim($course['nilai_huruf'])) : '';
                    $isGraded = $huruf !== '' && $huruf !== '-' && $huruf !== 'T';
                    $am = $isGraded ? ($bobotMap[$huruf] ?? (float)($course['bobot'] ?? 0)) : 0.0;
                    $k = (int) ($course['sks'] ?? 0);
                    $mutu = $am * $k;
                    $totalSks += $k;
                    if ($isGraded) {
                        $totalSksDinilai += $k;
                        $totalBobot += $mutu;
                        if (in_array($huruf, ['A', 'B+', 'B', 'C+', 'C', 'D'])) {
                            $totalSksLulus += $k;
                        }
                    }
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $course['kode'] }}</td>
                    <td>{{ $course['nama'] }}</td>
                    <td class="center">{{ $k }}</td>
                    <td class="center"><strong>{{ $course['nilai_huruf'] }}</strong></td>
                    <td class="center">{{ $isGraded ? number_format($am, 1) : '-' }}</td>
                    <td class="center">{{ $isGraded ? number_format($mutu, 1) : '0' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="center">Belum ada mata kuliah yang diprogram.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                @php
                    $calculatedIpk = $totalSksDinilai > 0 ? round($totalBobot / $totalSksDinilai, 2) : 0.00;
                @endphp
                <tr style="background-color: #f3f4f6; font-weight: bold;">
                    <td colspan="3" class="right">Total SKS & Bobot Mutu</td>
                    <td class="center">{{ $totalSks }}</td>
                    <td colspan="2" class="center">IPK</td>
                    <td class="center">{{ number_format($calculatedIpk, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Summary -->
        @php
            $ipk = $calculatedIpk;
            $predikat = 'Cukup';
            if ($ipk >= 3.51) $predikat = 'DENGAN PUJIAN (CUMLAUDE)';
            elseif ($ipk >= 2.76) $predikat = 'SANGAT MEMUASKAN';
            elseif ($ipk >= 2.00) $predikat = 'MEMUASKAN';
        @endphp
        <div class="summary-container">
            <table class="summary-table">
                <tr><td class="label">Jumlah Matakuliah Diprogram</td><td class="separator">:</td><td>{{ $courses->count() }}</td></tr>
                <tr><td class="label">Total Satuan Kredit Semester (SKS) Diprogram</td><td class="separator">:</td><td>{{ $totalSks }} SKS</td></tr>
                <tr><td class="label">Total SKS Lulus</td><td class="separator">:</td><td>{{ $totalSksLulus }} SKS</td></tr>
                <tr><td class="label">Total Nilai Mutu</td><td class="separator">:</td><td>{{ number_format($totalBobot, 1) }}</td></tr>
                <tr><td class="label">Indeks Prestasi Kumulatif (IPK)</td><td class="separator">:</td><td><strong>{{ number_format($ipk, 2, ',', '.') }}</strong></td></tr>
                <tr><td class="label">Predikat Kelulusan</td><td class="separator">:</td><td>{{ $predikat }}</td></tr>
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
                <p>DEKAN / WAKIL DEKAN I</p>
                <div class="signature-space"></div>
                <p style="text-decoration: underline; font-weight: bold;">{{ $mahasiswa->prodi?->fakultas?->nama_wakil_dekan1 ?? '........................................' }}</p>
                <p>NBM. &nbsp; {{ $mahasiswa->prodi?->fakultas?->nidn_wakil_dekan1 ?? '....................' }}</p>
            </div>
        </div>

    </div>
</body>
</html>
