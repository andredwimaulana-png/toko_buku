<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

include '../config/koneksi.php';
$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

$query = "SELECT p.*, b.judul, b.penulis, b.gambar 
          FROM peminjaman p 
          JOIN buku b ON p.buku_id = b.id 
          WHERE p.user_id = $user_id AND p.status = 'dikembalikan' 
          ORDER BY p.tanggal_kembali DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Riwayat - Toko Peminjaman Buku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>👋 Halo, <?php echo htmlspecialchars($nama); ?></h2>
                <span class="role-badge">Siswa</span>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
                <a href="peminjaman.php" class="nav-link">📘 Buku Saya</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="content">
            <div class="section-header">
                <h3>📜 Riwayat Peminjaman</h3>
                <span class="subtitle">Buku yang sudah kamu kembalikan</span>
            </div>

            <?php if(mysqli_num_rows($result) > 0): ?>
                <div class="riwayat-list">
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="riwayat-item">
                        <div class="riwayat-image">
                            <img src="../assets/img/<?php echo $row['gambar']; ?>" alt="<?php echo $row['judul']; ?>">
                        </div>
                        <div class="riwayat-info">
                            <h4><?php echo htmlspecialchars($row['judul']); ?></h4>
                            <p class="penulis">✍️ <?php echo htmlspecialchars($row['penulis']); ?></p>
                            <div class="detail-pinjam">
                                <span>📅 Pinjam: <?php echo date('d M Y', strtotime($row['tanggal_pinjam'])); ?></span>
                                <span>✅ Kembali: <?php echo date('d M Y', strtotime($row['tanggal_kembali'])); ?></span>
                            </div>
                        </div>
                        <div class="riwayat-status">
                            <span class="status-selesai">✅ Selesai</span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>📭 Belum ada riwayat peminjaman.</p>
                    <a href="dashboard.php" class="btn-pinjam">Mulai Membaca</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>