CREATE TABLE users (
  id_user INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(10) NOT NULL DEFAULT 'pendonor' CHECK (role IN ('admin','pendonor')),
  created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE pendonor (
  id_pendonor INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  id_user INT NULL UNIQUE REFERENCES users(id_user) ON DELETE SET NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  nik CHAR(16) NULL UNIQUE,
  golongan_darah VARCHAR(2) NULL CHECK (golongan_darah IN ('A','B','AB','O')),
  rhesus CHAR(1) NULL CHECK (rhesus IN ('+','-')),
  jenis_kelamin CHAR(1) NOT NULL CHECK (jenis_kelamin IN ('L','P')),
  tanggal_lahir DATE NOT NULL,
  no_hp VARCHAR(20),
  alamat TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE jadwal_donor (
  id_jadwal INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  tanggal DATE NOT NULL,
  jam_mulai TIME NOT NULL,
  jam_selesai TIME NOT NULL,
  lokasi VARCHAR(200) NOT NULL,
  keterangan TEXT,
  kuota INT NOT NULL DEFAULT 50,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE pendaftaran_donor (
  id_pendaftaran INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  id_pendonor INT NOT NULL REFERENCES pendonor(id_pendonor) ON DELETE CASCADE,
  id_jadwal INT NOT NULL REFERENCES jadwal_donor(id_jadwal) ON DELETE CASCADE,
  status VARCHAR(12) NOT NULL DEFAULT 'menunggu'
    CHECK (status IN ('menunggu','diterima','dibatalkan','selesai')),
  tanggal_daftar TIMESTAMPTZ DEFAULT NOW(),
  UNIQUE (id_pendonor, id_jadwal)
);
