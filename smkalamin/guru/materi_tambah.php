<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('guru');

if (isset($_POST['upload'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $mapel = $_POST['mapel_id'];
    $kelas = $_POST['kelas_id'];
    $desc  = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $guru  = $_SESSION['user_id'];

    $file = $_FILES['file']['name'];
    $tmp  = $_FILES['file']['tmp_name'];
    $new  = "materi_" . time() . "." . pathinfo($file, PATHINFO_EXTENSION);

    if (move_uploaded_file($tmp, "../assets/uploads/" . $new)) {
        $q = "INSERT INTO materi (guru_id, mapel_id, kelas_id, judul, deskripsi, file_path) VALUES ('$guru', '$mapel', '$kelas', '$judul', '$desc', '$new')";
        if(mysqli_query($conn, $q)) {
            echo "<script>alert('Berhasil upload!'); window.location='materi.php';</script>";
        }
    } else {
        echo "<script>alert('Gagal upload file!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Materi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { background-color: #f3f4f6; } </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Upload Materi Baru</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Judul Materi</label>
                                <input type="text" name="judul" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mata Pelajaran</label>
                                    <select name="mapel_id" class="form-select" required>
                                        <?php 
                                        $q = mysqli_query($conn, "SELECT * FROM mata_pelajaran");
                                        while($r=mysqli_fetch_assoc($q)) echo "<option value='{$r['id']}'>{$r['nama']}</option>";
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kelas</label>
                                    <select name="kelas_id" class="form-select" required>
                                        <?php 
                                        $q = mysqli_query($conn, "SELECT * FROM kelas");
                                        while($r=mysqli_fetch_assoc($q)) echo "<option value='{$r['id']}'>{$r['nama']}</option>";
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">File (PDF/DOC/PPT)</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" name="upload" class="btn btn-primary w-100">Upload</button>
                            <a href="materi.php" class="btn btn-link w-100 mt-2 text-center text-decoration-none">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>