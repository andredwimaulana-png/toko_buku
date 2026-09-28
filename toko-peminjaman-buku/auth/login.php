<?php
session_start();
include '../config/koneksi.php';

// Fungsi kecil untuk redirect sesuai role (dipakai 2x di file ini)
function redirectSesuaiRole($role) {
    if ($role == 'admin') {
        header('Location: ../admin/dashboard.php');
    } elseif ($role == 'petugas') {
        header('Location: ../petugas/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }
    exit();
}

// Cek jika sudah login
if (isset($_SESSION['user_id'])) {
    redirectSesuaiRole($_SESSION['role']);
}

// Logika Login
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $data = mysqli_fetch_assoc($query);

    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];

        redirectSesuaiRole($data['role']);
    } else {
        $error = "Login Gagal! Email atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Toko Peminjaman Buku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="logo-area">
                <h1>📚 Toko Peminjaman Buku</h1>
                <p>Login untuk meminjam buku</p>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login" class="btn-login">Login</button>
            </form>
            
            <p class="register-link">
                Belum punya akun? <a href="register.php">Daftar di sini</a>
            </p>
        </div>
    </div>
</body>
</html>