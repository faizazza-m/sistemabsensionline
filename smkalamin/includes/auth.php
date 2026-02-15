<?php
// includes/auth.php

// 1. CEK SESSION (WAJIB ADA DI PALING ATAS)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. FUNGSI LOGIN
function login($identifier, $password, $role) {
    global $conn;
    
    $result = [
        'success' => false,
        'message' => '',
        'user_id' => null,
        'nama' => '',
        'data' => []
    ];
    
    // Tentukan tabel berdasarkan role
    switch($role) {
        case 'siswa':
            $table = 'siswa';
            $id_field = 'nis';
            break;
        case 'guru':
            $table = 'guru';
            $id_field = 'nip';
            break;
        case 'admin':
            $table = 'admin';
            $id_field = 'username';
            break;
        default:
            $result['message'] = 'Role tidak valid';
            return $result;
    }
    
    // Sanitasi input
    $identifier = mysqli_real_escape_string($conn, $identifier);
    
    // Query cari user
    $query = "SELECT * FROM $table WHERE $id_field = '$identifier'";
    $sql = mysqli_query($conn, $query);
    
    if ($sql && mysqli_num_rows($sql) === 1) {
        $user = mysqli_fetch_assoc($sql);
        
        // Verifikasi Password (Mendukung hash dan plain text untuk legacy)
        $is_valid = false;
        
        // Cek apakah password ter-hash (panjang string > 50 karakter biasanya hash)
        if (strlen($user['password']) > 50) {
            if (password_verify($password, $user['password'])) $is_valid = true;
        } else {
            // Fallback untuk password biasa (misal: 123456)
            if ($password == $user['password']) $is_valid = true; 
        }

        if ($is_valid) {
            $result['success'] = true;
            $result['user_id'] = $user['id']; // Pastikan kolom ID di db bernama 'id'
            
            // Ambil nama
            if (isset($user['nama_lengkap'])) $result['nama'] = $user['nama_lengkap'];
            elseif (isset($user['nama'])) $result['nama'] = $user['nama'];
            else $result['nama'] = $identifier;
            
            $result['data'] = $user;
        } else {
            $result['message'] = 'Password salah';
        }
    } else {
        $result['message'] = 'Akun tidak ditemukan';
    }
    
    return $result;
}

// 3. FUNGSI REDIRECT (Penyebab Fatal Error sebelumnya)
function redirectBasedOnRole() {
    if (isset($_SESSION['role'])) {
        $role = $_SESSION['role'];
        if ($role == 'admin') {
            header('Location: admin/index.php');
        } elseif ($role == 'guru') {
            header('Location: guru/index.php');
        } elseif ($role == 'siswa') {
            header('Location: siswa/index.php');
        }
        exit();
    }
}

// 4. FUNGSI CEK LOGIN DI DASHBOARD
function redirectIfNotLoggedIn($role = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
    
    if ($role && $_SESSION['role'] !== $role) {
        // Redirect ke dashboard yang benar jika salah kamar
        if($_SESSION['role'] == 'admin') header('Location: ../admin/index.php');
        else if($_SESSION['role'] == 'guru') header('Location: ../guru/index.php');
        else if($_SESSION['role'] == 'siswa') header('Location: ../siswa/index.php');
        exit();
    }
}
?>