# Frontend SiDarah (Next.js App Router)

Aplikasi frontend modern untuk Sistem Informasi Donor Darah (**SiDarah**), dibangun menggunakan **Next.js (App Router, JavaScript, React)** dengan mempertahankan 100% gaya desain, palet warna merah khas, dan komponen asli SiDarah.

---

## Fitur Utama

- **100% Desain Asli SiDarah**: Menggunakan stylesheet murni dan ikon SVG khas SiDarah (`#c62828`, `#8e0000`, `#fdecea`).
- **Autentikasi Terintegrasi**: Login & Registrasi dengan JWT token, validasi form, dan proteksi rute (`ProtectedRoute`).
- **Area Pendonor**:
  - Dashboard statistik & perkiraan jadwal boleh donor kembali
  - Pendaftaran donor darah dengan pemilihan jadwal dan pengecekan usia minimal 17 tahun
  - Status pendaftaran & riwayat pendaftaran dengan fitur pembatalan
  - Jadwal aktif saya
  - Pengelolaan profil & ganti password
- **Panel Administrator**:
  - Dashboard rekapitulasi data & sebaran golongan darah
  - Kelola jadwal kegiatan donor darah (Tambah, Edit, Hapus)
  - Kelola data pendaftar & pembaruan status pendaftaran
  - Kelola data pendonor (Cari, Filter golongan darah, Tambah, Edit, Hapus, Detail riwayat)
  - Pengelolaan profil admin & ganti password

---

## Menjalankan Secara Lokal

1. Masuk ke folder frontend:
   ```bash
   cd frontend
   ```
2. Pastikan file `.env.local` sudah ada:
   ```env
   NEXT_PUBLIC_API_URL=http://localhost:3000
   ```
3. Pasang dependensi jika belum:
   ```bash
   npm install
   ```
4. Jalankan mode pengembangan:
   ```bash
   npm run dev
   ```
   Aplikasi akan berjalan di [http://localhost:3001](http://localhost:3001) (port 3001 agar tidak bentrok dengan backend port 3000).

5. Untuk build produksi lokal:
   ```bash
   npm run build
   npm run start
   ```

---

## Panduan Deployment ke Vercel

1. Buka dashboard [Vercel](https://vercel.com/) dan import repository ini.
2. Atur **Root Directory** ke folder `frontend`.
3. Di bagian **Environment Variables**, tambahkan:
   - `NEXT_PUBLIC_API_URL`: URL publik backend Express Anda (misalnya `https://api-sidarah.up.railway.app` atau URL backend di Render).
4. Klik **Deploy**.
