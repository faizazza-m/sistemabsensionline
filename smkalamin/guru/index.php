<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn('guru');
$id_guru = $_SESSION['user_id'];

// FUNGSI AMAN (Mencegah Fatal Error jika tabel kosong)
function hitungData($conn, $query) {
    $result = mysqli_query($conn, $query);
    if ($result) {
        return mysqli_num_rows($result);
    }
    return 0; // Kembalikan 0 jika query gagal
}

$jml_kelas  = hitungData($conn, "SELECT * FROM kelas");
$jml_siswa  = hitungData($conn, "SELECT * FROM siswa");
$jml_materi = hitungData($conn, "SELECT * FROM materi WHERE guru_id = '$id_guru'");
$jml_tugas  = hitungData($conn, "SELECT * FROM tugas WHERE guru_id = '$id_guru'");

// Get today's attendance for teacher's classes
$today = date('Y-m-d');
$attendance_today = 0;
$attendance_query = mysqli_query($conn, 
    "SELECT COUNT(DISTINCT a.siswa_id) as total 
     FROM absensi_siswa a 
     JOIN siswa s ON a.siswa_id = s.id 
     WHERE DATE(a.tanggal) = '$today' 
     AND s.kelas_id IN (SELECT kelas_id FROM jadwal WHERE guru_id = '$id_guru')"
);
if ($attendance_query && mysqli_num_rows($attendance_query) > 0) {
    $attendance_data = mysqli_fetch_assoc($attendance_query);
    $attendance_today = $attendance_data['total'];
}

// Get recent activities for teacher
$activities_query = "SELECT t.judul as tugas_judul, t.deskripsi, t.tenggat, 
                    m.judul as materi_judul, m.tanggal_dibuat,
                    s.nama as siswa_nama, a.status, a.tanggal as absensi_tanggal
                    FROM (
                        SELECT 'tugas' as type, judul, deskripsi, tenggat, NULL as tanggal_dibuat, 
                               NULL as siswa_nama, NULL as status, NULL as tanggal
                        FROM tugas 
                        WHERE guru_id = '$id_guru' 
                        ORDER BY created_at DESC 
                        LIMIT 3
                    ) t
                    UNION ALL
                    SELECT NULL, NULL, NULL, m.judul, m.tanggal_dibuat, 
                           NULL, NULL, NULL
                    FROM materi m
                    WHERE m.guru_id = '$id_guru'
                    ORDER BY tenggat DESC, tanggal_dibuat DESC 
                    LIMIT 10";
$activities_result = mysqli_query($conn, $activities_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - SMK Al Amin</title>
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
        
        /* Statistics Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            height: 100%;
            border-left: 5px solid var(--primary);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            font-size: 2.2rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .stat-card h3 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            color: var(--dark);
        }
        
        /* Activity Items */
        .activity-item {
            border-left: 4px solid var(--primary);
            padding-left: 1.2rem;
            margin-bottom: 1.2rem;
            padding-bottom: 1.2rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .activity-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
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
        
        .card-header.bg-success {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%) !important;
            color: white;
        }
        
        /* Today's Attendance Card */
        .attendance-card .display-1 {
            font-size: 4rem;
            font-weight: 800;
            color: var(--success);
            margin: 1rem 0;
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
            
            .stat-card h3 {
                font-size: 1.8rem;
            }
            
            .stat-icon {
                font-size: 1.8rem;
            }
            
            .attendance-card .display-1 {
                font-size: 3.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 0.8rem;
            }
            
            .stat-card {
                padding: 1.2rem;
            }
            
            .card-header {
                padding: 1rem 1.2rem;
            }
            
            .attendance-card .display-1 {
                font-size: 3rem;
            }
            
            .activity-item {
                padding-left: 1rem;
                margin-bottom: 1rem;
                padding-bottom: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
            }
            
            .stat-card {
                padding: 1rem;
                margin-bottom: 0.8rem;
            }
            
            .stat-card h3 {
                font-size: 1.6rem;
            }
            
            .stat-icon {
                font-size: 1.5rem;
                margin-bottom: 0.7rem;
            }
            
            .attendance-card .display-1 {
                font-size: 2.5rem;
            }
            
            .btn-admin {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
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
        
        /* Quick Actions */
        .quick-action-btn {
            padding: 1rem;
            text-align: center;
            border-radius: 10px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.08);
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            text-decoration: none;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .quick-action-btn i {
            font-size: 1.8rem;
            margin-bottom: 0.8rem;
        }
        
        /* Chart container */
        .chart-container {
            position: relative;
            height: 200px;
            width: 100%;
        }
        
        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(30, 64, 175, 0.05) 100%);
            border: 1px solid rgba(37, 99, 235, 0.15);
        }
        
        /* Progress Bar */
        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
        }
        
        .progress-bar {
            border-radius: 4px;
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
                <a class="nav-link active" href="index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a class="nav-link" href="absensi.php">
                    <i class="fas fa-calendar-check"></i> Absensi Siswa
                </a>
                <a class="nav-link" href="materi.php">
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
                    <h5 class="mb-0 text-dark">Dashboard Guru</h5>
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
        
        <!-- Welcome Card -->
        <div class="card welcome-card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2 fw-bold text-primary">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h2>
                        <p class="mb-0 text-muted">Selamat beraktivitas di panel Guru. Anda dapat mengelola materi, tugas, dan absensi siswa dari sini.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="badge bg-light text-dark p-3 fs-6">
                            <i class="fas fa-calendar-day me-2"></i>
                            <?php echo date('d F Y'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4 g-3">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card" style="border-left-color: var(--primary);">
                    <div class="stat-icon text-primary">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <h3><?php echo $jml_kelas; ?></h3>
                    <p class="text-muted mb-0">Total Kelas</p>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card" style="border-left-color: var(--success);">
                    <div class="stat-icon text-success">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php echo $jml_siswa; ?></h3>
                    <p class="text-muted mb-0">Total Siswa</p>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card" style="border-left-color: var(--warning);">
                    <div class="stat-icon text-warning">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3><?php echo $jml_materi; ?></h3>
                    <p class="text-muted mb-0">Materi Diupload</p>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card" style="border-left-color: var(--danger);">
                    <div class="stat-icon text-danger">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3><?php echo $jml_tugas; ?></h3>
                    <p class="text-muted mb-0">Tugas Aktif</p>
                </div>
            </div>
        </div>
        
        <!-- Attendance and Performance -->
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>Statistik Kelas (Minggu Ini)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="classChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card attendance-card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check me-2"></i>Kehadiran Hari Ini
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <h1 class="display-1 fw-bold"><?php echo $attendance_today; ?></h1>
                        <p class="text-muted mb-3 text-center">Siswa sudah absen di kelas Anda hari ini</p>
                        <a href="absensi.php" class="btn btn-admin">
                            <i class="fas fa-eye me-2"></i>Kelola Absensi
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Aktivitas Terbaru
                </h5>
                <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body">
                <?php if($activities_result && mysqli_num_rows($activities_result) > 0): ?>
                    <?php while($activity = mysqli_fetch_assoc($activities_result)): ?>
                    <div class="activity-item">
                        <?php if($activity['tugas_judul']): ?>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-semibold text-primary">
                                    <i class="fas fa-tasks me-2"></i>Tugas Baru: <?php echo htmlspecialchars($activity['tugas_judul']); ?>
                                </h6>
                                <p class="mb-1 small text-muted">
                                    <?php echo htmlspecialchars(substr($activity['deskripsi'], 0, 100)); ?>...
                                </p>
                                <small class="text-danger">
                                    <i class="fas fa-clock me-1"></i>
                                    Tenggat: <?php echo date('d/m/Y', strtotime($activity['tenggat'])); ?>
                                </small>
                            </div>
                            <small class="text-muted">
                                <?php echo date('H:i', strtotime($activity['tenggat'])); ?>
                            </small>
                        </div>
                        <?php elseif($activity['materi_judul']): ?>
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-semibold text-success">
                                    <i class="fas fa-book me-2"></i>Materi: <?php echo htmlspecialchars($activity['materi_judul']); ?>
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Diupload: <?php echo date('d/m/Y', strtotime($activity['tanggal_dibuat'])); ?>
                                </small>
                            </div>
                            <small class="text-muted">
                                <?php echo date('H:i', strtotime($activity['tanggal_dibuat'])); ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted text-center py-3 mb-0">Belum ada aktivitas tercatat</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <a href="materi.php?action=add" class="quick-action-btn">
                            <i class="fas fa-file-upload text-primary"></i>
                            <span>Upload Materi</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="tugas.php?action=add" class="quick-action-btn">
                            <i class="fas fa-plus-circle text-success"></i>
                            <span>Tambah Tugas</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="absensi.php" class="quick-action-btn">
                            <i class="fas fa-clipboard-check text-warning"></i>
                            <span>Input Absensi</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="nilai.php" class="quick-action-btn">
                            <i class="fas fa-star text-info"></i>
                            <span>Input Nilai</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-5 pt-4 border-top text-center text-muted">
            <p class="mb-1">© <?php echo date('Y'); ?> SMK Al Amin Cibening. All rights reserved.</p>
            <p class="small">Guru Dashboard v1.0</p>
        </footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        // Class Performance Chart
        const ctx = document.getElementById('classChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Kelas X IPA', 'Kelas X IPS', 'Kelas XI IPA', 'Kelas XI IPS', 'Kelas XII IPA', 'Kelas XII IPS'],
                datasets: [{
                    label: 'Rata-rata Nilai',
                    data: [75, 78, 82, 76, 85, 80],
                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    borderColor: '#2563eb',
                    borderWidth: 2,
                    borderRadius: 8,
                }, {
                    label: 'Kehadiran (%)',
                    data: [88, 85, 92, 83, 95, 87],
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#10b981',
                    borderWidth: 2,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
        
        // Adjust chart on window resize
        window.addEventListener('resize', function() {
            // Chart will automatically resize with responsive: true option
        });
        
        // Add animation to stat cards
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>