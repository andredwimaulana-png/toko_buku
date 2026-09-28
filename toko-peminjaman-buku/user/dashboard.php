<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['siswa']);

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

// Ambil buku terlaris
$query_buku = "SELECT b.*, COUNT(p.id) as total_pinjam 
               FROM buku b 
               LEFT JOIN peminjaman p ON b.id = p.buku_id AND p.status = 'dipinjam'
               GROUP BY b.id 
               ORDER BY total_pinjam DESC, b.id DESC
               LIMIT 12";
$result_buku = mysqli_query($conn, $query_buku);

// Statistik
$total_dipinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE user_id = $user_id AND status = 'dipinjam'"))['total'];
$total_riwayat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE user_id = $user_id AND status = 'dikembalikan'"))['total'];

// Hitung total keranjang
$total_keranjang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(qty) as total FROM keranjang WHERE user_id = $user_id"))['total'];
if(!$total_keranjang) $total_keranjang = 0;

// Notifikasi belum dibaca
$notif_baru = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM notifikasi WHERE user_id = $user_id AND is_read = 0"))['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Toko Peminjaman Buku</title>
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
                <a href="notifikasi.php" class="nav-link">🔔 Notifikasi<?php echo $notif_baru > 0 ? " ($notif_baru)" : ''; ?></a>
                <a href="keranjang.php" class="nav-link keranjang-link">
                    🛒 Keranjang 
                    <?php if($total_keranjang > 0): ?>
                        <span class="badge-keranjang"><?php echo $total_keranjang; ?></span>
                    <?php endif; ?>
                </a>
                <a href="peminjaman.php" class="nav-link">📘 Buku Saya</a>
                <a href="riwayat.php" class="nav-link">📜 Riwayat</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h3><?php echo $total_dipinjam; ?></h3>
                    <p>Sedang Dipinjam</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?php echo $total_riwayat; ?></h3>
                    <p>Selesai Dibaca</p>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="section-header">
                <h3>🔥 Buku Terlaris</h3>
                <span class="subtitle">Pilih buku favoritmu!</span>
            </div>
            <div class="buku-grid">
                <?php if(mysqli_num_rows($result_buku) > 0): ?>
                    <?php while($buku = mysqli_fetch_assoc($result_buku)): ?>
                    <div class="buku-card">
                        <div class="buku-image">
                            <img src="../assets/img/<?php echo $buku['gambar']; ?>" alt="<?php echo $buku['judul']; ?>">
                            <?php if($buku['stok'] > 0): ?>
                                <span class="stok-badge tersedia">Tersedia</span>
                            <?php else: ?>
                                <span class="stok-badge habis">Stok Habis</span>
                            <?php endif; ?>
                        </div>
                        <div class="buku-info">
                            <h4><?php echo htmlspecialchars($buku['judul']); ?></h4>
                            <p class="penulis">✍️ <?php echo htmlspecialchars($buku['penulis']); ?></p>
                            <p class="genre">🏷️ <?php echo htmlspecialchars($buku['genre']); ?></p>
                            <p class="harga">💰 Rp <?php echo number_format(50000, 0, ',', '.'); ?></p>
                            <?php if($buku['stok'] > 0): ?>
                                <a href="keranjang.php?add=<?php echo $buku['id']; ?>" class="btn-pinjam">🛒 Masukkan Keranjang</a>
                            <?php else: ?>
                                <span class="btn-disabled">Stok Habis</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-message">Belum ada buku terdaftar.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>