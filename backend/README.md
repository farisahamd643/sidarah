# SiDarah Backend (REST API)

API backend untuk Sistem Informasi Donor Darah SiDarah.

**Stack:** Node.js, Express.js, PostgreSQL (Supabase), JWT, bcrypt.

## Menjalankan

1. `npm install`
2. Salin `.env.example` menjadi `.env`, lalu isi nilainya.
3. Jalankan skema database `schema.postgres.sql` di Supabase (SQL Editor).
4. `npm run dev` (pengembangan) atau `npm start` (produksi).

## Environment variable

| Nama | Keterangan |
|------|------------|
| DATABASE_URL | Connection string PostgreSQL Supabase (Session pooler) |
| JWT_SECRET | Kunci rahasia penanda tangan token JWT |
| PORT | Port server (default 3000) |
| CORS_ORIGIN | Daftar origin frontend dipisah koma (opsional, default semua) |

## Autentikasi

Endpoint bertanda login memakai header `Authorization: Bearer <token>`. Token didapat dari `POST /api/auth/login`.

## Endpoint

| Method | Path | Akses | Fungsi |
|--------|------|-------|--------|
| GET | /health | Publik | Cek server dan koneksi database |
| POST | /api/auth/register | Publik | Daftar akun pendonor |
| POST | /api/auth/login | Publik | Login, mengembalikan token |
| GET | /api/jadwal | Publik | Daftar jadwal donor |
| GET | /api/jadwal/:id | Publik | Detail jadwal |
| POST | /api/jadwal | Admin | Tambah jadwal |
| PUT | /api/jadwal/:id | Admin | Ubah jadwal |
| DELETE | /api/jadwal/:id | Admin | Hapus jadwal |
| POST | /api/pendaftaran | Login | Daftar ke jadwal donor |
| GET | /api/pendaftaran/saya | Login | Riwayat pendaftaran sendiri |
| PATCH | /api/pendaftaran/:id/batal | Login | Batalkan pendaftaran sendiri |
| GET | /api/pendaftaran | Admin | Semua pendaftar (opsional ?id_jadwal=) |
| PATCH | /api/pendaftaran/:id/status | Admin | Ubah status pendaftaran |
| GET | /api/profil | Login | Lihat profil |
| PUT | /api/profil | Login | Ubah profil |
| GET | /api/pendonor | Admin | Daftar pendonor (?cari=, ?golongan_darah=) |
| GET | /api/pendonor/:id | Admin | Detail pendonor dan riwayat |
| GET | /api/dashboard | Login | Ringkasan statistik (admin atau pendonor) |

## Aturan pendaftaran donor

Usia minimal 17 tahun, jadwal belum lewat, tidak boleh daftar ganda, dan jumlah pendaftar tidak boleh melebihi kuota.
