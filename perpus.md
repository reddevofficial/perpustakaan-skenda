Buatkan **aplikasi Sistem Informasi Perpustakaan Sekolah berbasis web** menggunakan **Laravel versi terbaru yang stabil** dan **Filament versi terbaru yang kompatibel**.

Aplikasi harus dirancang sebagai sistem yang benar-benar siap digunakan untuk operasional perpustakaan sekolah, bukan sekadar CRUD sederhana.

Gunakan struktur kode Laravel yang bersih, modular, mudah dikembangkan, dan mengikuti best practice Laravel serta Filament.

# 1. Tujuan Sistem

Sistem digunakan untuk mengelola:

- Data buku.
- Data siswa/anggota.
- Kategori buku.
- Peminjaman buku.
- Pengembalian buku.
- Perpanjangan peminjaman.
- Reservasi buku.
- Barcode buku.
- Stok buku.
- Denda keterlambatan.
- Notifikasi/peringatan peminjaman.
- Riwayat transaksi perpustakaan.
- Laporan perpustakaan.
- Akun pengguna.
- Role dan permission.
- Pengaturan sistem.

Sistem memiliki tiga role utama:

1. **Siswa**
2. **Petugas**
3. **Super Admin**

Gunakan authorization yang benar menggunakan **Policy / Gate / Permission**, jangan hanya menyembunyikan menu pada tampilan.

---

# 2. Role dan Hak Akses

## Siswa

Siswa hanya dapat mengakses fitur yang berhubungan dengan dirinya sendiri.

Siswa dapat:

- Login.
- Melihat dashboard siswa.
- Melihat katalog buku.
- Mencari buku.
- Memfilter buku berdasarkan kategori.
- Melihat detail buku.
- Melihat ketersediaan/stok buku.
- Meminjam buku dengan scan barcode.
- Mengajukan peminjaman.
- Melihat status pengajuan peminjaman.
- Melihat daftar buku yang sedang dipinjam.
- Melihat tanggal jatuh tempo.
- Melihat jumlah denda.
- Mengajukan perpanjangan peminjaman.
- Membuat reservasi buku.
- Membatalkan reservasi selama belum diproses.
- Melihat antrean reservasi apabila diperlukan.
- Melihat riwayat peminjaman.
- Melihat riwayat reservasi.
- Melihat notifikasi/peringatan keterlambatan.

Siswa **tidak boleh**:

- Mengedit data buku.
- Mengubah stok.
- Mengubah data siswa lain.
- Menyetujui peminjaman.
- Memproses pengembalian.
- Mengubah nominal denda.
- Mengakses laporan administratif.
- Mengelola akun pengguna.

---

# 3. Role Petugas

Petugas bertugas mengelola aktivitas harian perpustakaan.

Petugas dapat:

- Melihat dashboard petugas.
- Mengelola data buku.
- Mengelola kategori buku.
- Mengelola data siswa/anggota.
- Mengelola peminjaman.
- Menyetujui peminjaman.
- Menolak peminjaman.
- Menandai buku sebagai sudah dipinjam/diserahkan.
- Memproses pengembalian.
- Memperpanjang peminjaman.
- Menyetujui atau menolak permintaan perpanjangan.
- Melihat keterlambatan.
- Mengelola denda.
- Melihat reservasi.
- Memproses reservasi.
- Membatalkan reservasi jika diperlukan.
- Scan barcode buku untuk pencarian cepat.
- Scan barcode ketika peminjaman.
- Scan barcode ketika pengembalian.
- Melihat riwayat peminjaman siswa.
- Melihat stok buku.

Petugas tidak boleh mengubah konfigurasi sistem penting atau mengelola akun Super Admin kecuali diberikan permission khusus.

---

# 4. Role Super Admin

Super Admin memiliki akses penuh.

Super Admin dapat:

- Mengakses seluruh fitur Petugas.
- Mengelola seluruh akun pengguna.
- Membuat akun Petugas.
- Membuat akun Siswa.
- Mengubah role.
- Menonaktifkan akun.
- Mengelola permission.
- Mengakses semua laporan.
- Mengelola pengaturan sistem.
- Mengatur durasi peminjaman.
- Mengatur maksimal jumlah buku yang dapat dipinjam.
- Mengatur nominal denda.
- Mengatur aturan perpanjangan.
- Mengatur batas maksimal perpanjangan.
- Mengatur lama reservasi.
- Mengatur pengaturan notifikasi.
- Melihat aktivitas/audit sistem.

---

# 5. Data Buku

Buat CRUD data buku.

Minimal field buku:

- `id`
- `isbn`
- `barcode`
- `title`
- `author`
- `publisher`
- `publication_year`
- `category_id`
- `stock`
- `available_stock`
- `shelf_location`
- `cover`
- `description`
- `created_at`
- `updated_at`

Gunakan nama field database berbahasa Inggris tetapi label interface boleh berbahasa Indonesia.

Contoh informasi pada form:

- ID Buku
- ISBN
- Barcode
- Judul
- Penulis
- Penerbit
- Tahun Terbit
- Kategori
- Jumlah Stok
- Stok Tersedia
- Lokasi Rak
- Cover
- Deskripsi

## Ketentuan Buku

- ISBN boleh nullable jika buku tidak mempunyai ISBN.
- Barcode harus unik.
- Jangan gunakan ISBN sebagai barcode internal jika desainnya lebih aman menggunakan kode terpisah.
- Jika barcode kosong ketika buku dibuat, sistem dapat menghasilkan barcode unik secara otomatis.
- Stock tidak boleh negatif.
- Available stock tidak boleh lebih besar daripada total stock.
- Penghapusan buku yang mempunyai riwayat transaksi tidak boleh merusak riwayat.
- Gunakan soft delete apabila sesuai.

Cover disimpan melalui Filament FileUpload dengan validasi gambar.

---

# 6. Barcode Buku

Setiap buku harus mempunyai barcode unik yang dapat digunakan saat proses peminjaman dan pengembalian.

Contoh format:

`BK-000001`

atau format lain yang konsisten.

Sediakan fitur:

- Generate barcode otomatis.
- Menampilkan barcode pada halaman detail buku.
- Download/print barcode.
- Scan barcode menggunakan kamera perangkat jika memungkinkan.
- Input manual barcode dari USB barcode scanner.
- Pencarian berdasarkan barcode.

Pastikan scanner USB yang bekerja seperti keyboard tetap dapat digunakan hanya dengan fokus ke input barcode.

---

# 7. Data Kategori

Buat CRUD kategori buku.

Field minimal:

- `id`
- `name`
- `slug`
- `description`
- `created_at`
- `updated_at`

Fitur:

- Tambah kategori.
- Edit kategori.
- Hapus kategori.
- Melihat jumlah buku pada kategori.
- Filter katalog berdasarkan kategori.

Jangan mengizinkan penghapusan kategori jika menyebabkan data buku menjadi invalid. Gunakan strategi `restrict`, nullable category, atau pendekatan aman lainnya dan jelaskan keputusan implementasinya.

---

# 8. Data Siswa / Anggota

Buat CRUD anggota perpustakaan.

Minimal field:

- `id`
- `user_id`
- `student_number`
- `name`
- `class`
- `major`
- `phone`
- `email`
- `address`
- `status`
- `joined_at`
- `created_at`
- `updated_at`

Status anggota misalnya:

- Aktif
- Nonaktif
- Diblokir

Student Number / NIS harus unik.

Relasikan data siswa dengan akun login.

Tampilkan pada detail siswa:

- Identitas.
- Buku yang sedang dipinjam.
- Jumlah pinjaman aktif.
- Buku terlambat.
- Total denda belum dibayar.
- Reservasi aktif.
- Riwayat peminjaman.
- Riwayat denda.

Siswa yang `nonaktif` atau `diblokir` tidak boleh membuat peminjaman baru.

---

# 9. Sistem Peminjaman

Buat workflow peminjaman yang jelas.

Gunakan tabel utama misalnya:

`loans`

dan detail:

`loan_items`

agar satu transaksi peminjaman dapat berisi lebih dari satu buku.

## loans

Minimal:

- `id`
- `loan_number`
- `student_id`
- `status`
- `requested_at`
- `approved_at`
- `borrowed_at`
- `due_at`
- `returned_at`
- `approved_by`
- `rejected_by`
- `rejection_reason`
- `notes`
- `created_at`
- `updated_at`

## loan_items

Minimal:

- `id`
- `loan_id`
- `book_id`
- `quantity`
- `returned_quantity`
- `due_at`
- `returned_at`
- `status`

Sesuaikan struktur apabila setiap copy buku nantinya dibuat sebagai unit individual.

---

# 10. Status Peminjaman

Jangan menyimpan status sebagai string acak di berbagai tempat.

Buat PHP Enum seperti:

- `PENDING`
- `APPROVED`
- `BORROWED`
- `PARTIALLY_RETURNED`
- `RETURNED`
- `REJECTED`
- `OVERDUE`
- `CANCELLED`

Berikan method `label()` untuk label Bahasa Indonesia.

Contoh:

- Pending → Menunggu Persetujuan
- Approved → Disetujui
- Borrowed → Sedang Dipinjam
- Returned → Dikembalikan
- Rejected → Ditolak
- Overdue → Terlambat
- Cancelled → Dibatalkan

Pastikan transisi status tervalidasi.

Contohnya jangan mengizinkan transaksi:

`RETURNED -> BORROWED`

secara sembarangan.

---

# 11. Alur Peminjaman oleh Siswa

Workflow:

1. Siswa login.
2. Siswa membuka menu peminjaman.
3. Siswa scan barcode buku.
4. Sistem mencari buku berdasarkan barcode.
5. Sistem menampilkan:
   - Cover.
   - Judul.
   - Penulis.
   - Kategori.
   - Stok tersedia.
   - Lokasi rak.
6. Sistem memvalidasi apakah buku tersedia.
7. Sistem memvalidasi apakah siswa diperbolehkan meminjam.
8. Sistem memvalidasi jumlah pinjaman aktif siswa.
9. Sistem memeriksa apakah siswa mempunyai pinjaman terlambat.
10. Sistem memeriksa aturan denda/blokir.
11. Siswa mengajukan peminjaman.
12. Status menjadi `PENDING`.
13. Petugas menerima notifikasi/pengajuan.
14. Petugas dapat menyetujui atau menolak.
15. Jika disetujui, sistem menentukan tanggal jatuh tempo.
16. Setelah buku benar-benar diberikan, status menjadi `BORROWED`.

Jangan langsung mengurangi stok hanya karena siswa membuka halaman atau scan barcode.

Tentukan secara jelas pada event apa stok dianggap keluar.

Saran:

Stok tersedia dikurangi ketika peminjaman sudah dikonfirmasi menjadi `BORROWED`.

---

# 12. Persetujuan Petugas

Pada halaman detail peminjaman, petugas harus mempunyai action:

- Setujui.
- Tolak.
- Tandai sebagai dipinjam/diserahkan.
- Perpanjang.
- Proses pengembalian.
- Batalkan apabila memenuhi kondisi tertentu.

Ketika menolak:

Petugas wajib dapat mengisi alasan penolakan.

Simpan:

- Petugas yang melakukan tindakan.
- Timestamp tindakan.
- Alasan jika ada.

Gunakan database transaction agar perubahan status dan stok tetap konsisten.

---

# 13. Durasi Peminjaman

Durasi default jangan hardcode langsung di source code.

Simpan pada pengaturan sistem, misalnya:

`loan_duration_days = 7`

Super Admin dapat mengubahnya melalui menu Pengaturan.

Ketika buku dipinjam:

`due_at = borrowed_at + loan_duration_days`

Pastikan perubahan konfigurasi setelah transaksi terjadi tidak mengubah due date pinjaman lama.

---

# 14. Pengembalian Buku

Pengembalian dapat dilakukan petugas menggunakan scan barcode.

Workflow:

1. Petugas membuka menu Pengembalian.
2. Scan barcode.
3. Sistem mencari peminjaman aktif buku tersebut.
4. Tampilkan:
   - Siswa.
   - Buku.
   - Tanggal pinjam.
   - Jatuh tempo.
   - Lama keterlambatan.
   - Denda.
5. Petugas mengonfirmasi pengembalian.
6. Sistem mencatat waktu pengembalian.
7. Stok tersedia bertambah.
8. Status peminjaman diperbarui.
9. Sistem menghitung denda jika terlambat.

Gunakan database transaction.

Jangan sampai stok bertambah dua kali jika tombol pengembalian ditekan ulang.

Pastikan operasi bersifat aman terhadap double submit.

---

# 15. Keterlambatan

Sistem harus dapat mendeteksi pinjaman yang:

`due_at < sekarang`

dan belum dikembalikan.

Status atau indikator harus menunjukkan:

`OVERDUE / Terlambat`

Buat scheduled command/job Laravel yang dijalankan secara berkala untuk memproses keterlambatan dan notifikasi.

Gunakan Laravel Scheduler.

Jangan sepenuhnya bergantung pada user membuka halaman agar status keterlambatan terdeteksi.

---

# 16. Sistem Denda

Buat sistem denda otomatis.

Contoh konfigurasi:

- Denda keterlambatan per hari: Rp1.000.
- Grace period: 0 hari.
- Maksimal denda: configurable atau nullable.

Konfigurasi harus dapat diubah Super Admin.

Contoh perhitungan:

`jumlah_hari_terlambat × denda_per_hari`

Buat tabel misalnya:

`fines`

Field:

- `id`
- `loan_id`
- `student_id`
- `amount`
- `late_days`
- `status`
- `paid_at`
- `paid_by`
- `notes`
- `created_at`
- `updated_at`

Status:

- `UNPAID`
- `PAID`
- `WAIVED`

Jangan kehilangan riwayat denda jika nilai konfigurasi denda berubah setelah transaksi.

Simpan nilai denda aktual yang sudah dihitung.

---

# 17. Notifikasi Keterlambatan

Siswa harus mendapatkan peringatan ketika:

- H-3 jatuh tempo.
- H-1 jatuh tempo.
- Hari jatuh tempo.
- Sudah terlambat.

Minimal tampilkan notifikasi di aplikasi menggunakan Filament/Laravel Notification.

Struktur kode juga harus memungkinkan penambahan:

- Email.
- WhatsApp.
- Telegram.

di masa depan.

Notifikasi tidak boleh terkirim berulang kali tanpa kontrol.

Simpan log atau identifier notifikasi agar notifikasi H-1, misalnya, tidak dikirim berkali-kali pada hari yang sama.

---

# 18. Perpanjangan Peminjaman

Siswa dapat mengajukan perpanjangan.

Petugas juga dapat memperpanjang peminjaman secara manual apabila mempunyai izin.

Aturan default:

- Maksimal perpanjangan dapat dikonfigurasi.
- Buku yang sedang mempunyai antrean reservasi tidak dapat diperpanjang.
- Buku yang sudah terlalu terlambat dapat ditolak untuk diperpanjang.
- Siswa yang diblokir tidak dapat memperpanjang.

Buat tabel atau field untuk menyimpan jumlah perpanjangan.

Lebih baik buat riwayat:

`loan_extensions`

Field:

- `id`
- `loan_id`
- `requested_by`
- `old_due_at`
- `new_due_at`
- `status`
- `approved_by`
- `reason`
- `created_at`
- `updated_at`

Status:

- Pending
- Approved
- Rejected

Jangan hanya overwrite `due_at` tanpa menyimpan riwayat perubahan.

---

# 19. Reservasi Buku

Buat fitur reservasi.

Siswa dapat melakukan reservasi ketika:

- Buku sedang tidak tersedia.
- Atau berdasarkan aturan sistem yang ditentukan.

Gunakan tabel:

`reservations`

Minimal:

- `id`
- `reservation_number`
- `student_id`
- `book_id`
- `status`
- `reserved_at`
- `expires_at`
- `queue_position`
- `fulfilled_at`
- `cancelled_at`
- `created_at`
- `updated_at`

Status:

- `WAITING`
- `AVAILABLE`
- `FULFILLED`
- `EXPIRED`
- `CANCELLED`

Workflow:

1. Buku habis.
2. Siswa klik Reservasi.
3. Reservasi masuk antrean.
4. Ketika buku dikembalikan, sistem mencari reservasi aktif paling awal.
5. Siswa pertama mendapatkan prioritas.
6. Status menjadi `AVAILABLE`.
7. Siswa mendapatkan notifikasi.
8. Siswa mempunyai waktu tertentu untuk mengambil buku.
9. Jika tidak mengambil hingga `expires_at`, reservasi expired.
10. Sistem menawarkan buku kepada siswa berikutnya.

Pastikan seorang siswa tidak dapat membuat reservasi duplikat untuk buku yang sama jika masih mempunyai reservasi aktif.

---

# 20. Aturan Stok dan Reservasi

Perubahan stok harus konsisten.

Contoh:

Jika:

`stock = 5`

dan:

`4 buku sedang dipinjam`

maka:

`available_stock = 1`

Jangan hanya mengandalkan nilai manual yang rawan tidak sinkron.

Evaluasi apakah `available_stock` perlu disimpan di database atau dihitung dari transaksi aktif.

Jika disimpan demi performa, seluruh perubahan harus dilakukan melalui service/domain logic dan database transaction.

Jelaskan keputusan arsitektur yang digunakan.

---

# 21. Katalog Buku

Buat halaman katalog yang nyaman digunakan siswa.

Fitur:

- Search judul.
- Search ISBN.
- Search penulis.
- Search penerbit.
- Search barcode.
- Filter kategori.
- Filter tersedia/tidak tersedia.
- Pagination.
- Sorting.
- Cover buku.
- Badge stok.
- Badge kategori.

Detail buku menampilkan:

- Cover.
- Judul.
- ISBN.
- Penulis.
- Penerbit.
- Tahun terbit.
- Kategori.
- Deskripsi.
- Lokasi rak.
- Stok tersedia.
- Status tersedia/tidak tersedia.

Action:

- Pinjam.
- Reservasi.

Action hanya muncul jika memenuhi kondisi bisnis.

---

# 22. Dashboard Siswa

Dashboard siswa minimal menampilkan:

- Jumlah buku sedang dipinjam.
- Buku mendekati jatuh tempo.
- Buku terlambat.
- Total denda belum dibayar.
- Reservasi aktif.
- Riwayat terbaru.

Buat widget Filament yang sesuai.

---

# 23. Dashboard Petugas

Dashboard Petugas menampilkan:

- Total buku.
- Total anggota aktif.
- Peminjaman aktif.
- Pengajuan peminjaman pending.
- Buku terlambat.
- Pengembalian hari ini.
- Reservasi aktif.
- Total denda belum dibayar.
- Grafik peminjaman.

Tambahkan tabel:

**Peminjaman yang harus segera ditindaklanjuti**

misalnya:

- Pending.
- Jatuh tempo hari ini.
- Terlambat.

---

# 24. Dashboard Super Admin

Tambahkan statistik:

- Total buku.
- Total eksemplar.
- Total siswa.
- Total petugas.
- Peminjaman bulan ini.
- Pengembalian bulan ini.
- Buku terlambat.
- Denda.
- Buku paling sering dipinjam.
- Kategori paling populer.
- Siswa paling aktif.

Gunakan chart Filament jika tersedia.

---

# 25. Laporan Perpustakaan

Super Admin dapat mengakses laporan:

## Laporan peminjaman

Filter:

- Tanggal.
- Siswa.
- Kelas.
- Buku.
- Kategori.
- Status.

## Laporan pengembalian

Filter:

- Periode.
- Tepat waktu.
- Terlambat.

## Laporan keterlambatan

Tampilkan:

- Siswa.
- Buku.
- Jatuh tempo.
- Jumlah hari terlambat.
- Denda.

## Laporan buku

- Buku tersedia.
- Buku sedang dipinjam.
- Buku stok habis.
- Buku paling sering dipinjam.
- Buku jarang dipinjam.

## Laporan anggota

- Anggota aktif.
- Siswa paling aktif.
- Siswa dengan keterlambatan.

## Laporan denda

- Belum dibayar.
- Sudah dibayar.
- Total denda berdasarkan periode.

Tambahkan export:

- Excel.
- CSV.
- PDF apabila library yang digunakan kompatibel dan stabil.

Jangan memasang package secara sembarangan. Gunakan package yang masih aktif dipelihara dan kompatibel.

---

# 26. Pengaturan Sistem

Buat halaman Pengaturan untuk Super Admin.

Contoh setting:

- Nama perpustakaan.
- Nama sekolah.
- Logo.
- Alamat.
- Nomor telepon.
- Durasi peminjaman default.
- Maksimal jumlah buku per siswa.
- Maksimal perpanjangan.
- Durasi perpanjangan.
- Denda per hari.
- Grace period.
- Maksimal denda.
- Durasi reservasi setelah buku tersedia.
- Hari pengiriman reminder.
- Format nomor transaksi.

Jangan membaca setting dengan query database berulang kali pada setiap komponen.

Gunakan caching yang masuk akal.

---

# 27. Nomor Transaksi

Buat nomor transaksi yang human-readable dan unik.

Contoh:

Peminjaman:

`PJ-20260824-0001`

Reservasi:

`RSV-20260824-0001`

Pastikan generation aman terhadap race condition.

Jangan menggunakan pendekatan:

`count() + 1`

karena dapat menyebabkan nomor duplikat pada request bersamaan.

Gunakan database transaction/locking atau strategi sequence yang aman.

---

# 28. Login dan User

Gunakan autentikasi Laravel/Filament.

Tabel users minimal:

- `id`
- `name`
- `email`
- `username`
- `password`
- `role`
- `is_active`
- `last_login_at`
- timestamps

Gunakan Enum untuk role jika sesuai:

- `STUDENT`
- `STAFF`
- `SUPER_ADMIN`

Berikan label:

- Siswa
- Petugas
- Super Admin

Password harus menggunakan Laravel hashing.

Akun nonaktif tidak boleh login.

---

# 29. Authorization

Implementasikan authorization pada:

- Resource.
- Page.
- Action.
- Query.
- Policy.

Jangan hanya:

```php
if ($user->role === 'admin')
```

berulang kali di semua file.

Gunakan pola authorization yang terstruktur.

Petugas tidak boleh mengubah role dirinya sendiri menjadi Super Admin.

Siswa hanya boleh membaca transaksi miliknya sendiri.

---

# 30. Audit Log

Tambahkan audit trail untuk tindakan penting:

- Login.
- Tambah/edit/hapus buku.
- Perubahan stok.
- Approval peminjaman.
- Penolakan.
- Pengembalian.
- Perpanjangan.
- Pengubahan denda.
- Perubahan setting.
- Perubahan role pengguna.

Minimal simpan:

- user
- action
- model/type
- model_id
- old_values
- new_values
- ip address jika tersedia
- user agent jika tersedia
- timestamp

Jangan menyimpan password atau data sensitif ke audit log.

---

# 31. Database Integrity

Gunakan:

- Foreign key.
- Unique constraint.
- Index.
- Check constraint jika sesuai.
- Database transaction pada proses kritis.

Tambahkan index khusus untuk field yang sering dicari:

- barcode
- isbn
- title
- student_number
- loan_number
- reservation_number
- status
- due_at

Pertimbangkan database target sebelum menggunakan fitur DB-specific.

---

# 32. Race Condition

Sistem harus mempertimbangkan kasus dua siswa mencoba meminjam buku terakhir secara bersamaan.

Contoh:

`available_stock = 1`

Siswa A dan B mengajukan proses secara hampir bersamaan.

Jangan sampai keduanya berhasil mengambil stok terakhir.

Gunakan transaksi database dan row locking seperti:

`lockForUpdate()`

pada proses yang memang membutuhkan atomic operation.

---

# 33. Service Layer

Jangan menaruh seluruh business logic di dalam Filament Resource/Page.

Pisahkan logic penting ke service/action class, misalnya:

- `LoanService`
- `ReturnService`
- `FineService`
- `ReservationService`
- `BarcodeService`
- `BookStockService`
- `NotificationService`

Contoh:

`LoanService::borrow()`

bertanggung jawab atas:

- Validasi siswa.
- Validasi buku.
- Validasi stok.
- Validasi limit.
- Membuat loan.
- Mengubah stok.
- Membuat log.
- Mengirim event/notifikasi.

Filament Resource hanya menjadi layer interface.

---

# 34. Events dan Notifications

Gunakan event/listener jika masuk akal.

Contoh event:

- `LoanRequested`
- `LoanApproved`
- `BookBorrowed`
- `BookReturned`
- `LoanOverdue`
- `ReservationAvailable`

Listener dapat menangani:

- Notifikasi.
- Audit.
- Aktivitas lain.

Jangan membuat arsitektur terlalu kompleks tanpa alasan, tetapi pisahkan side effect dari core transaction jika bermanfaat.

---

# 35. Scheduled Tasks

Gunakan Laravel Scheduler untuk:

- Memeriksa jatuh tempo.
- Menandai keterlambatan.
- Mengirim reminder.
- Meng-expire reservasi.
- Memproses antrean reservasi.

Command harus aman jika dijalankan lebih dari sekali.

---

# 36. Validation

Gunakan validation Laravel yang lengkap.

Contoh:

- ISBN sesuai panjang/formats yang diperbolehkan.
- Barcode unik.
- Tahun terbit masuk akal.
- Stock integer >= 0.
- Nama wajib.
- Category harus valid.
- Student number unik.
- Nominal denda >= 0.

Buat error message dalam Bahasa Indonesia jika memungkinkan.

---

# 37. UI/UX Filament

Gunakan fitur Filament dengan baik:

- Resource.
- Relation Manager.
- Tables.
- Forms.
- Widgets.
- Actions.
- Notifications.
- Infolist.
- Filters.
- Bulk Action jika aman.

Gunakan badge warna untuk status.

Contoh:

- Hijau = tersedia/selesai.
- Kuning = pending.
- Merah = terlambat/ditolak.
- Biru = sedang dipinjam.

Gunakan confirmation dialog untuk action berbahaya.

---

# 38. Menu

Contoh menu Petugas/Admin:

## Dashboard

## Perpustakaan
- Buku
- Kategori
- Anggota

## Transaksi
- Peminjaman
- Pengembalian
- Reservasi
- Perpanjangan

## Keuangan
- Denda

## Laporan
- Peminjaman
- Pengembalian
- Keterlambatan
- Buku
- Anggota
- Denda

## Sistem
- Pengguna
- Role / Permission
- Pengaturan
- Audit Log

Menu Siswa cukup:

- Dashboard
- Katalog Buku
- Pinjam Buku
- Peminjaman Saya
- Reservasi Saya
- Riwayat
- Notifikasi

---

# 39. Relasi Database

Rancang relasi database dengan benar.

Minimal:

```text
users
  └── student

students
  ├── loans
  ├── reservations
  └── fines

categories
  └── books

books
  ├── loan_items
  └── reservations

loans
  ├── student
  ├── loan_items
  ├── fines
  └── loan_extensions

loan_items
  └── book

reservations
  ├── student
  └── book
```

Sesuaikan jika menemukan struktur yang lebih tepat.

---

# 40. Pertimbangkan Book Copy / Eksemplar

Sebelum implementasi final, analisis apakah perpustakaan perlu membedakan:

**Judul Buku**

dengan:

**Eksemplar fisik Buku**

Contoh:

Perpustakaan mempunyai:

`Laskar Pelangi`

sebanyak 5 eksemplar.

Idealnya setiap eksemplar dapat mempunyai barcode sendiri:

```text
BK-000001
BK-000002
BK-000003
BK-000004
BK-000005
```

Jika model ini digunakan, buat:

`books`

untuk metadata judul buku dan:

`book_copies`

untuk setiap buku fisik.

Contoh `book_copies`:

- `id`
- `book_id`
- `barcode`
- `inventory_code`
- `status`
- `condition`
- `shelf_location`
- timestamps

Status:

- AVAILABLE
- BORROWED
- RESERVED
- LOST
- DAMAGED
- MAINTENANCE

Untuk perpustakaan sekolah, **utamakan model book + book_copies** jika tidak menambah kompleksitas yang tidak diperlukan, karena barcode seharusnya mengidentifikasi eksemplar fisik yang benar-benar dipinjam.

Dengan desain tersebut, jangan hanya mencatat:

`book_id`

pada item pinjaman.

Catat:

`book_copy_id`

sehingga diketahui eksemplar fisik mana yang dibawa siswa.

---

# 41. Buku Hilang / Rusak

Tambahkan dukungan status eksemplar:

- Tersedia.
- Dipinjam.
- Direservasi.
- Rusak.
- Hilang.
- Maintenance.

Petugas dapat menandai buku rusak/hilang.

Buku rusak/hilang tidak boleh dianggap sebagai stok tersedia.

Simpan catatan perubahan kondisi bila diperlukan.

---

# 42. Riwayat Buku

Pada detail buku tampilkan:

- Jumlah eksemplar.
- Eksemplar tersedia.
- Eksemplar dipinjam.
- Eksemplar rusak.
- Eksemplar hilang.
- Riwayat peminjaman.
- Jumlah peminjaman.
- Reservasi aktif.

---

# 43. Import Data

Tambahkan fitur import:

## Buku

Dari Excel/CSV.

## Siswa

Dari Excel/CSV.

Sebelum import:

- Validasi file.
- Preview data.
- Tampilkan data invalid.
- Jangan melakukan partial import secara tidak jelas.

Berikan laporan:

- berhasil
- gagal
- dilewati

dan alasan kegagalan.

---

# 44. Export Data

Sediakan export:

- Buku.
- Anggota.
- Peminjaman.
- Pengembalian.
- Denda.

Minimal CSV/Excel.

---

# 45. Seeder

Buat seeder development untuk:

- Super Admin.
- Petugas.
- Siswa.
- Kategori.
- Buku.
- Book copies.
- Peminjaman.
- Reservasi.

Gunakan Factory jika sesuai.

Jangan masukkan password production secara hardcoded.

Untuk development, dokumentasikan credential demo secara jelas.

---

# 46. Testing

Buat automated test minimal untuk business logic kritis.

Test:

1. Siswa dapat membuat pengajuan.
2. Siswa tidak dapat meminjam stok kosong.
3. Siswa tidak dapat melebihi limit pinjaman.
4. Petugas dapat approve.
5. Stok berkurang hanya pada tahap yang ditentukan.
6. Pengembalian meningkatkan stok.
7. Double return tidak meningkatkan stok dua kali.
8. Denda dihitung dengan benar.
9. Reservasi masuk antrean.
10. Buku yang kembali diberikan ke reservasi pertama.
11. Reservasi expired dipindahkan ke antrean berikutnya.
12. Siswa tidak dapat melihat transaksi siswa lain.
13. Petugas tidak dapat mengakses fitur Super Admin tanpa permission.
14. Race condition stok tidak menyebabkan overselling/over-borrowing.

Gunakan PHPUnit/Pest sesuai standar project.

---

# 47. Bahasa

Gunakan:

- Nama class/code/database: Bahasa Inggris.
- Interface pengguna: Bahasa Indonesia.
- Pesan validasi: Bahasa Indonesia.
- Label enum: Bahasa Indonesia.

Contoh:

```php
enum LoanStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case BORROWED = 'borrowed';
    case RETURNED = 'returned';
    case REJECTED = 'rejected';
    case OVERDUE = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Persetujuan',
            self::APPROVED => 'Disetujui',
            self::BORROWED => 'Sedang Dipinjam',
            self::RETURNED => 'Dikembalikan',
            self::REJECTED => 'Ditolak',
            self::OVERDUE => 'Terlambat',
        };
    }
}
```

---

# 48. Coding Standard

Gunakan:

- Typed properties.
- Return type.
- PHP Enum.
- Eloquent relationship.
- Form Request/service validation jika sesuai.
- Laravel transaction.
- Laravel Scheduler.
- Laravel Notification.
- Policy.
- Events/listeners jika relevan.

Hindari:

- Raw SQL jika Eloquent/Query Builder lebih tepat.
- Business logic besar di Blade.
- Business logic besar di Filament Resource.
- Hardcoded role.
- Hardcoded denda.
- Hardcoded durasi.
- Perhitungan stock tanpa transaction.
- Query N+1.
- Duplikasi logic.

Gunakan eager loading secara tepat.

---

# 49. Dokumentasi

Buat `README.md` yang menjelaskan:

- Requirement.
- Installation.
- `.env`.
- Database setup.
- Migration.
- Seeder.
- Cara menjalankan development server.
- Queue.
- Scheduler.
- Storage link.
- Credential development.
- Role pengguna.
- Alur peminjaman.
- Alur pengembalian.
- Alur reservasi.

Berikan juga daftar command yang perlu dijalankan.

---

# 50. Tahapan Pengerjaan

Jangan langsung membuat seluruh file tanpa perencanaan.

Kerjakan dengan urutan:

## Tahap 1 — Analisis

Sebelum coding, berikan:

- Analisis kebutuhan.
- Daftar fitur.
- Business rules.
- Potensi edge case.
- Pilihan desain `book + stock` vs `book + book_copies`.

## Tahap 2 — Database Design

Berikan:

- Daftar tabel.
- Kolom.
- Foreign key.
- Index.
- Constraint.
- Relasi.
- ERD dalam Mermaid.

## Tahap 3 — Architecture

Tentukan:

- Model.
- Enum.
- Service.
- Policy.
- Event.
- Listener.
- Notification.
- Scheduled command.
- Filament Resource.
- Page.
- Widget.

## Tahap 4 — Implementasi Database

Buat:

- Migration.
- Model.
- Enum.
- Factory.
- Seeder.

## Tahap 5 — Authentication & Authorization

Buat:

- Login.
- Role.
- Permission.
- Policy.
- Pembatasan akses panel.

## Tahap 6 — Master Data

Implementasikan:

- Buku.
- Book Copies.
- Kategori.
- Siswa.
- User.

## Tahap 7 — Peminjaman

Implementasikan workflow peminjaman lengkap.

## Tahap 8 — Pengembalian & Denda

Implementasikan pengembalian, overdue, dan fine.

## Tahap 9 — Reservasi

Implementasikan antrean reservasi.

## Tahap 10 — Dashboard & Laporan

Implementasikan widget dan laporan.

## Tahap 11 — Scheduler & Notification

Implementasikan reminder dan background processing.

## Tahap 12 — Testing

Buat automated test.

## Tahap 13 — Review

Lakukan pemeriksaan:

- Security.
- Authorization.
- Database integrity.
- Race condition.
- N+1 query.
- Duplicate transaction.
- Business rule.
- UX.

---

# 51. Aturan Penting untuk AI Agent

Sebelum membuat perubahan:

1. Periksa struktur project yang sudah ada.
2. Periksa versi Laravel, Filament, PHP, dan package.
3. Jangan mengganti arsitektur project secara sembarangan.
4. Jangan menghapus file/function existing tanpa alasan.
5. Gunakan package tambahan hanya jika benar-benar diperlukan.
6. Pastikan package kompatibel dengan versi project.
7. Jangan menebak API package; cek dokumentasi/version jika tersedia.
8. Jika menemukan struktur existing yang bertentangan dengan rancangan ini, jelaskan sebelum melakukan perubahan besar.
9. Jangan membuat migration yang merusak data existing.
10. Pastikan rollback migration dapat dilakukan selama memungkinkan.

Jika project sudah mempunyai pola coding tertentu, ikuti pola tersebut selama tidak menimbulkan masalah teknis.

---

# 52. Edge Cases yang Wajib Ditangani

Pastikan implementasi mempertimbangkan:

- Dua siswa mencoba mengambil buku terakhir bersamaan.
- Peminjaman di-approve tetapi buku belum diambil.
- Buku dikembalikan dua kali karena double click.
- Buku hilang.
- Buku rusak.
- Siswa diblokir ketika masih mempunyai pinjaman.
- Akun siswa dihapus tetapi mempunyai history.
- Buku dihapus tetapi mempunyai history.
- Category ingin dihapus tetapi masih dipakai.
- Setting denda berubah ketika ada peminjaman aktif.
- Setting durasi berubah ketika ada peminjaman aktif.
- Reservasi expired.
- Siswa pertama dalam reservasi tidak mengambil buku.
- Siswa sudah mempunyai buku yang sama.
- Siswa reservasi buku yang sedang dia pinjam.
- Siswa memperpanjang buku yang sedang direservasi orang lain.
- Buku dikembalikan terlambat sebagian.
- Peminjaman mempunyai beberapa buku dengan due date berbeda jika model mendukung.
- Scheduler dijalankan dua kali.
- Notification job dijalankan dua kali.
- Request diproses ulang karena browser refresh.

Implementasi harus sebisa mungkin **idempotent** pada proses kritis.

---

# 53. Hasil Akhir

Target akhirnya adalah aplikasi perpustakaan sekolah yang:

- Aman.
- Mudah digunakan siswa dan petugas.
- Mempunyai barcode per eksemplar buku.
- Mempunyai alur peminjaman yang jelas.
- Mempunyai approval petugas.
- Mempunyai pengembalian.
- Mempunyai perpanjangan.
- Mempunyai reservasi dan antrean.
- Mempunyai reminder keterlambatan.
- Mempunyai denda otomatis.
- Mempunyai laporan.
- Mempunyai audit log.
- Mempunyai role dan permission yang benar.
- Mempunyai database integrity yang baik.
- Aman terhadap race condition pada stok.
- Mudah dikembangkan di kemudian hari.

**Jangan hanya menghasilkan demo CRUD. Perlakukan project ini sebagai sistem perpustakaan sekolah yang akan benar-benar digunakan.**

Mulai dengan **TAHAP 1: Analisis kebutuhan dan rancangan database terlebih dahulu**.

Jangan langsung membuat kode sebelum analisis dan struktur database selesai.

Pada tahap analisis, jika terdapat keputusan bisnis yang belum ditentukan, berikan rekomendasi terbaik dan jelaskan konsekuensinya, kemudian pilih default yang paling aman untuk sistem perpustakaan sekolah.