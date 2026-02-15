<?php
// Konfigurasi Database untuk InfinityFree
// PENTING: Data ini ada di Control Panel (vPanel) bagian "MySQL Databases"

// 1. MySQL Hostname
// Di InfinityFree, ini BUKAN 'localhost'.
// Contohnya: sql123.epizy.com atau sql300.infinityfree.com
define('DB_HOST', 'sql305.infinityfree.com'); 

// 2. MySQL Username
// Biasanya berawalan if0_, contoh: if0_35672839
define('DB_USER', 'if0_40709624');

// 3. MySQL Password
// Ini adalah password akun vPanel/Hosting Anda (bukan password login website)
// Bisa dilihat di Client Area -> Account Details -> Show Password
define('DB_PASS', 'wrekzFxX5TG');

// 4. MySQL Database Name
// Ada awalan user-nya, contoh: if0_35672839_smk_alaamin
define('DB_NAME', 'if0_40709624_smk_alaamin');

// Membuat koneksi
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, 'utf8');
?>