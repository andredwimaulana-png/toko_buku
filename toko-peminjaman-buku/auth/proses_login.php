<?php
session_start();
include '../config/koneksi.php';

$email = $_POST['email'];
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    if(password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        
        if($user['role'] == 'admin') {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: ../user/dashboard.php');
        }
    } else {
        echo "<script>alert('Password salah!'); window.location='login.php';</script>";
    }
} else {
    echo "<script>alert('Email tidak ditemukan!'); window.location='login.php';</script>";
}
?>