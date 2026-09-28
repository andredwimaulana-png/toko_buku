<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['petugas']);

$nama = $_SESSION['nama'];
$petugas_id = $_SESSION['user_id'];

$pending    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM peminjaman WHERE status='pending'"))['t'];
$verifikasi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM peminjaman WHERE status='menunggu_verifikasi'"))['t'];
$terlambat  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM peminjaman WHERE status='dipinjam' AND tanggal_tenggat < CURDATE()"))['t'];
$notif_baru = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM notifikasi WHERE user_id=$petugas_id AND is_read=0"))['t'];

// Daftar buku yang sedang dipinjam (belum dikembalikan)
$sedang_dipinjam = mysqli_query($conn, "SELECT p.*, u.nama as nama_siswa, b.judul,
                    DATEDIFF(p.tanggal_tenggat, CURDATE()) as sisa_hari
                    FROM peminjaman p
                    JOIN users u ON p.user_id = u.id
                    JOIN buku b ON p.buku_id = b.id
                    WHERE p.status = 'dipinjam'
                    ORDER BY p.tanggal_tenggat ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Petugas - Toko Peminjaman Buku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>👋 Halo, <?php echo htmlspecialchars($nama); ?></h2>
                <span class="role-badge">Petugas</span>
            </div>
            <div class="header-right">
                <a href="notifikasi.php" class="nav-link">🔔 Notifikasi <?php echo $notif_baru > 0 ? "($notif_baru)" : ''; ?></a>
                <a href="konfirmasi.php" class="nav-link">✅ Konfirmasi</a>
                <a href="verifikasi_kembali.php" class="nav-link">↩️ Verifikasi Kembali</a>
                <a href="denda.php" class="nav-link">💰 Denda</a>
                <a href="laporan.php" class="nav-link">📊 Laporan</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-info"><h3><?php echo $pending; ?></h3><p>Menunggu Konfirmasi</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">↩️</div>
                <div class="stat-info"><h3><?php echo $verifikasi; ?></h3><p>Menunggu Verifikasi Kembali</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⚠️</div>
                <div class="stat-info"><h3><?php echo $terlambat; ?></h3><p>Terlambat Dikembalikan</p></div>
            </div>
        </div>

        <div class="content">
            <div class="section-header">
                <h3>📘 Buku yang Sedang Dipinjam Siswa</h3>
            </div>
            <table border="1" cellpadding="8" style="width:100%;border-collapse:collapse;">
                <tr><th>Siswa</th><th>Buku</th><th>Tenggat</th><th>Status</th></tr>
                <?php if (mysqli_num_rows($sedang_dipinjam) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($sedang_dipinjam)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nama_siswa']); ?></td>
                        <td><?php echo htmlspecialchars($row['judul']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['tanggal_tenggat'])); ?></td>
                        <td>
                            <?php if ($row['sisa_hari'] >= 0): ?>
                                <span class="sisa-hari">⏳ Sisa <?php echo $row['sisa_hari']; ?> hari</span>
                            <?php else: ?>
                                <span class="terlambat">⚠️ Terlambat <?php echo abs($row['sisa_hari']); ?> hari</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">Tidak ada buku yang sedang dipinjam.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html> 