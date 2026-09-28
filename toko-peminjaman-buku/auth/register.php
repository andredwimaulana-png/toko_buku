<?php
session_start();
include '../config/koneksi.php';

// Cek jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }
    exit();
}

// Logika Register
if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Cek email sudah terdaftar
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if(mysqli_num_rows($cek) > 0) {
        $error = "❌ Email sudah terdaftar!";
    } else {
        $query = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', 'siswa')";
        if(mysqli_query($conn, $query)) {
            $success = "✅ Berhasil daftar! Silakan <a href='login.php'>login</a>.";
        } else {
            $error = "❌ Gagal daftar: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar - Toko Peminjaman Buku</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #e8f4fd 0%, #b8d4e3 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { background: white; width: 100%; max-width: 420px; padding: 40px; border-radius: 24px; box-shadow: 0 20px 60px rgba(74, 144, 217, 0.2); }
        .logo-area { text-align: center; margin-bottom: 30px; }
        .logo-area h1 { color: #2c3e50; font-size: 28px; }
        .logo-area p { color: #7f8c8d; font-size: 14px; margin-top: 5px; }
        
        .form-section { margin: 15px 0; }
        .form-section h3 { color: #2c3e50; font-size: 16px; margin-bottom: 10px; text-align: center; }
        .sub-text { color: #7f8c8d; font-size: 13px; text-align: center; margin-bottom: 10px; }
        
        input { width: 100%; padding: 12px 16px; margin: 8px 0; border: 2px solid #e8ecf1; border-radius: 10px; font-size: 14px; transition: all 0.3s; box-sizing: border-box; }
        input:focus { border-color: #4A90D9; outline: none; box-shadow: 0 0 0 4px rgba(74, 144, 217, 0.1); }
        
        button { width: 100%; padding: 12px; background: #2ecc71; color: white; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s; margin-top: 5px; }
        button:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(46, 204, 113, 0.3); }
        
        .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 15px; text-align: center; font-weight: 500; }
        .alert-error { background: #fee; color: #e74c3c; border: 1px solid #fcc; }
        .alert-success { background: #efe; color: #27ae60; border: 1px solid #cfc; }
        .alert-success a { color: #4A90D9; font-weight: 600; }
        
        .login-link { text-align: center; margin-top: 20px; color: #7f8c8d; }
        .login-link a { color: #4A90D9; text-decoration: none; font-weight: 600; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-area">
            <h1>📚 Toko Peminjaman Buku</h1>
            <p>Buat akun untuk meminjam buku</p>
        </div>
        
        <!-- ====== FORM REGISTER ====== -->
        <div class="form-section">
            <h3>Daftar Akun Baru</h3>
            <p class="sub-text">Siswa baru? Daftar di sini:</p>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="text" name="nama" placeholder="Nama Lengkap" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password (min 6 karakter)" required>
                <button type="submit" name="register">Daftar</button>
            </form>
            
            <p class="login-link">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </p>
        </div>
    </div>
</body>
</html>