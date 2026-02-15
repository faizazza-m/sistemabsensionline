<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Cek login admin
redirectIfNotLoggedIn('admin');

// Hitung statistik tugas
$query_stats = "
    SELECT 
        COUNT(*) as total_tugas,
        COUNT(DISTINCT guru_id) as total_guru,
        COUNT(DISTINCT kelas_id) as total_kelas
    FROM tugas
";
$stats_result = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($stats_result);

// Hitung tugas yang sudah lewat deadline
$today = date('Y-m-d H:i:s');
$query_overdue = "SELECT COUNT(*) as overdue FROM tugas WHERE deadline < '$today'";
$overdue_result = mysqli_query($conn, $query_overdue);
$overdue = mysqli_fetch_assoc($overdue_result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tugas - Admin</title>
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
        
        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }
        
        .btn-download {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%);
            border: none;
            color: white;
        }
        
        .btn-download:hover {
            background: linear-gradient(135deg, #0da271 0%, var(--success) 100%);
            transform: translateY(-2px);
            color: white;
        }
        
        /* Badge Styles */
        .badge {
            font-weight: 500;
            padding: 0.5em 1em;
        }
        
        /* Status Badges */
        .badge-overdue { background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%); }
        .badge-upcoming { background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%); }
        .badge-completed { background: linear-gradient(135deg, var(--success) 0%, #0da271 100%); }
        
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
        
        /* Deadline styling */
        .deadline-card {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--danger);
            padding: 0.8rem;
            border-radius: 8px;
        }
        
        .deadline-normal {
            background: rgba(245, 158, 11, 0.1);
            border-left: 4px solid var(--warning);
        }
        
        /* Tugas card */
        .tugas-card {
            transition: all 0.3s ease;
        }
        
        .tugas-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
            
            .card-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 1rem;
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
                font-size: 0.8rem;
            }
            
            .stats-row .col-md-4 {
                margin-bottom: 1rem;
            }
            
            .deadline-info {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
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
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
            
            .stat-card {
                padding: 0.8rem;
            }
            
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.5rem;
            }
            
            .deadline-card {
                padding: 0.6rem;
                font-size: 0.85rem;
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
        
        /* Filter buttons */
        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        /* Teacher avatar */
        .teacher-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 0.5rem;
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
                <a class="nav-link" href="laporan-absensi.php"><i class="fas fa-chart-bar"></i> Laporan Absensi</a>
                <a class="nav-link active" href="laporan-tugas.php"><i class="fas fa-file-alt"></i> Laporan Tugas</a>
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
                    <h5 class="mb-0 text-dark"><i class="fas fa-file-alt me-2"></i>Laporan Tugas</h5>
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
                                <i class="fas fa-file-alt fa-lg text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 text-primary fw-bold">Monitoring Tugas Siswa</h5>
                                <small class="text-muted">Pelacakan dan analisis tugas sekolah</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="filter-buttons d-flex justify-content-end">
                            <button class="btn btn-outline-primary btn-sm" onclick="filterTasks('all')">
                                <i class="fas fa-list me-1"></i>Semua
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="filterTasks('active')">
                                <i class="fas fa-clock me-1"></i>Aktif
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="filterTasks('overdue')">
                                <i class="fas fa-exclamation-triangle me-1"></i>Terlambat
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                                <i class="fas fa-print me-1"></i>Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Statistics Cards -->
                <div class="row stats-row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="stat-card bg-light border-start border-primary border-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0 fw-bold text-primary"><?= $stats['total_tugas'] ?: 0; ?></h4>
                                    <p class="text-muted mb-0 small">Total Tugas</p>
                                </div>
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-tasks"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-card bg-light border-start border-warning border-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0 fw-bold text-warning"><?= $overdue['overdue'] ?: 0; ?></h4>
                                    <p class="text-muted mb-0 small">Tugas Terlambat</p>
                                </div>
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-card bg-light border-start border-success border-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0 fw-bold text-success"><?= $stats['total_guru'] ?: 0; ?></h4>
                                    <p class="text-muted mb-0 small">Guru Aktif</p>
                                </div>
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Absensi Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tasksTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="30%">Tugas</th>
                                <th width="20%">Guru</th>
                                <th width="15%">Kelas</th>
                                <th width="20%" class="text-center">Deadline</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = "SELECT tugas.*, guru.nama_lengkap as nama_guru, kelas.nama as nama_kelas 
                                      FROM tugas 
                                      JOIN guru ON tugas.guru_id = guru.id 
                                      JOIN kelas ON tugas.kelas_id = kelas.id 
                                      ORDER BY tugas.deadline ASC";
                            
                            $result = mysqli_query($conn, $query);
                            
                            if(mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                                    $deadline_date = strtotime($row['deadline']);
                                    $current_date = strtotime($today);
                                    $is_overdue = $deadline_date < $current_date;
                                    $deadline_class = $is_overdue ? 'deadline-card' : 'deadline-card deadline-normal';
                                    
                                    // Get initials for teacher avatar
                                    $teacher_initials = '';
                                    $name_parts = explode(' ', $row['nama_guru']);
                                    if(count($name_parts) >= 2) {
                                        $teacher_initials = substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1);
                                    } else {
                                        $teacher_initials = substr($row['nama_guru'], 0, 2);
                                    }
                            ?>
                            <tr class="tugas-card <?= $is_overdue ? 'task-overdue' : 'task-active'; ?>">
                                <td class="text-center fw-bold"><?= $no++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                            <i class="fas fa-file-alt text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold"><?= $row['judul']; ?></h6>
                                            <small class="text-muted">
                                                <?= date('d M Y', strtotime($row['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="teacher-avatar">
                                            <?= strtoupper($teacher_initials); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold small"><?= $row['nama_guru']; ?></div>
                                            <small class="text-muted">Guru</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary px-3 py-2">
                                        <?= $row['nama_kelas']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="<?= $deadline_class; ?>">
                                        <div class="deadline-info d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                <span class="fw-bold"><?= date('d/m/Y', strtotime($row['deadline'])); ?></span>
                                            </div>
                                            <div>
                                                <small class="fw-bold"><?= date('H:i', strtotime($row['deadline'])); ?></small>
                                            </div>
                                        </div>
                                        <?php if($is_overdue): ?>
                                        <small class="text-danger d-block mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Terlambat
                                        </small>
                                        <?php else: ?>
                                        <small class="text-warning d-block mt-1">
                                            <i class="fas fa-clock me-1"></i>Batas waktu
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <?php if($row['file_tugas']): ?>
                                            <a href="../assets/uploads/<?= $row['file_tugas']; ?>" 
                                               class="btn btn-download btn-sm" 
                                               target="_blank"
                                               title="Download File">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Tidak ada file</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-file-alt text-muted"></i>
                                        <h5 class="mt-3 mb-2">Belum ada tugas</h5>
                                        <p class="text-muted mb-0">Belum ada tugas yang dibuat oleh guru</p>
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
                        Menampilkan <span class="fw-bold"><?= mysqli_num_rows($result); ?></span> tugas
                        <?php if($overdue['overdue'] > 0): ?>
                        • <span class="text-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <?= $overdue['overdue']; ?> tugas terlambat
                        </span>
                        <?php endif; ?>
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
            <p class="small">Laporan Tugas v1.0</p>
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
        
        // Filter tasks function
        function filterTasks(type) {
            const rows = document.querySelectorAll('#tasksTable tbody tr');
            rows.forEach(row => {
                switch(type) {
                    case 'all':
                        row.style.display = '';
                        break;
                    case 'active':
                        row.style.display = row.classList.contains('task-active') ? '' : 'none';
                        break;
                    case 'overdue':
                        row.style.display = row.classList.contains('task-overdue') ? '' : 'none';
                        break;
                }
            });
            
            // Update active filter button
            document.querySelectorAll('.filter-buttons .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
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
                    <title>Laporan Tugas - <?= date('d F Y'); ?></title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .table { font-size: 12px; }
                        .badge { padding: 0.3em 0.6em; }
                        .deadline-card { padding: 0.5rem; font-size: 0.9rem; }
                        @media print {
                            .no-print { display: none; }
                            .filter-buttons { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="container mt-4">
                        <h4 class="text-center mb-4">Laporan Tugas SMK Al Amin Cibening</h4>
                        <p class="text-center">Dicetak pada: <?= date('d F Y H:i'); ?></p>
                        ${printContents}
                    </div>
                </body>
                </html>
            `;
            
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }
        
        // Attach print function to print buttons
        document.querySelectorAll('[onclick*="window.print"]').forEach(button => {
            button.addEventListener('click', printReport);
        });
    </script>
</body>
</html>