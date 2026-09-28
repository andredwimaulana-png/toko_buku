<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['admin']);

$nama = $_SESSION['nama'];

$total_buku    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM buku"))['t'];
$total_stok    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stok) t FROM buku"))['t'] ?? 0;
$total_siswa   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM users WHERE role='siswa'"))['t'];
$total_petugas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM users WHERE role='petugas'"))['t'];
$pending       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM peminjaman WHERE status='pending'"))['t'];
$dipinjam      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM peminjaman WHERE status='dipinjam'"))['t'];
$terlambat     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM peminjaman WHERE status='dipinjam' AND tanggal_tenggat < CURDATE()"))['t'];
$total_denda_belum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(denda),0) t FROM peminjaman WHERE denda_status='belum_bayar'"))['t'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin - Toko Peminjaman Buku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>👋 Halo, <?php echo htmlspecialchars($nama); ?></h2>
                <span class="role-badge">Admin</span>
            </div>
            <div class="header-right">
                <a href="buku.php" class="nav-link">📚 Buku</a>
                <a href="anggota.php" class="nav-link">👥 Siswa</a>
                <a href="petugas.php" class="nav-link">🧑‍💼 Petugas</a>
                <a href="transaksi.php" class="nav-link">🧾 Transaksi</a>
                <a href="laporan.php" class="nav-link">📊 Laporan</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-info"><h3><?php echo $total_buku; ?></h3><p>Judul Buku</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info"><h3><?php echo $total_stok; ?></h3><p>Total Stok</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-info"><h3><?php echo $total_siswa; ?></h3><p>Siswa Terdaftar</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🧑‍💼</div>
                <div class="stat-info"><h3><?php echo $total_petugas; ?></h3><p>Petugas</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-info"><h3><?php echo $pending; ?></h3><p>Menunggu Konfirmasi</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📖</div>
                <div class="stat-info"><h3><?php echo $dipinjam; ?></h3><p>Sedang Dipinjam</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⚠️</div>
                <div class="stat-info"><h3><?php echo $terlambat; ?></h3><p>Terlambat</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info"><h3>Rp <?php echo number_format($total_denda_belum, 0, ',', '.'); ?></h3><p>Denda Belum Dibayar</p></div>
            </div>
        </div>

        <div class="content">
            <div class="section-header">
                <h3>🔗 Menu Cepat</h3>
            </div>
            <p>
                <a href="petugas.php" class="btn-pinjam">🧑‍💼 Kelola Petugas</a>
                &nbsp;
                <a href="buku.php" class="btn-pinjam">📚 Kelola Buku</a>
                &nbsp;
                <a href="laporan.php" class="btn-pinjam">📊 Lihat Laporan Peminjaman</a>
            </p>
        </div>
    </div>
</body>
</html>