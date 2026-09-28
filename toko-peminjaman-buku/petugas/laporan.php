<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['petugas']);

$nama = $_SESSION['nama'];

$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : 1;
if (!in_array($bulan, [1, 3, 5])) {
    $bulan = 1;
}

$data = mysqli_query($conn, "SELECT p.*, u.nama as nama_siswa, b.judul
                              FROM peminjaman p
                              JOIN users u ON p.user_id = u.id
                              JOIN buku b ON p.buku_id = b.id
                              WHERE p.tanggal_pinjam >= DATE_SUB(CURDATE(), INTERVAL $bulan MONTH)
                              ORDER BY p.tanggal_pinjam DESC");
$total = mysqli_num_rows($data);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Peminjaman - Petugas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>📊 Laporan Peminjaman</h2>
                <span class="role-badge"><?php echo htmlspecialchars($nama); ?> (Petugas)</span>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
                <a href="konfirmasi.php" class="nav-link">✅ Konfirmasi</a>
                <a href="denda.php" class="nav-link">💰 Denda</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="content">
            <div class="section-header">
                <h3>📊 Rekap Peminjaman</h3>
                <span class="subtitle"><?php echo $total; ?> peminjaman</span>
            </div>

            <div style="margin-bottom:16px;">
                <a href="laporan.php?bulan=1" class="btn-kembali" style="<?php echo $bulan==1 ? 'background:#4A90D9;color:white;' : ''; ?>">1 Bulan</a>
                <a href="laporan.php?bulan=3" class="btn-kembali" style="<?php echo $bulan==3 ? 'background:#4A90D9;color:white;' : ''; ?>">3 Bulan</a>
                <a href="laporan.php?bulan=5" class="btn-kembali" style="<?php echo $bulan==5 ? 'background:#4A90D9;color:white;' : ''; ?>">5 Bulan</a>
            </div>

            <table border="1" cellpadding="8" style="width:100%;border-collapse:collapse;">
                <tr><th>Siswa</th><th>Buku</th><th>Tgl Pinjam</th><th>Tgl Tenggat</th><th>Status</th><th>Denda</th></tr>
                <?php if ($total > 0): ?>
                    <?php while ($p = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['nama_siswa']); ?></td>
                        <td><?php echo htmlspecialchars($p['judul']); ?></td>
                        <td><?php echo date('d M Y', strtotime($p['tanggal_pinjam'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($p['tanggal_tenggat'])); ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $p['status'])); ?></td>
                        <td><?php echo $p['denda'] > 0 ? 'Rp ' . number_format($p['denda'], 0, ',', '.') : '-'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">Tidak ada data pada periode ini.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>