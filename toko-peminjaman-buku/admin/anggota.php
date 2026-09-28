<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['admin']);

$nama = $_SESSION['nama'];

// Hapus siswa (tidak boleh hapus admin/petugas dari sini)
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='siswa'");
    header('Location: anggota.php');
    exit();
}

$data = mysqli_query($conn, "SELECT * FROM users WHERE role='siswa' ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kelola Siswa - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>👥 Kelola Siswa</h2>
                <span class="role-badge"><?php echo htmlspecialchars($nama); ?> (Admin)</span>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
                <a href="buku.php" class="nav-link">📚 Buku</a>
                <a href="petugas.php" class="nav-link">🧑‍💼 Petugas</a>
                <a href="transaksi.php" class="nav-link">🧾 Transaksi</a>
                <a href="laporan.php" class="nav-link">📊 Laporan</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="content">
            <div class="section-header">
                <h3>👥 Daftar Siswa Terdaftar</h3>
                <span class="subtitle"><?php echo mysqli_num_rows($data); ?> siswa</span>
            </div>

            <table border="1" cellpadding="8" style="width:100%;border-collapse:collapse;">
                <tr><th>Nama</th><th>Email</th><th>Aksi</th></tr>
                <?php if (mysqli_num_rows($data) > 0): ?>
                    <?php while ($u = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['nama']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <a href="anggota.php?hapus=<?php echo $u['id']; ?>" onclick="return confirm('Hapus siswa ini? Riwayat peminjamannya akan tetap tersimpan.')">🗑️ Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">Belum ada siswa terdaftar.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>