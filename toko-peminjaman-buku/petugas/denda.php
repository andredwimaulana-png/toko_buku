<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['petugas']);

$nama = $_SESSION['nama'];

// Set/ubah nominal denda
if (isset($_POST['set_denda'])) {
    $id = intval($_POST['id']);
    $denda = intval(preg_replace('/[^0-9]/', '', $_POST['denda']));

    $q = mysqli_query($conn, "SELECT * FROM peminjaman WHERE id=$id");
    $p = mysqli_fetch_assoc($q);

    if ($p) {
        mysqli_query($conn, "UPDATE peminjaman SET denda=$denda, denda_status='belum_bayar' WHERE id=$id");
        kirimNotifikasi($conn, $p['user_id'],
            "⚠️ Kamu dikenakan denda Rp " . number_format($denda, 0, ',', '.') . " karena terlambat mengembalikan buku.",
            'denda', '../user/peminjaman.php');
    }
    header('Location: denda.php');
    exit();
}

// Tandai denda lunas
if (isset($_GET['lunas'])) {
    $id = intval($_GET['lunas']);
    mysqli_query($conn, "UPDATE peminjaman SET denda_status='lunas' WHERE id=$id");
    header('Location: denda.php');
    exit();
}

// Kirim pengingat manual
if (isset($_GET['ingatkan'])) {
    $id = intval($_GET['ingatkan']);
    $q = mysqli_query($conn, "SELECT p.*, b.judul FROM peminjaman p JOIN buku b ON p.buku_id=b.id WHERE p.id=$id");
    $p = mysqli_fetch_assoc($q);
    if ($p) {
        kirimNotifikasi($conn, $p['user_id'],
            "⏰ Pengingat: buku \"{$p['judul']}\" sudah melewati batas waktu pengembalian. Segera kembalikan.",
            'pengingat', '../user/peminjaman.php');
    }
    header('Location: denda.php');
    exit();
}

// Daftar peminjaman yang sedang dipinjam & sudah lewat tenggat
$data = mysqli_query($conn, "SELECT p.*, u.nama as nama_siswa, b.judul,
                              DATEDIFF(CURDATE(), p.tanggal_tenggat) as hari_telat
                              FROM peminjaman p
                              JOIN users u ON p.user_id = u.id
                              JOIN buku b ON p.buku_id = b.id
                              WHERE p.status = 'dipinjam' AND p.tanggal_tenggat < CURDATE()
                              ORDER BY p.tanggal_tenggat ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Denda &amp; Pengingat - Petugas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>💰 Denda &amp; Pengingat Keterlambatan</h2>
                <span class="role-badge"><?php echo htmlspecialchars($nama); ?> (Petugas)</span>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
                <a href="konfirmasi.php" class="nav-link">✅ Konfirmasi</a>
                <a href="verifikasi_kembali.php" class="nav-link">↩️ Verifikasi Kembali</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="content">
            <div class="section-header">
                <h3>⚠️ Peminjaman Terlambat</h3>
                <span class="subtitle"><?php echo mysqli_num_rows($data); ?> buku terlambat</span>
            </div>

            <?php if (mysqli_num_rows($data) > 0): ?>
                <?php while ($p = mysqli_fetch_assoc($data)): ?>
                <div class="nota-item" style="align-items:flex-start;flex-direction:column;gap:8px;">
                    <div>
                        <h4><?php echo htmlspecialchars($p['judul']); ?></h4>
                        <p>👤 <?php echo htmlspecialchars($p['nama_siswa']); ?> |
                           ⏳ Tenggat: <?php echo date('d M Y', strtotime($p['tanggal_tenggat'])); ?> |
                           ⚠️ Terlambat <?php echo $p['hari_telat']; ?> hari</p>
                        <p>Denda saat ini:
                            <strong><?php echo $p['denda'] > 0 ? 'Rp ' . number_format($p['denda'], 0, ',', '.') . ' (' . $p['denda_status'] . ')' : 'Belum ditentukan'; ?></strong>
                        </p>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;width:100%;">
                        <form method="POST" style="display:flex;gap:6px;">
                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                            <input type="text" name="denda" placeholder="Nominal denda (contoh: <?php echo $p['hari_telat'] * 5000; ?>)" style="padding:8px;border:2px solid #e8ecf1;border-radius:8px;width:220px;">
                            <button type="submit" name="set_denda" class="btn-pinjam">💾 Simpan Denda</button>
                        </form>
                        <a href="denda.php?ingatkan=<?php echo $p['id']; ?>" class="btn-kembali">⏰ Kirim Pengingat</a>
                        <?php if ($p['denda'] > 0 && $p['denda_status'] === 'belum_bayar'): ?>
                            <a href="denda.php?lunas=<?php echo $p['id']; ?>" class="btn-kembali" onclick="return confirm('Tandai denda ini sudah lunas?')">✅ Tandai Lunas</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state"><p>✅ Tidak ada peminjaman yang terlambat saat ini.</p></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>