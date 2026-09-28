<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['petugas']);

$nama = $_SESSION['nama'];
$petugas_id = $_SESSION['user_id'];

// Proses konfirmasi (setuju / tolak)
if (isset($_POST['aksi'])) {
    $id = intval($_POST['id']);
    $aksi = $_POST['aksi']; // 'setuju' atau 'tolak'

    $q = mysqli_query($conn, "SELECT * FROM peminjaman WHERE id=$id AND status='pending'");
    $p = mysqli_fetch_assoc($q);

    if ($p) {
        if ($aksi === 'setuju') {
            // Cek stok masih ada sebelum menyetujui
            $stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok FROM buku WHERE id={$p['buku_id']}"))['stok'];
            if ($stok > 0) {
                mysqli_query($conn, "UPDATE peminjaman SET status='dipinjam', petugas_id=$petugas_id WHERE id=$id");
                mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id={$p['buku_id']}");
                kirimNotifikasi($conn, $p['user_id'], "✅ Peminjaman bukumu telah disetujui. Silakan ambil bukunya!", 'konfirmasi', '../user/peminjaman.php');
            } else {
                mysqli_query($conn, "UPDATE peminjaman SET status='ditolak', petugas_id=$petugas_id, alasan_tolak='Stok habis' WHERE id=$id");
                kirimNotifikasi($conn, $p['user_id'], "❌ Peminjaman ditolak karena stok buku habis.", 'konfirmasi', '../user/peminjaman.php');
            }
        } elseif ($aksi === 'tolak') {
            $alasan = mysqli_real_escape_string($conn, $_POST['alasan'] ?? 'Ditolak petugas');
            mysqli_query($conn, "UPDATE peminjaman SET status='ditolak', petugas_id=$petugas_id, alasan_tolak='$alasan' WHERE id=$id");
            kirimNotifikasi($conn, $p['user_id'], "❌ Peminjaman ditolak: $alasan", 'konfirmasi', '../user/peminjaman.php');
        }
    }
    header('Location: konfirmasi.php');
    exit();
}

$data = mysqli_query($conn, "SELECT p.*, u.nama as nama_siswa, b.judul, b.stok
                              FROM peminjaman p
                              JOIN users u ON p.user_id = u.id
                              JOIN buku b ON p.buku_id = b.id
                              WHERE p.status = 'pending'
                              ORDER BY p.tanggal_pinjam ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Peminjaman - Petugas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>✅ Konfirmasi Peminjaman</h2>
                <span class="role-badge"><?php echo htmlspecialchars($nama); ?> (Petugas)</span>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
                <a href="verifikasi_kembali.php" class="nav-link">↩️ Verifikasi Kembali</a>
                <a href="denda.php" class="nav-link">💰 Denda</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="content">
            <div class="section-header">
                <h3>⏳ Menunggu Konfirmasi</h3>
                <span class="subtitle"><?php echo mysqli_num_rows($data); ?> pengajuan</span>
            </div>

            <?php if (mysqli_num_rows($data) > 0): ?>
                <?php while ($p = mysqli_fetch_assoc($data)): ?>
                <div class="nota-item" style="align-items:flex-start;flex-direction:column;gap:8px;">
                    <div style="display:flex;justify-content:space-between;width:100%;">
                        <div>
                            <h4><?php echo htmlspecialchars($p['judul']); ?></h4>
                            <p>👤 <?php echo htmlspecialchars($p['nama_siswa']); ?> | 📅 Diajukan: <?php echo date('d M Y', strtotime($p['tanggal_pinjam'])); ?></p>
                            <p>📦 Stok tersisa saat ini: <?php echo $p['stok']; ?></p>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;width:100%;">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                            <button type="submit" name="aksi" value="setuju" class="btn-pinjam" onclick="return confirm('Setujui peminjaman ini?')">✅ Setujui</button>
                        </form>
                        <form method="POST" style="display:flex;gap:6px;flex:1;min-width:220px;">
                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                            <input type="text" name="alasan" placeholder="Alasan tolak (opsional)" style="flex:1;padding:8px;border:2px solid #e8ecf1;border-radius:8px;">
                            <button type="submit" name="aksi" value="tolak" class="btn-remove" onclick="return confirm('Tolak peminjaman ini?')">❌ Tolak</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state"><p>📭 Tidak ada pengajuan peminjaman yang menunggu.</p></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>