-- ============================================================================
-- UPDATE DATABASE SiDarah  (jalankan SEKALI pada database yang SUDAH ada)
-- Alasan perubahan:
--   1. nik            : kolom baru untuk NIK pendonor (diisi saat Register). UNIQUE agar
--                       satu NIK tidak terdaftar dua kali. NULL diizinkan agar data lama aman.
--   2. golongan_darah : dibuat boleh kosong (NULL), karena Register tidak lagi menanyakan
--   3. rhesus         : golongan darah & rhesus; pendonor mengisinya nanti lewat menu Profil.
-- Data yang sudah ada TIDAK berubah/terhapus.
-- Cara: phpMyAdmin -> pilih database sidarah -> tab SQL -> tempel -> Kirim/Go.
-- Instalasi baru (database belum ada) cukup menjalankan Sidarah.sql versi terbaru.
-- ============================================================================
USE sidarah;

ALTER TABLE pendonor
  ADD COLUMN nik CHAR(16) NULL UNIQUE AFTER nama_lengkap,
  MODIFY golongan_darah ENUM('A','B','AB','O') NULL,
  MODIFY rhesus ENUM('+','-') NULL DEFAULT NULL;

  