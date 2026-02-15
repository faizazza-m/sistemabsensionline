<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('admin');

// Ambil ID dari URL
$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM kelas WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan, kembalikan ke index
if(mysqli_num_rows($query) < 1) {
    header("location:kelas.php");
}

// --- PROSES UPDATE ---
if (isset($_POST['update'])) {
    $nama_kelas = mysqli_real_escape_string($conn, $_POST['nama']);

    if (!empty($nama_kelas)) {
        $update = mysqli_query($conn, "UPDATE kelas SET nama = '$nama_kelas' WHERE id = '$id'");
        if ($update) {
            echo "<script>alert('Data berhasil diperbarui!'); window.location='kelas.php';</script>";
        } else {
            $error = "Gagal mengupdate database.";
        }
    } else {
        $error = "Nama kelas tidak boleh kosong.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Kelas - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Edit Data Kelas</h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nama Kelas</label>
                                <input type="text" name="nama" class="form-control" value="<?php echo $data['nama']; ?>" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="kelas.php" class="btn btn-secondary">Kembali</a>
                                <button type="submit" name="update" class="btn btn-primary">Update Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>