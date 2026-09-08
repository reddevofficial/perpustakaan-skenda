# Manual Pengguna Sistem Informasi Perpustakaan Skenda

## 1. Tentang Aplikasi

Sistem ini digunakan untuk mengelola katalog buku, anggota, eksemplar/barcode, peminjaman, pengembalian, reservasi, denda, inventaris, laporan, label buku, notifikasi, dan activity log.

Aplikasi memiliki tiga jenis pengguna:

- **Siswa**: mencari buku, mengajukan peminjaman, melihat riwayat, dan membuat reservasi.
- **Petugas**: mengelola operasional perpustakaan, transaksi, barcode, inventaris, dan laporan.
- **Super Admin**: memiliki seluruh akses termasuk activity log dan pengaturan administrasi.

## 2. Menjalankan Aplikasi

Pastikan project sudah memiliki dependency dan file `.env` yang benar. Sesuaikan koneksi database MySQL pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=perpustakaan_skenda
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan aplikasi menggunakan perintah Laravel yang tersedia di lingkungan project. Setelah server berjalan, buka:

```texthttp://127.0.0.1:8000
```

Alamat dapat berbeda jika server dijalankan pada port lain.

## 3. Akun Demo

Seeder menyediakan akun berikut:

| Peran | Email | Username | Password |
|---|---|---|---|
| Super Admin | `admin@perpustakaan.test` | `superadmin` | `password` |
| Petugas | `petugas@perpustakaan.test` | `petugas` | `password` |

Seeder juga membuat 10 akun siswa dengan password `password`. Email siswa dibuat oleh Faker sehingga alamatnya berbeda setiap kali data dibuat. Lihat tabel `users` untuk mengetahui email siswa yang dibuat.

> Untuk penggunaan nyata, segera ganti password akun demo dan jangan menggunakan password `password` di production.

## 4. Halaman Utama

| Halaman | URL | Keterangan |
|---|---|---|
| Katalog | `/katalog` | Dapat dibuka tanpa login |
| Detail buku | `/katalog/{id-buku}` | Detail dan ketersediaan buku |
| Panel Super Admin | `/admin` | Login Super Admin |
| Panel Petugas | `/staff` | Login Petugas |
| Panel Siswa | `/siswa` | Login Siswa |
| Peminjaman saya | `/peminjaman-saya` | Riwayat siswa yang sedang login |
| Scan barcode | `/staff/barcode` | Pencarian eksemplar oleh petugas |
| Inventaris | `/staff/inventaris` | Pemeriksaan eksemplar |
| Laporan | `/staff/laporan` | Laporan dan export CSV |

Pengguna yang mencoba membuka panel yang bukan haknya akan ditolak oleh `canAccessPanel()` dan Resource authorization.

## 5. Panduan Siswa

### 5.1 Mencari Buku

1. Buka `/katalog`.
2. Masukkan kata kunci pada kolom pencarian.
3. Pencarian dapat menggunakan judul, penulis, ISBN, ID buku, atau barcode.
4. Gunakan filter kategori, tahun terbit, dan ketersediaan.
5. Gunakan pilihan sorting untuk mengurutkan judul, penulis, tahun, atau stok.
6. Klik **Lihat detail** untuk melihat informasi lengkap buku.

### 5.2 Mengajukan Peminjaman

1. Login melalui panel siswa.
2. Buka detail buku yang memiliki status **Tersedia**.
3. Klik **Ajukan peminjaman**.
4. Sistem memilih satu eksemplar tersedia dan menahannya sebagai `reserved`.
5. Pengajuan masuk dengan status **PENDING**.
6. Tunggu petugas menyetujui pengajuan.
7. Setelah disetujui dan buku diserahkan, status menjadi **BORROWED**.

Aturan default:

- Maksimal 3 peminjaman aktif.
- Durasi peminjaman 7 hari.
- Siswa harus berstatus aktif.
- Buku tidak dapat dipinjam jika stok habis, rusak, hilang, atau tidak aktif.

Semua aturan tersebut disimpan di `system_settings` dan dapat diubah oleh Super Admin pada tahap pengaturan.

### 5.3 Melihat Peminjaman

Buka `/peminjaman-saya` untuk melihat:

- Kode transaksi.
- Judul buku.
- Status peminjaman.
- Tanggal pengajuan.
- Tanggal jatuh tempo.
- Jumlah denda.

### 5.4 Perpanjangan

Perpanjangan hanya dapat dilakukan jika:

- Peminjaman masih aktif.
- Belum melewati tanggal jatuh tempo.
- Belum mencapai batas maksimal perpanjangan.
- Belum ada permintaan perpanjangan yang sedang menunggu.

Permintaan perpanjangan dikirim ke petugas untuk disetujui atau ditolak.

### 5.5 Reservasi

Jika stok buku habis:

1. Buka detail buku.
2. Klik **Reservasi buku**.
3. Sistem membuat nomor antrean.
4. Siswa tidak dapat membuat reservasi aktif ganda untuk buku yang sama.
5. Saat buku dikembalikan, antrean paling lama diproses terlebih dahulu.
6. Jika reservasi tersedia, siswa menerima notifikasi.
7. Reservasi harus diambil sebelum batas waktu berakhir.

Reservasi dapat dibatalkan selama statusnya masih menunggu atau tersedia.

## 6. Panduan Petugas

### 6.1 Mengelola Kategori

1. Login di `/staff`.
2. Buka menu **Manajemen > Kategori**.
3. Tambah, ubah, cari, atau filter kategori.
4. Kategori yang masih digunakan oleh buku tidak boleh dihapus.

### 6.2 Mengelola Buku

1. Buka menu **Manajemen > Buku**.
2. Klik **Tambah buku**.
3. Isi judul, penulis, ISBN, penerbit, tahun, kategori, lokasi rak, cover, dan deskripsi.
4. Simpan buku.
5. `book_code` dibuat otomatis jika tidak tersedia.
6. Gunakan filter status dan ketersediaan untuk menemukan buku.

Stok buku sebaiknya dikelola melalui data **Eksemplar Buku**, bukan dengan mengubah stok transaksi secara manual.

### 6.3 Mengelola Eksemplar dan Barcode

1. Buka menu **Manajemen > Eksemplar Buku**.
2. Pilih buku induk.
3. Masukkan barcode unik.
4. Pilih kondisi dan status eksemplar.
5. Simpan.

Barcode dapat dicari melalui halaman `/staff/barcode`. Scanner USB yang bekerja seperti keyboard dapat digunakan dengan menempatkan fokus pada input barcode.

### 6.4 Memproses Peminjaman

Urutan operasional:

1. Periksa pengajuan berstatus **PENDING**.
2. Setujui atau tolak pengajuan.
3. Jika disetujui, verifikasi identitas siswa dan barcode buku.
4. Saat buku diserahkan, proses **Serahkan/Mark Borrowed**.
5. Sistem mengubah eksemplar menjadi `borrowed` dan mengisi tanggal jatuh tempo.

Jika pengajuan ditolak, eksemplar reserved dikembalikan menjadi tersedia.

### 6.5 Memproses Pengembalian

1. Cari transaksi berdasarkan kode atau barcode.
2. Pastikan status transaksi sedang dipinjam atau terlambat.
3. Proses pengembalian.
4. Sistem mengubah eksemplar menjadi tersedia.
5. Stok tersedia bertambah.
6. Denda keterlambatan dihitung dan disimpan.
7. Jika ada reservasi, antrean paling lama diaktifkan.

### 6.6 Memeriksa Barcode

1. Buka `/staff/barcode`.
2. Klik atau fokus pada input barcode.
3. Scan barcode dengan scanner USB atau masukkan barcode manual.
4. Klik **Cari**.
5. Sistem menampilkan judul, penulis, barcode, status, dan lokasi rak.

### 6.7 Inventaris dan Stock Opname

1. Buka `/staff/inventaris`.
2. Scan barcode eksemplar.
3. Pilih kondisi terbaru.
4. Pilih status yang sesuai.
5. Isi lokasi rak dan catatan.
6. Klik **Simpan pemeriksaan**.

Setiap perubahan disimpan sebagai `inventory_log` dan dapat dilihat melalui menu **Inventaris** di Filament.

Eksemplar yang sedang dipinjam tidak boleh diubah menjadi tersedia melalui pemeriksaan inventaris. Gunakan proses pengembalian.

### 6.8 Laporan

1. Buka `/staff/laporan`.
2. Pilih jenis laporan:
   - Peminjaman
   - Denda
   - Reservasi
   - Inventaris
   - Buku
3. Atur tanggal, kategori, kelas, atau status.
4. Klik **Terapkan filter**.
5. Klik **Export CSV** untuk membuka data di Excel atau spreadsheet.
6. Klik **Print / PDF** untuk mencetak atau menyimpan halaman sebagai PDF melalui browser.

### 6.9 Cetak Label Buku

1. Buka menu **Eksemplar Buku** di Filament.
2. Pilih beberapa eksemplar.
3. Klik bulk action **Cetak label**.
4. Halaman label akan terbuka.
5. Klik **Cetak label**.
6. Pilih printer atau **Save as PDF**.

Label berisi nama perpustakaan, judul, ISBN, lokasi rak, barcode, dan kode barcode.

## 7. Panduan Super Admin

Super Admin login melalui `/admin` menggunakan akun admin.

Super Admin dapat:

- Mengakses semua Resource.
- Mengelola buku, kategori, siswa, dan eksemplar.
- Melihat dashboard statistik.
- Melihat laporan.
- Melihat activity log.
- Mengelola data operasional bersama petugas.
- Mengatur role pada data user sesuai konfigurasi aplikasi.

Activity log dapat dibuka melalui menu **Pengaturan > Activity Log**. Activity log bersifat hanya-baca dan mencatat model, aksi, pengguna, IP, waktu, dan deskripsi.

## 8. Status Transaksi

### Peminjaman

```text
DRAFT -> PENDING -> APPROVED -> BORROWED -> OVERDUE -> RETURNED
```

Status tambahan:

- `REJECTED`: ditolak petugas.
- `CANCELLED`: dibatalkan.
- `LOST`: buku hilang.

### Eksemplar

- `AVAILABLE`: tersedia.
- `RESERVED`: ditahan untuk pengajuan atau reservasi.
- `BORROWED`: sedang dipinjam.
- `DAMAGED`: rusak.
- `LOST`: hilang.
- `INACTIVE`: tidak aktif.

### Reservasi

- `WAITING`: menunggu antrean.
- `AVAILABLE`: siap diambil.
- `PICKED_UP`: sudah diambil.
- `CANCELLED`: dibatalkan.
- `EXPIRED`: melewati batas waktu.

## 9. Notifikasi dan Scheduler

Notifikasi database dibuat untuk:

- Pengajuan peminjaman.
- Persetujuan peminjaman.
- Penolakan peminjaman.
- Keterlambatan.
- Persetujuan perpanjangan.
- Penolakan perpanjangan.
- Reservasi tersedia.

Scheduler memiliki dua proses utama:

- `library:check-overdue`: menandai peminjaman terlambat dan menghitung denda.
- `library:expire-reservations`: mengubah reservasi yang melewati batas waktu menjadi kadaluarsa.

Scheduler harus dijalankan oleh scheduler Laravel pada lingkungan server agar proses otomatis berjalan.

## 10. Troubleshooting

### Tidak dapat login

- Pastikan email dan password benar.
- Pastikan status user aktif.
- Pastikan akun memiliki role yang sesuai dengan panel.
- Super Admin menggunakan `/admin`, petugas `/staff`, siswa `/siswa`.

### Katalog kosong

- Pastikan migration sudah dijalankan.
- Pastikan seeder sudah dijalankan.
- Pastikan buku berstatus `active`.
- Periksa koneksi database pada `.env`.

### Barcode tidak ditemukan

- Pastikan barcode diketik sama persis.
- Pastikan eksemplar belum dihapus.
- Pastikan scanner USB fokus ke input barcode.

### Export CSV tidak muncul

- Pastikan pengguna adalah petugas atau super admin.
- Periksa filter tanggal karena tanggal akhir harus sama atau setelah tanggal awal.
- Coba buka ulang `/staff/laporan`.

### Scheduler tidak memperbarui keterlambatan

- Pastikan scheduler Laravel aktif di server.
- Jalankan command overdue sesuai prosedur operasional server.
- Periksa log aplikasi jika queue atau notifikasi gagal.

## 11. Catatan Operasional

- Jangan menghapus buku atau eksemplar yang memiliki riwayat transaksi tanpa prosedur administrasi.
- Gunakan status tidak aktif jika data tidak boleh lagi digunakan tetapi riwayat harus dipertahankan.
- Jangan membagikan password akun petugas atau Super Admin.
- Ganti password demo sebelum aplikasi digunakan di sekolah.
- Lakukan backup database secara berkala.
- Export CSV dapat digunakan di Microsoft Excel, LibreOffice Calc, atau Google Sheets.
- Fitur PDF pada versi ini menggunakan Print browser karena library PDF eksternal belum dipasang.
