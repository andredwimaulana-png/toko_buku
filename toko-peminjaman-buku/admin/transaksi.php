<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['admin']);

$nama = $_SESSION['nama'];

$data = mysqli_query($conn, "SELECT t.*, u.nama as nama_siswa
                              FROM transaksi t
                              JOIN users u ON t.user_id = u.id
                              ORDER BY t.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Semua Transaksi - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>🧾 Semua Transaksi</h2>
                <span class="role-badge"><?php echo htmlspecialchars($nama); ?> (Admin)</span>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
                <a href="buku.php" class="nav-link">📚 Buku</a>
                <a href="anggota.php" class="nav-link">👥 Siswa</a>
                <a href="petugas.php" class="nav-link">🧑‍💼 Petugas</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="content">
            <div class="section-header">
                <h3>🧾 Riwayat Transaksi</h3>
                <span class="subtitle"><?php echo mysqli_num_rows($data); ?> transaksi</span>
            </div>

            <table border="1" cellpadding="8" style="width:100%;border-collapse:collapse;">
                <tr>
                    <th>Invoice</th><th>Siswa</th><th>Tanggal</th><th>Item</th>
                    <th>Total</th><th>Metode</th><th>Bayar</th><th>Kembalian</th>
                </tr>
                <?php if (mysqli_num_rows($data) > 0): ?>
                    <?php while ($t = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['invoice']); ?></td>
                        <td><?php echo htmlspecialchars($t['nama_siswa']); ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($t['created_at'])); ?></td>
                        <td><?php echo $t['total_item']; ?> buku</td>
                        <td>Rp <?php echo number_format($t['total_harga'], 0, ',', '.'); ?></td>
                        <td><?php echo strtoupper($t['metode']); ?></td>
                        <td>Rp <?php echo number_format($t['bayar'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($t['kembalian'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">Belum ada transaksi.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>