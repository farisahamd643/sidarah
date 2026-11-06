CREATE DATABASE IF NOT EXISTS sidarah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sidarah;

CREATE TABLE users (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','pendonor') NOT NULL DEFAULT 'pendonor',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pendonor (
  id_pendonor INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NULL UNIQUE,
  nama_lengkap VARCHAR(100) NOT NULL,
  nik CHAR(16) NULL UNIQUE,
  golongan_darah ENUM('A','B','AB','O') NULL,
  rhesus ENUM('+','-') NULL,
  jenis_kelamin ENUM('L','P') NOT NULL,
  tanggal_lahir DATE NOT NULL,
  no_hp VARCHAR(20),
  alamat TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE jadwal_donor (
  id_jadwal INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  tanggal DATE NOT NULL,
  jam_mulai TIME NOT NULL,
  jam_selesai TIME NOT NULL,
  lokasi VARCHAR(200) NOT NULL,
  keterangan TEXT,
  kuota INT NOT NULL DEFAULT 50,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pendaftaran_donor (
  id_pendaftaran INT AUTO_INCREMENT PRIMARY KEY,
  id_pendonor INT NOT NULL,
  id_jadwal INT NOT NULL,
  status ENUM('menunggu','diterima','dibatalkan','selesai') NOT NULL DEFAULT 'menunggu',
  tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_daftar (id_pendonor, id_jadwal),
  FOREIGN KEY (id_pendonor) REFERENCES pendonor(id_pendonor) ON DELETE CASCADE,
  FOREIGN KEY (id_jadwal) REFERENCES jadwal_donor(id_jadwal) ON DELETE CASCADE
) ENGINE=InnoDB;