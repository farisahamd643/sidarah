<?php
unset($_SESSION['old']);
?>
</main>
<footer class="foot">
    © <?= date('Y') ?> SiDarah Admin – Sistem Informasi Donor Darah · <a href="../index.php">Halaman Pendonor</a>
</footer>
<script>
document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (ev) {
        if (!confirm(f.dataset.confirm)) {
            ev.preventDefault();
        }
    });
});

document.querySelectorAll('.eye').forEach(function (b) {
    b.addEventListener('click', function () {
        var i = document.getElementById(b.dataset.target);
        if (!i) return;
        var tampil = i.type === 'password';
        i.type = tampil ? 'text' : 'password';
        b.classList.toggle('on', tampil);
    });
});

var mb = document.getElementById('menuBtn');
if (mb) {
    mb.addEventListener('click', function () {
        document.getElementById('nav').classList.toggle('open');
    });
}
</script>
</body>
</html>
