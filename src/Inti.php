<?php
/**
 * ============================================================================
 *  SiDarah - INTI (dipakai bersama oleh index.php dan admin.php)
 * ============================================================================
 *  File ini TIDAK dibuka langsung di browser. Ia dipanggil oleh:
 *    index.php : sisi PENDONOR  (Register, Login, Jadwal & Daftar, Riwayat, Profil)
 *    admin.php : sisi ADMIN     (Login admin, Pendonor, Jadwal, Pendaftaran, Golongan)
 *
 *  Letakkan ketiga file satu folder: C:\xampp\htdocs\sidarah\
 *
 *  PETA FITUR   kiri = DATABASE (database/sidarah.sql)  |  kanan = KODE (file : fungsi)
 *  ----------------------------------------------------------------------------
 *  No Fitur                 DATABASE                          KODE
 *  -- --------------------- --------------------------------- ----------------------------------
 *  1  Login                 users(email,password,role)        inti.php   : aksi_login, halaman_login,
 *                                                                          halaman_logout
 *                                                             admin.php  : halaman_setup (admin pertama)
 *  2  Register              users + pendonor (1 transaksi)    index.php  : aksi_register, halaman_register, halaman_syarat
 *  3  Tambah Pendonor       pendonor(semua kolom)             admin.php  : pendonor_tambah, halaman_pendonor_form
 *  4  Lihat Pendonor        pendonor, users(email)            admin.php  : halaman_pendonor, halaman_pendonor_detail
 *  5  Edit Pendonor         pendonor, users(nama)             admin.php  : pendonor_ubah, halaman_pendonor_form
 *  6  Hapus Pendonor        pendonor, users (cascade)         admin.php  : aksi_pendonor_hapus
 *  7  Data Jadwal Donor     jadwal_donor                      admin.php  : aksi_jadwal_simpan/_hapus, halaman_jadwal(_form)
 *  8  Pendaftaran Donor     pendaftaran_donor                 index.php  : aksi_daftar, aksi_batal, halaman_pendaftaran
 *  9  Riwayat Donor         pendaftaran_donor(status=selesai) index.php  : halaman_status (kartu Riwayat Donor)
 *  10 Pencarian & Filter    pendonor(nama,golongan,rhesus)    admin.php  : cari_pendonor, halaman_pendonor
 *  11 Status Pendaftaran    pendaftaran_donor(status)         admin.php  : aksi_status, halaman_pendaftaran
 *                                                             index.php  : halaman_status (pendonor melihat status)
 *  12 Jadwal Mendatang      jadwal_donor(tanggal>=hari ini)   inti.php   : jadwal_mendatang, tabel_jadwal
 *                                                             index.php  : halaman_home, halaman_dashboard
 *                                                             admin.php  : halaman_dashboard
 *  13 Profil Pengguna       users(nama,password), pendonor    inti.php   : aksi_profil, halaman_profil
 *  14 Informasi Gol. Darah  pendonor(golongan_darah,rhesus)   inti.php   : form_golongan, label_gol (diisi lewat Profil)
 *                                                             admin.php  : halaman_golongan, halaman_dashboard
 *
 *  DAFTAR ISI FILE INI
 *    BAGIAN A  Konfigurasi & koneksi database
 *    BAGIAN B  Fungsi bantu (umum, database, format, validasi, form)
 *    BAGIAN C  Tampilan umum (header, CSS, footer, JS, tabel jadwal)
 *    BAGIAN D  Fitur yang dipakai bersama (1, 12, 13)
 *    BAGIAN E  Router (jalankan)
 * ============================================================================
 */
if (!defined('PANEL') || !defined('FILE')) {
    http_response_code(403);
    exit('Buka index.php (pendonor) atau admin.php (admin), bukan file ini.');
}
session_start();

// ############################################################################
// BAGIAN A - KONFIGURASI & KONEKSI DATABASE
// ############################################################################
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';          // XAMPP bawaan: kosong. JANGAN upload password asli ke GitHub.
$DB_NAME = 'sidarah';

const STATUS_DONOR = ['menunggu', 'diterima', 'dibatalkan', 'selesai'];
const GOLONGAN     = ['A', 'B', 'AB', 'O'];
const RHESUS       = ['+', '-'];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Koneksi database gagal. Pastikan MySQL di XAMPP menyala dan database "sidarah" sudah dibuat.');
}

// ############################################################################
// BAGIAN B - FUNGSI BANTU
// ############################################################################

// ---- B1. Umum ---------------------------------------------------------------
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function go($page, $extra = '') { header('Location: ' . FILE . '?page=' . $page . $extra); exit; }
/** Nama file panel lainnya (admin <-> pendonor). */
function panel_lain() { return PANEL === 'admin' ? 'index.php' : 'admin.php'; }
function flash($type, $msg) { $_SESSION['flash'] = [$type, $msg]; }
function me() { return $_SESSION['user'] ?? null; }

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
function csrf() { return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">'; }

/** Wajib login. (Pemisahan peran admin/pendonor dijaga oleh jalankan() di Bagian E.) */
function need() {
    if (!me()) { flash('danger', 'Silakan login terlebih dahulu.'); go('login'); }
}
/** $hak: null = publik, 'login' = wajib login. */
function izin($hak) {
    if ($hak === 'login') need();
}

// ---- B2. Database (prepared statement) --------------------------------------
function q($sql, $types = '', $params = []) {
    global $db;
    $st = $db->prepare($sql);
    if ($types !== '') $st->bind_param($types, ...$params);
    $st->execute();
    return $st;
}
function rows($sql, $types = '', $p = []) { return q($sql, $types, $p)->get_result()->fetch_all(MYSQLI_ASSOC); }
function row($sql, $types = '', $p = []) { $r = rows($sql, $types, $p); return $r[0] ?? null; }
function hitung($sql, $types = '', $p = []) { return (int)(row($sql, $types, $p)['c'] ?? 0); }
function pendonor_saya() { return row('SELECT * FROM pendonor WHERE id_user=?', 'i', [me()['id']]); }

// ---- B3. Format tampilan ----------------------------------------------------
function tgl($d) {
    $b = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($d);
    return date('j', $t) . ' ' . $b[(int)date('n', $t)] . ' ' . date('Y', $t);
}
function jam($j) { return substr($j, 0, 5); }
/** Panjang teks (aman walau ekstensi mbstring tidak aktif). */
function panjang($s) { return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s); }
/** Teks status untuk tampilan. Di database tetap 'menunggu', tetapi ditampilkan sebagai "Terdaftar". */
function label_status($s) { return $s === 'menunggu' ? 'Terdaftar' : ucfirst($s); }
function badge($s) { return '<span class="badge b-' . e($s) . '">' . e(label_status($s)) . '</span>'; }
/** FITUR 14: label golongan darah, mis. "A+". Bila belum diisi tampil "-". */
function label_gol($p) {
    if (empty($p['golongan_darah'])) return '<span class="muted">-</span>';
    return '<span class="gol">' . e($p['golongan_darah'] . $p['rhesus']) . '</span>';
}

// ---- B4. Validasi -----------------------------------------------------------
function tgl_valid($d) { $x = DateTime::createFromFormat('Y-m-d', $d); return $x && $x->format('Y-m-d') === $d; }

/** Membaca & memeriksa data pendonor dari formulir. $mode: 'register' | 'profil' | 'admin'. Dipakai Fitur 2, 3, 5, 13. */
function baca_pendonor($mode = 'admin', $idp = 0) {
    $nik = preg_replace('/\s+/', '', trim($_POST['nik'] ?? ''));
    $d = [
        'nama'   => trim($_POST['nama_lengkap'] ?? ''),
        'nik'    => $nik,
        'gol'    => null,
        'rh'     => null,
        'jk'     => $_POST['jenis_kelamin'] ?? '',
        'lahir'  => $_POST['tanggal_lahir'] ?? '',
        'hp'     => trim($_POST['no_hp'] ?? ''),
        'alamat' => trim($_POST['alamat'] ?? ''),
    ];
    $err = [];
    if ($d['nama'] === '' || panjang($d['nama']) > 100) $err[] = 'Nama wajib diisi (maks. 100 karakter).';

    // NIK: wajib saat register & profil, boleh kosong bila diisi admin. $idp = id pendonor yang sedang diedit.
    if ($nik === '') {
        if ($mode !== 'admin') $err[] = 'NIK wajib diisi.';
        $d['nik'] = null;
    } elseif (!preg_match('/^[0-9]{16}$/', $nik)) {
        $err[] = 'NIK harus 16 digit angka.';
    } elseif (row('SELECT 1 FROM pendonor WHERE nik=? AND id_pendonor<>?', 'si', [$nik, (int)$idp])) {
        $err[] = 'NIK sudah terdaftar.';
    }

    // Golongan darah & rhesus: TIDAK diminta saat register; diisi lewat Profil (atau admin).
    // Boleh dikosongkan, tetapi keduanya harus terisi bersamaan.
    if ($mode !== 'register') {
        $g = $_POST['golongan_darah'] ?? '';
        $r = $_POST['rhesus'] ?? '';
        if ($g !== '' || $r !== '') {
            if (!in_array($g, GOLONGAN, true))     $err[] = 'Pilih golongan darah (atau kosongkan keduanya).';
            elseif (!in_array($r, RHESUS, true))   $err[] = 'Pilih rhesus (atau kosongkan keduanya).';
            else { $d['gol'] = $g; $d['rh'] = $r; }
        }
    }

    if (!in_array($d['jk'], ['L', 'P'], true)) $err[] = 'Jenis kelamin tidak valid.';
    if (!tgl_valid($d['lahir']) || $d['lahir'] > date('Y-m-d')) $err[] = 'Tanggal lahir tidak valid.';
    if ($d['hp'] !== '' && !preg_match('/^[0-9+\-\s]{6,20}$/', $d['hp'])) $err[] = 'Nomor HP tidak valid.';
    return [$d, $err];
}

/** Simpan isian formulir (tanpa password) lalu kembali ke halaman formulir dengan pesan error. */
function gagal($err, $page, $extra = '') {
    $o = $_POST;
    unset($o['password'], $o['password2'], $o['password_lama'], $o['password_baru'], $o['csrf']);
    $_SESSION['old'] = $o;
    flash('danger', implode(' ', $err));
    go($page, $extra);
}

// ---- B5. Komponen formulir --------------------------------------------------
function fv($v, $k) { return e($_SESSION['old'][$k] ?? ($v[$k] ?? '')); }
function sel($v, $k, $val) { return (($_SESSION['old'][$k] ?? ($v[$k] ?? '')) === $val) ? 'selected' : ''; }

/** Ikon SVG 24x24 (warna mengikuti CSS). */
function ikon($nama, $kelas = 'ik') {
    static $p = [
        'orang'    => 'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z',
        'id'       => 'M20 7h-5V4c0-1.1-.9-2-2-2h-2c-1.1 0-2 .9-2 2v3H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zM9 12c.83 0 1.5.67 1.5 1.5S9.83 15 9 15s-1.5-.67-1.5-1.5S8.17 12 9 12zm3 6H6v-.75c0-1 2-1.5 3-1.5s3 .5 3 1.5V18zm1-9h-2V4h2v5zm5 7.5h-4V15h4v1.5zm0-3h-4V12h4v1.5z',
        'kalender' => 'M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zM7 12h5v5H7z',
        'telp'     => 'M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z',
        'surat'    => 'M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z',
        'pin'      => 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z',
        'kunci'    => 'M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z',
        'mata'     => 'M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z',
        'darah'    => 'M12 2c-5.33 4.55-8 8.48-8 11.8 0 4.98 3.8 8.2 8 8.2s8-3.22 8-8.2c0-3.32-2.67-7.25-8-11.8z',
        'info'     => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z',
        'catatan'  => 'M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z',
    ];
    return '<svg' . ($kelas !== '' ? ' class="' . $kelas . '"' : '') . ' viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="' . $p[$nama] . '"/></svg>';
}
/** Membuka kolom isian berikon di kiri (tutup dengan fld_tutup). $extra: 'ta' = textarea, 'pw' = password. */
function fld_buka($ikon, $extra = '') { return '<div class="fld ' . $extra . '">' . ikon($ikon); }
function fld_tutup() { return '</div>'; }
/** Tombol mata untuk menampilkan/menyembunyikan password (JavaScript ada di foot()). */
function tombol_mata($id) {
    return '<button type="button" class="eye" data-target="' . e($id) . '" aria-label="Tampilkan atau sembunyikan password">' . ikon('mata', '') . '</button>';
}

/**
 * Formulir DATA DIRI (urutan sama dengan halaman Register): nama, NIK, tanggal lahir,
 * jenis kelamin, nomor HP, [email], alamat. Dipakai Fitur 2 (register), 3/5 (admin), 13 (profil).
 * $emailHtml = HTML kolom email (diisi pemanggil), $nikWajib = NIK wajib diisi atau tidak.
 */
function form_data_diri($v = [], $emailHtml = '', $nikWajib = true) {
    $req = $nikWajib ? 'required' : ''; ?>
    <label>Nama Lengkap</label>
    <?= fld_buka('orang') ?><input name="nama_lengkap" required maxlength="100" placeholder="Masukkan nama lengkap" value="<?= fv($v, 'nama_lengkap') ?>"><?= fld_tutup() ?>
    <label>NIK</label>
    <?= fld_buka('id') ?><input name="nik" <?= $req ?> inputmode="numeric" maxlength="16" pattern="[0-9]{16}" title="NIK terdiri dari 16 digit angka" placeholder="Masukkan NIK (16 digit)" value="<?= fv($v, 'nik') ?>"><?= fld_tutup() ?>
    <label>Tanggal Lahir</label>
    <?= fld_buka('kalender') ?><input type="date" name="tanggal_lahir" required max="<?= date('Y-m-d') ?>" value="<?= fv($v, 'tanggal_lahir') ?>"><?= fld_tutup() ?>
    <label>Jenis Kelamin</label>
    <?= fld_buka('orang') ?><select name="jenis_kelamin" required>
        <option value="">Pilih jenis kelamin</option>
        <option value="L" <?= sel($v, 'jenis_kelamin', 'L') ?>>Laki-laki</option>
        <option value="P" <?= sel($v, 'jenis_kelamin', 'P') ?>>Perempuan</option>
    </select><?= fld_tutup() ?>
    <label>Nomor HP</label>
    <?= fld_buka('telp') ?><input type="tel" name="no_hp" maxlength="20" placeholder="Masukkan nomor handphone" value="<?= fv($v, 'no_hp') ?>"><?= fld_tutup() ?>
    <?= $emailHtml ?>
    <label>Alamat</label>
    <?= fld_buka('pin', 'ta') ?><textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap"><?= fv($v, 'alamat') ?></textarea><?= fld_tutup() ?>
<?php }

/** FITUR 14 (input): golongan darah & rhesus. Tidak ada di Register; diisi lewat Profil atau admin. */
function form_golongan($v = []) { ?>
    <div class="row">
        <div><label>Golongan Darah</label>
            <?= fld_buka('darah') ?><select name="golongan_darah">
                <option value="">Belum diisi</option>
                <?php foreach (GOLONGAN as $g): ?><option value="<?= $g ?>" <?= sel($v, 'golongan_darah', $g) ?>><?= $g ?></option><?php endforeach; ?>
            </select><?= fld_tutup() ?></div>
        <div><label>Rhesus</label>
            <?= fld_buka('darah') ?><select name="rhesus">
                <option value="">Belum diisi</option>
                <?php foreach (RHESUS as $r): ?><option value="<?= $r ?>" <?= sel($v, 'rhesus', $r) ?>><?= $r ?></option><?php endforeach; ?>
            </select><?= fld_tutup() ?></div>
    </div>
<?php }

// ############################################################################
// BAGIAN C - TAMPILAN UMUM
// ############################################################################

function head($title) {
    $u  = me();
    $f  = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    $pg = $_GET['page'] ?? 'home';
    $link = function ($p, $t) use ($pg) {
        return '<a class="' . ($pg === $p ? 'on' : '') . '" href="' . FILE . '?page=' . $p . '">' . $t . '</a>';
    };
    ?><!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> - SiDarah</title>
<style>
:root{--r:#c62828;--rd:#8e0000;--rl:#fdecea;--tx:#2b2b2b;--bd:#e5e5e5}
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,"Segoe UI",Arial,sans-serif;color:var(--tx);background:#faf6f6;line-height:1.5}
.top{background:var(--r);color:#fff;display:flex;align-items:center;justify-content:space-between;padding:.7rem 1.2rem;position:sticky;top:0;z-index:5;flex-wrap:wrap}
.brand{color:#fff;font-weight:700;font-size:1.3rem;text-decoration:none}
nav a{color:#fff;text-decoration:none;margin-left:.6rem;padding:.35rem .7rem;border-radius:6px;display:inline-block}
nav a:hover,nav a.on{background:rgba(255,255,255,.22)}
.burger{display:none;background:none;border:0;color:#fff;font-size:1.6rem;cursor:pointer}
.wrap{max-width:1050px;margin:1.5rem auto;padding:0 1rem;min-height:60vh}
.card{background:#fff;border:1px solid var(--bd);border-radius:12px;padding:1.2rem;margin-bottom:1.2rem;box-shadow:0 1px 4px rgba(0,0,0,.05)}
h1,h2{color:var(--rd)} h1{margin-top:0}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;margin-bottom:1.2rem}
.stat{background:#fff;border-left:5px solid var(--r);border-radius:10px;padding:1rem;box-shadow:0 1px 4px rgba(0,0,0,.06);color:var(--tx);text-decoration:none}
.stat b{display:block;font-size:2rem;color:var(--r)}
a.stat:hover{background:var(--rl)}
.btn{display:inline-block;background:var(--r);color:#fff;border:0;border-radius:8px;padding:.5rem 1rem;cursor:pointer;text-decoration:none;font:inherit;font-size:.95rem}
.btn:hover{background:var(--rd)} .btn.o{background:#fff;color:var(--r);border:1px solid var(--r)} .btn.s{padding:.25rem .65rem;font-size:.85rem}
label{display:block;margin:.8rem 0 .25rem;font-weight:600}
input,select,textarea{width:100%;padding:.55rem;border:1px solid #ccc;border-radius:8px;font:inherit;background:#fff}
input:focus,select:focus,textarea:focus{outline:3px solid var(--rl);border-color:var(--r)}
.row{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem}
.form{max-width:580px} .form .btn{margin-top:1rem}
.bar{display:flex;gap:.6rem;flex-wrap:wrap;align-items:end;margin-bottom:1rem}
.bar>*{flex:1 1 150px} .bar .btn{flex:0 0 auto}
.tw{overflow-x:auto}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{padding:.6rem .7rem;border-bottom:1px solid var(--bd);text-align:left;white-space:nowrap}
th{background:var(--rl);color:var(--rd)}
.alert{padding:.8rem 1rem;border-radius:8px;margin-bottom:1rem}
.a-success{background:#e6f4ea;color:#1e6b34}.a-danger{background:var(--rl);color:var(--rd)}.a-warning{background:#fff4e0;color:#8a5a00}
.badge{padding:.15rem .6rem;border-radius:99px;font-size:.8rem;font-weight:600}
.b-menunggu{background:#fff4e0;color:#8a5a00}.b-diterima{background:#e6f4ea;color:#1e6b34}.b-dibatalkan{background:#eee;color:#666}.b-selesai{background:#e3ecfb;color:#1a4a9c}
.gol{display:inline-block;background:var(--r);color:#fff;border-radius:6px;padding:.05rem .5rem;font-weight:700}
.inline{display:inline;margin:0}
.hero{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border-radius:16px;padding:2.5rem 1.5rem;text-align:center;margin-bottom:1.5rem}
.hero h1{color:#fff;font-size:2rem}.hero .btn{background:#fff;color:var(--r)}.hero .btn.o{background:transparent;color:#fff;border-color:#fff}
.foot{text-align:center;color:#777;padding:1.5rem;font-size:.9rem}
.muted{color:#777}
.auth{max-width:520px;margin-left:auto;margin-right:auto;border-radius:24px;padding:1.6rem}
.fld{position:relative}
.fld>svg.ik{position:absolute;left:14px;top:50%;transform:translateY(-50%);width:20px;height:20px;fill:var(--r);pointer-events:none}
.fld.ta>svg.ik{top:22px;transform:none}
.fld input,.fld select,.fld textarea{padding:.85rem .9rem .85rem 2.8rem;border:1.5px solid #ecd6d6;border-radius:14px;background:#fff}
.fld.pw input{padding-right:3rem}
.fld input[readonly]{background:#f7f2f2;color:#666}
.fld .eye{position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:0;cursor:pointer;padding:6px;line-height:0}
.fld .eye svg{width:22px;height:22px;fill:#8a8a8a}
.fld .eye.on svg{fill:var(--r)}
.cek{display:flex;gap:.6rem;align-items:flex-start;font-weight:400;margin:1.1rem 0 .4rem}
.cek input{width:auto;margin-top:.3rem;accent-color:var(--r)}
.btn.blok{display:block;width:100%;text-align:center;padding:.9rem;border-radius:14px;font-weight:700;font-size:1.05rem;margin-top:1rem}
.tautan{text-align:center;margin-top:1rem}
.sub{color:var(--r);font-weight:700;font-size:1.15rem;margin:1.5rem 0 .7rem}
.kartu-info{display:flex;align-items:center;gap:.9rem;background:#fff;border:1px solid var(--bd);border-radius:16px;padding:.75rem 1rem;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:.7rem}
.kartu-info svg{width:24px;height:24px;fill:var(--r);flex:none}
.kartu-info b{display:block}
.kartu-info .kosong{color:#999}
.kepala-form{display:flex;align-items:center;gap:1rem}
.kepala-form .lingkar{background:var(--rl);border-radius:16px;padding:.9rem;line-height:0}
.kepala-form svg{width:36px;height:36px;fill:var(--r)}
.kepala-form h1{margin:0 0 .2rem;font-size:1.4rem}
.kepala-form p{margin:0}
.perhatian{display:flex;gap:.8rem;align-items:flex-start;background:#fdf1f0;border-radius:14px;padding:1rem 1.1rem;margin-top:1.2rem}
.perhatian svg{width:24px;height:24px;fill:var(--r);flex:none}
.perhatian b{color:var(--r);display:block}
.detail{padding:.3rem 1.2rem}
.baris{display:flex;gap:1rem;align-items:center;padding:.9rem 0;border-bottom:1px solid var(--bd)}
.baris .lingkar{background:var(--rl);border-radius:12px;padding:.65rem;line-height:0;flex:none}
.baris .lingkar svg{width:22px;height:22px;fill:var(--r)}
.baris small{display:block;color:#777}
.baris b{display:block;font-size:1.05rem}
.baris .badge{display:inline-block;padding:.3rem .9rem;font-size:.95rem;margin-top:.15rem}
.detail .baris:last-of-type{border-bottom:0}
.btn svg{width:20px;height:20px;fill:currentColor;vertical-align:-4px;margin-right:.5rem}
.legenda{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:.6rem}
@media(max-width:700px){.burger{display:block}nav{display:none;width:100%}nav.open{display:block}nav a{display:block;margin:.2rem 0}}
</style></head><body>
<header class="top">
  <a class="brand" href="<?= FILE ?>">🩸 SiDarah<?= PANEL === 'admin' ? ' Admin' : '' ?></a>
  <button id="menuBtn" class="burger" aria-label="Menu">☰</button>
  <nav id="nav">
  <?php if (!$u && PANEL === 'admin'): ?>
    <?= $link('login', 'Login Admin') ?>
  <?php elseif (!$u): ?>
    <?= $link('home', 'Beranda') . $link('login', 'Login') . $link('register', 'Daftar') ?>
  <?php elseif (PANEL === 'admin'): ?>
    <?= $link('dashboard', 'Dashboard') . $link('pendonor', 'Data Pendonor') . $link('golongan', 'Golongan Darah') . $link('jadwal', 'Jadwal Donor') . $link('pendaftaran', 'Pendaftaran') . $link('profil', 'Profil') ?>
  <?php else: ?>
    <?= $link('dashboard', 'Dashboard') . $link('pendaftaran', 'Pendaftaran') . $link('status', 'Status Pendaftaran') . $link('profil', 'Profil') ?>
  <?php endif; ?>
  </nav>
</header>
<main class="wrap">
<?php if ($f): ?><div class="alert a-<?= e($f[0]) ?>"><?= e($f[1]) ?></div><?php endif;
}

function foot() {
    unset($_SESSION['old']);
    ?></main>
<footer class="foot">© <?= date('Y') ?> SiDarah – Sistem Informasi Donor Darah<?php if (PANEL !== 'pendonor'): ?> · <a href="index.php">Halaman Pendonor</a><?php endif; ?></footer>
<script>
// Konfirmasi sebelum form dengan atribut data-confirm dikirim (mis. hapus data)
document.querySelectorAll('form[data-confirm]').forEach(function (f) {
  f.addEventListener('submit', function (ev) { if (!confirm(f.dataset.confirm)) ev.preventDefault(); });
});
// Tombol mata: tampilkan / sembunyikan password
document.querySelectorAll('.eye').forEach(function (b) {
  b.addEventListener('click', function () {
    var i = document.getElementById(b.dataset.target);
    var tampil = i.type === 'password';
    i.type = tampil ? 'text' : 'password';
    b.classList.toggle('on', tampil);
  });
});
// Tombol menu untuk layar HP
var mb = document.getElementById('menuBtn');
if (mb) mb.addEventListener('click', function () { document.getElementById('nav').classList.toggle('open'); });
</script>
</body></html><?php
}

/**
 * FITUR 12 (tampilan): tabel jadwal donor mendatang.
 * $mode: 'publik' = tanpa kuota | 'info' = dengan kuota
 * (Pendaftaran dilakukan di halaman Pendaftaran, lihat index.php.)
 */
function tabel_jadwal($list, $mode = 'publik') {
    $kolom = $mode === 'publik' ? 4 : 5; ?>
    <div class="tw"><table>
        <tr><th>Kegiatan</th><th>Tanggal</th><th>Jam</th><th>Lokasi</th><?= $mode !== 'publik' ? '<th>Kuota</th>' : '' ?></tr>
        <?php foreach ($list as $j): ?>
        <tr>
            <td><?= e($j['judul']) ?></td><td><?= tgl($j['tanggal']) ?></td>
            <td><?= jam($j['jam_mulai']) ?>–<?= jam($j['jam_selesai']) ?></td><td><?= e($j['lokasi']) ?></td>
            <?php if ($mode !== 'publik'): ?><td><?= (int)$j['terisi'] ?>/<?= (int)$j['kuota'] ?></td><?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php if (!$list): ?><tr><td colspan="<?= $kolom ?>" class="muted">Belum ada jadwal donor mendatang.</td></tr><?php endif; ?>
    </table></div>
<?php }


// ############################################################################
// BAGIAN D - FITUR YANG DIPAKAI BERSAMA (1, 12, 13)
// ############################################################################

// ============================================================================
// FITUR 1 - LOGIN (dipakai bersama; tiap panel hanya menerima peran miliknya)
//   DATABASE : users (email, password [hash], role)
//   KODE     : aksi_login, halaman_login, halaman_logout
//              (halaman_setup = pembuat admin pertama, ada di admin.php)
// ============================================================================
function aksi_login() {
    $u = row('SELECT * FROM users WHERE email=?', 's', [strtolower(trim($_POST['email'] ?? ''))]);
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        if ($u['role'] !== PANEL) {
            flash('danger', PANEL === 'admin'
                ? 'Akun ini bukan akun admin. Pendaftar donor silakan login di halaman pendonor (' . panel_lain() . ').'
                : 'Akun ini adalah akun admin. Silakan login di halaman admin (' . panel_lain() . ').');
            go('login');
        }
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$u['id_user'], 'nama' => $u['nama'], 'role' => $u['role']];
        go('dashboard');
    }
    flash('danger', 'Email atau password salah.');
    go('login');
}

function halaman_login() {
    if (me()) go('dashboard');
    $admin = PANEL === 'admin';
    head($admin ? 'Login Admin' : 'Login'); ?>
    <div class="card form auth"><h1><?= $admin ? 'Login Admin' : 'Login Pendonor' ?></h1>
    <form method="post"><?= csrf() ?><input type="hidden" name="aksi" value="login">
        <label>Email</label>
        <?= fld_buka('surat') ?><input type="email" name="email" required autofocus placeholder="Masukkan email"><?= fld_tutup() ?>
        <label>Password</label>
        <?= fld_buka('kunci', 'pw') ?><input type="password" id="pw-login" name="password" required placeholder="Masukkan password"><?= tombol_mata('pw-login') ?><?= fld_tutup() ?>
        <button class="btn blok">Masuk</button>
    </form>
    <?php if (!$admin): ?>
    <p class="tautan muted">Belum punya akun? <a href="<?= FILE ?>?page=register">Daftar di sini</a></p>
    <?php endif; ?></div>
    <?php foot();
}

function halaman_logout() {
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
    flash('success', 'Anda sudah keluar.');
    go('login');
}

// ============================================================================
// FITUR 12 (bagian bersama) - DAFTAR JADWAL MENDATANG
//   DATABASE : jadwal_donor WHERE tanggal >= hari ini; kuota terisi dari pendaftaran_donor
//   KODE     : jadwal_mendatang (+ tabel_jadwal di Bagian C)
// ============================================================================
/** $idp = id pendonor (agar status pendaftarannya ikut tampil), 0 = pengunjung umum. */
function jadwal_mendatang($idp = 0, $limit = 0) {
    $sql = "SELECT j.*,
              (SELECT COUNT(*) FROM pendaftaran_donor x WHERE x.id_jadwal=j.id_jadwal AND x.status<>'dibatalkan') AS terisi,
              (SELECT y.status FROM pendaftaran_donor y WHERE y.id_jadwal=j.id_jadwal AND y.id_pendonor=?) AS status_saya,
              (SELECT y.id_pendaftaran FROM pendaftaran_donor y WHERE y.id_jadwal=j.id_jadwal AND y.id_pendonor=?) AS id_pdf
            FROM jadwal_donor j WHERE j.tanggal>=CURDATE() ORDER BY j.tanggal, j.jam_mulai";
    if ($limit > 0) $sql .= ' LIMIT ' . (int)$limit;
    return rows($sql, 'ii', [$idp, $idp]);
}

// ============================================================================
// FITUR 13 - PROFIL PENGGUNA (admin & pendonor)
//   DATABASE : users (nama, password) ; pendonor (data pribadi, hanya untuk pendonor)
//   KODE     : aksi_profil, halaman_profil
// ============================================================================
function aksi_profil() {
    $u = me();
    $err = [];
    if ($u['role'] === 'pendonor') {
        $p = pendonor_saya();
        [$d, $err] = baca_pendonor('profil', (int)($p['id_pendonor'] ?? 0));
        $nama = $d['nama'];
    } else {
        $nama = trim($_POST['nama_lengkap'] ?? '');
        if ($nama === '') $err[] = 'Nama wajib diisi.';
    }

    $pwBaru = $_POST['password_baru'] ?? '';
    if ($pwBaru !== '') {
        $cur = row('SELECT password FROM users WHERE id_user=?', 'i', [$u['id']]);
        if (!password_verify($_POST['password_lama'] ?? '', $cur['password'])) $err[] = 'Password lama salah.';
        if (strlen($pwBaru) < 6) $err[] = 'Password baru minimal 6 karakter.';
    }
    if ($err) gagal($err, 'profil');

    q('UPDATE users SET nama=? WHERE id_user=?', 'si', [$nama, $u['id']]);
    if ($pwBaru !== '') q('UPDATE users SET password=? WHERE id_user=?', 'si', [password_hash($pwBaru, PASSWORD_DEFAULT), $u['id']]);
    if ($u['role'] === 'pendonor') {
        q('UPDATE pendonor SET nama_lengkap=?,nik=?,golongan_darah=?,rhesus=?,jenis_kelamin=?,tanggal_lahir=?,no_hp=?,alamat=? WHERE id_user=?',
          'ssssssssi', [$d ['nama'], $d['nik'], $d['gol'], $d['rh'], $d['jk'], $d['lahir'], $d['hp'], $d['alamat'], $u['id']]);
    }
    $_SESSION['user']['nama'] = $nama;
    flash('success', 'Profil diperbarui.');
    go('profil');
}

function halaman_profil() {
    $u   = me();
    $acc = row('SELECT * FROM users WHERE id_user=?', 'i', [$u['id']]);
    $p   = $u['role'] === 'pendonor' ? pendonor_saya() : [];
    $emailHtml = '<label>Email</label>' . fld_buka('surat') . '<input type="email" value="' . e($acc['email']) . '" readonly>' . fld_tutup();
    head('Profil'); ?>
    <div class="card form auth"><h1>Profil Saya</h1>
    <p class="muted">Peran: <?= e($acc['role']) ?><?php if ($u['role'] === 'pendonor' && empty($p['golongan_darah'])): ?> · <b>Golongan darah belum diisi, lengkapi di bawah.</b><?php endif; ?></p>
    <form method="post"><?= csrf() ?><input type="hidden" name="aksi" value="profil">
        <?php if ($u['role'] === 'pendonor') {
            form_data_diri($p, $emailHtml);
            echo '<h2>Data Donor</h2>';
            form_golongan($p);
        } else { ?>
            <label>Nama</label>
            <?= fld_buka('orang') ?><input name="nama_lengkap" required maxlength="100" value="<?= fv(['nama_lengkap' => $acc['nama']], 'nama_lengkap') ?>"><?= fld_tutup() ?>
            <?= $emailHtml ?>
        <?php } ?>
        <h2>Ganti Password (opsional)</h2>
        <label>Password Lama</label>
        <?= fld_buka('kunci', 'pw') ?><input type="password" id="pw-lama" name="password_lama" placeholder="Masukkan password lama"><?= tombol_mata('pw-lama') ?><?= fld_tutup() ?>
        <label>Password Baru (min. 6 karakter)</label>
        <?= fld_buka('kunci', 'pw') ?><input type="password" id="pw-baru" name="password_baru" placeholder="Masukkan password baru"><?= tombol_mata('pw-baru') ?><?= fld_tutup() ?>
        <button class="btn blok">Simpan Profil</button>
    </form>
    <a class="btn o blok" href="<?= FILE ?>?page=logout">Keluar</a></div>
    <?php foot();
}


// ############################################################################
// BAGIAN E - ROUTER
//   Dipanggil di akhir index.php / admin.php:  jalankan($aksi, $halaman)
//   $aksi    : nama aksi (form POST)  => [fungsi, hak akses]
//   $halaman : nama halaman (?page=)  => [fungsi, hak akses]
//   Hak akses: null = publik | 'login' = wajib login
// ############################################################################
function jalankan($daftarAksi, $daftarHalaman) {
    // Pengguna yang login dengan peran lain diarahkan ke panelnya sendiri.
    if (me() && me()['role'] !== PANEL) {
        header('Location: ' . panel_lain() . '?page=dashboard');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
            die('Formulir tidak valid. Kembali, muat ulang halaman, lalu coba lagi.');
        }
        $aksi = $_POST['aksi'] ?? '';
        if (!isset($daftarAksi[$aksi])) go('home');
        [$fungsi, $hak] = $daftarAksi[$aksi];
        izin($hak);
        $fungsi();
        go('home');
    }

    $page = $_GET['page'] ?? 'home';
    if (!isset($daftarHalaman[$page])) {
        http_response_code(404);
        head('Tidak ditemukan'); ?>
        <div class="card"><h1>Halaman tidak ditemukan</h1><a class="btn" href="<?= FILE ?>">Kembali ke beranda</a></div>
        <?php foot();
        exit;
    }
    [$fungsi, $hak] = $daftarHalaman[$page];
    izin($hak);
    $fungsi();
}