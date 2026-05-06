# Roadmap Pengembangan Fitur SIAKAD (Future Updates)

Dokumen ini merangkum 5 fitur utama yang perlu dikembangkan untuk meningkatkan fungsionalitas, keamanan data, dan pengalaman pengguna dalam sistem SIAKAD.

---

## 1. Modul Persetujuan KRS (Admin/Dosen PA)
**Tujuan:** Memberikan kendali penuh kepada institusi untuk memverifikasi rencana studi mahasiswa sebelum menjadi aktif.

*   **Detail Fitur:**
    *   Halaman khusus bagi Admin/Dosen PA untuk melihat daftar KRS dengan status `pending` (Menunggu Verifikasi).
    *   Fungsi **Approve**: Mengubah status KRS menjadi `approved` (Sukses/Aktif).
    *   Fungsi **Reject**: Mengembalikan KRS ke status `rejected` dengan kolom catatan alasan penolakan (misal: "SKS melebihi batas" atau "Salah ambil matkul").
    *   Integrasi tombol **Cetak** hanya akan muncul di sisi mahasiswa jika status sudah `approved`.

## 2. Validasi Bentrok Jadwal & Kapasitas
**Tujuan:** Mencegah terjadinya kesalahan input data yang mengakibatkan jadwal ganda atau kelas yang terlalu penuh.

*   **Detail Fitur:**
    *   **Cek Bentrok Ruangan:** Sistem menolak pembuatan kelas jika Ruangan, Hari, dan Jam yang dipilih sudah digunakan oleh kelas lain.
    *   **Cek Bentrok Dosen:** Memastikan dosen tidak mengajar dua mata kuliah berbeda di jam yang bersamaan.
    *   **Validasi Kapasitas:** Mengunci tombol "Ambil Kelas" bagi mahasiswa jika kuota kelas (kapasitas ruangan) sudah terpenuhi.

## 3. Manajemen Kenaikan Semester Masal
**Tujuan:** Mempermudah Admin dalam mengelola perpindahan semester mahasiswa secara efisien setiap awal tahun ajaran.

*   **Detail Fitur:**
    *   Tombol "Naikkan Semester" di dashboard Admin.
    *   Proses otomatis menambah +1 pada field `semester_sekarang` untuk semua mahasiswa yang berstatus aktif.
    *   Fitur pengecualian (Skip) bagi mahasiswa yang cuti atau sudah semester akhir (lulus).

## 4. Info Kuota SKS & Validasi Akademik
**Tujuan:** Memberikan transparansi kepada mahasiswa mengenai beban studi yang diperbolehkan.

*   **Detail Fitur:**
    *   Menampilkan "Maksimal SKS" di dashboard KRS mahasiswa (berdasarkan IPK semester lalu).
    *   Progress bar "SKS Diambil vs Kuota SKS".
    *   Sistem memblokir penambahan mata kuliah baru jika total SKS yang dipilih sudah mencapai batas kuota.

## 5. Optimasi Manajemen Mata Kuliah Umum (MKU)
**Tujuan:** Mempermudah pengelolaan mata kuliah yang bersifat lintas prodi (seperti AIK atau Pancasila).

*   **Detail Fitur:**
    *   Dashboard khusus untuk memantau Mata Kuliah Umum (MKU) secara terpisah dari Mata Kuliah Prodi.
    *   Fitur "Salin Mata Kuliah": Memungkinkan Admin menyalin MKU yang sama ke berbagai kurikulum atau prodi berbeda dengan satu klik.
    *   Laporan jumlah peserta MKU dari gabungan berbagai program studi.

---

> [!TIP]
> Prioritaskan fitur **Nomor 1 (Persetujuan KRS)** terlebih dahulu, karena saat ini status KRS mahasiswa akan tetap tertahan di 'Pending' jika Admin tidak memiliki tombol untuk menyetujuinya.
