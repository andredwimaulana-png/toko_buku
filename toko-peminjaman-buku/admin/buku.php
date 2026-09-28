<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['admin']);

$nama = $_SESSION['nama'];

// Tambah buku baru
if (isset($_POST['tambah'])) {
    $judul   = mysqli_real_escape_string($conn, $_POST['judul']);
    $penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
    $genre   = mysqli_real_escape_string($conn, $_POST['genre']);
    $stok    = intval($_POST['stok']);
    $gambar  = mysqli_real_escape_string($conn, $_POST['gambar']);

    mysqli_query($conn, "INSERT INTO buku (judul, penulis, genre, stok, gambar)
                          VALUES ('$judul', '$penulis', '$genre', $stok, '$gambar')");
    header('Location: buku.php');
    exit();
}

// Edit buku
if (isset($_POST['edit'])) {
    $id      = intval($_POST['id']);
    $judul   = mysqli_real_escape_string($conn, $_POST['judul']);
    $penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
    $genre   = mysqli_real_escape_string($conn, $_POST['genre']);
    $stok    = intval($_POST['stok']);
    $gambar  = mysqli_real_escape_string($conn, $_POST['gambar']);

    mysqli_query($conn, "UPDATE buku SET judul='$judul', penulis='$penulis', genre='$genre',
                          stok=$stok, gambar='$gambar' WHERE id=$id");
    header('Location: buku.php');
    exit();
}

// Hapus buku
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM buku WHERE id=$id");
    header('Location: buku.php');
    exit();
}

// Data untuk mode edit (kalau ada ?edit_id=)
$buku_edit = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    $q = mysqli_query($conn, "SELECT * FROM buku WHERE id=$id");
    $buku_edit = mysqli_fetch_assoc($q);
}

$data_buku = mysqli_query($conn, "SELECT * FROM buku ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kelola Buku - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>📚 Kelola Buku</h2>
                <span class="role-badge"><?php echo htmlspecialchars($nama); ?> (Admin)</span>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
                <a href="anggota.php" class="nav-link">👥 Siswa</a>
                <a href="petugas.php" class="nav-link">🧑‍💼 Petugas</a>
                <a href="transaksi.php" class="nav-link">🧾 Transaksi</a>
                <a href="laporan.php" class="nav-link">📊 Laporan</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="content">
            <div class="section-header">
                <h3><?php echo $buku_edit ? '✏️ Edit Buku' : '➕ Tambah Buku'; ?></h3>
            </div>

            <form method="POST" style="max-width:420px;display:flex;flex-direction:column;gap:8px;">
                <?php if ($buku_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $buku_edit['id']; ?>">
                <?php endif; ?>
                <input name="judul" placeholder="Judul" required value="<?php echo $buku_edit ? htmlspecialchars($buku_edit['judul']) : ''; ?>">
                <input name="penulis" placeholder="Penulis" required value="<?php echo $buku_edit ? htmlspecialchars($buku_edit['penulis']) : ''; ?>">
                <input name="genre" placeholder="Genre" required value="<?php echo $buku_edit ? htmlspecialchars($buku_edit['genre']) : ''; ?>">
                <input name="stok" type="number" min="0" placeholder="Stok" required value="<?php echo $buku_edit ? $buku_edit['stok'] : ''; ?>">
                <input name="gambar" placeholder="Nama file gambar (contoh: buku1.jpg)" required value="<?php echo $buku_edit ? htmlspecialchars($buku_edit['gambar']) : ''; ?>">
                <button type="submit" name="<?php echo $buku_edit ? 'edit' : 'tambah'; ?>" class="btn-pinjam">
                    <?php echo $buku_edit ? 'Simpan Perubahan' : 'Tambah Buku'; ?>
                </button>
                <?php if ($buku_edit): ?>
                    <a href="buku.php" class="btn-kembali">Batal Edit</a>
                <?php endif; ?>
            </form>

            <div class="section-header" style="margin-top:24px;">
                <h3>📖 Daftar Buku</h3>
            </div>
            <table border="1" cellpadding="8" style="width:100%;border-collapse:collapse;">
                <tr>
                    <th>Judul</th><th>Penulis</th><th>Genre</th><th>Stok</th><th>Aksi</th>
                </tr>
                <?php while ($b = mysqli_fetch_assoc($data_buku)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($b['judul']); ?></td>
                    <td><?php echo htmlspecialchars($b['penulis']); ?></td>
                    <td><?php echo htmlspecialchars($b['genre']); ?></td>
                    <td><?php echo $b['stok']; ?></td>
                    <td>
                        <a href="buku.php?edit_id=<?php echo $b['id']; ?>">✏️ Edit</a> |
                        <a href="buku.php?hapus=<?php echo $b['id']; ?>" onclick="return confirm('Hapus buku ini?')">🗑️ Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>