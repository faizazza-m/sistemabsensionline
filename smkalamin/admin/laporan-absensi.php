<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Cek login admin
redirectIfNotLoggedIn('admin');

// --- FILTER TANGGAL ---
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Hitung statistik
$query_stats = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) as alpha
    FROM absensi_siswa 
    WHERE DATE(tanggal) = '$tanggal'
";
$stats_result = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi - Admin</title>
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
        
        /* Table Styles */
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid var(--primary);
            color: var(--dark);
            font-weight: 600;
            padding: 1rem;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 0.9rem 1rem;
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(37, 99, 235, 0.04);
        }
        
        .table-bordered {
            border: 1px solid rgba(0,0,0,0.08);
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
        }
        
        .btn-export {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%);
            border: none;
            color: white;
        }
        
        .btn-export:hover {
            background: linear-gradient(135deg, #0da271 0%, var(--success) 100%);
            transform: translateY(-3px);
        }
        
        /* Badge Styles */
        .badge {
            font-weight: 500;
            padding: 0.5em 1em;
            min-width: 80px;
        }
        
        /* Status Badges */
        .badge-hadir { background: linear-gradient(135deg, var(--success) 0%, #0da271 100%); }
        .badge-sakit { background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%); }
        .badge-izin { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .badge-alpha { background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%); }
        
        /* Stats Cards */
        .stat-card {
            border-radius: 10px;
            padding: 1rem;
            height: 100%;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
        }
        
        .stat-icon {
            font-size: 1.8rem;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            margin-bottom: 0.5rem;
        }
        
        /* Date Picker */
        .date-picker-container {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        /* Alert */
        .alert-info-custom {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(30, 64, 175, 0.05) 100%);
            border: 1px solid rgba(37, 99, 235, 0.2);
            color: var(--primary);
            border-radius: 10px;
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
            
            .table thead th,
            .table tbody td {
                padding: 0.7rem;
                font-size: 0.9rem;
            }
            
            .stat-card h4 {
                font-size: 1.3rem;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 0.8rem;
            }
            
            .card-header .row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .card-header .col-md-6 {
                width: 100%;
            }
            
            .date-picker-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .date-picker-container input,
            .date-picker-container button {
                width: 100%;
            }
            
            .table-responsive {
                font-size: 0.85rem;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.6rem;
            }
            
            .badge {
                padding: 0.4em 0.8em;
                min-width: 70px;
                font-size: 0.8rem;
            }
            
            .stats-row .col-md-3 {
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.5rem;
            }
            
            .badge {
                padding: 0.3em 0.6em;
                min-width: 60px;
                font-size: 0.75rem;
            }
            
            .stat-card {
                padding: 0.8rem;
            }
            
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.5rem;
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
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        /* Progress bar */
        .progress-custom {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
        }
        
        /* Export buttons */
        .export-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0"><i class="fas fa-user-shield me-2"></i>Admin Panel</h4>
            <small>SMK Al Amin Cibening</small>
        </div>
        <div class="sidebar-menu">
            <nav class="nav flex-column">
                <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                <a class="nav-link" href="users.php"><i class="fas fa-users"></i> Manajemen User</a>
                <a class="nav-link" href="mapel.php"><i class="fas fa-book"></i> Mata Pelajaran</a>
                <a class="nav-link" href="kelas.php"><i class="fas fa-chalkboard"></i> Kelas</a>
                <a class="nav-link active" href="laporan-absensi.php"><i class="fas fa-chart-bar"></i> Laporan Absensi</a>
                <a class="nav-link" href="laporan-tugas.php"><i class="fas fa-file-alt"></i> Laporan Tugas</a>
                <div class="mt-auto pt-3">
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
                    <h5 class="mb-0 text-dark"><i class="fas fa-chart-bar me-2"></i>Laporan Absensi</h5>
                </div>
                
                <div class="ms-auto">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-2"></i>
                            <?php echo $_SESSION['nama']; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-user me-2"></i>Profil Admin
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
        
        <!-- Header Card -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                <i class="fas fa-chart-bar fa-lg text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 text-primary fw-bold">Laporan Absensi Siswa</h5>
                                <small class="text-muted">Analisis dan monitoring kehadiran siswa</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- PERBAIKAN DI SINI: Form dengan method GET dan action kosong -->
                        <form method="GET" action="" class="date-picker-container d-flex gap-2 justify-content-end align-items-center">
                            <input type="date" name="tanggal" class="form-control" value="<?= $tanggal; ?>" 
                                   style="max-width: 200px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                            <div class="export-buttons">
                                <button type="button" class="btn btn-export" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i>Print
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Date Info Alert -->
                <div class="alert alert-info-custom mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt fa-lg me-3"></i>
                        <div>
                            <h6 class="mb-1 fw-bold">Data Absensi</h6>
                            <p class="mb-0">Menampilkan data absensi untuk tanggal: 
                                <span class="fw-bold"><?= date('d F Y', strtotime($tanggal)); ?></span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row stats-row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card bg-light border-start border-primary border-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0 fw-bold text-primary"><?= $stats['total'] ?: 0; ?></h4>
                                    <p class="text-muted mb-0 small">Total Absensi</p>
                                </div>
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card bg-light border-start border-success border-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0 fw-bold text-success"><?= $stats['hadir'] ?: 0; ?></h4>
                                    <p class="text-muted mb-0 small">Hadir</p>
                                </div>
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <?php if($stats['total'] > 0): ?>
                            <div class="progress-custom mt-2">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= ($stats['hadir'] / $stats['total'] * 100) ?>%"></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card bg-light border-start border-warning border-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0 fw-bold text-warning"><?= $stats['sakit'] + $stats['izin'] ?: 0; ?></h4>
                                    <p class="text-muted mb-0 small">Tidak Hadir</p>
                                </div>
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-user-times"></i>
                                </div>
                            </div>
                            <?php if($stats['total'] > 0): ?>
                            <div class="progress-custom mt-2">
                                <div class="progress-bar bg-warning" role="progressbar" 
                                     style="width: <?= (($stats['sakit'] + $stats['izin']) / $stats['total'] * 100) ?>%"></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card bg-light border-start border-danger border-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0 fw-bold text-danger"><?= $stats['alpha'] ?: 0; ?></h4>
                                    <p class="text-muted mb-0 small">Alpha</p>
                                </div>
                                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                            <?php if($stats['total'] > 0): ?>
                            <div class="progress-custom mt-2">
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: <?= ($stats['alpha'] / $stats['total'] * 100) ?>%"></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Absensi Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="35%">Nama Siswa</th>
                                <th width="20%">Kelas</th>
                                <th width="20%" class="text-center">Status</th>
                                <th width="20%" class="text-center">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = "SELECT absensi_siswa.*, siswa.nama, kelas.nama as nama_kelas 
                                      FROM absensi_siswa 
                                      JOIN siswa ON absensi_siswa.siswa_id = siswa.id 
                                      JOIN kelas ON siswa.kelas_id = kelas.id 
                                      WHERE DATE(absensi_siswa.tanggal) = '$tanggal'
                                      ORDER BY kelas.nama ASC, siswa.nama ASC";
                            
                            $result = mysqli_query($conn, $query);
                            
                            if(mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                                    $badge_class = 'badge-secondary';
                                    $icon = 'fa-question';
                                    
                                    switch($row['status']) {
                                        case 'Hadir':
                                            $badge_class = 'badge-hadir';
                                            $icon = 'fa-check-circle';
                                            break;
                                        case 'Sakit':
                                            $badge_class = 'badge-sakit';
                                            $icon = 'fa-head-side-cough';
                                            break;
                                        case 'Izin':
                                            $badge_class = 'badge-izin';
                                            $icon = 'fa-envelope';
                                            break;
                                        case 'Alpha':
                                            $badge_class = 'badge-alpha';
                                            $icon = 'fa-times-circle';
                                            break;
                                    }
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                            <i class="fas fa-user-graduate text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold"><?= $row['nama']; ?></h6>
                                            <small class="text-muted">Siswa</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3 py-2">
                                        <?= $row['nama_kelas']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $badge_class; ?> text-white px-3 py-2">
                                        <i class="fas <?= $icon; ?> me-1"></i><?= $row['status']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="fw-bold"><?= date('H:i', strtotime($row['tanggal'])); ?></span>
                                        <small class="text-muted">WIB</small>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-clipboard-list text-muted"></i>
                                        <h5 class="mt-3 mb-2">Belum ada data absensi</h5>
                                        <p class="text-muted mb-0">Tidak ada data absensi untuk tanggal <?= date('d F Y', strtotime($tanggal)); ?></p>
                                        <div class="mt-3">
                                            <a href="?tanggal=<?= date('Y-m-d'); ?>" class="btn btn-primary">
                                                <i class="fas fa-calendar-day me-2"></i>Lihat Hari Ini
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary -->
                <?php if(mysqli_num_rows($result) > 0): ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Menampilkan <span class="fw-bold"><?= mysqli_num_rows($result); ?></span> data absensi
                    </div>
                    <div>
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Cetak Laporan
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-5 pt-4 border-top text-center text-muted">
            <p class="mb-1">© <?php echo date('Y'); ?> SMK Al Amin Cibening. All rights reserved.</p>
            <p class="small">Laporan Absensi v1.0</p>
        </footer>
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
        
        // Add hover effect to table rows
        const tableRows = document.querySelectorAll('.table tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(37, 99, 235, 0.04)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
        
        // Print functionality
        function printReport() {
            const printContents = document.querySelector('.card').innerHTML;
            const originalContents = document.body.innerHTML;
            
            document.body.innerHTML = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Laporan Absensi - <?= date('d F Y', strtotime($tanggal)); ?></title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .table { font-size: 12px; }
                        .badge { padding: 0.3em 0.6em; }
                        @media print {
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="container mt-4">
                        <h4 class="text-center mb-4">Laporan Absensi SMK Al Amin Cibening</h4>
                        <p class="text-center">Tanggal: <?= date('d F Y', strtotime($tanggal)); ?></p>
                        ${printContents}
                    </div>
                </body>
                </html>
            `;
            
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }
        
        // Attach print function to print button
        document.querySelectorAll('[onclick*="window.print"]').forEach(button => {
            button.addEventListener('click', printReport);
        });
        
        // Auto-submit on date change (alternative method)
        const dateInput = document.querySelector('input[name="tanggal"]');
        if (dateInput) {
            dateInput.addEventListener('change', function() {
                this.closest('form').submit();
            });
        }
    </script>
</body>
</html>