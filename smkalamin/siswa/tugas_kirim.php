<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('siswa');

$id_tugas = $_GET['id'];
$id_siswa = $_SESSION['user_id'];
$info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tugas WHERE id='$id_tugas'"));

if (isset($_POST['kirim'])) {
    $file = $_FILES['file']['name'];
    $tmp  = $_FILES['file']['tmp_name'];
    $new  = "jawaban_" . time() . "_" . $id_siswa . "." . pathinfo($file, PATHINFO_EXTENSION);

    if (move_uploaded_file($tmp, "../assets/uploads/" . $new)) {
        $q = "INSERT INTO tugas_siswa (tugas_id, siswa_id, file_jawaban) VALUES ('$id_tugas', '$id_siswa', '$new')";
        if(mysqli_query($conn, $q)) {
            echo "<script>alert('Tugas dikumpulkan!'); window.location='tugas.php';</script>";
        }
    } else {
        echo "<script>alert('Gagal upload!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kirim Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { background-color: #f3f4f6; } </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Kumpulkan Tugas: <?= $info['judul']; ?></h5>
                    </div>
                    <div class="card-body p-4">
                        <p><strong>Instruksi:</strong><br><?= nl2br($info['deskripsi']); ?></p>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">File Jawaban</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <button type="submit" name="kirim" class="btn btn-primary w-100">Kirim</button>
                            <a href="tugas.php" class="btn btn-link w-100 mt-2 text-decoration-none">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>