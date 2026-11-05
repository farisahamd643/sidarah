<?php
/**
 * ============================================================================
 *  SiDarah - Halaman PENDONOR (pendaftar donor darah)
 * ============================================================================
 *  Lokasi : C:\xampp\htdocs\sidarah\index.php
 *  Buka   : http://localhost/sidarah/
 *  Bergantung pada inti.php (satu folder) dan database/sidarah.sql.
 *
 *  FITUR DI FILE INI:
 *    2 Register | 8 Pendaftaran Donor | 9 Riwayat Donor | 11 Status (sisi pendonor)
 *    12 Beranda & Dashboard pendonor
 *    (Login, Profil, Jadwal mendatang: lihat inti.php)
 * ============================================================================
 */
date_default_timezone_set('Asia/Jakarta');
define('PANEL', 'pendonor');
define('FILE', 'index.php');
require __DIR__ . '/inti.php';

// ============================================================================
// FITUR 2 - REGISTER
//   DATABASE : users (nama, email, password, role='pendonor') + pendonor (id_user)
//              dibuat bersamaan dalam 1 transaksi
//              golongan_darah & rhesus sengaja dikosongkan (NULL): diisi lewat Profil
//   KODE     : aksi_register, halaman_register, halaman_syarat
// ============================================================================
function aksi_register() {
    global $db;
    [$d, $err] = baca_pendonor('register');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pw    = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err[] = 'Email tidak valid.';
    if (strlen($pw) < 6) $err[] = 'Password minimal 6 karakter.';
    if ($pw !== ($_POST['password2'] ?? '')) $err[] = 'Konfirmasi password tidak sama.';
    if (empty($_POST['setuju'])) $err[] = 'Anda harus menyetujui Syarat & Ketentuan dan Kebijakan Privasi.';
    if (!$err && row('SELECT 1 FROM users WHERE email=?', 's', [$email])) $err[] = 'Email sudah terdaftar.';
    if ($err) gagal($err, 'register');

    try {
        $db->begin_transaction();
        q("INSERT INTO users (nama,email,password,role) VALUES (?,?,?, 'pendonor')", 'sss',
          [$d['nama'], $email, password_hash($pw, PASSWORD_DEFAULT)]);
        $uid = $db->insert_id;
        // golongan_darah & rhesus sengaja NULL: diisi pendonor nanti lewat menu Profil
        q('INSERT INTO pendonor (id_user,nama_lengkap,nik,golongan_darah,rhesus,jenis_kelamin,tanggal_lahir,no_hp,alamat) VALUES (?,?,?,NULL,NULL,?,?,?,?)',
          'issssss', [$uid, $d['nama'], $d['nik'], $d['jk'], $d['lahir'], $d['hp'], $d['alamat']]);
        $db->commit();
    } catch (Throwable $ex) {
        $db->rollback();
        gagal(['Registrasi gagal disimpan. Coba lagi.'], 'register');
    }
    flash('success', 'Registrasi berhasil. Silakan login, lalu lengkapi golongan darah Anda di menu Profil.');
    go('login');
}

function halaman_register() {
    if (me()) go('dashboard');
    $emailHtml = '<label>Email</label>' . fld_buka('surat')
               . '<input type="email" name="email" required maxlength="100" placeholder="Masukkan email" value="' . fv([], 'email') . '">' . fld_tutup();
    head('Daftar'); ?>
    <div class="card form auth"><h1>Buat Akun Pendonor</h1>
    <p class="muted">Isi data diri Anda. Golongan darah bisa dilengkapi nanti di menu Profil.</p>
    <form method="post"><?= csrf() ?><input type="hidden" name="aksi" value="register">
        <?php form_data_diri([], $emailHtml); ?>
        <label>Password (min. 6 karakter)</label>
        <?= fld_buka('kunci', 'pw') ?><input type="password" id="pw1" name="password" required minlength="6" placeholder="Masukkan password"><?= tombol_mata('pw1') ?><?= fld_tutup() ?>
        <label>Konfirmasi Password</label>
        <?= fld_buka('kunci', 'pw') ?><input type="password" id="pw2" name="password2" required placeholder="Masukkan konfirmasi password"><?= tombol_mata('pw2') ?><?= fld_tutup() ?>
        <label class="cek"><input type="checkbox" name="setuju" value="1" required>
            <span>Saya menyetujui <a href="<?= FILE ?>?page=syarat" target="_blank" rel="noopener">Syarat &amp; Ketentuan dan Kebijakan Privasi</a></span></label>
        <button class="btn blok">Daftar Akun</button>
    </form>
    <p class="tautan muted">Sudah punya akun? <a href="<?= FILE ?>?page=login">Masuk di sini</a></p></div>
    <?php foot();
}

/** Halaman Syarat & Ketentuan dan Kebijakan Privasi (dibuka dari formulir Register). */
function halaman_syarat() {
    head('Syarat & Ketentuan'); ?>
    <div class="card auth" style="max-width:760px"><h1>Syarat &amp; Ketentuan dan Kebijakan Privasi</h1>
    <h2>1. Tujuan</h2>
    <p>Akun SiDarah dipakai untuk mendaftar kegiatan donor darah dan untuk membantu admin mengelola data pendonor serta jadwal kegiatan.</p>
    <h2>2. Data yang disimpan</h2>
    <p>Nama lengkap, NIK, tanggal lahir, jenis kelamin, nomor HP, email, alamat, golongan darah dan rhesus, serta riwayat pendaftaran donor Anda.</p>
    <h2>3. Penggunaan data</h2>
    <p>Data hanya digunakan untuk pengelolaan kegiatan donor darah oleh admin SiDarah dan tidak dibagikan kepada pihak lain di luar keperluan tersebut.</p>
    <h2>4. Keamanan</h2>
    <p>Password disimpan dalam bentuk terenkripsi (hash), bukan teks biasa. Jaga kerahasiaan password Anda.</p>
    <h2>5. Hak pengguna</h2>
    <p>Anda dapat melihat dan memperbarui data pribadi di menu Profil. Permintaan penghapusan akun dapat disampaikan kepada admin.</p>
    <h2>6. Kewajiban pengguna</h2>
    <p>Pengguna wajib mengisi data dengan benar dan tidak menyalahgunakan akun.</p>
    <p class="muted"><em>Catatan: dokumen ini contoh untuk tugas Software Development. Sesuaikan dengan kebijakan penyelenggara yang sebenarnya.</em></p>
    <p><a class="btn o" href="javascript:window.close()">Tutup halaman ini</a></p></div>
    <?php foot();
}


// ============================================================================
// FITUR 8 - PENDAFTARAN DONOR
//   Pendonor memilih LOKASI (puskesmas), TANGGAL (bebas, Senin-Minggu) dan JAM,
//   lalu menekan "Daftar Donor" -> langsung ke halaman Status Pendaftaran (hijau: Terdaftar).
//   DATABASE : pendaftaran_donor (id_pendonor, id_jadwal, status='menunggu');
//              pilihan lokasi+tanggal+jam dicocokkan dengan baris jadwal_donor;
//              bila belum ada, baris jadwal_donor dibuat otomatis (kuota 50), sehingga
//              halaman admin (Jadwal Donor & Pendaftaran) tetap berfungsi tanpa ubah database.
//   ATURAN   : satu pendonor hanya boleh punya 1 pendaftaran aktif (batalkan dulu bila ingin ganti).
//   KODE     : aksi_daftar, aksi_batal, halaman_pendaftaran, halaman_status, halaman_jadwal_saya
// ============================================================================

/** Daftar puskesmas yang bisa dipilih (ubah nama/alamat di sini sesuai kebutuhan). */
const PUSKESMAS = [
    'Puskesmas A' => 'Jl. Raya A No. 11, Kota Kediri',
    'Puskesmas B' => 'Jl. Raya B No. 22, Kabupaten Kediri',
    'Puskesmas C' => 'Jl. Raya C No. 33, Kabupaten Kediri',
];
/** Pilihan jam kegiatan: kode => [jam mulai, jam selesai]. */
const JAM_DONOR = [
    'pagi'  => ['08:00:00', '12:00:00'],
    'siang' => ['13:00:00', '15:00:00'],
];
const BATAS_HARI_DAFTAR = 90; // tanggal paling jauh yang boleh dipilih (hari dari sekarang)

function hari_nama($d) {
    static $h = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return $h[(int)date('w', strtotime($d))];
}
/** Contoh: "28 Oktober 2026" */
function tgl_panjang($d) {
    static $b = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $t = strtotime($d);
    return date('j', $t) . ' ' . $b[(int)date('n', $t)] . ' ' . date('Y', $t);
}
/** Contoh: "Selasa, 24 Oktober 2026" */
function jadwal_teks($d) { return hari_nama($d) . ', ' . tgl_panjang($d); }
/** Contoh: "08.00 – 12.00 WIB" */
function jam_range($m, $s) { return str_replace(':', '.', jam($m)) . ' – ' . str_replace(':', '.', jam($s)) . ' WIB'; }

/** Pendaftaran aktif (Terdaftar/Diterima) yang jadwalnya belum lewat. null bila tidak ada. */
function pendaftaran_aktif($idp) {
    return row("SELECT d.id_pendaftaran,d.status,d.tanggal_daftar,j.tanggal,j.jam_mulai,j.jam_selesai,j.lokasi
                FROM pendaftaran_donor d JOIN jadwal_donor j ON j.id_jadwal=d.id_jadwal
                WHERE d.id_pendonor=? AND d.status IN ('menunggu','diterima') AND j.tanggal>=CURDATE()
                ORDER BY d.tanggal_daftar DESC, d.id_pendaftaran DESC LIMIT 1", 'i', [$idp]);
}

function aksi_daftar() {
    $p = pendonor_saya();
    if (!$p) { flash('danger', 'Data pendonor tidak ditemukan.'); go('pendaftaran'); }

    $lokasi  = $_POST['lokasi'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $kodeJam = $_POST['jam'] ?? '';
    $err = [];
    if (!isset(PUSKESMAS[$lokasi])) $err[] = 'Pilih lokasi donor.';
    if (!tgl_valid($tanggal)) {
        $err[] = 'Pilih tanggal donor.';
    } elseif ($tanggal < date('Y-m-d')) {
        $err[] = 'Tanggal donor tidak boleh sebelum hari ini.';
    } elseif ($tanggal > date('Y-m-d', strtotime('+' . BATAS_HARI_DAFTAR . ' days'))) {
        $err[] = 'Tanggal donor maksimal ' . BATAS_HARI_DAFTAR . ' hari dari sekarang.';
    }
    if (!isset(JAM_DONOR[$kodeJam])) $err[] = 'Pilih jam donor.';
    if (!$err && $tanggal === date('Y-m-d') && JAM_DONOR[$kodeJam][1] <= date('H:i:s')) $err[] = 'Jam donor hari ini sudah lewat, pilih jam lain.';
    if ($err) gagal($err, 'pendaftaran');

    if (pendaftaran_aktif($p['id_pendonor'])) {
        flash('warning', 'Anda masih punya pendaftaran aktif. Batalkan dulu di halaman Status Pendaftaran bila ingin mendaftar ulang.');
        go('status');
    }

    [$mulai, $selesai] = JAM_DONOR[$kodeJam];
    $j = row('SELECT * FROM jadwal_donor WHERE lokasi=? AND tanggal=? AND jam_mulai=? AND jam_selesai=? LIMIT 1',
             'ssss', [$lokasi, $tanggal, $mulai, $selesai]);
    if (!$j) {
        q('INSERT INTO jadwal_donor (judul,tanggal,jam_mulai,jam_selesai,lokasi,keterangan,kuota) VALUES (?,?,?,?,?,?,50)',
          'ssssss', ['Donor Darah - ' . $lokasi, $tanggal, $mulai, $selesai, $lokasi, 'Dibuat otomatis dari pendaftaran pendonor']);
        $j = row('SELECT * FROM jadwal_donor WHERE id_jadwal=?', 'i', [$GLOBALS['db']->insert_id]);
    }
    $idj    = (int)$j['id_jadwal'];
    $terisi = hitung("SELECT COUNT(*) c FROM pendaftaran_donor WHERE id_jadwal=? AND status<>'dibatalkan'", 'i', [$idj]);
    if ($terisi >= (int)$j['kuota']) { flash('danger', 'Kuota pada jadwal ini sudah penuh, pilih tanggal/jam lain.'); go('pendaftaran'); }

    $ada = row('SELECT id_pendaftaran FROM pendaftaran_donor WHERE id_pendonor=? AND id_jadwal=?', 'ii', [$p['id_pendonor'], $idj]);
    if ($ada) q("UPDATE pendaftaran_donor SET status='menunggu', tanggal_daftar=NOW() WHERE id_pendaftaran=?", 'i', [$ada['id_pendaftaran']]);
    else      q('INSERT INTO pendaftaran_donor (id_pendonor,id_jadwal) VALUES (?,?)', 'ii', [$p['id_pendonor'], $idj]);

    flash('success', 'Pendaftaran berhasil. Status pendaftaran Anda: Terdaftar.');
    go('status');
}

function aksi_batal() {
    $p = pendonor_saya();
    if ($p) q("UPDATE pendaftaran_donor SET status='dibatalkan' WHERE id_pendaftaran=? AND id_pendonor=? AND status IN ('menunggu','diterima')",
              'ii', [(int)($_POST['id'] ?? 0), $p['id_pendonor']]);
    flash('success', 'Pendaftaran dibatalkan.');
    go('status');
}

/** CSS tambahan untuk halaman pendaftaran, status, dan jadwal pendonor. */
function css_pendonor() { ?>
<style>
.pilih{position:relative;display:flex;align-items:center;gap:1rem;background:#fff;border:1px solid var(--bd);border-radius:16px;padding:.9rem 1rem;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:.7rem;cursor:pointer}
.pilih>svg{width:24px;height:24px;fill:var(--r);flex:none}
.pilih>svg.pan{width:22px;height:22px;fill:none;stroke:#777;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}
.pilih .pt{flex:1;min-width:0}
.pilih .pt b{display:block}
.pilih .pv{display:block;color:var(--tx)}
.pilih .pv.ph{color:#9a9a9a}
.pilih select,.pilih input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer;border:0;padding:0;font-size:16px}
.pilih:focus-within{outline:3px solid var(--rl);border-color:var(--r)}
.baris .badge::before{content:"";display:inline-block;width:8px;height:8px;border-radius:50%;background:currentColor;margin-right:.45rem}
.b-menunggu,.b-diterima{background:#e6f4ea;color:#1e8e3e}
.jd{display:flex;align-items:center;gap:1rem;background:#fff;border:1px solid var(--bd);border-radius:16px;padding:1rem;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:.9rem;color:var(--tx);text-decoration:none}
.jd:hover{background:#fffafa}
.jd .lingkar{background:var(--rl);border-radius:14px;padding:.9rem;line-height:0;flex:none}
.jd .lingkar svg{width:34px;height:34px;fill:var(--r)}
.jd .isi{flex:1;min-width:0}
.jd h3{margin:0 0 .3rem;font-size:1.2rem}
.jd .chip{display:inline-block;background:var(--rl);color:var(--r);border-radius:99px;padding:.1rem .75rem;font-size:.85rem;font-weight:700;margin-bottom:.35rem}
.jd .info{display:flex;align-items:center;gap:.4rem;color:#555}
.jd .info svg{width:18px;height:18px;fill:var(--r);flex:none}
.jd .pan{width:22px;height:22px;fill:none;stroke:var(--r);stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;flex:none}
</style>
<?php }

/** Satu kartu pilihan (ikon, judul, teks isian, panah) dengan kontrol asli transparan di atasnya. */
function kartu_pilih($ikonHtml, $judul, $placeholder, $kontrolHtml) {
    return '<div class="pilih" data-ph="' . e($placeholder) . '">' . $ikonHtml
         . '<div class="pt"><b>' . e($judul) . '</b><span class="pv ph">' . e($placeholder) . '</span></div>'
         . '<svg class="pan" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>'
         . $kontrolHtml . '</div>';
}
function ikon_jam($kelas = '') {
    return '<svg' . ($kelas ? ' class="' . $kelas . '"' : '') . ' viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"/></svg>';
}

/** Formulir pendaftaran: Data Pribadi (dari Profil, hanya tampil) + Data Pendaftaran (lokasi, tanggal, jam). */
function halaman_pendaftaran() {
    $p = pendonor_saya();
    head('Pendaftaran Donor');
    css_pendonor();
    if (!$p) { ?>
        <div class="alert a-warning">Data pendonor untuk akun ini tidak ditemukan.</div>
        <?php foot();
        return;
    }
    $aktif = pendaftaran_aktif($p['id_pendonor']);
    $kartu = function ($ikon, $label, $nilai) {
        $kosong = ($nilai === null || $nilai === '');
        return '<div class="kartu-info">' . ikon($ikon, '') . '<div><b>' . e($label) . '</b>'
             . ($kosong ? '<span class="kosong">Belum diisi</span>' : '<span>' . e($nilai) . '</span>') . '</div></div>';
    }; ?>
    <div class="card kepala-form">
        <div class="lingkar"><?= ikon('catatan', '') ?></div>
        <div><h1>Daftar Donor Darah</h1><p class="muted">Lengkapi data berikut untuk mendaftar donor darah.</p></div>
    </div>

    <h2 class="sub">Data Pribadi</h2>
    <?= $kartu('orang', 'Nama Lengkap', $p['nama_lengkap']) ?>
    <?= $kartu('id', 'NIK', $p['nik']) ?>
    <?= $kartu('kalender', 'Tanggal Lahir', tgl($p['tanggal_lahir'])) ?>
    <?= $kartu('orang', 'Jenis Kelamin', $p['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan') ?>
    <?= $kartu('telp', 'Nomor HP', $p['no_hp']) ?>
    <?= $kartu('darah', 'Golongan Darah', $p['golongan_darah'] ? $p['golongan_darah'] . $p['rhesus'] : null) ?>
    <p class="muted">Data di atas diambil dari <a href="<?= FILE ?>?page=profil">Profil</a>. Ada yang belum benar atau belum diisi? Perbarui dulu di Profil.</p>

    <h2 class="sub">Data Pendaftaran</h2>
    <?php if ($aktif): ?>
        <div class="alert a-warning">Anda sudah punya pendaftaran aktif (<?= e($aktif['lokasi']) ?>, <?= e(jadwal_teks($aktif['tanggal'])) ?>).
            Lihat di <a href="<?= FILE ?>?page=status">Status Pendaftaran</a>. Batalkan dulu bila ingin mendaftar ulang.</div>
    <?php else:
        // Pilihan lokasi
        $selLokasi = '<select name="lokasi" required><option value="">Pilih lokasi donor</option>';
        foreach (PUSKESMAS as $nama => $alamat) $selLokasi .= '<option value="' . e($nama) . '" ' . sel([], 'lokasi', $nama) . '>' . e($nama) . '</option>';
        $selLokasi .= '</select>';
        // Pilihan tanggal (bebas, Senin-Minggu)
        $inTanggal = '<input type="date" name="tanggal" required min="' . date('Y-m-d') . '" max="' . date('Y-m-d', strtotime('+' . BATAS_HARI_DAFTAR . ' days')) . '" value="' . fv([], 'tanggal') . '">';
        // Pilihan jam
        $selJam = '<select name="jam" required><option value="">Pilih jam donor</option>';
        foreach (JAM_DONOR as $kode => [$m, $s]) $selJam .= '<option value="' . $kode . '" ' . sel([], 'jam', $kode) . '>' . e(jam_range($m, $s)) . '</option>';
        $selJam .= '</select>'; ?>
    <form method="post"><?= csrf() ?><input type="hidden" name="aksi" value="daftar">
        <?= kartu_pilih(ikon('pin', ''), 'Pilih Lokasi', 'Pilih lokasi donor', $selLokasi) ?>
        <?= kartu_pilih(ikon('kalender', ''), 'Pilih Jadwal', 'Pilih hari dan tanggal', $inTanggal) ?>
        <?= kartu_pilih(ikon_jam(), 'Pilih Jam', 'Pilih jam donor', $selJam) ?>
        <div class="perhatian"><?= ikon('info', '') ?>
            <div><b>Perhatian</b>Pastikan semua data sudah benar sebelum menekan tombol Daftar Donor.</div></div>
        <button class="btn blok"><?= ikon('catatan', '') ?>Daftar Donor</button>
    </form>
    <script>
    // Kartu pilihan: teks pilihan tampil di kartu; tanggal ditampilkan lengkap dengan nama hari
    document.querySelectorAll('.pilih').forEach(function (k) {
      var el = k.querySelector('select,input'), pv = k.querySelector('.pv'), ph = k.dataset.ph;
      function ubah() {
        var t = '';
        if (el.tagName === 'SELECT') { t = el.value ? el.options[el.selectedIndex].text : ''; }
        else if (el.value) {
          var d = new Date(el.value + 'T00:00:00');
          t = d.toLocaleDateString('id-ID', {weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'});
        }
        pv.textContent = t || ph;
        pv.classList.toggle('ph', !t);
      }
      el.addEventListener('change', ubah); ubah();
      if (el.type === 'date') k.addEventListener('click', function () { try { el.showPicker(); } catch (x) {} });
    });
    </script>
    <?php endif;
    foot();
}

/** Status pendaftaran donor milik pendonor (FITUR 11): kartu detail per pendaftaran. */
function halaman_status() {
    $p = pendonor_saya();
    $saya = $p ? rows('SELECT d.id_pendaftaran,d.status,d.tanggal_daftar,j.tanggal,j.jam_mulai,j.jam_selesai,j.lokasi
                       FROM pendaftaran_donor d JOIN jadwal_donor j ON j.id_jadwal=d.id_jadwal
                       WHERE d.id_pendonor=? ORDER BY d.tanggal_daftar DESC, d.id_pendaftaran DESC', 'i', [$p['id_pendonor']]) : [];
    // Satu baris detail: ikon di kiri, label kecil, nilai tebal, keterangan tambahan (opsional)
    $baris = function ($ikon, $label, $nilaiHtml, $ket = '') {
        return '<div class="baris"><div class="lingkar">' . $ikon . '</div><div><small>' . e($label) . '</small>'
             . '<b>' . $nilaiHtml . '</b>' . ($ket !== '' ? '<small>' . e($ket) . '</small>' : '') . '</div></div>';
    };
    $catatan = [
        'menunggu'   => 'Harap hadir 15 menit sebelum jadwal donor dan membawa KTP asli.',
        'diterima'   => 'Pendaftaran disetujui admin. Harap hadir 15 menit sebelum jadwal donor dan membawa KTP asli.',
        'selesai'    => 'Anda sudah mengikuti kegiatan donor ini. Terima kasih!',
        'dibatalkan' => 'Pendaftaran ini sudah dibatalkan.',
    ];
    head('Status Pendaftaran');
    css_pendonor(); ?>
    <div class="card kepala-form">
        <div class="lingkar"><?= ikon('kalender', '') ?></div>
        <div><h1>Status Pendaftaran</h1><p class="muted">Cek detail status pendaftaran donor darah Anda.</p></div>
    </div>

    <h2 class="sub">Detail Pendaftaran</h2>
    <?php if (!$saya): ?>
        <div class="card"><p class="muted" style="margin:0">Anda belum mendaftar kegiatan apa pun. <a href="<?= FILE ?>?page=pendaftaran">Daftar sekarang</a>.</p></div>
    <?php endif; ?>
    <?php foreach ($saya as $s): ?>
    <div class="card detail">
        <?= $baris(ikon('orang', ''), 'Nama', e($p['nama_lengkap'])) ?>
        <?= $baris(ikon('kalender', ''), 'Tanggal Pendaftaran', e(tgl_panjang($s['tanggal_daftar']))) ?>
        <?= $baris(ikon('pin', ''), 'Lokasi Donor', e($s['lokasi']), PUSKESMAS[$s['lokasi']] ?? '') ?>
        <?= $baris(ikon('kalender', ''), 'Jadwal Donor', e(jadwal_teks($s['tanggal'])), jam_range($s['jam_mulai'], $s['jam_selesai'])) ?>
        <?= $baris(ikon('catatan', ''), 'Status Pendaftaran', badge($s['status'])) ?>
        <?= $baris(ikon('info', ''), 'Catatan', e($catatan[$s['status']] ?? '')) ?>
        <?php if (in_array($s['status'], ['menunggu', 'diterima'], true) && $s['tanggal'] >= date('Y-m-d')): ?>
        <form method="post" data-confirm="Batalkan pendaftaran ini?"><?= csrf() ?>
            <input type="hidden" name="aksi" value="batal"><input type="hidden" name="id" value="<?= (int)$s['id_pendaftaran'] ?>">
            <button class="btn o blok" style="margin-bottom:1rem">Batalkan Pendaftaran</button></form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <a class="btn blok" href="<?= FILE ?>?page=jadwal"><?= ikon('kalender', '') ?>Lihat Jadwal</a>
    <?php foot();
}

/** Lihat Jadwal: hanya jadwal dari pendaftaran aktif milik pendonor ini (maksimal 1). */
function halaman_jadwal_saya() {
    $p = pendonor_saya();
    $a = $p ? pendaftaran_aktif($p['id_pendonor']) : null;
    head('Jadwal Donor');
    css_pendonor(); ?>
    <div class="card kepala-form">
        <div class="lingkar"><?= ikon('kalender', '') ?></div>
        <div><h1>Jadwal Donor</h1><p class="muted">Informasi jadwal donor darah Anda.</p></div>
    </div>
    <?php if (!$a): ?>
        <div class="card"><p class="muted" style="margin:0">Anda belum punya jadwal donor aktif. <a href="<?= FILE ?>?page=pendaftaran">Daftar sekarang</a>.</p></div>
    <?php else: ?>
        <a class="jd" href="<?= FILE ?>?page=status">
            <div class="lingkar"><?= ikon('kalender', '') ?></div>
            <div class="isi">
                <h3><?= e(jadwal_teks($a['tanggal'])) ?></h3>
                <span class="chip"><?= e(label_status($a['status'])) ?></span>
                <div class="info"><?= ikon('pin', '') ?><span><?= e($a['lokasi']) ?></span></div>
                <div class="info"><?= ikon_jam() ?><span><?= e(jam_range($a['jam_mulai'], $a['jam_selesai'])) ?></span></div>
            </div>
            <svg class="pan" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
        </a>
    <?php endif; ?>
    <a class="btn o blok" href="<?= FILE ?>?page=status">Kembali ke Status Pendaftaran</a>
    <?php foot();
}


// ============================================================================
// FITUR 9 - RIWAYAT DONOR (hanya kegiatan yang benar-benar diikuti)
//   DATABASE : pendaftaran_donor WHERE status='selesai' JOIN jadwal_donor
//              (tidak butuh tabel baru)
//   KODE     : bagian Riwayat Donor sudah dihapus dari halaman_status; halaman_riwayat hanya mengalihkan ke Status
// ============================================================================
/** Kartu Riwayat Donor sudah dihapus; link lama diarahkan ke halaman Status Pendaftaran. */
function halaman_riwayat() { go('status'); }


// ============================================================================
// FITUR 12 - BERANDA & DASHBOARD PENDONOR
//   DATABASE : jadwal_donor, pendaftaran_donor, pendonor
//   KODE     : halaman_home, halaman_dashboard
// ============================================================================
function halaman_home() {
    if (me()) go('dashboard');
    head('Beranda'); ?>
    <div class="hero">
        <h1>Setetes Darah, Sejuta Harapan</h1>
        <p>Selamat datang di website SiDarah</p>
    </div>
    <?php foot();
}

/** Dashboard pendonor: ringkasan + jadwal mendatang. */
function halaman_dashboard() {
    $u = me();
    $p = pendonor_saya();
    head('Dashboard'); ?>
    <h1>Halo, <?= e($u['nama']) ?></h1>
    <?php if ($p): ?>
    <?php if (empty($p['golongan_darah'])): ?>
    <div class="alert a-warning">Golongan darah Anda belum diisi. <a href="<?= FILE ?>?page=profil">Lengkapi di Profil</a>.</div>
    <?php endif; ?>
    <div class="grid">
        <div class="stat"><b><?= $p['golongan_darah'] ? e($p['golongan_darah'] . $p['rhesus']) : '-' ?></b>Golongan darah Anda</div>
        <div class="stat"><b><?= hitung("SELECT COUNT(*) c FROM pendaftaran_donor WHERE id_pendonor=? AND status='selesai'", 'i', [$p['id_pendonor']]) ?></b>Donor yang pernah diikuti</div>
        <a class="stat" href="<?= FILE ?>?page=status"><b><?= hitung("SELECT COUNT(*) c FROM pendaftaran_donor WHERE id_pendonor=? AND status IN ('menunggu','diterima')", 'i', [$p['id_pendonor']]) ?></b>Pendaftaran aktif</a>
    </div>
    <div class="card" id="jadwal-mendatang"><h2>Jadwal Donor Mendatang</h2>
        <?php tabel_jadwal(jadwal_mendatang($p['id_pendonor'], 5), 'info'); ?>
        <p><a class="btn" href="<?= FILE ?>?page=pendaftaran">Daftar Donor</a></p></div>
    <?php else: ?><div class="alert a-warning">Data pendonor untuk akun ini tidak ditemukan.</div><?php endif;
    foot();
}

// ############################################################################
// ROUTER sisi pendonor
// ############################################################################
jalankan(
    [   // AKSI (form POST)
        'login'    => ['aksi_login',    null],
        'register' => ['aksi_register', null],
        'daftar'   => ['aksi_daftar',   'login'],
        'batal'    => ['aksi_batal',    'login'],
        'profil'   => ['aksi_profil',   'login'],
    ],
    [   // HALAMAN (?page=)
        'home'      => ['halaman_home',      null],
        'login'     => ['halaman_login',     null],
        'register'  => ['halaman_register',  null],
        'syarat'    => ['halaman_syarat',    null],
        'logout'    => ['halaman_logout',    null],
        'dashboard' => ['halaman_dashboard', 'login'],
        'profil'    => ['halaman_profil',    'login'],
        'pendaftaran' => ['halaman_pendaftaran', 'login'],
        'status'      => ['halaman_status',      'login'],
        'jadwal'      => ['halaman_jadwal_saya', 'login'],
        'riwayat'     => ['halaman_riwayat',     'login'],
    ]
);