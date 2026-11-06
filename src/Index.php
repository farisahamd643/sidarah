<?php
require_once __DIR__ . '/includes/functions.php';

// Dukungan kompatibilitas route legacy (?page=...)
if (isset($_GET['page'])) {
    $p = $_GET['page'];
    $redirects = [
        'login'       => 'login.php',
        'register'    => 'register.php',
        'syarat'      => 'syarat.php',
        'dashboard'   => 'dashboard.php',
        'pendaftaran' => 'pendaftaran.php',
        'status'      => 'status.php',
        'jadwal'      => 'jadwal.php',
        'profil'      => 'profil.php',
        'logout'      => 'logout.php',
    ];
    if (isset($redirects[$p])) {
        redirect($redirects[$p]);
    }
}

$u = me();
if ($u) {
    if ($u['role'] === 'pendonor') {
        redirect('dashboard.php');
    } else {
        redirect('admin/index.php');
    }
}

$title = 'Beranda';
require_once __DIR__ . '/includes/header.php';
?>
<div class="hero">
    <h1>Setetes Darah, Sejuta Harapan</h1>
    <p>Selamat datang di website SiDarah – Sistem Informasi Donor Darah</p>
    <div style="margin-top:1.5rem">
        <a class="btn" href="register.php">Daftar Jadi Pendonor</a>
        <a class="btn o" href="login.php" style="margin-left:.5rem">Masuk ke Akun</a>
    </div>
</div>

<div class="card">
    <h2>Jadwal Donor Darah Mendatang</h2>
    <p class="muted">Berikut kegiatan donor darah terdekat yang dapat Anda ikuti.</p>
    <?php tabel_jadwal(jadwal_mendatang(0, 5), 'publik'); ?>
</div>
<?php
require_once __DIR__ . '/includes/footer.php';
