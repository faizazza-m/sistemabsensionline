<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('guru');

if (isset($_POST['simpan'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $kelas = $_POST['kelas_id'];
    $dl    = $_POST['deadline'];
    $desc  = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $guru  = $_SESSION['user_id'];
    $file_name = "";

    if (!empty($_FILES['file']['name'])) {
        $file_name = "tugas_" . time() . "." . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['file']['tmp_name'], "../assets/uploads/" . $file_name);
    }

    $q = "INSERT INTO tugas (guru_id, kelas_id, judul, deskripsi, file_tugas, deadline) VALUES ('$guru', '$kelas', '$judul', '$desc', '$file_name', '$dl')";
    if(mysqli_query($conn, $q)) {
        echo "<script>alert('Tugas diterbitkan!'); window.location='tugas.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { background-color: #f3f4f6; } </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Buat Tugas Baru</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Judul Tugas</label>
                                <input type="text" name="judul" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kelas Target</label>
                                    <select name="kelas_id" class="form-select" required>
                                        <?php 
                                        $q = mysqli_query($conn, "SELECT * FROM kelas");
                                        while($r=mysqli_fetch_assoc($q)) echo "<option value='{$r['id']}'>{$r['nama']}</option>";
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Deadline</label>
                                    <input type="datetime-local" name="deadline" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lampiran (Opsional)</label>
                                <input type="file" name="file" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instruksi</label>
                                <textarea name="deskripsi" class="form-control" rows="4"></textarea>
                            </div>
                            <button type="submit" name="simpan" class="btn btn-primary w-100">Terbitkan</button>
                            <a href="tugas.php" class="btn btn-link w-100 mt-2 text-decoration-none">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>