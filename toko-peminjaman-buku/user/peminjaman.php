<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['siswa']);

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

// Semua peminjaman yang masih "aktif" dalam arti luas: menunggu konfirmasi,
// sedang dipinjam, menunggu verifikasi kembali, atau baru saja ditolak
$query = "SELECT p.*, b.judul, b.penulis, b.gambar,
          DATEDIFF(p.tanggal_tenggat, CURDATE()) as sisa_hari
          FROM peminjaman p
          JOIN buku b ON p.buku_id = b.id
          WHERE p.user_id = $user_id AND p.status IN ('pending','dipinjam','ditolak','menunggu_verifikasi')
          ORDER BY FIELD(p.status,'pending','dipinjam','menunggu_verifikasi','ditolak'), p.tanggal_tenggat ASC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Buku Saya - Toko Peminjaman Buku</title>
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
                <a href="notifikasi.php" class="nav-link">🔔 Notifikasi</a>
                <a href="riwayat.php" class="nav-link">📜 Riwayat</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="content">
            <div class="section-header">
                <h3>📘 Status Peminjaman</h3>
                <span class="subtitle">Jangan lupa kembalikan tepat waktu!</span>
            </div>

            <?php if(mysqli_num_rows($result) > 0): ?>
                <div class="peminjaman-list">
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="peminjaman-item">
                        <div class="peminjaman-image">
                            <img src="../assets/img/<?php echo $row['gambar']; ?>" alt="<?php echo $row['judul']; ?>">
                        </div>
                        <div class="peminjaman-info">
                            <h4><?php echo htmlspecialchars($row['judul']); ?></h4>
                            <p class="penulis">✍️ <?php echo htmlspecialchars($row['penulis']); ?></p>

                            <?php if ($row['status'] === 'pending'): ?>
                                <div class="sisa-waktu"><span class="sisa-hari">⏳ Menunggu konfirmasi petugas</span></div>
                            <?php elseif ($row['status'] === 'ditolak'): ?>
                                <div class="sisa-waktu"><span class="terlambat">❌ Ditolak<?php echo $row['alasan_tolak'] ? ': ' . htmlspecialchars($row['alasan_tolak']) : ''; ?></span></div>
                            <?php elseif ($row['status'] === 'menunggu_verifikasi'): ?>
                                <div class="sisa-waktu"><span class="sisa-hari">↩️ Menunggu verifikasi pengembalian</span></div>
                            <?php else: ?>
                                <div class="detail-pinjam">
                                    <span>📅 Pinjam: <?php echo date('d M Y', strtotime($row['tanggal_pinjam'])); ?></span>
                                    <span>⏳ Tenggat: <?php echo date('d M Y', strtotime($row['tanggal_tenggat'])); ?></span>
                                </div>
                                <div class="sisa-waktu">
                                    <?php if($row['sisa_hari'] >= 0): ?>
                                        <span class="sisa-hari">⏳ Sisa <?php echo $row['sisa_hari']; ?> hari</span>
                                    <?php else: ?>
                                        <span class="terlambat">⚠️ Terlambat <?php echo abs($row['sisa_hari']); ?> hari</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($row['denda'] > 0): ?>
                                <p style="color:#e74c3c;font-weight:600;">
                                    💰 Denda: Rp <?php echo number_format($row['denda'], 0, ',', '.'); ?>
                                    (<?php echo $row['denda_status'] === 'lunas' ? 'Lunas' : 'Belum dibayar'; ?>)
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php if ($row['status'] === 'dipinjam'): ?>
                        <div class="peminjaman-action">
                            <a href="kembali.php?id=<?php echo $row['id']; ?>" class="btn-kembali" onclick="return confirm('Yakin ingin mengembalikan buku ini?')">↩️ Kembalikan</a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>📭 Tidak ada peminjaman aktif.</p>
                    <a href="dashboard.php" class="btn-pinjam">Cari Buku</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>