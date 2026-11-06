<?php
/**
 * ============================================================================
 *  SiDarah - Halaman ADMIN
 * ============================================================================
 *  Lokasi : C:\xampp\htdocs\sidarah\admin.php
 *  Buka   : http://localhost/sidarah/admin.php   (admin pertama: admin.php?page=setup)
 *  Bergantung pada inti.php (satu folder) dan database/sidarah.sql.
 *
 *  FITUR DI FILE INI:
 *    1 Login admin (+ pembuat admin pertama) | 3 Tambah | 4 Lihat | 5 Edit | 6 Hapus pendonor
 *    7 Jadwal donor | 10 Pencarian & filter | 11 Status pendaftaran
 *    12 Dashboard admin | 14 Informasi golongan darah
 *    (Login, Profil, Jadwal mendatang: lihat inti.php)
 * ============================================================================
 */
define('PANEL', 'admin');
define('FILE', 'admin.php');
require __DIR__ . '/inti.php';

// ============================================================================
// FITUR 1 (khusus admin) - PEMBUAT AKUN ADMIN PERTAMA & BERANDA
//   DATABASE : users (role='admin')
//   KODE     : halaman_setup, halaman_beranda
// ============================================================================
/** Membuat akun admin pertama (hanya jika belum ada admin). */
function halaman_setup() {
    if (row("SELECT 1 FROM users WHERE role='admin' LIMIT 1")) {
        flash('warning', 'Akun admin sudah ada.');
    } else {
        q("INSERT INTO users (nama,email,password,role) VALUES ('Administrator','admin@sidarah.test',?, 'admin')", 's',
          [password_hash('admin123', PASSWORD_DEFAULT)]);
        flash('success', 'Admin dibuat: admin@sidarah.test / admin123. Segera ganti password di menu Profil.');
    }
    go('login');
}

/** Alamat admin.php tanpa ?page= : menuju dashboard bila sudah login, bila belum ke login. */
function halaman_beranda() { go(me() ? 'dashboard' : 'login'); }

// ============================================================================
// FITUR 3 - TAMBAH DATA PENDONOR (admin)
//   DATABASE : pendonor (nama_lengkap, golongan_darah, rhesus, jenis_kelamin,
//              tanggal_lahir, no_hp, alamat) - id_user dibiarkan NULL
//   KODE     : pendonor_tambah, aksi_pendonor_simpan, halaman_pendonor_form
// FITUR 5 - EDIT DATA PENDONOR (admin)
//   DATABASE : pendonor (semua kolom di atas), users.nama bila pendonor punya akun
//   KODE     : pendonor_ubah, aksi_pendonor_simpan, halaman_pendonor_form
// ============================================================================
function pendonor_tambah($d) {
    q('INSERT INTO pendonor (nama_lengkap,nik,golongan_darah,rhesus,jenis_kelamin,tanggal_lahir,no_hp,alamat) VALUES (?,?,?,?,?,?,?,?)',
      'ssssssss', [$d['nama'], $d['nik'], $d['gol'], $d['rh'], $d['jk'], $d['lahir'], $d['hp'], $d['alamat']]);
}

function pendonor_ubah($id, $d) {
    q('UPDATE pendonor SET nama_lengkap=?,nik=?,golongan_darah=?,rhesus=?,jenis_kelamin=?,tanggal_lahir=?,no_hp=?,alamat=? WHERE id_pendonor=?',
      'ssssssssi', [$d['nama'], $d['nik'], $d['gol'], $d['rh'], $d['jk'], $d['lahir'], $d['hp'], $d['alamat'], $id]);
    q('UPDATE users u JOIN pendonor p ON p.id_user=u.id_user SET u.nama=? WHERE p.id_pendonor=?', 'si', [$d['nama'], $id]);
}

function aksi_pendonor_simpan() {
    $id = (int)($_POST['id'] ?? 0);
    [$d, $err] = baca_pendonor('admin', $id);
    if ($err) gagal($err, 'pendonor_form', $id ? '&id=' . $id : '');
    if ($id) { pendonor_ubah($id, $d);  flash('success', 'Data pendonor diperbarui.'); }
    else     { pendonor_tambah($d);     flash('success', 'Pendonor baru ditambahkan.'); }
    go('pendonor');
}

function halaman_pendonor_form() {
    $id = (int)($_GET['id'] ?? 0);
    $x  = $id ? row('SELECT * FROM pendonor WHERE id_pendonor=?', 'i', [$id]) : [];
    if ($id && !$x) { flash('danger', 'Pendonor tidak ditemukan.'); go('pendonor'); }
    head($id ? 'Edit Pendonor' : 'Tambah Pendonor'); ?>
    <div class="card form auth"><h1><?= $id ? 'Edit' : 'Tambah' ?> Pendonor</h1>
    <form method="post"><?= csrf() ?><input type="hidden" name="aksi" value="pendonor_simpan"><input type="hidden" name="id" value="<?= $id ?>">
        <?php form_data_diri($x, '', false);
              echo '<h2>Data Donor</h2>';
              form_golongan($x); ?>
        <button class="btn">Simpan</button> <a class="btn o" href="<?= FILE ?>?page=pendonor">Batal</a>
    </form></div>
    <?php foot();
}


// ============================================================================
// FITUR 4  - LIHAT DATA PENDONOR (daftar + detail)   [admin]
// FITUR 10 - PENCARIAN & FILTER PENDONOR (nama, golongan darah, rhesus)
//   DATABASE : pendonor (nama_lengkap LIKE, golongan_darah, rhesus), users.email,
//              pendaftaran_donor + jadwal_donor (pada halaman detail)
//   KODE     : cari_pendonor, halaman_pendonor, halaman_pendonor_detail
// ============================================================================
function cari_pendonor($cari, $gol, $rh) {
    $sql = 'SELECT * FROM pendonor WHERE 1=1'; $t = ''; $p = [];
    if ($cari !== '')                        { $sql .= ' AND nama_lengkap LIKE ?'; $t .= 's'; $p[] = '%' . $cari . '%'; }
    if ($gol === 'kosong')                   { $sql .= ' AND golongan_darah IS NULL'; }
    elseif (in_array($gol, GOLONGAN, true))  { $sql .= ' AND golongan_darah=?';    $t .= 's'; $p[] = $gol; }
    if (in_array($rh, RHESUS, true))         { $sql .= ' AND rhesus=?';            $t .= 's'; $p[] = $rh; }
    return rows($sql . ' ORDER BY nama_lengkap', $t, $p);
}

function halaman_pendonor() {
    $cari = trim($_GET['cari'] ?? '');
    $gol  = $_GET['gol'] ?? '';
    $rh   = $_GET['rh'] ?? '';
    $list = cari_pendonor($cari, $gol, $rh);
    head('Data Pendonor'); ?>
    <h1>Data Pendonor</h1>
    <div class="card">
    <form method="get" class="bar"><input type="hidden" name="page" value="pendonor">
        <div><label>Cari nama</label><input name="cari" value="<?= e($cari) ?>" placeholder="Ketik nama..."></div>
        <div><label>Golongan darah</label><select name="gol"><option value="">Semua</option>
            <?php foreach (GOLONGAN as $x): ?><option <?= $gol === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?>
            <option value="kosong" <?= $gol === 'kosong' ? 'selected' : '' ?>>Belum diisi</option></select></div>
        <div><label>Rhesus</label><select name="rh"><option value="">Semua</option>
            <?php foreach (RHESUS as $x): ?><option <?= $rh === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></div>
        <button class="btn">Cari</button>
        <a class="btn o" href="<?= FILE ?>?page=pendonor_form">+ Tambah Pendonor</a>
    </form>
    <p class="muted"><?= count($list) ?> pendonor ditemukan.</p>
    <div class="tw"><table>
        <tr><th>Nama</th><th>Gol.</th><th>JK</th><th>Tgl lahir</th><th>No. HP</th><th>Aksi</th></tr>
        <?php foreach ($list as $x): ?>
        <tr><td><?= e($x['nama_lengkap']) ?></td><td><?= label_gol($x) ?></td>
            <td><?= e($x['jenis_kelamin']) ?></td><td><?= tgl($x['tanggal_lahir']) ?></td><td><?= e($x['no_hp']) ?></td>
            <td><a class="btn o s" href="<?= FILE ?>?page=pendonor_detail&id=<?= (int)$x['id_pendonor'] ?>">Detail</a>
                <a class="btn o s" href="<?= FILE ?>?page=pendonor_form&id=<?= (int)$x['id_pendonor'] ?>">Edit</a>
                <form method="post" class="inline" data-confirm="Hapus pendonor ini? Akun dan pendaftarannya ikut terhapus."><?= csrf() ?>
                    <input type="hidden" name="aksi" value="pendonor_hapus"><input type="hidden" name="id" value="<?= (int)$x['id_pendonor'] ?>">
                    <button class="btn s">Hapus</button></form></td></tr>
        <?php endforeach; if (!$list): ?><tr><td colspan="6" class="muted">Data tidak ditemukan.</td></tr><?php endif; ?>
    </table></div></div>
    <?php foot();
}

function halaman_pendonor_detail() {
    $x = row('SELECT p.*,u.email FROM pendonor p LEFT JOIN users u ON u.id_user=p.id_user WHERE p.id_pendonor=?', 'i', [(int)($_GET['id'] ?? 0)]);
    if (!$x) { flash('danger', 'Pendonor tidak ditemukan.'); go('pendonor'); }
    $riw = rows('SELECT d.status,j.judul,j.tanggal,j.lokasi FROM pendaftaran_donor d JOIN jadwal_donor j ON j.id_jadwal=d.id_jadwal WHERE d.id_pendonor=? ORDER BY j.tanggal DESC', 'i', [$x['id_pendonor']]);
    head('Detail Pendonor'); ?>
    <h1><?= e($x['nama_lengkap']) ?></h1>
    <div class="card">
        <p><b>NIK:</b> <?= e($x['nik'] ?: '-') ?></p>
        <p><b>Golongan darah:</b> <?= $x['golongan_darah'] ? label_gol($x) : 'Belum diisi' ?></p>
        <p><b>Jenis kelamin:</b> <?= $x['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></p>
        <p><b>Tanggal lahir:</b> <?= tgl($x['tanggal_lahir']) ?></p>
        <p><b>No. HP:</b> <?= e($x['no_hp'] ?: '-') ?></p>
        <p><b>Email akun:</b> <?= e($x['email'] ?: '(belum punya akun)') ?></p>
        <p><b>Alamat:</b> <?= e($x['alamat'] ?: '-') ?></p>
        <a class="btn o" href="<?= FILE ?>?page=pendonor">← Kembali</a>
    </div>
    <div class="card"><h2>Pendaftaran Donor</h2><div class="tw"><table>
        <tr><th>Kegiatan</th><th>Tanggal</th><th>Lokasi</th><th>Status</th></tr>
        <?php foreach ($riw as $r): ?><tr><td><?= e($r['judul']) ?></td><td><?= tgl($r['tanggal']) ?></td><td><?= e($r['lokasi']) ?></td><td><?= badge($r['status']) ?></td></tr><?php endforeach; ?>
        <?php if (!$riw): ?><tr><td colspan="4" class="muted">Belum ada pendaftaran.</td></tr><?php endif; ?>
    </table></div></div>
    <?php foot();
}


// ============================================================================
// FITUR 6 - HAPUS DATA PENDONOR (admin, dengan konfirmasi)
//   DATABASE : pendonor dihapus; akun users terkait ikut dihapus;
//              pendaftaran_donor ikut terhapus otomatis (ON DELETE CASCADE)
//   KODE     : aksi_pendonor_hapus (konfirmasi: atribut data-confirm + JS di foot())
// ============================================================================
function aksi_pendonor_hapus() {
    $id = (int)($_POST['id'] ?? 0);
    $p  = row('SELECT id_user FROM pendonor WHERE id_pendonor=?', 'i', [$id]);
    if ($p) {
        q('DELETE FROM pendonor WHERE id_pendonor=?', 'i', [$id]);
        if ($p['id_user']) q("DELETE FROM users WHERE id_user=? AND role='pendonor'", 'i', [$p['id_user']]);
        flash('success', 'Pendonor dihapus.');
    }
    go('pendonor');
}


// ============================================================================
// FITUR 7 - DATA JADWAL DONOR (admin: tambah, lihat, ubah, hapus)
//   DATABASE : jadwal_donor (judul, tanggal, jam_mulai, jam_selesai, lokasi,
//              keterangan, kuota)
//   KODE     : aksi_jadwal_simpan, aksi_jadwal_hapus, halaman_jadwal, halaman_jadwal_form
// ============================================================================
function aksi_jadwal_simpan() {
    $id      = (int)($_POST['id'] ?? 0);
    $judul   = trim($_POST['judul'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $mulai   = $_POST['jam_mulai'] ?? '';
    $selesai = $_POST['jam_selesai'] ?? '';
    $lokasi  = trim($_POST['lokasi'] ?? '');
    $ket     = trim($_POST['keterangan'] ?? '');
    $kuota   = (int)($_POST['kuota'] ?? 0);

    $err = [];
    if ($judul === '') $err[] = 'Judul wajib diisi.';
    if (!tgl_valid($tanggal)) $err[] = 'Tanggal tidak valid.';
    if (!preg_match('/^\d{2}:\d{2}$/', $mulai) || !preg_match('/^\d{2}:\d{2}$/', $selesai)) $err[] = 'Jam tidak valid.';
    elseif ($selesai <= $mulai) $err[] = 'Jam selesai harus setelah jam mulai.';
    if ($lokasi === '') $err[] = 'Lokasi wajib diisi.';
    if ($kuota < 1) $err[] = 'Kuota minimal 1.';
    if ($err) gagal($err, 'jadwal_form', $id ? '&id=' . $id : '');

    if ($id) {
        q('UPDATE jadwal_donor SET judul=?,tanggal=?,jam_mulai=?,jam_selesai=?,lokasi=?,keterangan=?,kuota=? WHERE id_jadwal=?',
          'ssssssii', [$judul, $tanggal, $mulai, $selesai, $lokasi, $ket, $kuota, $id]);
        flash('success', 'Jadwal diperbarui.');
    } else {
        q('INSERT INTO jadwal_donor (judul,tanggal,jam_mulai,jam_selesai,lokasi,keterangan,kuota) VALUES (?,?,?,?,?,?,?)',
          'ssssssi', [$judul, $tanggal, $mulai, $selesai, $lokasi, $ket, $kuota]);
        flash('success', 'Jadwal ditambahkan.');
    }
    go('jadwal');
}

function aksi_jadwal_hapus() {
    q('DELETE FROM jadwal_donor WHERE id_jadwal=?', 'i', [(int)($_POST['id'] ?? 0)]);
    flash('success', 'Jadwal dihapus (pendaftaran pada jadwal itu ikut terhapus).');
    go('jadwal');
}

function halaman_jadwal() {
    $list = rows("SELECT j.*,(SELECT COUNT(*) FROM pendaftaran_donor x WHERE x.id_jadwal=j.id_jadwal AND x.status<>'dibatalkan') terisi
                  FROM jadwal_donor j ORDER BY j.tanggal DESC,j.jam_mulai");
    head('Jadwal Donor'); ?>
    <h1>Jadwal Donor</h1>
    <div class="card"><p><a class="btn" href="<?= FILE ?>?page=jadwal_form">+ Tambah Jadwal</a></p>
    <div class="tw"><table>
        <tr><th>Kegiatan</th><th>Tanggal</th><th>Jam</th><th>Lokasi</th><th>Terisi</th><th>Aksi</th></tr>
        <?php foreach ($list as $j): ?>
        <tr><td><?= e($j['judul']) ?></td><td><?= tgl($j['tanggal']) ?></td><td><?= jam($j['jam_mulai']) ?>–<?= jam($j['jam_selesai']) ?></td>
            <td><?= e($j['lokasi']) ?></td><td><?= (int)$j['terisi'] ?>/<?= (int)$j['kuota'] ?></td>
            <td><a class="btn o s" href="<?= FILE ?>?page=jadwal_form&id=<?= (int)$j['id_jadwal'] ?>">Edit</a>
                <form method="post" class="inline" data-confirm="Hapus jadwal ini? Pendaftaran pada jadwal ini ikut terhapus."><?= csrf() ?>
                    <input type="hidden" name="aksi" value="jadwal_hapus"><input type="hidden" name="id" value="<?= (int)$j['id_jadwal'] ?>">
                    <button class="btn s">Hapus</button></form></td></tr>
        <?php endforeach; if (!$list): ?><tr><td colspan="6" class="muted">Belum ada jadwal.</td></tr><?php endif; ?>
    </table></div></div>
    <?php foot();
}

function halaman_jadwal_form() {
    $id = (int)($_GET['id'] ?? 0);
    $x  = $id ? row('SELECT * FROM jadwal_donor WHERE id_jadwal=?', 'i', [$id]) : ['kuota' => 50];
    if ($id && !$x) { flash('danger', 'Jadwal tidak ditemukan.'); go('jadwal'); }
    if ($id) { $x['jam_mulai'] = jam($x['jam_mulai']); $x['jam_selesai'] = jam($x['jam_selesai']); }
    head($id ? 'Edit Jadwal' : 'Tambah Jadwal'); ?>
    <div class="card form"><h1><?= $id ? 'Edit' : 'Tambah' ?> Jadwal Donor</h1>
    <form method="post"><?= csrf() ?><input type="hidden" name="aksi" value="jadwal_simpan"><input type="hidden" name="id" value="<?= $id ?>">
        <label>Judul kegiatan</label><input name="judul" required maxlength="150" value="<?= fv($x, 'judul') ?>">
        <label>Tanggal</label><input type="date" name="tanggal" required value="<?= fv($x, 'tanggal') ?>">
        <div class="row">
            <div><label>Jam mulai</label><input type="time" name="jam_mulai" required value="<?= fv($x, 'jam_mulai') ?>"></div>
            <div><label>Jam selesai</label><input type="time" name="jam_selesai" required value="<?= fv($x, 'jam_selesai') ?>"></div>
            <div><label>Kuota</label><input type="number" name="kuota" min="1" required value="<?= fv($x, 'kuota') ?>"></div>
        </div>
        <label>Lokasi</label><input name="lokasi" required maxlength="200" value="<?= fv($x, 'lokasi') ?>">
        <label>Keterangan</label><textarea name="keterangan" rows="3"><?= fv($x, 'keterangan') ?></textarea>
        <button class="btn">Simpan</button> <a class="btn o" href="<?= FILE ?>?page=jadwal">Batal</a>
    </form></div>
    <?php foot();
}


// ============================================================================
// FITUR 11 - STATUS PENDAFTARAN DONOR (admin mengubah, pendonor melihat)
//   DATABASE : pendaftaran_donor.status ENUM(menunggu, diterima, dibatalkan, selesai)
//   KODE     : aksi_status, halaman_pendaftaran   (sisi pendonor: halaman_status di index.php)
// ============================================================================
function aksi_status() {
    $st = $_POST['status'] ?? '';
    if (in_array($st, STATUS_DONOR, true)) {
        q('UPDATE pendaftaran_donor SET status=? WHERE id_pendaftaran=?', 'si', [$st, (int)($_POST['id'] ?? 0)]);
        flash('success', 'Status pendaftaran diperbarui.');
    }
    go('pendaftaran');
}

function halaman_pendaftaran() {
    $s   = $_GET['s'] ?? '';
    $sql = 'SELECT d.*,p.nama_lengkap,p.golongan_darah,p.rhesus,j.judul,j.tanggal FROM pendaftaran_donor d
            JOIN pendonor p ON p.id_pendonor=d.id_pendonor JOIN jadwal_donor j ON j.id_jadwal=d.id_jadwal';
    $t = ''; $pr = [];
    if (in_array($s, STATUS_DONOR, true)) { $sql .= ' WHERE d.status=?'; $t = 's'; $pr[] = $s; }
    $list = rows($sql . ' ORDER BY j.tanggal DESC,d.tanggal_daftar DESC', $t, $pr);
    head('Pendaftaran Donor'); ?>
    <h1>Pendaftaran Donor</h1>
    <div class="card">
    <form method="get" class="bar"><input type="hidden" name="page" value="pendaftaran">
        <div><label>Filter status</label><select name="s"><option value="">Semua</option>
            <?php foreach (STATUS_DONOR as $x): ?><option value="<?= $x ?>" <?= $s === $x ? 'selected' : '' ?>><?= e(label_status($x)) ?></option><?php endforeach; ?></select></div>
        <button class="btn">Terapkan</button>
    </form>
    <div class="tw"><table>
        <tr><th>Pendonor</th><th>Gol.</th><th>Kegiatan</th><th>Tanggal</th><th>Status</th><th>Ubah status</th></tr>
        <?php foreach ($list as $d): ?>
        <tr><td><?= e($d['nama_lengkap']) ?></td><td><?= label_gol($d) ?></td>
            <td><?= e($d['judul']) ?></td><td><?= tgl($d['tanggal']) ?></td><td><?= badge($d['status']) ?></td>
            <td><form method="post" class="inline"><?= csrf() ?><input type="hidden" name="aksi" value="status"><input type="hidden" name="id" value="<?= (int)$d['id_pendaftaran'] ?>">
                <select name="status" style="width:auto">
                    <?php foreach (STATUS_DONOR as $x): ?><option value="<?= $x ?>" <?= $d['status'] === $x ? 'selected' : '' ?>><?= e(label_status($x)) ?></option><?php endforeach; ?>
                </select> <button class="btn s">Simpan</button></form></td></tr>
        <?php endforeach; if (!$list): ?><tr><td colspan="6" class="muted">Belum ada pendaftaran.</td></tr><?php endif; ?>
    </table></div>
    <p class="muted"><b>Terdaftar</b> = pendaftaran baru yang belum dikonfirmasi. Ubah ke <b>Diterima</b> bila disetujui, dan ke <b>Selesai</b> setelah pendonor benar-benar mengikuti kegiatan; hanya status Selesai yang masuk Riwayat Donor.</p>
    </div>
    <?php foot();
}


// ============================================================================
// FITUR 12 (admin) - DASHBOARD ADMIN
//   DATABASE : pendonor, jadwal_donor, pendaftaran_donor
//   KODE     : halaman_dashboard
// ============================================================================
/** Dashboard admin: statistik, jadwal mendatang, sebaran golongan darah (Fitur 14). */
function halaman_dashboard() {
    $u = me();
    $gdist = rows('SELECT golongan_darah,rhesus,COUNT(*) c FROM pendonor GROUP BY golongan_darah,rhesus ORDER BY golongan_darah,rhesus');
    head('Dashboard Admin'); ?>
    <h1>Halo, <?= e($u['nama']) ?></h1>
    <div class="grid">
        <div class="stat"><b><?= hitung('SELECT COUNT(*) c FROM pendonor') ?></b>Total pendonor</div>
        <div class="stat"><b><?= hitung('SELECT COUNT(*) c FROM jadwal_donor WHERE tanggal>=CURDATE()') ?></b>Jadwal mendatang</div>
        <div class="stat"><b><?= hitung("SELECT COUNT(*) c FROM pendaftaran_donor WHERE status='menunggu'") ?></b>Pendaftaran baru (Terdaftar)</div>
    </div>
    <div class="card"><h2>Jadwal Donor Mendatang</h2><?php tabel_jadwal(jadwal_mendatang(0, 5), 'info'); ?></div>
    <div class="card"><h2>Sebaran Golongan Darah</h2>
    <div class="tw"><table><tr><th>Golongan</th><th>Jumlah pendonor</th></tr>
        <?php foreach ($gdist as $g): ?><tr><td><?= $g['golongan_darah'] ? label_gol($g) : '<span class="muted">Belum diisi</span>' ?></td><td><?= (int)$g['c'] ?></td></tr><?php endforeach; ?>
        <?php if (!$gdist): ?><tr><td colspan="2" class="muted">Belum ada data pendonor.</td></tr><?php endif; ?>
    </table></div>
    <p><a class="btn o s" href="<?= FILE ?>?page=golongan">Lihat informasi golongan darah</a></p></div>
    <?php foot();
}

// ============================================================================
// FITUR 14 - INFORMASI GOLONGAN DARAH
//   DATABASE : pendonor.golongan_darah ENUM(A,B,AB,O) + pendonor.rhesus ENUM(+,-)
//   KODE     : form_golongan (input di Profil / form admin), label_gol (tampilan), halaman_golongan,
//              halaman_dashboard (sebaran). Klik kartu -> filter Fitur 10.
// ============================================================================
function halaman_golongan() {
    $jumlah = [];
    foreach (rows('SELECT golongan_darah g, rhesus r, COUNT(*) c FROM pendonor WHERE golongan_darah IS NOT NULL GROUP BY golongan_darah, rhesus') as $x) {
        $jumlah[$x['g'] . $x['r']] = (int)$x['c'];
    }
    $kosong = hitung('SELECT COUNT(*) c FROM pendonor WHERE golongan_darah IS NULL');
    $kombinasi = [];
    foreach (GOLONGAN as $g) foreach (RHESUS as $r) $kombinasi[] = [$g, $r];
    head('Informasi Golongan Darah'); ?>
    <h1>Informasi Golongan Darah</h1>
    <p class="muted">Klik salah satu golongan untuk melihat daftar pendonornya.</p>
    <div class="grid">
        <?php foreach ($kombinasi as [$g, $r]): ?>
        <a class="stat" href="<?= FILE ?>?page=pendonor&amp;gol=<?= e($g) ?>&amp;rh=<?= urlencode($r) ?>">
            <b><?= e($g . $r) ?></b><?= $jumlah[$g . $r] ?? 0 ?> pendonor</a>
        <?php endforeach; ?>
        <a class="stat" href="<?= FILE ?>?page=pendonor&amp;gol=kosong">
            <b>-</b><?= $kosong ?> belum mengisi golongan darah</a>
    </div>
    <?php foot();
}


// ############################################################################
// ROUTER sisi admin
// ############################################################################
jalankan(
    [   // AKSI (form POST)
        'login'           => ['aksi_login',           null],
        'pendonor_simpan' => ['aksi_pendonor_simpan', 'login'],
        'pendonor_hapus'  => ['aksi_pendonor_hapus',  'login'],
        'jadwal_simpan'   => ['aksi_jadwal_simpan',   'login'],
        'jadwal_hapus'    => ['aksi_jadwal_hapus',    'login'],
        'status'          => ['aksi_status',          'login'],
        'profil'          => ['aksi_profil',          'login'],
    ],
    [   // HALAMAN (?page=)
        'home'            => ['halaman_beranda',         null],
        'login'           => ['halaman_login',           null],
        'logout'          => ['halaman_logout',          null],
        'setup'           => ['halaman_setup',           null],
        'dashboard'       => ['halaman_dashboard',       'login'],
        'profil'          => ['halaman_profil',          'login'],
        'pendonor'        => ['halaman_pendonor',        'login'],
        'pendonor_detail' => ['halaman_pendonor_detail', 'login'],
        'pendonor_form'   => ['halaman_pendonor_form',   'login'],
        'golongan'        => ['halaman_golongan',        'login'],
        'jadwal'          => ['halaman_jadwal',          'login'],
        'jadwal_form'     => ['halaman_jadwal_form',     'login'],
        'pendaftaran'     => ['halaman_pendaftaran',     'login'],
    ]
);