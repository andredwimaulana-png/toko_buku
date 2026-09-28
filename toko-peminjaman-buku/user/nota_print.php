<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

include '../config/koneksi.php';
$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

$transaksi_id = $_GET['id'];
$query = "SELECT t.*, u.nama 
          FROM transaksi t 
          JOIN users u ON t.user_id = u.id 
          WHERE t.id = $transaksi_id AND t.user_id = $user_id";
$result = mysqli_query($conn, $query);
$trans = mysqli_fetch_assoc($result);

if(!$trans) {
    header('Location: dashboard.php');
    exit();
}

$query_detail = "SELECT td.*, b.judul, b.penulis 
                 FROM transaksi_detail td 
                 JOIN buku b ON td.buku_id = b.id 
                 WHERE td.transaksi_id = $transaksi_id";
$detail = mysqli_query($conn, $query_detail);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nota - Toko Peminjaman Buku</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Courier New', monospace; }
        body { background: #f0f0f0; display: flex; justify-content: center; padding: 40px 20px; }
        .nota {
            background: white;
            width: 360px;
            padding: 24px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .nota-header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 16px;
            margin-bottom: 16px;
        }
        .nota-header h1 {
            font-size: 20px;
            letter-spacing: 2px;
        }
        .nota-header p {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
        .nota-info {
            font-size: 12px;
            margin-bottom: 16px;
            line-height: 1.6;
        }
        .nota-info .row {
            display: flex;
            justify-content: space-between;
        }
        .nota-table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
            margin: 12px 0;
        }
        .nota-table th {
            text-align: left;
            border-bottom: 1px solid #333;
            padding: 6px 0;
        }
        .nota-table td {
            padding: 4px 0;
        }
        .nota-table .text-right {
            text-align: right;
        }
        .nota-total {
            border-top: 2px dashed #333;
            padding-top: 12px;
            margin-top: 12px;
        }
        .nota-total .row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            padding: 4px 0;
        }
        .nota-total .total {
            font-size: 16px;
            font-weight: 700;
            border-top: 1px solid #333;
            padding-top: 8px;
            margin-top: 4px;
        }
        .nota-footer {
            text-align: center;
            font-size: 12px;
            border-top: 2px dashed #333;
            padding-top: 16px;
            margin-top: 16px;
            color: #666;
        }
        .nota-footer .thankyou {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 6px;
        }
        .btn-print {
            display: block;
            width: 100%;
            padding: 12px;
            background: #4A90D9;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 16px;
            font-family: 'Segoe UI', sans-serif;
        }
        .btn-print:hover { background: #357ABD; }
        .btn-back {
            display: block;
            width: 100%;
            padding: 10px;
            background: #e8ecf1;
            color: #2c3e50;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            margin-top: 8px;
            text-align: center;
            text-decoration: none;
            font-family: 'Segoe UI', sans-serif;
        }
        .btn-back:hover { background: #d5dbe3; }
        @media print {
            .btn-print, .btn-back, .no-print { display: none; }
            body { background: white; padding: 0; }
            .nota { box-shadow: none; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div style="display:flex;flex-direction:column;align-items:center;gap:12px;">
        <div class="nota" id="notaContent">
            <div class="nota-header">
                <h1>📚 TOKO PEMINJAMAN BUKU</h1>
                <p>Jl. Soekarno Hatta, Ponorogo</p>
                <p>Telp: 0857-0857-8056</p>
            </div>

            <div class="nota-info">
                <div class="row">
                    <span>No. Nota</span>
                    <span><?php echo $trans['invoice']; ?></span>
                </div>
                <div class="row">
                    <span>Tanggal</span>
                    <span><?php echo date('d M Y H:i', strtotime($trans['created_at'])); ?></span>
                </div>
                <div class="row">
                    <span>Peminjam</span>
                    <span><?php echo htmlspecialchars($nama); ?></span>
                </div>
            </div>

            <table class="nota-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($detail)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['judul']); ?></td>
                        <td class="text-right"><?php echo $row['qty']; ?></td>
                        <td class="text-right">Rp <?php echo number_format($row['subtotal'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="font-size:10px;color:#999;">
                            Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?> / buku
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="nota-total">
                <div class="row">
                    <span>Total Item</span>
                    <span><?php echo $trans['total_item']; ?> buku</span>
                </div>
                <div class="row">
                    <span>Metode</span>
                    <span><?php echo strtoupper($trans['metode']); ?></span>
                </div>
                <div class="row">
                    <span>Total Belanja</span>
                    <span>Rp <?php echo number_format($trans['total_harga'], 0, ',', '.'); ?></span>
                </div>
                <div class="row">
                    <span>Bayar</span>
                    <span>Rp <?php echo number_format($trans['bayar'], 0, ',', '.'); ?></span>
                </div>
                <div class="row total">
                    <span>Kembalian</span>
                    <span>Rp <?php echo number_format($trans['kembalian'], 0, ',', '.'); ?></span>
                </div>
            </div>

            <div class="nota-footer">
                <div class="thankyou">✨ Terima kasih telah berbelanja!</div>
                <p>Barang yang sudah dibeli tidak dapat ditukar</p>
                <p>kecuali ada perjanjian sebelumnya.</p>
                <p style="margin-top:4px;">Hubungi: 0857-0857-8056</p>
                <p style="margin-top:8px;font-size:10px;color:#aaa;">— Andzzvg Buku —</p>
            </div>
        </div>

        <button class="btn-print no-print" onclick="window.print()">🖨️ Cetak Nota</button>
        <a href="dashboard.php" class="btn-back no-print">🏠 Kembali ke Dashboard</a>
    </div>
</body>
</html>