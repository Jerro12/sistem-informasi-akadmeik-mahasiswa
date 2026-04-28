<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KRS_{{ $mahasiswa->nim }}_{{ $krs->tahunAkademik->tahun }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.4; color: #000; margin: 0; padding: 0; }
        .container { width: 21cm; min-height: 29.7cm; padding: 1.5cm; margin: auto; background: #fff; }
        
        /* Header / Kop Surat */
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; position: relative; }
        .header h1 { font-size: 16pt; margin: 0; text-transform: uppercase; }
        .header h2 { font-size: 14pt; margin: 5px 0; text-transform: uppercase; }
        .header p { font-size: 10pt; margin: 2px 0; }
        
        .title { text-align: center; text-decoration: underline; font-weight: bold; font-size: 14pt; margin-bottom: 20px; text-transform: uppercase; }
        
        /* Info Table */
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 3px 0; vertical-align: top; font-size: 11pt; }
        .info-table td.label { width: 140px; }
        .info-table td.separator { width: 15px; text-align: center; }
        
        /* Course Table */
        .course-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .course-table th, .course-table td { border: 1px solid #000; padding: 6px 8px; font-size: 10pt; }
        .course-table th { background-color: #f2f2f2; text-transform: uppercase; font-weight: bold; }
        .course-table .center { text-align: center; }
        .total-row { font-weight: bold; }
        
        /* Signature Area */
        .signature-container { width: 100%; margin-top: 40px; }
        .signature-box { width: 33.3%; float: left; text-align: center; font-size: 10pt; }
        .signature-box p { margin: 0; }
        .signature-space { height: 70px; }
        .clear { clear: both; }

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
            <h1>UNIVERSITAS CONTOH INDONESIA</h1>
            <h2>FAKULTAS {{ strtoupper($mahasiswa->prodi->fakultas->nama ?? 'AKADEMIK') }}</h2>
            <p>Alamat Kampus Utama, Jl. Pendidikan No. 123, Kota, Indonesia</p>
            <p>Email: info@universitas.ac.id | Website: www.universitas.ac.id</p>
        </div>

        <div class="title">KARTU RENCANA STUDI (KRS)</div>

        <!-- Student Info -->
        <table class="info-table">
            <tr>
                <td class="label">NIM</td>
                <td class="separator">:</td>
                <td><strong>{{ $mahasiswa->nim }}</strong></td>
                <td class="label">Semester</td>
                <td class="separator">:</td>
                <td>{{ ucfirst($krs->tahunAkademik->semester) }}</td>
            </tr>
            <tr>
                <td class="label">Nama Mahasiswa</td>
                <td class="separator">:</td>
                <td><strong>{{ $mahasiswa->user->name }}</strong></td>
                <td class="label">Tahun Akademik</td>
                <td class="separator">:</td>
                <td>{{ $krs->tahunAkademik->tahun }}</td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td class="separator">:</td>
                <td>{{ $mahasiswa->prodi->nama ?? '-' }}</td>
                <td class="label">Dosen PA</td>
                <td class="separator">:</td>
                <td>{{ $mahasiswa->dosenPa->user->name ?? '-' }}</td>
            </tr>
        </table>

        <!-- Courses -->
        <table class="course-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 15%;">Kode MK</th>
                    <th>Mata Kuliah</th>
                    <th style="width: 8%;">SKS</th>
                    <th style="width: 25%;">Dosen Pengampu</th>
                </tr>
            </thead>
            <tbody>
                @php $totalSks = 0; @endphp
                @foreach($krs->krsDetail as $index => $detail)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $detail->kelas->mataKuliah->kode_mk }}</td>
                    <td>{{ $detail->kelas->mataKuliah->nama_mk }}</td>
                    <td class="center">{{ $detail->kelas->mataKuliah->sks }}</td>
                    <td>{{ $detail->kelas->dosen->user->name ?? '-' }}</td>
                </tr>
                @php $totalSks += $detail->kelas->mataKuliah->sks; @endphp
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="center">TOTAL SKS YANG DIAMBIL</td>
                    <td class="center">{{ $totalSks }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <!-- Signatures -->
        <div class="signature-container">
            <div class="signature-box">
                <p>Mengetahui,</p>
                <p>Dosen Pembimbing Akademik</p>
                <div class="signature-space"></div>
                <p><strong>( {{ $mahasiswa->dosenPa->user->name ?? '........................................' }} )</strong></p>
                <p>NIDN. {{ $mahasiswa->dosenPa->nidn ?? '....................' }}</p>
            </div>
            
            <div class="signature-box">
                <p>&nbsp;</p>
                <p>Mahasiswa,</p>
                <div class="signature-space"></div>
                <p><strong>( {{ $mahasiswa->user->name }} )</strong></p>
                <p>NIM. {{ $mahasiswa->nim }}</p>
            </div>

            <div class="signature-box">
                <p>Disetujui,</p>
                <p>Ketua Program Studi</p>
                <div class="signature-space"></div>
                <p><strong>( ........................................ )</strong></p>
                <p>NIP. ....................</p>
            </div>
            <div class="clear"></div>
        </div>

        <div style="margin-top: 30px; font-size: 8pt; italic; text-align: right; color: #555;">
            Dicetak melalui Sistem Informasi Akademik pada: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
    
    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px; background: white; padding: 10px; border-radius: 10px; shadow: 0 0 10px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Cetak Sekarang</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: #fff; border: none; border-radius: 6px; cursor: pointer; margin-left: 8px;">Tutup</button>
    </div>
</body>
</html>
