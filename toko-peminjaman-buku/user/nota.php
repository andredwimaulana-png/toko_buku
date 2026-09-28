<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

include '../config/koneksi.php';
$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

// Ambil data keranjang
$query = "SELECT k.*, b.judul, b.penulis, b.gambar 
          FROM keranjang k 
          JOIN buku b ON k.buku_id = b.id 
          WHERE k.user_id = $user_id";
$result = mysqli_query($conn, $query);

$total_item = 0;
$total_harga = 0;
$items = [];
while($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
    $total_item += $row['qty'];
    $total_harga += $row['qty'] * $row['harga'];
}

// Proses pembayaran
if (isset($_POST['submit_bayar'])) {
    // Ambil nilai dari input dan bersihkan dari titik/koma (dukung "100000" maupun "100.000")
    $bayar_input = $_POST['bayar'] ?? '';
    $bayar_input = str_replace(['.', ','], '', $bayar_input); // hapus pemisah ribuan/desimal
    $bayar_input = preg_replace('/[^0-9]/', '', $bayar_input); // jaga-jaga: buang karakter non-digit lain
    $bayar = ($bayar_input === '') ? 0 : (int) $bayar_input;

    $metode = $_POST['metode'];
    
    if(empty($items)) {
        $error = "❌ Keranjang kosong!";
    } elseif($bayar < $total_harga) {
        $error = "❌ Uang tidak cukup! Total: Rp " . number_format($total_harga, 0, ',', '.') . " | Anda bayar: Rp " . number_format($bayar, 0, ',', '.');
    } else {
        $kembalian = $bayar - $total_harga;
        $invoice = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        // Insert transaksi
        $query_trans = "INSERT INTO transaksi (invoice, user_id, total_item, total_harga, bayar, kembalian, metode) 
                        VALUES ('$invoice', $user_id, $total_item, $total_harga, $bayar, $kembalian, '$metode')";
        mysqli_query($conn, $query_trans);
        $transaksi_id = mysqli_insert_id($conn);
        
        // Insert detail transaksi
        foreach($items as $item) {
            $subtotal = $item['qty'] * $item['harga'];
            mysqli_query($conn, "INSERT INTO transaksi_detail (transaksi_id, buku_id, qty, harga, subtotal) 
                                VALUES ($transaksi_id, {$item['buku_id']}, {$item['qty']}, {$item['harga']}, $subtotal)");
            
            // Kurangi stok buku
            mysqli_query($conn, "UPDATE buku SET stok = stok - {$item['qty']} WHERE id = {$item['buku_id']}");
        }
        
        // Hapus semua keranjang
        mysqli_query($conn, "DELETE FROM keranjang WHERE user_id = $user_id");
        
        // Redirect ke nota print
        header("Location: nota_print.php?id=$transaksi_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nota - Toko Peminjaman Buku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .nota-item {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        .nota-item:hover {
            background: #eef5fb;
        }
        .nota-item .info {
            flex: 1;
        }
        .nota-item .info h4 {
            color: #2c3e50;
            font-size: 15px;
        }
        .nota-item .info p {
            color: #7f8c8d;
            font-size: 13px;
        }
        .nota-item .subtotal {
            font-weight: 600;
            color: #2c3e50;
        }
        .nota-total {
            background: #f0f8ff;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }
        .nota-total .row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 16px;
        }
        .nota-total .total {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            border-top: 2px solid #dce8f0;
            padding-top: 12px;
            margin-top: 8px;
        }
        .payment-section {
            margin-top: 20px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
        }
        .payment-section input, .payment-section select {
            padding: 10px 14px;
            border: 2px solid #e8ecf1;
            border-radius: 10px;
            font-size: 15px;
            width: 100%;
            margin: 6px 0;
            box-sizing: border-box;
        }
        .payment-section input:focus, .payment-section select:focus {
            border-color: #4A90D9;
            outline: none;
        }
        .btn-bayar {
            background: #2ecc71;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            margin-top: 12px;
            transition: all 0.3s;
        }
        .btn-bayar:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }
        .btn-kembali {
            display: inline-block;
            background: #e8ecf1;
            color: #2c3e50;
            padding: 10px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 12px;
        }
        .btn-kembali:hover {
            background: #d5dbe3;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 500;
        }
        .alert-error {
            background: #fee;
            color: #e74c3c;
            border: 1px solid #fcc;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        .empty-state p {
            font-size: 18px;
            margin-bottom: 16px;
        }
        .nota-header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .total-harga-info {
            margin-top: 8px;
            font-size: 14px;
            color: #2c3e50;
        }
        .total-harga-info strong {
            color: #4A90D9;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="header-left">
                <h2>🧾 Nota Peminjaman</h2>
                <span class="role-badge"><?php echo htmlspecialchars($nama); ?></span>
            </div>
            <div class="header-right">
                <a href="keranjang.php" class="nav-link">🛒 Keranjang</a>
                <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
                <a href="../auth/logout.php" class="logout-btn">🚪 Log Out</a>
            </div>
        </div>

        <div class="content">
            <div class="nota-header-actions">
                <div class="section-header">
                    <h3>📋 Ringkasan Belanja</h3>
                    <span class="subtitle"><?php echo $total_item; ?> buku akan dibeli</span>
                </div>
                <a href="keranjang.php" class="btn-kembali">⬅ Kembali ke Keranjang</a>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if(empty($items)): ?>
                <div class="empty-state">
                    <p>🛒 Keranjang kosong.</p>
                    <a href="dashboard.php" class="btn-pinjam">Cari Buku</a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <?php foreach($items as $item): ?>
                    <div class="nota-item">
                        <div class="info">
                            <h4><?php echo htmlspecialchars($item['judul']); ?></h4>
                            <p>✍️ <?php echo htmlspecialchars($item['penulis']); ?> | <?php echo $item['qty']; ?> x Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></p>
                        </div>
                        <div class="subtotal">
                            Rp <?php echo number_format($item['qty'] * $item['harga'], 0, ',', '.'); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="nota-total">
                        <div class="row">
                            <span>Total Item</span>
                            <span><strong><?php echo $total_item; ?> buku</strong></span>
                        </div>
                        <div class="row total">
                            <span>Total Harga</span>
                            <span>Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></span>
                        </div>
                    </div>

                    <div class="payment-section">
                        <h4>💳 Metode Pembayaran</h4>
                        <select name="metode" required>
                            <option value="tunai">Tunai</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                        
                        <h4 style="margin-top:16px;">💰 Bayar</h4>
                        <input type="text" name="bayar" id="bayarInput" placeholder="Masukkan jumlah uang (contoh: 50000)" required oninput="formatRupiah(this)">
                        
                        <div class="total-harga-info">
                            Total yang harus dibayar: <strong>Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></strong>
                        </div>
                        
                        <button type="submit" name="submit_bayar" class="btn-bayar">💳 Bayar Sekarang</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function formatRupiah(input) {
            // Hapus semua karakter selain angka
            let value = input.value.replace(/[^0-9]/g, '');
            if (value) {
                // Tampilkan dengan format ribuan
                input.value = new Intl.NumberFormat('id-ID').format(value);
            }
        }
    </script>
</body>
</html>