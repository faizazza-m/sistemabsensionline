<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('guru');

$id_guru = $_SESSION['user_id'];

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $q = mysqli_query($conn, "SELECT file_path FROM materi WHERE id='$id' AND guru_id='$id_guru'");
    if ($row = mysqli_fetch_assoc($q)) {
        $file = "../assets/uploads/" . $row['file_path'];
        if (file_exists($file)) unlink($file);
        mysqli_query($conn, "DELETE FROM materi WHERE id='$id'");
        echo "<script>alert('Materi dihapus'); window.location='materi.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Materi - Dashboard Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #1e40af;
            --danger: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            overflow-x: hidden;
            font-size: 0.95rem;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 260px;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            background: rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header h4 {
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .sidebar-header small {
            opacity: 0.9;
            font-size: 0.85rem;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
            height: calc(100vh - 120px);
            overflow-y: auto;
        }
        
        .sidebar-menu::-webkit-scrollbar {
            width: 5px;
        }
        
        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.9rem;
            transition: all 0.3s;
            font-weight: 500;
            border-left: 3px solid transparent;
            margin: 2px 0;
        }
        
        .sidebar-menu .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.12);
            border-left: 3px solid rgba(255,255,255,0.7);
            padding-left: 1.7rem;
        }
        
        .sidebar-menu .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.15);
            border-left: 3px solid white;
            font-weight: 600;
        }
        
        .sidebar-menu .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        /* Main Content Styles */
        .main-content {
            margin-left: 260px;
            padding: 1.2rem;
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        /* Navbar Styles */
        .navbar-custom {
            background: white;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            padding: 0.8rem 0;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            border-radius: 12px 12px 0 0 !important;
            padding: 1.2rem 1.5rem;
            font-weight: 600;
        }
        
        .card-header.bg-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%) !important;
            color: white;
        }
        
        /* Buttons */
        .btn-admin {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
        }
        
        .btn-admin:hover {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
            color: white;
        }
        
        .btn-outline-admin {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
            padding: 0.7rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-admin:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.2);
        }
        
        /* Table styling */
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.08);
        }
        
        .table {
            margin-bottom: 0;
            font-size: 0.95rem;
        }
        
        .table thead {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        
        .table thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            font-size: 0.95rem;
            vertical-align: middle;
        }
        
        .table tbody tr {
            transition: all 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #e2e8f0;
        }
        
        /* Badge styles */
        .badge {
            padding: 0.4rem 0.8rem;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        
        .badge-primary {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
            color: var(--primary);
            border: 1px solid rgba(37, 99, 235, 0.2);
        }
        
        .badge-secondary {
            background: linear-gradient(135deg, rgba(100, 116, 139, 0.1) 0%, rgba(100, 116, 139, 0.05) 100%);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.2);
        }
        
        /* File icon styling */
        .file-card {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.05) 0%, rgba(37, 99, 235, 0.02) 100%);
            border-radius: 10px;
            border: 1px solid rgba(37, 99, 235, 0.1);
            transition: all 0.3s ease;
        }
        
        .file-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-color: var(--primary);
        }
        
        .file-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            font-size: 1.5rem;
        }
        
        .file-icon.pdf { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .file-icon.word { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
        .file-icon.excel { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .file-icon.ppt { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .file-icon.image { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
        .file-icon.zip { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }
        .file-icon.default { background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); }
        
        .file-info h6 {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--dark);
        }
        
        .file-info p {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        
        .file-link {
            text-decoration: none;
            color: inherit;
            transition: color 0.2s;
        }
        
        .file-link:hover {
            color: var(--primary);
        }
        
        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }
        
        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Empty state */
        .empty-state {
            padding: 4rem 1rem;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }
        
        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar {
                margin-left: -260px;
                width: 260px;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .main-content.sidebar-active {
                margin-left: 260px;
            }
            
            .navbar-custom {
                margin-top: 0.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 0.8rem;
            }
            
            .card-header {
                padding: 1rem 1.2rem;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.75rem;
            }
            
            .btn-admin, .btn-outline-admin {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
            
            .file-card {
                flex-direction: column;
                text-align: center;
            }
            
            .file-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .table {
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
            }
            
            .table-responsive {
                border: none;
                box-shadow: none;
            }
            
            .table thead {
                display: none;
            }
            
            .table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            }
            
            .table tbody td {
                display: block;
                text-align: left;
                border: none;
                position: relative;
                padding-left: 50%;
                border-bottom: 1px solid #e2e8f0;
            }
            
            .table tbody td:last-child {
                border-bottom: none;
            }
            
            .table tbody td:before {
                content: attr(data-label);
                position: absolute;
                left: 1rem;
                width: 45%;
                padding-right: 1rem;
                font-weight: 600;
                color: var(--primary);
            }
            
            .empty-state {
                padding: 3rem 1rem;
            }
        }
        
        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                Guru Dashboard
            </h4>
            <small><?php echo htmlspecialchars($_SESSION['nama']); ?></small>
        </div>
        
        <div class="sidebar-menu">
            <nav class="nav flex-column">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a class="nav-link" href="absensi.php">
                    <i class="fas fa-calendar-check"></i> Absensi Siswa
                </a>
                <a class="nav-link active" href="materi.php">
                    <i class="fas fa-book-open"></i> Materi
                </a>
                <a class="nav-link" href="tugas.php">
                    <i class="fas fa-tasks"></i> Tugas
                </a>
                <a class="nav-link" href="jadwal.php">
                    <i class="fas fa-calendar-alt"></i> Jadwal Mengajar
                </a>
                <a class="nav-link" href="absensi_saya.php">
                    <i class="fas fa-fingerprint"></i> Absensi Saya
                </a>
                <div class="mt-auto pt-3">
                    <a class="nav-link" href="ganti_password.php">
                        <i class="fas fa-key"></i> Ganti Password
                    </a>
                    <a class="nav-link text-danger" href="../includes/logout.php" style="border-left: 3px solid transparent;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-custom rounded">
            <div class="container-fluid px-0">
                <button class="btn btn-outline-primary d-lg-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="navbar-brand d-none d-md-block ms-2">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-book-open me-2"></i>
                        Manajemen Materi Pembelajaran
                    </h5>
                </div>
                
                <div class="ms-auto">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center" type="button" 
                                data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['nama']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <div class="dropdown-header">
                                    <strong>Akun Guru</strong>
                                    <div class="small text-muted"><?php echo htmlspecialchars($_SESSION['nama']); ?></div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="ganti_password.php">
                                <i class="fas fa-key me-2"></i>Ganti Password
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../includes/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Card -->
        <div class="card fade-in">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-book-open me-2"></i>Daftar Materi Pembelajaran
                    </h5>
                    <p class="mb-0 opacity-90 small">Kelola semua materi pembelajaran yang telah diupload</p>
                </div>
                <a href="materi_tambah.php" class="btn btn-admin">
                    <i class="fas fa-upload me-2"></i>Upload Materi Baru
                </a>
            </div>
            
            <div class="card-body">
                <?php
                $query = "SELECT m.*, mp.nama as mapel, k.nama as kelas FROM materi m 
                          JOIN mata_pelajaran mp ON m.mapel_id = mp.id 
                          JOIN kelas k ON m.kelas_id = k.id 
                          WHERE m.guru_id = '$id_guru' ORDER BY m.tanggal DESC";
                $res = mysqli_query($conn, $query);
                $total_materi = mysqli_num_rows($res);
                ?>
                
                <?php if($total_materi > 0): ?>
                    <div class="mb-4">
                        <div class="alert alert-info border-0 d-flex align-items-center" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%); border-left: 4px solid var(--primary);">
                            <i class="fas fa-info-circle me-3" style="color: var(--primary); font-size: 1.2rem;"></i>
                            <div>
                                <strong class="d-block">Total <?= $total_materi; ?> Materi</strong>
                                <small class="text-muted">Semua materi yang telah diupload akan ditampilkan di sini</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th>Judul Materi</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas</th>
                                    <th>Tanggal Upload</th>
                                    <th class="text-center" width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                mysqli_data_seek($res, 0);
                                while($row = mysqli_fetch_assoc($res)):
                                    // Tentukan icon berdasarkan ekstensi file
                                    $file_ext = strtolower(pathinfo($row['file_path'], PATHINFO_EXTENSION));
                                    $icon = 'fa-file-alt'; // default
                                    $icon_class = 'default';
                                    
                                    if (in_array($file_ext, ['pdf'])) {
                                        $icon = 'fa-file-pdf';
                                        $icon_class = 'pdf';
                                    } elseif (in_array($file_ext, ['doc', 'docx'])) {
                                        $icon = 'fa-file-word';
                                        $icon_class = 'word';
                                    } elseif (in_array($file_ext, ['xls', 'xlsx'])) {
                                        $icon = 'fa-file-excel';
                                        $icon_class = 'excel';
                                    } elseif (in_array($file_ext, ['ppt', 'pptx'])) {
                                        $icon = 'fa-file-powerpoint';
                                        $icon_class = 'ppt';
                                    } elseif (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        $icon = 'fa-file-image';
                                        $icon_class = 'image';
                                    } elseif (in_array($file_ext, ['zip', 'rar'])) {
                                        $icon = 'fa-file-archive';
                                        $icon_class = 'zip';
                                    }
                                ?>
                                <tr>
                                    <td data-label="No" class="text-center fw-semibold"><?= $no++; ?></td>
                                    <td data-label="Judul Materi">
                                        <a href="../assets/uploads/<?= htmlspecialchars($row['file_path']); ?>" 
                                           target="_blank" 
                                           class="file-link">
                                            <div class="file-card">
                                                <div class="file-icon <?= $icon_class; ?>">
                                                    <i class="fas <?= $icon; ?>"></i>
                                                </div>
                                                <div class="file-info">
                                                    <h6 class="mb-1"><?= htmlspecialchars($row['judul']); ?></h6>
                                                    <?php if(!empty($row['deskripsi'])): ?>
                                                        <p class="mb-1"><?= htmlspecialchars(substr($row['deskripsi'], 0, 60)); ?>...</p>
                                                    <?php endif; ?>
                                                    <small class="text-muted">
                                                        <i class="far fa-file me-1"></i>
                                                        <?= $file_ext; ?> • 
                                                        <i class="far fa-clock me-1"></i>
                                                        <?= date('H:i', strtotime($row['tanggal'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td data-label="Mata Pelajaran">
                                        <span class="badge badge-primary">
                                            <?= htmlspecialchars($row['mapel']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Kelas">
                                        <span class="badge badge-secondary">
                                            <?= htmlspecialchars($row['kelas']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Tanggal Upload">
                                        <div class="text-muted">
                                            <i class="far fa-calendar me-1"></i>
                                            <?= date('d/m/Y', strtotime($row['tanggal'])); ?>
                                        </div>
                                    </td>
                                    <td data-label="Aksi" class="text-center">
                                        <div class="action-buttons">
                                            <a href="../assets/uploads/<?= htmlspecialchars($row['file_path']); ?>" 
                                               target="_blank" 
                                               class="action-btn btn btn-outline-primary btn-sm" 
                                               title="Lihat Materi">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="materi.php?hapus=<?= $row['id']; ?>" 
                                               class="action-btn btn btn-danger btn-sm" 
                                               onclick="return confirm('Yakin ingin menghapus materi ini?')" 
                                               title="Hapus Materi">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Summary -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Materi terakhir diupload: 
                                    <?php 
                                    $last_upload = mysqli_query($conn, "SELECT MAX(tanggal) as last_date FROM materi WHERE guru_id='$id_guru'");
                                    $last_date = mysqli_fetch_assoc($last_upload)['last_date'];
                                    echo $last_date ? date('d F Y, H:i', strtotime($last_date)) : '-';
                                    ?>
                                </small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <small class="text-muted">
                                    Total file: <?= $total_materi; ?> • 
                                    Sistem Materi © <?php echo date('Y'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state fade-in">
                        <i class="fas fa-folder-open"></i>
                        <h4 class="text-muted mt-3 mb-2">Belum Ada Materi</h4>
                        <p class="text-muted mb-4">Anda belum mengupload materi pembelajaran.</p>
                        <a href="materi_tambah.php" class="btn btn-admin btn-lg px-5">
                            <i class="fas fa-upload me-2"></i>Upload Materi Pertama
                        </a>
                        <div class="mt-4">
                            <div class="alert alert-info border-0" style="max-width: 500px; margin: 0 auto;">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Tips:</strong> Upload materi dalam format PDF, DOC, PPT, atau gambar untuk dibagikan kepada siswa.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            mainContent.classList.toggle('sidebar-active');
        }
        
        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
        
        // Close sidebar when clicking on a link (for mobile)
        if (window.innerWidth < 992) {
            const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (sidebar.classList.contains('active')) {
                        toggleSidebar();
                    }
                });
            });
        }
        
        // Mobile table conversion
        function convertTableToCards() {
            if (window.innerWidth < 576) {
                const table = document.querySelector('table');
                if (!table) return;
                
                const headers = [];
                const headerCells = table.querySelectorAll('thead th');
                headerCells.forEach((cell, index) => {
                    headers[index] = cell.textContent.trim();
                });
                
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    cells.forEach((cell, index) => {
                        if (headers[index]) {
                            cell.setAttribute('data-label', headers[index]);
                        }
                    });
                });
            }
        }
        
        // Confirmation for delete
        document.addEventListener('DOMContentLoaded', function() {
            convertTableToCards();
            window.addEventListener('resize', convertTableToCards);
            
            const deleteButtons = document.querySelectorAll('a[href*="hapus="]');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Yakin ingin menghapus materi ini? File yang dihapus tidak dapat dikembalikan.')) {
                        e.preventDefault();
                    }
                });
            });
            
            // Add hover effects to table rows
            const tableRows = document.querySelectorAll('.table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>
</body>
</html>