<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Permohonan KP_{{ $mahasiswa->nim }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.35; color: #000; margin: 0; padding: 0; }
        .container { width: 21cm; min-height: 29.7cm; padding: 1.5cm 2cm 2cm 2.5cm; margin: auto; background: #fff; box-sizing: border-box; }
        
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2.5px solid #990000; padding-bottom: 4px; margin-bottom: 18px; }
        .header-logo { width: 75px; margin-right: 15px; display: flex; align-items: center; justify-content: center; }
        .header-logo img { width: 100%; height: auto; }
        .header-text { flex: 1; text-align: left; }
        .header-text h3 { font-family: 'Arial', sans-serif; font-size: 11.5pt; font-weight: bold; margin: 0; color: #000; line-height: 1.1; }
        .header-text h1 { font-family: 'Arial', sans-serif; font-size: 19.5pt; font-weight: 900; margin: 0; color: #990000; line-height: 1.1; letter-spacing: 0.5px; }
        .header-text h2 { font-family: 'Arial', sans-serif; font-size: 17pt; font-weight: 900; margin: 0; color: #990000; line-height: 1.1; letter-spacing: 0.5px; }
        .header-text h4 { font-family: 'Arial', sans-serif; font-size: 8.5pt; font-weight: bold; margin: 2px 0 0 0; color: #990000; line-height: 1.1; letter-spacing: 0.8px; }
        .header-address { font-family: 'Arial', sans-serif; font-size: 7.5pt; text-align: right; line-height: 1.15; padding-left: 10px; font-weight: bold; }
        
        .meta-surat { width: 100%; border-collapse: collapse; margin-bottom: 18px; margin-top: 10px; }
        .meta-surat td { padding: 1px 0; vertical-align: top; font-size: 11pt; }
        
        .recipient-block { margin-bottom: 20px; line-height: 1.4; font-size: 11pt; }
        .isi-surat { text-align: justify; line-height: 1.45; margin-bottom: 12px; font-size: 11pt; }
        
        .tabel-mhs { width: 100%; margin-left: 20px; margin-top: 8px; margin-bottom: 15px; border-collapse: collapse; }
        .tabel-mhs td { padding: 4px 0; vertical-align: top; font-size: 11pt; }
        
        .signature-container { width: 100%; margin-top: 35px; }
        .signature-box { width: 280px; float: right; text-align: left; font-size: 11pt; line-height: 1.35; }
        .signature-box p { margin: 2px 0; }
        .clear { clear: both; }
        
        .tembusan { margin-top: 30px; font-size: 10pt; line-height: 1.35; }
        .tembusan table { margin-top: 2px; font-size: 10pt; }
        .tembusan td { padding: 1px 0; }
        
        @media print {
            body { background: none; }
            .container { width: 100%; padding: 0; margin: 0; }
            .no-print { display: none; }
            @page { margin: 1.5cm 1.5cm 1.5cm 2cm; size: A4 portrait; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-logo">
                <img src="{{ asset('images/logo-umpar.png') }}" alt="Logo UMPAR" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display:none; width:75px;height:75px;background:#ccc;border-radius:50%;"></div>
            </div>
            <div class="header-text">
                <h3>PROGRAM STUDI</h3>
                <h1>{{ strtoupper($mahasiswa->prodi->nama ?? 'TEKNIK INFORMATIKA') }}</h1>
                <h2>{{ str_starts_with(strtolower($mahasiswa->prodi->fakultas->nama ?? ''), 'fakultas') ? strtoupper($mahasiswa->prodi->fakultas->nama) : 'FAKULTAS ' . strtoupper($mahasiswa->prodi->fakultas->nama ?? 'TEKNIK') }}</h2>
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
                http://upar.ac.id/faktek
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
                <td style="width: 55px;">Nomor</td>
                <td style="width: 15px;">:</td>
                <td>{{ $kp->nomor_surat ?? ('127/II.3.AU/' . strtoupper(substr($mahasiswa->prodi->nama ?? 'TI', 0, 2)) . '//' . $tglSurat->year) }}</td>
            </tr>
            <tr>
                <td>Lamp</td>
                <td>:</td>
                <td>-</td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td><strong><u>Pengantar Permohonan Praktek Kerja</u></strong></td>
            </tr>
        </table>

        <!-- Tujuan -->
        <div class="recipient-block">
            Dengan Hormat,<br>
            Kepada Yth,<br>
            <div style="padding-left: 35px; font-weight: bold; text-transform: uppercase;">
                PIMPINAN {{ $kp->nama_perusahaan }}
            </div>
            di-<br>
            <div style="padding-left: 35px; font-weight: bold;">
                Tempat
            </div>
        </div>

        <!-- Isi Surat -->
        <div class="isi-surat">
            Dengan Hormat,
        </div>
        
        <div class="isi-surat">
            Sehubungan dengan kewajiban mahasiswa Program Studi {{ $mahasiswa->prodi->nama ?? 'Teknik Informatika' }} untuk melulusi Mata Kuliah Praktek Kerja yang pelaksanaannya dilakukan di luar ruang kuliah selama 1 (Satu) Bulan.
        </div>
        
        <div class="isi-surat">
            Sehubungan dengan hal tersebut, kami mohon bantuannya untuk memberikan izin kepada mahasiswa kami untuk melaksanakan Magang/Praktek Kerja pada perusahaan yang bapak/ibu pimpin.
            Pelaksanaan Magang/Praktek Kerja direncanakan akan dilaksanakan pada:
        </div>

        <table style="margin-left: 80px; margin-bottom: 15px; line-height: 1.45; font-size: 11pt;">
            <tr>
                <td style="width: 100px;">Tanggal</td>
                <td style="width: 15px;">:</td>
                <td>{{ $tglMulai->day }} {{ $namaBulan[$tglMulai->month] }} {{ $tglMulai->year }} s.d {{ $tglSelesai->day }} {{ $namaBulan[$tglSelesai->month] }} {{ $tglSelesai->year }}</td>
            </tr>
            <tr>
                <td>Prodi</td>
                <td>:</td>
                <td>{{ $mahasiswa->prodi->nama ?? 'Teknik Informatika' }}</td>
            </tr>
        </table>

        <div class="isi-surat">
            Adapun mahasiswa yang akan melaksanakan Praktek Kerja adalah:
        </div>

        <table class="tabel-mhs">
            @foreach($groupMembers as $index => $member)
            <tr>
                <td style="width: 30px; font-weight: bold;">{{ $index + 1 }}.</td>
                <td style="width: 280px; font-weight: bold; text-transform: uppercase;">{{ $member->mahasiswa->user->name }}</td>
                <td>NIM : <span style="font-weight: bold;">{{ $member->mahasiswa->nim }}</span></td>
            </tr>
            @endforeach
        </table>

        <div class="isi-surat">
            Demikian permohonan kami, atas perhatian dan kerja samanya, kami haturkan terima kasih.
        </div>

        <!-- Date & Signature -->
        <div class="signature-container">
            <div class="signature-box">
                <p>Parepare, {{ $tglSurat->day }} {{ $namaBulan[$tglSurat->month] }} {{ $tglSurat->year }}</p>
                <p>Plt. Ketua Program Studi</p>
                <p>{{ $mahasiswa->prodi->nama ?? 'Teknik Informatika' }}</p>
                <div style="height: 65px;"></div>
                <p><strong><u>{{ $ketuaProdi }}</u></strong></p>
                <p>NBM. {{ $nidnKetuaProdi }}</p>
            </div>
            <div class="clear"></div>
        </div>

        <!-- Tembusan -->
        <div class="tembusan">
            <strong>Tembusan kepada Yth:</strong>
            <table>
                <tr>
                    <td style="width: 25px; vertical-align: top;">1.)</td>
                    <td>Mahasiswa yang bersangkutan</td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">2.)</td>
                    <td>Arsip</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px; background: white; padding: 10px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); z-index: 9999;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Cetak Surat</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: #fff; border: none; border-radius: 6px; cursor: pointer; margin-left: 8px;">Tutup</button>
    </div>
</body>
</html>
