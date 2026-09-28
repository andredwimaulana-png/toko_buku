<?php
$host = 'sql108.infinityfree.com';
$user = 'if0_42920925';
$pass = 'Andresaya3';
$db = 'if0_42920925_toko_buku';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>