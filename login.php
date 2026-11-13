<?php
require_once __DIR__ . '/includes/functions.php';

$u = me();
if ($u) {
    if ($u['role'] === 'pendonor') {
        redirect('dashboard.php');
    } else {
        redirect('admin/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $user = row('SELECT * FROM users WHERE email=?', 's', [$email]);
    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] !== 'pendonor') {
            flash('danger', 'Akun ini adalah akun admin. Silakan login di halaman admin.');
            redirect('admin/login.php');
        }
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'   => (int)$user['id_user'],
            'nama' => $user['nama'],
            'role' => $user['role'],
        ];
        redirect('dashboard.php');
    }
    flash('danger', 'Email atau password salah.');
    redirect('login.php');
}

$title = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<div class="card form auth">
    <h1>Login Pendonor</h1>
    <form method="post">
        <?= csrf() ?>
        <label>Email</label>
        <?= fld_buka('surat') ?>
        <input type="email" name="email" required autofocus placeholder="Masukkan email" value="<?= fv([], 'email') ?>">
        <?= fld_tutup() ?>

        <label>Password</label>
        <?= fld_buka('kunci', 'pw') ?>
        <input type="password" id="pw-login" name="password" required placeholder="Masukkan password">
        <?= tombol_mata('pw-login') ?>
        <?= fld_tutup() ?>

        <button class="btn blok">Masuk</button>
    </form>
    <p class="tautan muted">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</div>
<?php
require_once __DIR__ . '/includes/footer.php';
