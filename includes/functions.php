<?php
/**
 * ============================================================================
 *  SiDarah - Fungsi Bantu & Helper
 * ============================================================================
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// ---- CSRF Protection --------------------------------------------------------
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

function csrf() {
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
            die('Formulir tidak valid atau sesi telah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.');
        }
    }
}

// ---- Auth & Sesi ------------------------------------------------------------
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function flash($type, $msg) {
    $_SESSION['flash'] = [$type, $msg];
}

function me() {
    return $_SESSION['user'] ?? null;
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function need_login($redirectUrl = 'login.php') {
    if (!me()) {
        flash('danger', 'Silakan login terlebih dahulu.');
        redirect($redirectUrl);
    }
}

function need_admin($redirectUrl = 'login.php') {
    need_login($redirectUrl);
    if (me()['role'] !== 'admin') {
        flash('danger', 'Akses ditolak. Halaman ini hanya untuk Administrator.');
        redirect($redirectUrl);
    }
}

function need_pendonor($redirectUrl = 'login.php') {
    need_login($redirectUrl);
    if (me()['role'] !== 'pendonor') {
        flash('danger', 'Akun Anda adalah Admin. Silakan akses panel admin.');
        redirect('admin/index.php');
    }
}

// ---- Database Helpers -------------------------------------------------------
function q($sql, $types = '', $params = []) {
    global $db;
    $st = $db->prepare($sql);
    if ($types !== '') {
        $st->bind_param($types, ...$params);
    }
    $st->execute();
    return $st;
}

function rows($sql, $types = '', $p = []) {
    return q($sql, $types, $p)->get_result()->fetch_all(MYSQLI_ASSOC);
}

function row($sql, $types = '', $p = []) {
    $r = rows($sql, $types, $p);
    return $r[0] ?? null;
}

function hitung($sql, $types = '', $p = []) {
    return (int)(row($sql, $types, $p)['c'] ?? 0);
}

function pendonor_saya() {
    $u = me();
    if (!$u) return null;
    return row('SELECT * FROM pendonor WHERE id_user=?', 'i', [$u['id']]);
}

// ---- Format Tampilan --------------------------------------------------------
function tgl($d) {
    if (!$d) return '-';
    $b = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($d);
    return date('j', $t) . ' ' . $b[(int)date('n', $t)] . ' ' . date('Y', $t);
}

function tgl_panjang($d) {
    if (!$d) return '-';
    static $b = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $t = strtotime($d);
    return date('j', $t) . ' ' . $b[(int)date('n', $t)] . ' ' . date('Y', $t);
}

function hari_nama($d) {
    static $h = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return $h[(int)date('w', strtotime($d))];
}

function jadwal_teks($d) {
    return hari_nama($d) . ', ' . tgl_panjang($d);
}

function jam($j) {
    return substr($j, 0, 5);
}

function jam_range($m, $s) {
    return str_replace(':', '.', jam($m)) . ' – ' . str_replace(':', '.', jam($s)) . ' WIB';
}

function panjang($s) {
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
}

function label_status($s) {
    return $s === 'menunggu' ? 'Terdaftar' : ucfirst($s);
}

function badge($s) {
    return '<span class="badge b-' . e($s) . '">' . e(label_status($s)) . '</span>';
}

function label_gol($p) {
    if (empty($p['golongan_darah'])) return '<span class="muted">-</span>';
    return '<span class="gol">' . e($p['golongan_darah'] . $p['rhesus']) . '</span>';
}

// ---- Validasi ---------------------------------------------------------------
function tgl_valid($d) {
    $x = DateTime::createFromFormat('Y-m-d', $d);
    return $x && $x->format('Y-m-d') === $d;
}

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

    if ($nik === '') {
        if ($mode !== 'admin') $err[] = 'NIK wajib diisi.';
        $d['nik'] = null;
    } elseif (!preg_match('/^[0-9]{16}$/', $nik)) {
        $err[] = 'NIK harus 16 digit angka.';
    } elseif (row('SELECT 1 FROM pendonor WHERE nik=? AND id_pendonor<>?', 'si', [$nik, (int)$idp])) {
        $err[] = 'NIK sudah terdaftar.';
    }

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

function gagal($err, $targetUrl) {
    $o = $_POST;
    unset($o['password'], $o['password2'], $o['password_lama'], $o['password_baru'], $o['csrf']);
    $_SESSION['old'] = $o;
    flash('danger', implode(' ', $err));
    redirect($targetUrl);
}

// ---- Komponen Formulir & UI -------------------------------------------------
function fv($v, $k) {
    return e($_SESSION['old'][$k] ?? ($v[$k] ?? ''));
}

function sel($v, $k, $val) {
    return (($_SESSION['old'][$k] ?? ($v[$k] ?? '')) === $val) ? 'selected' : '';
}

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
    return '<svg' . ($kelas !== '' ? ' class="' . $kelas . '"' : '') . ' viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="' . ($p[$nama] ?? '') . '"/></svg>';
}

function fld_buka($ikon, $extra = '') {
    return '<div class="fld ' . $extra . '">' . ikon($ikon);
}

function fld_tutup() {
    return '</div>';
}

function tombol_mata($id) {
    return '<button type="button" class="eye" data-target="' . e($id) . '" aria-label="Tampilkan atau sembunyikan password">' . ikon('mata', '') . '</button>';
}

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

function kartu_pilih($ikonHtml, $judul, $placeholder, $kontrolHtml) {
    return '<div class="pilih" data-ph="' . e($placeholder) . '">' . $ikonHtml
         . '<div class="pt"><b>' . e($judul) . '</b><span class="pv ph">' . e($placeholder) . '</span></div>'
         . '<svg class="pan" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>'
         . $kontrolHtml . '</div>';
}

function ikon_jam($kelas = '') {
    return '<svg' . ($kelas ? ' class="' . $kelas . '"' : '') . ' viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"/></svg>';
}

function pendaftaran_aktif($idp) {
    return row("SELECT d.id_pendaftaran,d.status,d.tanggal_daftar,j.tanggal,j.jam_mulai,j.jam_selesai,j.lokasi
                FROM pendaftaran_donor d JOIN jadwal_donor j ON j.id_jadwal=d.id_jadwal
                WHERE d.id_pendonor=? AND d.status IN ('menunggu','diterima') AND j.tanggal>=CURDATE()
                ORDER BY d.tanggal_daftar DESC, d.id_pendaftaran DESC LIMIT 1", 'i', [$idp]);
}

function jadwal_mendatang($idp = 0, $limit = 0) {
    $sql = "SELECT j.*,
              (SELECT COUNT(*) FROM pendaftaran_donor x WHERE x.id_jadwal=j.id_jadwal AND x.status<>'dibatalkan') AS terisi,
              (SELECT y.status FROM pendaftaran_donor y WHERE y.id_jadwal=j.id_jadwal AND y.id_pendonor=?) AS status_saya,
              (SELECT y.id_pendaftaran FROM pendaftaran_donor y WHERE y.id_jadwal=j.id_jadwal AND y.id_pendonor=?) AS id_pdf
            FROM jadwal_donor j WHERE j.tanggal>=CURDATE() ORDER BY j.tanggal, j.jam_mulai";
    if ($limit > 0) $sql .= ' LIMIT ' . (int)$limit;
    return rows($sql, 'ii', [$idp, $idp]);
}

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
