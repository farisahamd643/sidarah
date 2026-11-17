<?php


    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $kelamin = $_POST['kelamin'];
    $peminatan = $_POST['peminatan'];

    echo "<h2>Hasil Registrasi</h2>";
    echo "Nama : $nama <br>";
    echo "Alamat : $alamat <br>";
    echo "Jenis Kelamin : $kelamin <br>";
    echo "Peminatan : " . implode(" dan ", $peminatan);

?>

<h1>Form Registrasi</h1>

<form method="post">

Nama :
<input type="text" name="nama"><br><br>

Alamat :
<textarea name="alamat"></textarea><br><br>

Jenis Kelamin :
<input type="radio" name="kelamin" value="Laki-Laki"> Laki-laki
<input type="radio" name="kelamin" value="Perempuan"> Perempuan
<br><br>

Peminatan :<br>
<input type="checkbox" name="peminatan[]" value="RPL"> RPL<br>
<input type="checkbox" name="peminatan[]" value="AI"> AI<br>
<input type="checkbox" name="peminatan[]" value="DSS"> DSS<br>
<input type="checkbox" name="peminatan[]" value="Matematika"> Matematika<br><br>

<input type="submit" name="submit" value="Submit">

</form>

<?php
}
?>