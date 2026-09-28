<?php
include '../config/koneksi.php';

// Cari peminjaman yang sudah lewat tenggat
$query = "SELECT id, buku_id FROM peminjaman 
          WHERE status = 'dipinjam' AND tanggal_tenggat < CURDATE()";
$result = mysqli_query($conn, $query);

$count = 0;
while($row = mysqli_fetch_assoc($result)) {
    // Update status jadi dikembalikan
    mysqli_query($conn, "UPDATE peminjaman 
                        SET status = 'dikembalikan', tanggal_kembali = CURDATE() 
                        WHERE id = {$row['id']}");
    
    // Tambah stok
    mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id = {$row['buku_id']}");
    $count++;
}

echo "✅ $count buku berhasil dikembalikan otomatis.";
?>