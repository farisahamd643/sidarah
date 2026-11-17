<?php
if (!defined('APP_INIT')) {
    require_once __DIR__ . '/functions.php';
}
$u  = me();
$f  = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$currentPage = basename($_SERVER['PHP_SELF']);

function nav_active($page, $current) {
    return ($page === $current) ? 'on' : '';
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'SiDarah') ?> - SiDarah</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="top">
    <a class="brand" href="index.php">🩸 SiDarah</a>
    <button id="menuBtn" class="burger" aria-label="Menu">☰</button>
    <nav id="nav">
    <?php if (!$u): ?>
        <a class="<?= nav_active('index.php', $currentPage) ?>" href="index.php">Beranda</a>
        <a class="<?= nav_active('login.php', $currentPage) ?>" href="login.php">Login</a>
        <a class="<?= nav_active('register.php', $currentPage) ?>" href="register.php">Daftar</a>
    <?php elseif ($u['role'] === 'pendonor'): ?>
        <a class="<?= nav_active('dashboard.php', $currentPage) ?>" href="dashboard.php">Dashboard</a>
        <a class="<?= nav_active('pendaftaran.php', $currentPage) ?>" href="pendaftaran.php">Pendaftaran</a>
        <a class="<?= nav_active('status.php', $currentPage) ?>" href="status.php">Status Pendaftaran</a>
        <a class="<?= nav_active('jadwal.php', $currentPage) ?>" href="jadwal.php">Jadwal Saya</a>
        <a class="<?= nav_active('profil.php', $currentPage) ?>" href="profil.php">Profil</a>
        <a href="logout.php">Keluar</a>
    <?php else: ?>
        <a href="admin/index.php">Panel Admin</a>
        <a href="logout.php">Keluar</a>
    <?php endif; ?>
    </nav>
</header>
<main class="wrap">
<?php if ($f): ?>
    <div class="alert a-<?= e($f[0]) ?>"><?= e($f[1]) ?></div>
<?php endif; ?>
