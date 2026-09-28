<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi.php';
cekRole(['siswa']);

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];
$peminjaman_id = intval($_GET['id']);

$query_cek = "SELECT p.*, b.judul FROM peminjaman p JOIN buku b ON p.buku_id = b.id
              WHERE p.id = $peminjaman_id AND p.user_id = $user_id AND p.status = 'dipinjam'";
$result = mysqli_query($conn, $query_cek);

if (mysqli_num_rows($result) > 0) {
    $p = mysqli_fetch_assoc($result);

    // Catatan: status jadi 'menunggu_verifikasi' dulu, BUKAN langsung
    // 'dikembalikan'. Stok baru dikembalikan setelah petugas memverifikasi
    // (lihat petugas/verifikasi_kembali.php).
    $query_update = "UPDATE peminjaman SET status = 'menunggu_verifikasi' WHERE id = $peminjaman_id";

    if (mysqli_query($conn, $query_update)) {
        $nama_esc = mysqli_real_escape_string($conn, $nama);
        kirimNotifikasiSemuaPetugas($conn,
            "↩️ $nama_esc mengembalikan buku \"{$p['judul']}\", perlu diverifikasi.",
            'pengembalian', '../petugas/verifikasi_kembali.php');
        echo "<script>alert('✅ Permintaan pengembalian terkirim! Menunggu verifikasi petugas.'); window.location='peminjaman.php';</script>";
    } else {
        echo "Gagal mengembalikan: " . mysqli_error($conn);
    }
} else {
    echo "<script>alert('❌ Data tidak valid!'); window.location='peminjaman.php';</script>";
}
?>