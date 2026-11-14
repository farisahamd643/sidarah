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

## Endpoint tambahan

| Method | Path | Akses | Fungsi |
|--------|------|-------|--------|
| GET | /api/akun | Login | Data akun sendiri |
| PUT | /api/akun | Login | Ubah nama |
| PUT | /api/akun/password | Login | Ganti password (butuh password_lama dan password_baru) |
| POST | /api/pendonor | Admin | Tambah data pendonor |
| PUT | /api/pendonor/:id | Admin | Ubah data pendonor |
| DELETE | /api/pendonor/:id | Admin | Hapus pendonor beserta akunnya |
| GET | /api/dashboard/golongan | Admin | Rekap jumlah pendonor per golongan darah |

## Panduan serah terima

### Untuk yang mengurus hosting (DevOps)

- Folder backend: `backend`
- Build command: `npm install`, start command: `npm start`
- Tes untuk CI: `npm ci` lalu `npm test` (tidak butuh database)
- Environment variable (isi di dashboard hosting atau GitHub Secrets, jangan di repo): `DATABASE_URL` (Session pooler Supabase), `JWT_SECRET` (string acak panjang), `CORS_ORIGIN` (alamat frontend, boleh lebih dari satu dipisah koma), `PORT` (biasanya diatur otomatis oleh hosting).
- Cek hidup: buka `/health`, hasilnya harus `"db":"terhubung"`.
- Membuat akun admin: set `ADMIN_EMAIL` dan `ADMIN_PASSWORD` (opsional `ADMIN_NAMA`) sementara di environment, jalankan `npm run buat-admin`, lalu hapus variabel itu.
- Free tier hosting bisa tidur saat idle, jadi buka `/health` beberapa menit sebelum demo.

### Untuk yang mengurus frontend

- Base URL: alamat backend + `/api`
- Setelah `POST /api/auth/login`, simpan `token`, lalu kirim di setiap permintaan dengan header `Authorization: Bearer <token>`. Token berlaku 1 hari.
- Semua error berbentuk JSON `{ "pesan": "..." }` dengan status 400, 401, 403, 404, 409, atau 500.
- Format tanggal `YYYY-MM-DD`, format jam `HH:MM:SS`.
- Frontend PHP lama memakai sesi PHP dan query langsung ke database. Agar memakai backend ini, halaman harus memanggil endpoint di atas.
