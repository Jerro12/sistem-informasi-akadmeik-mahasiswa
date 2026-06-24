<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Permohonan KP - {{ $kp->mahasiswa->nim }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.8; color: #000; margin: 0; padding: 0; }
        .container { width: 21cm; min-height: 29.7cm; padding: 2.5cm 2cm; margin: auto; background: #fff; }
        
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header .logo-area { float: left; width: 80px; }
        .header .title-area { text-align: center; }
        .header h1 { font-size: 15pt; margin: 0 0 3px 0; text-transform: uppercase; font-weight: bold; }
        .header h2 { font-size: 13pt; margin: 0 0 3px 0; text-transform: uppercase; }
        .header p { font-size: 10pt; margin: 0; }
        
        .surat-number { margin-bottom: 20px; font-size: 11pt; }
        .surat-number p { margin: 0; line-height: 1.4; }
        
        .penerima { margin-bottom: 20px; font-size: 12pt; line-height: 1.6; }
        .penerima p { margin: 0; }
        
        .isi { font-size: 12pt; line-height: 1.8; margin-bottom: 20px; text-align: justify; }
        .isi p { margin: 0 0 10px 0; }
        .isi .indent { text-indent: 2cm; }
        
        .data-table { width: 100%; margin: 15px 0; font-size: 12pt; }
        .data-table td { padding: 3px 0; vertical-align: top; }
        .data-table td.label { width: 180px; }
        .data-table td.separator { width: 20px; }
        
        .penutup { font-size: 12pt; line-height: 1.8; text-align: justify; }
        
        .signature-area { margin-top: 30px; float: right; text-align: center; width: 280px; font-size: 12pt; }
        .signature-area p { margin: 0; line-height: 1.6; }
        .signature-space { height: 70px; }
        .clear { clear: both; }

        @media print {
            body { background: none; }
            .container { width: 100%; padding: 2cm; margin: 0; }
            .no-print { display: none; }
            @page { margin: 1cm; size: A4 portrait; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <!-- Header Kop Surat -->
        <div class="header">
            <div class="title-area">
                <h1>UNIVERSITAS CONTOH INDONESIA</h1>
                <h2>FAKULTAS {{ strtoupper($kp->mahasiswa->prodi->fakultas->nama ?? 'AKADEMIK') }}</h2>
                <p>Program Studi {{ $kp->mahasiswa->prodi->nama ?? '-' }}</p>
                <p>Jl. Pendidikan No. 123, Kota, Indonesia | Email: info@universitas.ac.id</p>
            </div>
        </div>

        <!-- Nomor & Sifat Surat -->
        @php
            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $tgl = now();
        @endphp
        <div class="surat-number">
            <p>Nomor&nbsp;&nbsp;: ..../UN.X/KP/{{ $tgl->year }}</p>
            <p>Lampiran: -</p>
            <p>Perihal&nbsp;&nbsp;: <strong>Permohonan Kerja Praktek</strong></p>
        </div>

        <!-- Penerima Surat -->
        <div class="penerima">
            <p>Kepada Yth.</p>
            <p>Pimpinan / HRD</p>
            <p><strong>{{ $kp->nama_perusahaan }}</strong></p>
            <p>{{ $kp->alamat_perusahaan }}</p>
        </div>

        <!-- Salam Pembuka -->
        <div class="isi">
            <p class="indent">Dengan hormat,</p>
            <p class="indent">
                Sehubungan dengan kurikulum Program Studi <strong>{{ $kp->mahasiswa->prodi->nama ?? '-' }}</strong>, Universitas Contoh Indonesia, yang mewajibkan mahasiswa untuk melaksanakan Kerja Praktek (KP), kami mengajukan permohonan kepada Bapak/Ibu untuk dapat menerima mahasiswa berikut:
            </p>

            <table class="data-table">
                <tr>
                    <td class="label">Nama Mahasiswa</td>
                    <td class="separator">:</td>
                    <td><strong>{{ $kp->mahasiswa->user->name }}</strong></td>
                </tr>
                <tr>
                    <td class="label">NIM</td>
                    <td class="separator">:</td>
                    <td>{{ $kp->mahasiswa->nim }}</td>
                </tr>
                <tr>
                    <td class="label">Program Studi</td>
                    <td class="separator">:</td>
                    <td>{{ $kp->mahasiswa->prodi->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">No. HP Mahasiswa</td>
                    <td class="separator">:</td>
                    <td>{{ $kp->no_hp_mahasiswa ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Bidang yang Diminati</td>
                    <td class="separator">:</td>
                    <td>{{ $kp->bidang_usaha ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Periode KP</td>
                    <td class="separator">:</td>
                    <td>
                        {{ $kp->tanggal_mulai ? $kp->tanggal_mulai->format('d') . ' ' . $namaBulan[$kp->tanggal_mulai->month] . ' ' . $kp->tanggal_mulai->year : '-' }}
                        s/d
                        {{ $kp->tanggal_selesai ? $kp->tanggal_selesai->format('d') . ' ' . $namaBulan[$kp->tanggal_selesai->month] . ' ' . $kp->tanggal_selesai->year : '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Pembimbing Kampus</td>
                    <td class="separator">:</td>
                    <td>{{ $kp->pembimbing?->user?->name ?? 'Akan ditentukan' }}</td>
                </tr>
            </table>

            <p class="indent">
                Atas kesediaan Bapak/Ibu menerima mahasiswa kami untuk melaksanakan Kerja Praktek, kami ucapkan terima kasih. Besar harapan kami agar mahasiswa tersebut dapat memperoleh pengalaman dan pengetahuan yang berharga selama pelaksanaan Kerja Praktek.
            </p>
        </div>

        <!-- Tanda Tangan -->
        @php $tglCetak = now(); @endphp
        <div class="signature-area">
            <p>{{ $kp->mahasiswa->prodi->fakultas->nama ?? 'Kampus' }}, {{ $tglCetak->day }} {{ $namaBulan[$tglCetak->month] }} {{ $tglCetak->year }}</p>
            <p>Ketua Program Studi</p>
            <p>{{ $kp->mahasiswa->prodi->nama ?? '-' }},</p>
            <div class="signature-space"></div>
            <p><strong>{{ $kp->mahasiswa->prodi->nama_ketua_prodi ?? '........................................' }}</strong></p>
            <p>NIDN. {{ $kp->mahasiswa->prodi->nidn_ketua_prodi ?? '....................' }}</p>
        </div>
        <div class="clear"></div>

        <div style="margin-top: 10px; font-size: 8pt; text-align: right; color: #555;">
            Dicetak melalui Sistem Informasi Akademik pada: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px; background: white; padding: 10px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Cetak Sekarang</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: #fff; border: none; border-radius: 6px; cursor: pointer; margin-left: 8px;">Tutup</button>
    </div>
</body>
</html>
