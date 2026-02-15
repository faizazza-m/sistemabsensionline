<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('admin');

// --- PROSES SIMPAN USER ---
if (isset($_POST['simpan'])) {
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Enkripsi password

    if ($role == 'siswa') {
        $nama = mysqli_real_escape_string($conn, $_POST['nama']);
        $nis = mysqli_real_escape_string($conn, $_POST['nis']);
        $kelas_id = $_POST['kelas_id'];
        
        // Simpan ke tabel SISWA
        $query = "INSERT INTO siswa (nis, nama, password, kelas_id) VALUES ('$nis', '$nama', '$password', '$kelas_id')";
    
    } elseif ($role == 'guru') {
        $nama = mysqli_real_escape_string($conn, $_POST['nama']);
        $nip = mysqli_real_escape_string($conn, $_POST['nip']);
        $mapel = mysqli_real_escape_string($conn, $_POST['mapel']);
        
        // Simpan ke tabel GURU
        $query = "INSERT INTO guru (nip, nama_lengkap, password, mata_pelajaran) VALUES ('$nip', '$nama', '$password', '$mapel')";
    
    } elseif ($role == 'admin') {
        $nama = mysqli_real_escape_string($conn, $_POST['nama']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        
        // Simpan ke tabel ADMIN
        $query = "INSERT INTO admin (nama, username, password) VALUES ('$nama', '$username', '$password')";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('User berhasil ditambahkan!'); window.location='users.php';</script>";
    } else {
        echo "<script>alert('Gagal! Pastikan NIS/NIP/Username belum terpakai.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah User - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #f3f4f6; }
        .form-section { display: none; } /* Sembunyikan form dulu */
        .form-section.active { display: block; animation: fadeIn 0.5s; }
        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tambah User Baru</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Pilih Role User</label>
                                <select class="form-select" name="role" id="roleSelect" required>
                                    <option value="" selected disabled>-- Pilih Role --</option>
                                    <option value="siswa">Siswa</option>
                                    <option value="guru">Guru</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            <hr>

                            <div id="form-siswa" class="form-section">
                                <div class="mb-3">
                                    <label class="form-label">NIS (Nomor Induk Siswa)</label>
                                    <input type="number" name="nis" class="form-control" placeholder="Contoh: 10112">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pilih Kelas</label>
                                    <select name="kelas_id" class="form-select">
                                        <?php
                                        $kelas = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama ASC");
                                        while($k = mysqli_fetch_assoc($kelas)){
                                            echo "<option value='{$k['id']}'>{$k['nama']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div id="form-guru" class="form-section">
                                <div class="mb-3">
                                    <label class="form-label">NIP (Nomor Induk Pegawai)</label>
                                    <input type="number" name="nip" class="form-control" placeholder="Contoh: 19800101...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mata Pelajaran yang Diampu</label>
                                    <input type="text" name="mapel" class="form-control" placeholder="Contoh: Matematika">
                                </div>
                            </div>

                            <div id="form-admin" class="form-section">
                                <div class="mb-3">
                                    <label class="form-label">Username Login</label>
                                    <input type="text" name="username" class="form-control" placeholder="Contoh: admin2">
                                </div>
                            </div>

                            <div id="form-umum" style="display:none;">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" required placeholder="Nama Lengkap User">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required placeholder="Masukkan Password">
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="users.php" class="btn btn-secondary">Kembali</a>
                                    <button type="submit" name="simpan" class="btn btn-primary px-4">Simpan Data</button>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const roleSelect = document.getElementById('roleSelect');
        const formSiswa = document.getElementById('form-siswa');
        const formGuru = document.getElementById('form-guru');
        const formAdmin = document.getElementById('form-admin');
        const formUmum = document.getElementById('form-umum');

        roleSelect.addEventListener('change', function() {
            // Sembunyikan semua dulu
            formSiswa.classList.remove('active');
            formGuru.classList.remove('active');
            formAdmin.classList.remove('active');
            formUmum.style.display = 'block'; // Tampilkan form nama & password

            // Tampilkan sesuai pilihan
            if(this.value === 'siswa') {
                formSiswa.classList.add('active');
            } else if(this.value === 'guru') {
                formGuru.classList.add('active');
            } else if(this.value === 'admin') {
                formAdmin.classList.add('active');
            }
        });
    </script>
</body>
</html>