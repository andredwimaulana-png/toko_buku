<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['admin']);

$nama = $_SESSION['nama'];
$error = null;

// Tambah petugas baru
if (isset($_POST['tambah'])) {
    $nama_baru = mysqli_real_escape_string($conn, $_POST['nama']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "❌ Email sudah dipakai akun lain!";
    } else {
        mysqli_query($conn, "INSERT INTO users (nama, email, password, role)
                              VALUES ('$nama_baru', '$email', '$password', 'petugas')");
        header('Location: petugas.php');
        exit();
    }
}

// Edit petugas (nama & email saja; password opsional)
if (isset($_POST['edit'])) {
    $id        = intval($_POST['id']);
    $nama_baru = mysqli_real_escape_string($conn, $_POST['nama']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);

    mysqli_query($conn, "UPDATE users SET nama='$nama_baru', email='$email'
                          WHERE id=$id AND role='petugas'");

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$password' WHERE id=$id AND role='petugas'");
    }
    header('Location: petugas.php');
    exit();
}

// Hapus petugas
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='petugas'");
    header('Location: petugas.php');
    exit();
}

// Mode edit
$petugas_edit = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    $q = mysqli_query($conn, "SELECT * FROM users WHERE id=$id AND role='petugas'");
    $petugas_edit = mysqli_fetch_assoc($q);
}

$data = mysqli_query($conn, "SELECT * FROM users WHERE role='petugas' ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kelola Petugas - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>🧑‍💼 Kelola Petugas</h2>
                <span class="role-badge"><?php echo htmlspecialchars($nama); ?> (Admin)</span>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
                <a href="buku.php" class="nav-link">📚 Buku</a>
                <a href="anggota.php" class="nav-link">👥 Siswa</a>
                <a href="transaksi.php" class="nav-link">🧾 Transaksi</a>
                <a href="laporan.php" class="nav-link">📊 Laporan</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="content">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="section-header">
                <h3><?php echo $petugas_edit ? '✏️ Edit Petugas' : '➕ Tambah Petugas'; ?></h3>
            </div>

            <form method="POST" style="max-width:420px;display:flex;flex-direction:column;gap:8px;">
                <?php if ($petugas_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $petugas_edit['id']; ?>">
                <?php endif; ?>
                <input name="nama" placeholder="Nama Lengkap" required value="<?php echo $petugas_edit ? htmlspecialchars($petugas_edit['nama']) : ''; ?>">
                <input name="email" type="email" placeholder="Email" required value="<?php echo $petugas_edit ? htmlspecialchars($petugas_edit['email']) : ''; ?>">
                <input name="password" type="password" placeholder="<?php echo $petugas_edit ? 'Password baru (kosongkan jika tidak diubah)' : 'Password'; ?>" <?php echo $petugas_edit ? '' : 'required'; ?>>
                <button type="submit" name="<?php echo $petugas_edit ? 'edit' : 'tambah'; ?>" class="btn-pinjam">
                    <?php echo $petugas_edit ? 'Simpan Perubahan' : 'Tambah Petugas'; ?>
                </button>
                <?php if ($petugas_edit): ?>
                    <a href="petugas.php" class="btn-kembali">Batal Edit</a>
                <?php endif; ?>
            </form>

            <div class="section-header" style="margin-top:24px;">
                <h3>🧑‍💼 Daftar Petugas</h3>
            </div>
            <table border="1" cellpadding="8" style="width:100%;border-collapse:collapse;">
                <tr><th>Nama</th><th>Email</th><th>Aksi</th></tr>
                <?php if (mysqli_num_rows($data) > 0): ?>
                    <?php while ($p = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['nama']); ?></td>
                        <td><?php echo htmlspecialchars($p['email']); ?></td>
                        <td>
                            <a href="petugas.php?edit_id=<?php echo $p['id']; ?>">✏️ Edit</a> |
                            <a href="petugas.php?hapus=<?php echo $p['id']; ?>" onclick="return confirm('Hapus petugas ini?')">🗑️ Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">Belum ada petugas terdaftar.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>