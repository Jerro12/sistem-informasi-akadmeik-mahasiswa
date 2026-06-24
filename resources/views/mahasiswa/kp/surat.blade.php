<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Permohonan KP_{{ $mahasiswa->nim }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; color: #000; margin: 0; padding: 0; }
        .container { width: 21cm; min-height: 29.7cm; padding: 2cm 2cm; margin: auto; background: #fff; box-sizing: border-box; }
        
        .header { display: flex; align-items: stretch; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 20px; }
        .header-logo { width: 80px; margin-right: 20px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .header-logo img { width: 100%; height: auto; object-fit: contain; }
        .header-text { flex: 1; }
        .header-text h3 { font-size: 14pt; margin: 0; font-weight: normal; color: #900; }
        .header-text h1 { font-size: 16pt; margin: 0; text-transform: uppercase; font-weight: normal; }
        .header-text h2 { font-size: 14pt; margin: 0; text-transform: uppercase; font-weight: normal; }
        .header-text h4 { font-size: 10pt; margin: 5px 0 0 0; font-weight: bold; border-top: 1px solid #000; padding-top: 2px; }
        
        .header-address { width: 250px; font-size: 8pt; text-align: right; line-height: 1.2; padding-top: 5px; }
        
        .meta-surat { width: 100%; margin-bottom: 20px; }
        .meta-surat td { padding: 1px 0; vertical-align: top; }
        .meta-surat .label { width: 50px; }
        .meta-surat .separator { width: 15px; text-align: center; }
        
        .tujuan { margin-bottom: 25px; line-height: 1.5; }
        .isi-surat { text-align: justify; line-height: 1.5; margin-bottom: 15px; text-indent: 0; }
        
        .tabel-mhs { width: 85%; margin: 20px auto; border-collapse: collapse; font-weight: bold; }
        .tabel-mhs td { padding: 5px; }
        .tabel-mhs .no { width: 30px; text-align: right; padding-right: 15px; }
        
        .signature-container { width: 100%; margin-top: 40px; }
        .signature-box { width: 40%; float: right; text-align: left; line-height: 1.3; }
        .signature-box p { margin: 2px 0; }
        .signature-space { height: 70px; }
        .clear { clear: both; }
        
        .tembusan { margin-top: 40px; font-size: 10pt; }
        .tembusan p { margin: 2px 0; }
        .tembusan ol { margin-top: 2px; padding-left: 20px; margin-bottom: 0; }
        
        @media print {
            body { background: none; }
            .container { width: 100%; padding: 0; margin: 0; }
            .no-print { display: none; }
            @page { margin: 2cm; size: A4 portrait; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-logo">
                <img src="{{ asset('images/logo-umpar.png') }}" alt="Logo UMPAR" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display:none; width:80px;height:80px;background:#ccc;border-radius:50%;"></div>
            </div>
            <div class="header-text">
                <h3>PROGRAM STUDI</h3>
                <h1>{{ strtoupper($mahasiswa->prodi->nama ?? 'TEKNIK INFORMATIKA') }}</h1>
                <h2>FAKULTAS {{ strtoupper($mahasiswa->prodi->fakultas->nama ?? 'TEKNIK') }}</h2>
                <h4>UNIVERSITAS MUHAMMADIYAH PAREPARE</h4>
            </div>
            <div class="header-address">
                Kampus II: Gedung F Lt. 1 Jl. Jend. A. Yani<br>
                KM. 6 Kelurahan Bukit Harapan<br>
                Kecamatan Soreang Kota Parepare<br>
                Kode Pos 91131 Provinsi Sulawesi Selatan<br>
                Telp: (0421) 22757 Fax. (0421) 25524<br>
                Email: fakultasteknikumpar@gmail.com<br>
                Instagram: teknikumpar.official<br>
                http://umpar.ac.id/faktek
            </div>
        </div>

        @php
            $tglSurat = now();
            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            
            $tglMulai = \Carbon\Carbon::parse($kp->tanggal_mulai);
            $tglSelesai = \Carbon\Carbon::parse($kp->tanggal_selesai);
        @endphp

        <!-- Nomor Surat -->
        <table class="meta-surat">
            <tr>
                <td class="label">Nomor</td>
                <td class="separator">:</td>
                <td>......../II.3.AU/{{ strtoupper(substr($mahasiswa->prodi->nama ?? 'TI', 0, 2)) }}/{{ $tglSurat->year }}</td>
            </tr>
            <tr>
                <td class="label">Lamp</td>
                <td class="separator">:</td>
                <td>-</td>
            </tr>
            <tr>
                <td class="label">Perihal</td>
                <td class="separator">:</td>
                <td><strong><u>Pengantar Permohonan Praktek Kerja</u></strong></td>
            </tr>
        </table>

        <!-- Tujuan -->
        <div class="tujuan">
            Dengan Hormat,<br>
            Kepada Yth,<br>
            <div style="padding-left: 20px; font-weight: bold; text-transform: uppercase;">
                PIMPINAN {{ $kp->nama_perusahaan }}
            </div>
            di-<br>
            <div style="padding-left: 20px;">
                Tempat
            </div>
        </div>

        <!-- Isi Surat -->
        <div class="isi-surat">
            Dengan Hormat,
            <br><br>
            Sehubungan dengan kewajiban mahasiswa Program Studi {{ $mahasiswa->prodi->nama ?? 'Teknik Informatika' }} untuk melulusi Mata Kuliah Praktek Kerja yang pelaksanaannya dilakukan di luar ruang kuliah selama 1 (Satu) Bulan.
            <br><br>
            Sehubungan dengan hal tersebut, kami mohon bantuannya untuk memberikan izin kepada mahasiswa kami untuk melaksanakan Magang/Praktek Kerja pada perusahaan yang bapak/ibu pimpin.
            <br>
            Pelaksanaan Magang/Praktek Kerja direncanakan akan dilaksanakan pada:
        </div>

        <table style="margin-left: 40px; margin-bottom: 20px; line-height: 1.5;">
            <tr>
                <td style="width: 80px;">Tanggal</td>
                <td>: {{ $tglMulai->day }} {{ $namaBulan[$tglMulai->month] }} {{ $tglMulai->year }} s.d {{ $tglSelesai->day }} {{ $namaBulan[$tglSelesai->month] }} {{ $tglSelesai->year }}</td>
            </tr>
            <tr>
                <td>Prodi</td>
                <td>: {{ $mahasiswa->prodi->nama ?? 'Teknik Informatika' }}</td>
            </tr>
        </table>

        <div class="isi-surat">
            Adapun mahasiswa yang akan melaksanakan Praktek Kerja adalah:
        </div>

        <table class="tabel-mhs">
            <tr>
                <td class="no">1.</td>
                <td>{{ strtoupper($mahasiswa->user->name) }}</td>
                <td>NIM : {{ $mahasiswa->nim }}</td>
            </tr>
        </table>

        <div class="isi-surat">
            Demikian permohonan kami, atas perhatian dan kerja samanya, kami haturkan terima kasih.
        </div>

        <!-- Date & Signature -->
        <div class="signature-container">
            <div class="signature-box">
                <p>Parepare, {{ $tglSurat->day }} {{ $namaBulan[$tglSurat->month] }} {{ $tglSurat->year }}</p>
                <p>Ketua Program Studi</p>
                <p>{{ $mahasiswa->prodi->nama ?? 'Teknik Informatika' }}</p>
                <div class="signature-space"></div>
                <p><span style="text-decoration: underline; font-weight: bold;">{{ $ketuaProdi }}</span></p>
                <p>NBM. {{ $nidnKetuaProdi }}</p>
            </div>
            <div class="clear"></div>
        </div>

        <!-- Tembusan -->
        <div class="tembusan">
            Tembusan kepada Yth:<br>
            <ol>
                <li>Mahasiswa yang bersangkutan</li>
                <li>Arsip</li>
            </ol>
        </div>
    </div>

    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px; background: white; padding: 10px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Cetak Surat</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: #fff; border: none; border-radius: 6px; cursor: pointer; margin-left: 8px;">Tutup</button>
    </div>
</body>
</html>
