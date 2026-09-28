<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

include '../config/koneksi.php';
$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

// Tambah ke keranjang
if (isset($_GET['add'])) {
    $buku_id = $_GET['add'];
    $cek = mysqli_query($conn, "SELECT * FROM keranjang WHERE user_id = $user_id AND buku_id = $buku_id");
    if(mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "UPDATE keranjang SET qty = qty + 1 WHERE user_id = $user_id AND buku_id = $buku_id");
    } else {
        mysqli_query($conn, "INSERT INTO keranjang (user_id, buku_id, qty, harga) VALUES ($user_id, $buku_id, 1, 50000)");
    }
    echo "<script>alert('✅ Buku ditambahkan ke keranjang!'); window.location='keranjang.php';</script>";
}

// Hapus dari keranjang
if (isset($_GET['remove'])) {
    $id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM keranjang WHERE id = $id AND user_id = $user_id");
    echo "<script>window.location='keranjang.php';</script>";
}

// Kosongkan keranjang
if (isset($_GET['clear'])) {
    mysqli_query($conn, "DELETE FROM keranjang WHERE user_id = $user_id");
    echo "<script>window.location='keranjang.php';</script>";
}

// Update qty
if (isset($_POST['update_qty'])) {
    $id = $_POST['id'];
    $qty = $_POST['qty'];
    if($qty > 0) {
        mysqli_query($conn, "UPDATE keranjang SET qty = $qty WHERE id = $id AND user_id = $user_id");
    }
    echo "<script>window.location='keranjang.php';</script>";
}

// Ambil data keranjang
$query = "SELECT k.*, b.judul, b.penulis, b.gambar 
          FROM keranjang k 
          JOIN buku b ON k.buku_id = b.id 
          WHERE k.user_id = $user_id";
$result = mysqli_query($conn, $query);

// Hitung total
$total_item = 0;
$total_harga = 0;
while($row = mysqli_fetch_assoc($result)) {
    $total_item += $row['qty'];
    $total_harga += $row['qty'] * $row['harga'];
}
// Reset pointer
mysqli_data_seek($result, 0);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Keranjang - Toko Peminjaman Buku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .btn-kembali-dashboard {
            display: inline-block;
            background: #e8ecf1;
            color: #2c3e50;
            padding: 8px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-kembali-dashboard:hover {
            background: #d5dbe3;
        }
        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>🛒 Keranjang Belanja</h2>
                <span class="role-badge"><?php echo htmlspecialchars($nama); ?></span>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
                <a href="peminjaman.php" class="nav-link">📘 Buku Saya</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="content">
            <div class="section-header">
                <div class="header-actions">
                    <h3>📦 Keranjang Belanja</h3>
                    <a href="dashboard.php" class="btn-kembali-dashboard">⬅ Kembali</a>
                </div>
                <span class="subtitle"><?php echo $total_item; ?> item</span>
            </div>

            <?php if(mysqli_num_rows($result) > 0): ?>
                <div class="keranjang-list">
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="keranjang-item">
                        <div class="keranjang-image">
                            <img src="../assets/img/<?php echo $row['gambar']; ?>" alt="<?php echo $row['judul']; ?>">
                        </div>
                        <div class="keranjang-info">
                            <h4><?php echo htmlspecialchars($row['judul']); ?></h4>
                            <p class="penulis">✍️ <?php echo htmlspecialchars($row['penulis']); ?></p>
                            <p class="harga">💰 Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                        </div>
                        <div class="keranjang-qty">
                            <form method="POST" style="display:flex;align-items:center;gap:8px;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="number" name="qty" value="<?php echo $row['qty']; ?>" min="1" style="width:60px;padding:6px;border:2px solid #e8ecf1;border-radius:8px;text-align:center;">
                                <button type="submit" name="update_qty" class="btn-update">🔄</button>
                            </form>
                        </div>
                        <div class="keranjang-subtotal">
                            <p>Rp <?php echo number_format($row['qty'] * $row['harga'], 0, ',', '.'); ?></p>
                        </div>
                        <div class="keranjang-action">
                            <a href="keranjang.php?remove=<?php echo $row['id']; ?>" class="btn-remove" onclick="return confirm('Hapus dari keranjang?')">🗑️</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="keranjang-total">
                    <div class="total-info">
                        <span>Total Item: <strong><?php echo $total_item; ?> buku</strong></span>
                        <span>Total Harga: <strong>Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></strong></span>
                    </div>
                    <div class="total-action">
                        <a href="nota.php" class="btn-checkout">🛒 Lanjut ke Nota</a>
                        <a href="keranjang.php?clear=all" class="btn-clear" onclick="return confirm('Kosongkan keranjang?')">🗑️ Kosongkan</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>🛒 Keranjang kosong.</p>
                    <a href="dashboard.php" class="btn-pinjam">Cari Buku</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>