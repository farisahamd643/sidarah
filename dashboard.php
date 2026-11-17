<?php
require_once __DIR__ . '/includes/functions.php';

need_pendonor('login.php');

$u = me();
$p = pendonor_saya();

$title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>
<h1>Halo, <?= e($u['nama']) ?></h1>
<?php if ($p): ?>
    <?php if (empty($p['golongan_darah'])): ?>
        <div class="alert a-warning">Golongan darah Anda belum diisi. <a href="profil.php">Lengkapi di Profil</a>.</div>
    <?php endif; ?>
    <div class="grid">
        <div class="stat">
            <b><?= $p['golongan_darah'] ? e($p['golongan_darah'] . $p['rhesus']) : '-' ?></b>
            Golongan darah Anda
        </div>
        <div class="stat">
            <b><?= hitung("SELECT COUNT(*) c FROM pendaftaran_donor WHERE id_pendonor=? AND status='selesai'", 'i', [$p['id_pendonor']]) ?></b>
            Donor yang pernah diikuti
        </div>
        <a class="stat" href="status.php">
            <b><?= hitung("SELECT COUNT(*) c FROM pendaftaran_donor WHERE id_pendonor=? AND status IN ('menunggu','diterima')", 'i', [$p['id_pendonor']]) ?></b>
            Pendaftaran aktif
        </a>
    </div>

    <div class="card" id="jadwal-mendatang">
        <h2>Jadwal Donor Mendatang</h2>
        <?php tabel_jadwal(jadwal_mendatang($p['id_pendonor'], 5), 'info'); ?>
        <p style="margin-top:1rem"><a class="btn" href="pendaftaran.php">Daftar Donor</a></p>
    </div>
<?php else: ?>
    <div class="alert a-warning">Data pendonor untuk akun ini tidak ditemukan.</div>
<?php endif; ?>

<?php
require_once __DIR__ . '/includes/footer.php';
