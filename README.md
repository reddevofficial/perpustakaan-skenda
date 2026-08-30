# Perpustakaan SMKN 2 Banjarmasin

Sistem Informasi Perpustakaan Sekolah berbasis Laravel 13, Filament 5, dan Livewire 4.

## Requirements

- PHP 8.3+
- MySQL 8.0+
- Node.js / Bun.js
- Composer

## Installation

```bash
# 1. Clone repository
git clone <repo-url> perpustakaan-skenda
cd perpustakaan-skenda

# 2. Install PHP dependencies
composer install

# 3. Buat file .env
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Konfigurasi .env (koneksi database, dll.)

# 6. Jalankan migrasi
php artisan migrate --force

# 7. Seed database
php artisan db:seed

# 8. Install & build frontend assets
bun install && bun run build

# 9. Buat symbolic link untuk storage
php artisan storage:link
```

## Credentials (Development)

| Role       | Email                  | Password  |
|------------|------------------------|-----------|
| Admin      | admin@skenda.com       | password  |
| Petugas    | petugas@skenda.com     | password  |
| Siswa      | siswa@skenda.com       | password  |

Login siswa juga bisa menggunakan NIPD: `12088`.

## Architecture

- **Filament 5** admin panel untuk petugas & guru di `/admin`
- **Livewire 4** portal siswa di `/portal`
- Dua UI terpisah berbagi database yang sama

## Database

Menggunakan database `AbsensiSMKN2DB` yang sudah ada dengan tabel:

| Tabel | Keterangan |
|-------|------------|
| `murid` | Data siswa |
| `guru` | Data guru |
| `rombel` | Rombongan belajar |
| `jurusan` | Jurusan |
| `tingkat` | Tingkat kelas |
| `users` | Pengguna sistem |
| `role` | Role dengan JSON permissions |
| `books` | Data buku |
| `book_copies` | Fisik buku (barcode per copy) |
| `categories` | Kategori buku |
| ` loans` | Peminjaman |
| `loan_items` | Item pinjaman |
| `loan_extensions` | Perpanjangan pinjaman |
| `reservations` | Reservasi buku |
| `fines` | Denda |
| `audit_logs` | Log audit |
| `settings` | Pengaturan sistem |

## Menu Structure

### Admin / Staff (`/admin`)

- **Dashboard** — Ringkasan statistik
- **Perpustakaan**
  - Buku
  - Kategori
  - Anggota
- **Transaksi**
  - Peminjaman
  - Pengembalian
  - Reservasi
  - Perpanjangan
- **Keuangan**
  - Denda
- **Laporan**
  - Peminjaman
  - Pengembalian
  - Keterlambatan
  - Buku
  - Anggota
  - Denda
- **Sistem**
  - Pengguna
  - Pengaturan
  - Audit Log

### Siswa (`/portal`)

- Katalog Buku (pencarian, filter, paginasi)
- Pinjaman Saya
- Reservasi Saya
- Notifikasi
- Profil

## Features

- Katalog buku dengan pencarian, filter kategori, filter ketersediaan
- Alur peminjaman: request → approve → borrow → return
- Perhitungan denda otomatis saat pengembalian terlambat
- Antrian reservasi dengan masa berlaku
- Perpanjangan pinjaman dengan alur persetujuan
- Sistem notifikasi (database channel)
- Scheduled tasks: deteksi keterlambatan, kadaluarsa reservasi, pengingat, perhitungan denda
- Pengaturan sistem (durasi pinjaman, denda, dll. bisa dikonfigurasi)
- Halaman laporan dengan filter
- Audit logging
- Autorisasi berbasis role (Super Admin, Petugas, Siswa)
- Pindai barcode untuk pencarian buku
- Nomor transaksi race-condition-safe

## Commands

```bash
# Tandai peminjaman yang terlambat
php artisan loans:mark-overdue

# Kadaluarsa reservasi yang melewati batas waktu
php artisan reservations:expire

# Kirim pengingat pengembalian
php artisan loans:send-reminders

# Hitung denda untuk peminjaman terlambat
php artisan fines:calculate-overdue

# Jalankan scheduler (development)
php artisan schedule:work
```

## Development

```bash
# Jalankan Vite dev server
bun run dev

# Jalankan server lokal
php artisan serve

# Jalankan test
php artisan test
```

## Key Design Decisions

1. **Book + Book Copies** — Setiap buku (`books`) memiliki banyak fisik (`book_copies`), masing-masing dengan barcode unik.
2. **Settings table** — Konfigurasi disimpan di tabel `settings` dan di-cache via `Cache::remember`.
3. **Database notifications only** — Saat ini hanya notifikasi database, terstruktur untuk ekstensi ke email/WhatsApp di masa depan.
4. **Fine amount snapshots** — Jumlah denda disimpan sebagai snapshot saat pencatatan, bukan dihitung ulang dari konfigurasi.
5. **Transaction numbers** — Nomor transaksi menggunakan DB locking untuk mencegah duplikasi.
