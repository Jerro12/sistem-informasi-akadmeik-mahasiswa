<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAFTAR NILAI_{{ $mahasiswa->nim }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; line-height: 1.3; color: #000; margin: 0; padding: 0; }
        .container { width: 21cm; min-height: 29.7cm; padding: 1.5cm; margin: auto; background: #fff; }
        
        .header { display: flex; align-items: center; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 10px; }
        .header-logo { width: 65px; height: 65px; margin-right: 15px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .header-logo img { width: 100%; height: auto; object-fit: contain; }
        .header-text { flex: 1; }
        .header-text h1 { font-size: 14pt; margin: 0; text-transform: uppercase; letter-spacing: 1px; font-weight: normal; }
        .header-text h2 { font-size: 12pt; margin: 0; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
        
        .title { text-align: center; font-weight: bold; font-size: 11pt; margin-top: 10px; margin-bottom: 15px; text-transform: uppercase; }
        
        .info-table { width: 65%; margin-bottom: 10px; border-collapse: collapse; }
        .info-table td { padding: 1px 0; vertical-align: top; font-size: 9pt; }
        .info-table td.label { width: 140px; }
        .info-table td.separator { width: 10px; text-align: center; }
        
        .nilai-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; font-size: 8.5pt; }
        .nilai-table th, .nilai-table td { border: 1px solid #000; padding: 2px 4px; }
        .nilai-table th { text-align: center; font-weight: normal; }
        .nilai-table .center { text-align: center; }
        
        /* Two-line header for Nilai */
        .nilai-header-top th { border-bottom: none; }
        .nilai-header-bot th { border-top: 1px solid #000; }
        
        .summary-section { margin-top: 5px; font-size: 9pt; line-height: 1.5; }
        .summary-table { width: 50%; border-collapse: collapse; }
        .summary-table td { padding: 1px 0; vertical-align: top; }
        .summary-table td.label { width: 180px; }
        .summary-table td.separator { width: 15px; text-align: center; }
        
        .signature-container { width: 100%; margin-top: 20px; }
        .signature-box { width: 35%; float: right; text-align: left; font-size: 9pt; }
        .signature-box p { margin: 2px 0; }
        .signature-space { height: 60px; }
        .clear { clear: both; }

        @media print {
            body { background: none; }
            .container { width: 100%; padding: 0; margin: 0; }
            .no-print { display: none; }
            @page { margin: 1.5cm; size: A4 portrait; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-logo">
                @php
                    $logoPath = public_path('images/logo-umpar.png');
                    $logoSrc = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : asset('images/logo-umpar.png');
                @endphp
                <img src="{{ $logoSrc }}" alt="Logo UMPAR">
            </div>
            <div class="header-text">
                <h2>{{ str_starts_with(strtolower($mahasiswa->prodi->fakultas->nama ?? ''), 'fakultas') ? strtoupper($mahasiswa->prodi->fakultas->nama) : 'FAKULTAS ' . strtoupper($mahasiswa->prodi->fakultas->nama ?? 'TEKNIK') }}</h2>
                <h1>UNIVERSITAS MUHAMMADIYAH PAREPARE</h1>
            </div>
        </div>

        <div class="title">DAFTAR NILAI</div>

        <!-- Student Info -->
        <table class="info-table">
            <tr>
                <td class="label">Nama Mahasiswa</td>
                <td class="separator">:</td>
                <td>{{ strtoupper($mahasiswa->user->name) }}</td>
            </tr>
            <tr>
                <td class="label">Tempat/Tgl. Lahir</td>
                <td class="separator">:</td>
                <td>{{ strtoupper($mahasiswa->tempat_lahir ?? '-') }}, {{ $mahasiswa->tanggal_lahir ? \Carbon\Carbon::parse($mahasiswa->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">NIM</td>
                <td class="separator">:</td>
                <td>{{ $mahasiswa->nim }}</td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td class="separator">:</td>
                <td>{{ strtoupper($mahasiswa->prodi->nama ?? '-') }}</td>
            </tr>
            <tr>
                <td class="label">Jenjang Program</td>
                <td class="separator">:</td>
                <td>Strata-1</td>
            </tr>
            <tr>
                <td class="label">Tanggal Yudisium</td>
                <td class="separator">:</td>
                <td>-</td>
            </tr>
        </table>

        <!-- Flat List of Courses -->
        @php
            $bobotMap = ['A' => 4.0, 'B' => 3.0, 'C' => 2.0, 'D' => 1.0, 'E' => 0.0, 'T' => 0.0, '-' => 0.0];
            $flatCourses = [];
            foreach($transcript['semesters'] ?? [] as $sem) {
                foreach($sem['courses'] as $course) {
                    $flatCourses[] = $course;
                }
            }
            $totalSks = 0;
            $totalMutu = 0;
        @endphp

        <table class="nilai-table">
            <thead>
                <tr class="nilai-header-top">
                    <th rowspan="2" style="width: 4%;">No.</th>
                    <th rowspan="2" style="width: 12%;">KODE MK</th>
                    <th rowspan="2">MATAKULIAH</th>
                    <th colspan="3" style="border-bottom: 1px solid #000;">NILAI</th>
                    <th rowspan="2" style="width: 10%;">MUTU<br>N x K</th>
                </tr>
                <tr class="nilai-header-bot">
                    <th style="width: 5%;">AM</th>
                    <th style="width: 5%;">HM</th>
                    <th style="width: 5%;">K</th>
                </tr>
            </thead>
            <tbody>
                @foreach($flatCourses as $i => $course)
                @php
                    $isGraded = isset($course['nilai_huruf']) && $course['nilai_huruf'] !== '-' && $course['nilai_huruf'] !== 'T';
                    $am = $isGraded ? ($bobotMap[$course['nilai_huruf']] ?? (float)($course['bobot'] ?? 0)) : 0.0;
                    $k = $course['sks'];
                    $mutu = $am * $k;
                    $totalSks += $k;
                    $totalMutu += $mutu;
                @endphp
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td class="center">{{ $course['kode'] }}</td>
                    <td>{{ $course['nama'] }}</td>
                    <td class="center">{{ $isGraded ? number_format($am, 1) : '-' }}</td>
                    <td class="center">{{ $course['nilai_huruf'] }}</td>
                    <td class="center">{{ $k }}</td>
                    <td class="center">{{ $isGraded ? number_format($mutu, 1) : '0' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        @php
            $ipk = $totalSks > 0 ? $totalMutu / $totalSks : 0;
            $predikat = 'Cukup';
            if ($ipk >= 3.51) $predikat = 'DENGAN PUJIAN (CUMLAUDE)';
            elseif ($ipk >= 2.76) $predikat = 'SANGAT MEMUASKAN (B)';
            elseif ($ipk >= 2.00) $predikat = 'MEMUASKAN (C)';
        @endphp
        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <td class="label">Jumlah Matakuliah</td>
                    <td class="separator">:</td>
                    <td>{{ count($flatCourses) }}</td>
                </tr>
                <tr>
                    <td class="label">Total Satuan Kredit Semester (SKS)</td>
                    <td class="separator">:</td>
                    <td>{{ $totalSks }}</td>
                </tr>
                <tr>
                    <td class="label">Total Nilai</td>
                    <td class="separator">:</td>
                    <td>{{ $totalMutu }}</td>
                </tr>
                <tr>
                    <td class="label">Indeks Prestasi (IP)</td>
                    <td class="separator">:</td>
                    <td>{{ number_format($ipk, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Predikat Kelulusan</td>
                    <td class="separator">:</td>
                    <td>{{ $predikat }}</td>
                </tr>
                <tr>
                    <td class="label">Judul Skripsi</td>
                    <td class="separator">:</td>
                    <td>-</td>
                </tr>
            </table>
        </div>

        <!-- Date & Signature -->
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
                <p>Parepare, {{ $tglSekarang->day }} {{ $namaBulan[$tglSekarang->month] }} {{ $tglSekarang->year }}</p>
                <p>an. DEKAN</p>
                <p>WAKIL DEKAN I</p>
                <div class="signature-space"></div>
                <p><span style="text-decoration: underline;">{{ strtoupper($mahasiswa->prodi->fakultas->nama_wakil_dekan1 ?? '........................................') }}</span></p>
                <p>NBM. {{ $mahasiswa->prodi->fakultas->nidn_wakil_dekan1 ?? '......................' }}</p>
            </div>
            <div class="clear"></div>
        </div>
    </div>

    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px; background: white; padding: 10px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Cetak Sekarang</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: #fff; border: none; border-radius: 6px; cursor: pointer; margin-left: 8px;">Tutup</button>
    </div>
</body>
</html>
