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
    
    /* Pilihan lokasi, tanggal, jam */
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

    @media(max-width:700px){.burger{display:block}nav{display:none;width:100%}nav.open{display:block}nav a{display:block;margin:.2rem 0}}
    </style>
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
