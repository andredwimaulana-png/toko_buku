<?php
include '../config/koneksi.php';

$nama = $_POST['nama'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Cek email sudah terdaftar
$cek = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
if(mysqli_num_rows($cek) > 0) {
    echo "<script>alert('Email sudah terdaftar!'); window.location='register.php';</script>";
    exit();
}

$query = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', 'siswa')";

if(mysqli_query($conn, $query)) {
    echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
} else {
    echo "Gagal daftar: " . mysqli_error($conn);
}
?>