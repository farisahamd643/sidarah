<?php
/**
 * ============================================================================
 *  SiDarah - Konfigurasi & Koneksi Database
 * ============================================================================
 */
date_default_timezone_set('Asia/Jakarta');

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';          // Laragon / XAMPP bawaan: kosong
$DB_NAME = 'sidarah';

const STATUS_DONOR = ['menunggu', 'diterima', 'dibatalkan', 'selesai'];
const GOLONGAN     = ['A', 'B', 'AB', 'O'];
const RHESUS       = ['+', '-'];

/** Daftar puskesmas untuk pendaftaran donor darah */
const PUSKESMAS = [
    'Puskesmas A' => 'Jl. Raya A No. 11, Kota Kediri',
    'Puskesmas B' => 'Jl. Raya B No. 22, Kabupaten Kediri',
    'Puskesmas C' => 'Jl. Raya C No. 33, Kabupaten Kediri',
];

/** Pilihan jam kegiatan: kode => [jam mulai, jam selesai] */
const JAM_DONOR = [
    'pagi'  => ['08:00:00', '12:00:00'],
    'siang' => ['13:00:00', '15:00:00'],
];

/** Batas hari maksimal pendaftaran dari hari ini */
const BATAS_HARI_DAFTAR = 90;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Koneksi database gagal. Pastikan MySQL di Laragon/XAMPP menyala dan database "sidarah" sudah dibuat.');
}
