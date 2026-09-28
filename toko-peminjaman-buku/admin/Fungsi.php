<?php
// =====================================================================
// fungsi.php — kumpulan fungsi bantu yang dipakai di admin/, petugas/, user/
// Cara pakai: include '../config/fungsi.php'; SETELAH session_start()
// =====================================================================

/**
 * Pastikan user sudah login DAN rolenya termasuk yang diizinkan.
 * Kalau belum login -> lempar ke login. Kalau login tapi role tidak sesuai
 * -> lempar balik ke login juga (supaya tidak bisa nebak-nebak halaman lain).
 *
 * @param array  $roles_diizinkan  contoh: ['admin'] atau ['admin','petugas']
 * @param string $redirect_login   path relatif ke login.php dari file pemanggil
 */
function cekRole($roles_diizinkan, $redirect_login = '../auth/login.php') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . $redirect_login);
        exit();
    }
    if (!in_array($_SESSION['role'], $roles_diizinkan)) {
        header('Location: ' . $redirect_login);
        exit();
    }
}

/**
 * Kirim satu notifikasi ke satu user (siswa ATAU petugas, sama saja).
 */
function kirimNotifikasi($conn, $user_id, $pesan, $tipe = 'info', $link = null) {
    $user_id = intval($user_id);
    $pesan   = mysqli_real_escape_string($conn, $pesan);
    $tipe    = mysqli_real_escape_string($conn, $tipe);
    $link_sql = $link !== null ? "'" . mysqli_real_escape_string($conn, $link) . "'" : "NULL";

    mysqli_query($conn, "INSERT INTO notifikasi (user_id, tipe, pesan, link)
                          VALUES ($user_id, '$tipe', '$pesan', $link_sql)");
}

/**
 * Kirim notifikasi yang sama ke SEMUA petugas sekaligus.
 * Dipakai saat ada siswa mengajukan pinjam / mengembalikan buku.
 */
function kirimNotifikasiSemuaPetugas($conn, $pesan, $tipe = 'peminjaman', $link = null) {
    $q = mysqli_query($conn, "SELECT id FROM users WHERE role = 'petugas'");
    while ($row = mysqli_fetch_assoc($q)) {
        kirimNotifikasi($conn, $row['id'], $pesan, $tipe, $link);
    }
}

/**
 * Hitung berapa hari terlambat (0 kalau belum/tidak terlambat).
 */
function hitungHariTerlambat($tanggal_tenggat) {
    $tenggat = strtotime($tanggal_tenggat);
    $hari_ini = strtotime(date('Y-m-d'));
    $selisih = floor(($hari_ini - $tenggat) / 86400);
    return $selisih > 0 ? (int) $selisih : 0;
}

/**
 * Hitung saran nominal denda (Rp 5.000 / hari terlambat). Nilai ini cuma
 * SARAN — petugas tetap bisa mengubahnya manual di petugas/denda.php.
 */
function saranDenda($tanggal_tenggat, $per_hari = 5000) {
    return hitungHariTerlambat($tanggal_tenggat) * $per_hari;
}