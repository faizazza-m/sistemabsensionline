<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('admin');

// --- PROSES SIMPAN ---
if (isset($_POST['simpan'])) {
    $nama_kelas = mysqli_real_escape_string($conn, $_POST['nama']);

    if (!empty($nama_kelas)) {
        $insert = mysqli_query($conn, "INSERT INTO kelas (nama) VALUES ('$nama_kelas')");
        if ($insert) {
            echo "<script>alert('Kelas berhasil ditambahkan!'); window.location='kelas.php';</script>";
        } else {
            $error = "Gagal menyimpan ke database.";
        }
    } else {
        $error = "Nama kelas tidak boleh kosong.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Kelas - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tambah Kelas Baru</h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nama Kelas</label>
                                <input type="text" name="nama" class="form-control" placeholder="Contoh: X TKJ 1" required autofocus>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="kelas.php" class="btn btn-secondary">Kembali</a>
                                <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>