<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Perkuliahan - {{ $prodi->nama }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14pt;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            word-wrap: break-word;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            font-size: 9pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .day-row {
            background-color: #e9e9e9;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }
        .signature {
            width: 250px;
            text-align: center;
        }
        .signature p {
            margin: 0;
        }
        .signature .space {
            height: 70px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                -webkit-print-color-adjust: exact;
            }
        }
        .no-print {
            background: #234C6A;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">Cetak Halaman Ini</button>

    <div class="header">
        <h1>JADWAL PERKULIAHAN SEMESTER {{ strtoupper($semesterType) }}</h1>
        <h2>TAHUN AKADEMIK {{ $activeYear?->tahun }}</h2>
        <p>PROGRAM STUDI {{ strtoupper($prodi->nama) }} - {{ strtoupper($prodi->fakultas->nama) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="100">Hari</th>
                <th width="120">Waktu</th>
                <th>Mata Kuliah</th>
                <th width="50">Kelas</th>
                <th width="80">Ruangan</th>
                <th width="70">Semester</th>
                <th>Dosen Pengampu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groupedJadwal as $day => $list)
                @foreach($list as $index => $j)
                    <tr>
                        @if($index === 0)
                            <td rowspan="{{ count($list) }}" class="text-center font-bold" style="vertical-align: middle; background: #fafafa; border-right: 2px solid #000;">
                                {{ strtoupper($day) }}
                            </td>
                        @endif
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}
                        </td>
                        <td>{{ $j->kelas->mataKuliah->nama_mk }}</td>
                        <td class="text-center">{{ $j->kelas->nama_kelas }}</td>
                        <td class="text-center">{{ $j->ruangan ?? '-' }}</td>
                        <td class="text-center">{{ $j->kelas->mataKuliah->semester }}</td>
                        <td>{{ $j->kelas->dosen->user->name }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada jadwal kuliah yang tersedia untuk kriteria ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <p>Makassar, {{ date('d F Y') }}</p>
            <p>Ketua Program Studi,</p>
            <div class="space"></div>
            <p><strong>( __________________________ )</strong></p>
            <p>NIDN. ...........................</p>
        </div>
    </div>
</body>
</html>
