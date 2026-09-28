<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['siswa']);

$nama = $_SESSION['nama'];
$user_id = $_SESSION['user_id'];

// Tandai semua sudah dibaca setiap kali halaman ini dibuka
mysqli_query($conn, "UPDATE notifikasi SET is_read=1 WHERE user_id=$user_id");

$data = mysqli_query($conn, "SELECT * FROM notifikasi WHERE user_id=$user_id ORDER BY created_at DESC LIMIT 50");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notifikasi - Toko Peminjaman Buku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>🔔 Notifikasi</h2>
                <span class="role-badge"><?php echo htmlspecialchars($nama); ?></span>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
                <a href="peminjaman.php" class="nav-link">📘 Buku Saya</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="content">
            <div class="section-header"><h3>🔔 Semua Notifikasi</h3></div>

            <?php if (mysqli_num_rows($data) > 0): ?>
                <?php while ($n = mysqli_fetch_assoc($data)): ?>
                <div class="nota-item">
                    <div class="info">
                        <p><?php echo htmlspecialchars($n['pesan']); ?></p>
                        <p style="font-size:11px;color:#aaa;"><?php echo date('d M Y H:i', strtotime($n['created_at'])); ?></p>
                    </div>
                    <?php if ($n['link']): ?>
                        <a href="<?php echo htmlspecialchars($n['link']); ?>" class="btn-kembali">Lihat</a>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state"><p>📭 Belum ada notifikasi.</p></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>