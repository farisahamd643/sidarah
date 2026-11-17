<?php
require_once __DIR__ . '/../../includes/functions.php';

$u  = me();
$f  = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$currentPage = basename($_SERVER['PHP_SELF']);

function admin_nav_active($page, $current) {
    return ($page === $current) ? 'on' : '';
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Admin') ?> - SiDarah Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="top">
    <a class="brand" href="index.php">🩸 SiDarah Admin</a>
    <button id="menuBtn" class="burger" aria-label="Menu">☰</button>
    <nav id="nav">
    <?php if (!$u || $u['role'] !== 'admin'): ?>
        <a class="<?= admin_nav_active('login.php', $currentPage) ?>" href="login.php">Login Admin</a>
        <a href="../index.php">Halaman Pendonor</a>
    <?php else: ?>
        <a class="<?= admin_nav_active('index.php', $currentPage) ?>" href="index.php">Dashboard</a>
        <a class="<?= admin_nav_active('pendonor.php', $currentPage) || admin_nav_active('pendonor_form.php', $currentPage) || admin_nav_active('pendonor_detail.php', $currentPage) ? 'on' : '' ?>" href="pendonor.php">Data Pendonor</a>
        <a class="<?= admin_nav_active('golongan.php', $currentPage) ?>" href="golongan.php">Golongan Darah</a>
        <a class="<?= admin_nav_active('jadwal.php', $currentPage) || admin_nav_active('jadwal_form.php', $currentPage) ? 'on' : '' ?>" href="jadwal.php">Jadwal Donor</a>
        <a class="<?= admin_nav_active('pendaftaran.php', $currentPage) ?>" href="pendaftaran.php">Pendaftaran</a>
        <a class="<?= admin_nav_active('profil.php', $currentPage) ?>" href="profil.php">Profil</a>
        <a href="logout.php">Keluar</a>
    <?php endif; ?>
    </nav>
</header>
<main class="wrap">
<?php if ($f): ?>
    <div class="alert a-<?= e($f[0]) ?>"><?= e($f[1]) ?></div>
<?php endif; ?>
