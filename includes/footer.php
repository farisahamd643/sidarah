<?php
unset($_SESSION['old']);
?>
</main>
<footer class="foot">
    © <?= date('Y') ?> SiDarah – Sistem Informasi Donor Darah · <a href="admin/login.php">Login Admin</a>
</footer>
<script>
// Konfirmasi sebelum form dikirim (mis. hapus / batal)
document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (ev) {
        if (!confirm(f.dataset.confirm)) {
            ev.preventDefault();
        }
    });
});

// Tombol mata: tampilkan / sembunyikan password
document.querySelectorAll('.eye').forEach(function (b) {
    b.addEventListener('click', function () {
        var i = document.getElementById(b.dataset.target);
        if (!i) return;
        var tampil = i.type === 'password';
        i.type = tampil ? 'text' : 'password';
        b.classList.toggle('on', tampil);
    });
});

// Tombol menu untuk layar HP
var mb = document.getElementById('menuBtn');
if (mb) {
    mb.addEventListener('click', function () {
        document.getElementById('nav').classList.toggle('open');
    });
}
</script>
</body>
</html>
