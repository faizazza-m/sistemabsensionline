<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in as admin
redirectIfNotLoggedIn('admin');

// Get statistics
$students_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa"))['total'];
$teachers_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM guru"))['total'];
$subjects_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mata_pelajaran"))['total'];
$classes_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kelas"))['total'];

// Get today's attendance
$today = date('Y-m-d');
$attendance_today = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM absensi_siswa WHERE DATE(tanggal) = '$today'"
))['total'];

// Get recent activities
$activities_query = "SELECT a.*, s.nama as siswa_nama, s.nis 
                     FROM aktivitas a 
                     LEFT JOIN siswa s ON a.user_id = s.id 
                     ORDER BY a.timestamp DESC 
                     LIMIT 10";
$activities_result = mysqli_query($conn, $activities_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SMK Al Amin</title>
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
        }
        
        .card-header.bg-success {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%) !important;
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
    </style>
</head>
<body>
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">
                <i class="fas fa-user-shield me-2"></i>
                Admin Panel
            </h4>
            <small>SMK Al Amin Cibening</small>
        </div>
        
        <div class="sidebar-menu">
            <nav class="nav flex-column">
                <a class="nav-link active" href="index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a class="nav-link" href="users.php">
                    <i class="fas fa-users"></i> Manajemen User
                </a>
                <a class="nav-link" href="mapel.php">
                    <i class="fas fa-book"></i> Mata Pelajaran
                </a>
                <a class="nav-link" href="kelas.php">
                    <i class="fas fa-chalkboard"></i> Kelas
                </a>
                <a class="nav-link" href="laporan-absensi.php">
                    <i class="fas fa-chart-bar"></i> Laporan Absensi
                </a>
                <a class="nav-link" href="laporan-tugas.php">
                    <i class="fas fa-file-alt"></i> Laporan Tugas
                </a>
                <div class="mt-auto pt-3">
                    <a class="nav-link text-danger" href="logout.php" style="border-left: 3px solid transparent;">
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
                    <h5 class="mb-0 text-dark">Dashboard Admin</h5>
                </div>
                
                <div class="ms-auto">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" 
                                data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-2"></i>
                            <?php echo $_SESSION['nama']; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-user me-2"></i>Profil Admin
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">
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
                        <h2 class="mb-2 fw-bold text-primary">Selamat Datang, <?php echo $_SESSION['nama']; ?>!</h2>
                        <p class="mb-0 text-muted">Anda login sebagai Administrator Sistem</p>
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
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3><?php echo $students_count; ?></h3>
                    <p class="text-muted mb-0">Total Siswa</p>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card" style="border-left-color: var(--success);">
                    <div class="stat-icon text-success">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3><?php echo $teachers_count; ?></h3>
                    <p class="text-muted mb-0">Total Guru</p>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card" style="border-left-color: var(--warning);">
                    <div class="stat-icon text-warning">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3><?php echo $subjects_count; ?></h3>
                    <p class="text-muted mb-0">Mata Pelajaran</p>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card" style="border-left-color: var(--danger);">
                    <div class="stat-icon text-danger">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <h3><?php echo $classes_count; ?></h3>
                    <p class="text-muted mb-0">Kelas</p>
                </div>
            </div>
        </div>
        
        <!-- Charts and Activities -->
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>Statistik Presensi (Minggu Ini)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card attendance-card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check me-2"></i>Absensi Hari Ini
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <h1 class="display-1 fw-bold"><?php echo $attendance_today; ?></h1>
                        <p class="text-muted mb-3 text-center">Siswa sudah melakukan absen hari ini</p>
                        <a href="laporan-absensi.php" class="btn btn-admin">
                            <i class="fas fa-eye me-2"></i>Lihat Detail
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
                <?php if(mysqli_num_rows($activities_result) > 0): ?>
                    <?php while($activity = mysqli_fetch_assoc($activities_result)): ?>
                    <div class="activity-item">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1 fw-semibold"><?php echo $activity['aksi']; ?></h6>
                            <small class="text-muted">
                                <?php echo date('H:i', strtotime($activity['timestamp'])); ?>
                            </small>
                        </div>
                        <p class="mb-1 small">
                            <?php if($activity['siswa_nama']): ?>
                            <i class="fas fa-user-graduate me-1"></i>
                            <?php echo $activity['siswa_nama']; ?> (<?php echo $activity['nis']; ?>)
                            <?php else: ?>
                            <i class="fas fa-user me-1"></i>Sistem Administrator
                            <?php endif; ?>
                        </p>
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            <?php echo date('d/m/Y', strtotime($activity['timestamp'])); ?>
                        </small>
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
                        <a href="users.php?action=add" class="quick-action-btn">
                            <i class="fas fa-user-plus text-primary"></i>
                            <span>Tambah User</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="mapel.php?action=add" class="quick-action-btn">
                            <i class="fas fa-plus-circle text-success"></i>
                            <span>Tambah Mapel</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="laporan-absensi.php" class="quick-action-btn">
                            <i class="fas fa-file-pdf text-warning"></i>
                            <span>Export Laporan</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="backup.php" class="quick-action-btn">
                            <i class="fas fa-database text-info"></i>
                            <span>Backup Data</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-5 pt-4 border-top text-center text-muted">
            <p class="mb-1">© <?php echo date('Y'); ?> SMK Al Amin Cibening. All rights reserved.</p>
            <p class="small">Admin Dashboard v1.0</p>
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
        
        // Attendance Chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                datasets: [{
                    label: 'Persentase Kehadiran',
                    data: [85, 88, 90, 87, 92, 45],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
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
    </script>
</body>
</html>