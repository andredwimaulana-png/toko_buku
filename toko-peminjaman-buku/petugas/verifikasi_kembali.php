<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['petugas']);

$nama = $_SESSION['nama'];
$petugas_id = $_SESSION['user_id'];

// Verifikasi pengembalian
if (isset($_GET['verifikasi'])) {
    $id = intval($_GET['verifikasi']);
    $q = mysqli_query($conn, "SELECT * FROM peminjaman WHERE id=$id AND status='menunggu_verifikasi'");
    $p = mysqli_fetch_assoc($q);

    if ($p) {
        $tanggal_kembali = date('Y-m-d');
        mysqli_query($conn, "UPDATE peminjaman SET status='dikembalikan', tanggal_kembali='$tanggal_kembali',
                              petugas_id=$petugas_id WHERE id=$id");
        mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id={$p['buku_id']}");

        $pesan = "✅ Pengembalian bukumu sudah diverifikasi petugas.";
        if ($p['denda'] > 0) {
            $pesan .= " Jangan lupa selesaikan denda Rp " . number_format($p['denda'], 0, ',', '.') . ".";
        }
        kirimNotifikasi($conn, $p['user_id'], $pesan, 'pengembalian', '../user/riwayat.php');
    }
    header('Location: verifikasi_kembali.php');
    exit();
}

$data = mysqli_query($conn, "SELECT p.*, u.nama as nama_siswa, b.judul
                              FROM peminjaman p
                              JOIN users u ON p.user_id = u.id
                              JOIN buku b ON p.buku_id = b.id
                              WHERE p.status = 'menunggu_verifikasi'
                              ORDER BY p.tanggal_tenggat ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Pengembalian - Petugas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>↩️ Verifikasi Pengembalian</h2>
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
                <h3>↩️ Menunggu Verifikasi Pengembalian</h3>
                <span class="subtitle"><?php echo mysqli_num_rows($data); ?> pengembalian</span>
            </div>

            <table border="1" cellpadding="8" style="width:100%;border-collapse:collapse;">
                <tr><th>Siswa</th><th>Buku</th><th>Tenggat</th><th>Denda</th><th>Aksi</th></tr>
                <?php if (mysqli_num_rows($data) > 0): ?>
                    <?php while ($p = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['nama_siswa']); ?></td>
                        <td><?php echo htmlspecialchars($p['judul']); ?></td>
                        <td><?php echo date('d M Y', strtotime($p['tanggal_tenggat'])); ?></td>
                        <td><?php echo $p['denda'] > 0 ? 'Rp ' . number_format($p['denda'], 0, ',', '.') . ' (' . $p['denda_status'] . ')' : '-'; ?></td>
                        <td><a href="verifikasi_kembali.php?verifikasi=<?php echo $p['id']; ?>" onclick="return confirm('Konfirmasi buku sudah diterima kembali?')">✅ Verifikasi</a></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">Tidak ada pengembalian yang menunggu verifikasi.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>