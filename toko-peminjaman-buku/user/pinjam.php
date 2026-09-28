<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['siswa']);

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];
$buku_id = intval($_GET['id']);
$tanggal_pinjam = date('Y-m-d');
$tanggal_tenggat = date('Y-m-d', strtotime('+7 days'));

// Cek stok
$query_stok = "SELECT judul, stok FROM buku WHERE id = $buku_id";
$result_stok = mysqli_query($conn, $query_stok);
$buku = mysqli_fetch_assoc($result_stok);

if ($buku && $buku['stok'] > 0) {
    // Catatan: stok TIDAK dikurangi di sini. Stok baru dikurangi saat
    // petugas menyetujui pengajuan (lihat petugas/konfirmasi.php),
    // supaya buku tidak "terkunci" untuk pengajuan yang mungkin ditolak.
    $query = "INSERT INTO peminjaman (user_id, buku_id, tanggal_pinjam, tanggal_tenggat, status, biaya)
              VALUES ($user_id, $buku_id, '$tanggal_pinjam', '$tanggal_tenggat', 'pending', 50000)";

    if (mysqli_query($conn, $query)) {
        $nama_esc = mysqli_real_escape_string($conn, $nama);
        kirimNotifikasiSemuaPetugas($conn,
            "📥 $nama_esc mengajukan peminjaman buku \"{$buku['judul']}\", perlu dikonfirmasi.",
            'peminjaman', '../petugas/konfirmasi.php');
        echo "<script>alert('✅ Pengajuan peminjaman terkirim! Menunggu konfirmasi petugas.'); window.location='peminjaman.php';</script>";
    } else {
        echo "Gagal mengajukan pinjam: " . mysqli_error($conn);
    }
} else {
    echo "<script>alert('❌ Stok buku habis!'); window.location='dashboard.php';</script>";
}
?>