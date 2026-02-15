<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('guru');

$id_guru = $_SESSION['user_id'];

// Get current day in Indonesian
$hari_ini = date('N'); // 1=Monday, 7=Sunday
$hari_indonesia = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$nama_hari_ini = $hari_indonesia[date('w')];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Mengajar - Dashboard Guru</title>
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
        
        .stat-card.border-success { border-left-color: var(--success); }
        .stat-card.border-warning { border-left-color: var(--warning); }
        
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
        
        /* Today Highlight Card */
        .today-highlight {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.2);
        }
        
        /* Day Cards */
        .day-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .day-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .day-card.today {
            border-left-color: var(--success);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0.02) 100%);
            border-color: rgba(16, 185, 129, 0.2);
        }
        
        .day-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid rgba(0,0,0,0.08);
        }
        
        .day-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .day-name.today {
            color: var(--success);
        }
        
        /* Class Items */
        .class-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            border: 1px solid rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .class-item:hover {
            border-color: var(--primary);
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .class-item.current {
            border-color: var(--success);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
        }
        
        .time-badge {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
            color: var(--primary);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            min-width: 140px;
            text-align: center;
            border: 1px solid rgba(37, 99, 235, 0.2);
        }
        
        .time-badge.current {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            color: var(--success);
            border-color: rgba(16, 185, 129, 0.3);
        }
        
        /* Badge styles */
        .badge {
            padding: 0.4rem 0.8rem;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        
        .badge-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        
        .badge-success {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%);
            color: white;
        }
        
        .badge-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
            color: white;
        }
        
        .badge-light {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--dark);
            border: 1px solid #dee2e6;
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
            
            .stat-card h3 {
                font-size: 1.8rem;
            }
            
            .stat-icon {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 0.8rem;
            }
            
            .card-header {
                padding: 1rem 1.2rem;
            }
            
            .today-highlight {
                padding: 1rem;
            }
            
            .day-card {
                padding: 1rem;
            }
            
            .class-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .time-badge {
                width: 100%;
                text-align: left;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.75rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
            }
            
            .day-card {
                padding: 0.75rem;
            }
            
            .class-item {
                padding: 0.75rem;
            }
            
            .table-responsive {
                border: none;
                box-shadow: none;
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
        
        /* Current time indicator */
        .current-time {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%);
            color: white;
            border-radius: 8px;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }
        
        /* Class info */
        .class-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .class-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            font-size: 1.5rem;
        }
        
        .class-details h6 {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--dark);
        }
        
        .class-details p {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0;
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
                <a class="nav-link" href="materi.php">
                    <i class="fas fa-book-open"></i> Materi
                </a>
                <a class="nav-link" href="tugas.php">
                    <i class="fas fa-tasks"></i> Tugas
                </a>
                <a class="nav-link active" href="jadwal.php">
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
                        <i class="fas fa-calendar-alt me-2"></i>
                        Jadwal Mengajar
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
        
        <!-- Today Highlight -->
        <div class="today-highlight fade-in">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-2">
                        <i class="fas fa-calendar-day me-2"></i>Hari ini: <?= $nama_hari_ini ?>
                    </h3>
                    <p class="mb-0 opacity-90">
                        <?php 
                        echo date('d F Y'); 
                        $jam_sekarang = date('H:i');
                        echo " • <span class='current-time'><i class='fas fa-clock'></i> $jam_sekarang</span>";
                        ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="bg-white text-primary rounded-pill px-3 py-2 d-inline-block shadow-sm">
                        <i class="fas fa-chalkboard-teacher me-2"></i>
                        <span id="totalJadwalHariIni">Mengecek...</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <?php
        $query = "SELECT j.*, k.nama as kelas, mp.nama as mapel 
                  FROM jadwal_mengajar j 
                  JOIN kelas k ON j.kelas_id = k.id 
                  JOIN mata_pelajaran mp ON j.mapel_id = mp.id 
                  WHERE j.guru_id = '$id_guru' 
                  ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jam_mulai";
        
        $res = mysqli_query($conn, $query);
        $total_jadwal = $res ? mysqli_num_rows($res) : 0;
        
        // Count today's schedule
        $query_hari_ini = "SELECT COUNT(*) as total FROM jadwal_mengajar 
                          WHERE guru_id = '$id_guru' AND hari = '$nama_hari_ini'";
        $res_hari_ini = mysqli_query($conn, $query_hari_ini);
        $total_hari_ini = $res_hari_ini ? mysqli_fetch_assoc($res_hari_ini)['total'] : 0;
        
        // Count upcoming classes (today's future classes)
        $jam_sekarang = date('H:i:s');
        $query_upcoming = "SELECT COUNT(*) as total FROM jadwal_mengajar 
                          WHERE guru_id = '$id_guru' AND hari = '$nama_hari_ini' 
                          AND jam_mulai > '$jam_sekarang'";
        $res_upcoming = mysqli_query($conn, $query_upcoming);
        $total_upcoming = $res_upcoming ? mysqli_fetch_assoc($res_upcoming)['total'] : 0;
        ?>
        
        <div class="row mb-4 g-3">
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="stat-card fade-in">
                    <div class="stat-icon text-primary">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3><?php echo $total_jadwal; ?></h3>
                    <p class="text-muted mb-0">Total Jadwal</p>
                </div>
            </div>
            
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="stat-card border-success fade-in">
                    <div class="stat-icon text-success">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <h3><?php echo $total_hari_ini; ?></h3>
                    <p class="text-muted mb-0">Hari Ini</p>
                </div>
            </div>
            
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="stat-card border-warning fade-in">
                    <div class="stat-icon text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3><?php echo $total_upcoming; ?></h3>
                    <p class="text-muted mb-0">Akan Datang</p>
                </div>
            </div>
        </div>
        
        <!-- Main Card - Weekly Schedule -->
        <div class="card fade-in">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Jadwal Mengajar Mingguan
                    </h5>
                    <p class="mb-0 opacity-90 small">Lihat jadwal mengajar Anda untuk seluruh minggu</p>
                </div>
                <div class="badge bg-light text-dark">
                    Minggu ke-<?php echo date('W'); ?>
                </div>
            </div>
            
            <div class="card-body">
                <?php
                if ($res && $total_jadwal > 0) { 
                    $jadwal_per_hari = [];
                    
                    // Group by hari
                    mysqli_data_seek($res, 0);
                    while($row = mysqli_fetch_assoc($res)) {
                        $jadwal_per_hari[$row['hari']][] = $row;
                    }
                    
                    // Urutan hari
                    $urutan_hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                ?>
                
                <div class="jadwal-container">
                    <?php foreach($urutan_hari as $hari): 
                        if(isset($jadwal_per_hari[$hari])): 
                            $is_today = ($hari == $nama_hari_ini);
                    ?>
                    <div class="day-card <?= $is_today ? 'today' : '' ?> fade-in">
                        <div class="day-header">
                            <h5 class="day-name <?= $is_today ? 'today' : '' ?> mb-0">
                                <i class="fas fa-calendar-day me-2"></i><?= $hari ?>
                                <?php if($is_today): ?>
                                    <span class="badge badge-success ms-2">
                                        <i class="fas fa-star me-1"></i>HARI INI
                                    </span>
                                <?php endif; ?>
                            </h5>
                            <span class="text-muted">
                                <i class="fas fa-chalkboard-teacher me-1"></i>
                                <?= count($jadwal_per_hari[$hari]) ?> kelas
                            </span>
                        </div>
                        
                        <div class="class-list">
                            <?php foreach($jadwal_per_hari[$hari] as $index => $jadwal): 
                                $jam_mulai = date('H:i', strtotime($jadwal['jam_mulai']));
                                $jam_selesai = date('H:i', strtotime($jadwal['jam_selesai']));
                                $jam_sekarang = date('H:i');
                                $is_current = ($is_today && $jam_sekarang >= $jam_mulai && $jam_sekarang <= $jam_selesai);
                            ?>
                            <div class="class-item <?= $is_current ? 'current' : '' ?>">
                                <div class="class-info">
                                    <div class="class-icon">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div class="class-details">
                                        <h6 class="mb-1"><?= htmlspecialchars($jadwal['mapel']) ?></h6>
                                        <p class="mb-0">
                                            <i class="fas fa-users me-1"></i>
                                            <?= htmlspecialchars($jadwal['kelas']) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="time-badge <?= $is_current ? 'current' : '' ?>">
                                    <i class="fas fa-clock me-1"></i>
                                    <?= $jam_mulai ?> - <?= $jam_selesai ?>
                                    <?php if($is_current): ?>
                                        <br><small class="fst-italic fw-normal">Sedang berlangsung</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; endforeach; ?>
                </div>
                
                <?php } else { ?>
                    <div class="empty-state fade-in">
                        <i class="fas fa-calendar-times"></i>
                        <h4 class="text-muted mt-3 mb-2">Belum Ada Jadwal Mengajar</h4>
                        <p class="text-muted mb-4">Jadwal mengajar Anda belum ditentukan oleh administrator.</p>
                        <div class="alert alert-info border-0" style="max-width: 500px; margin: 0 auto;">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informasi:</strong> Silakan hubungi administrator untuk mengatur jadwal mengajar Anda.
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        
        <!-- Table View for Desktop -->
        <div class="card mt-4 fade-in d-none d-lg-block">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>Tabel Jadwal Lengkap
                </h5>
                <p class="mb-0 text-muted small">Tampilan tabel untuk melihat semua jadwal sekaligus</p>
            </div>
            <div class="card-body">
                <?php if ($res && $total_jadwal > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="120">Hari</th>
                                <th width="150">Waktu</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th width="120" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            mysqli_data_seek($res, 0);
                            while($jadwal = mysqli_fetch_assoc($res)):
                                $jam_mulai = date('H:i', strtotime($jadwal['jam_mulai']));
                                $jam_selesai = date('H:i', strtotime($jadwal['jam_selesai']));
                                $is_today = ($jadwal['hari'] == $nama_hari_ini);
                                $jam_sekarang = date('H:i');
                                $is_current = ($is_today && $jam_sekarang >= $jam_mulai && $jam_sekarang <= $jam_selesai);
                                $is_upcoming = ($is_today && $jam_sekarang < $jam_mulai);
                                $is_passed = ($is_today && $jam_sekarang > $jam_selesai);
                            ?>
                            <tr class="<?= $is_today ? ($is_current ? 'table-success' : 'table-info') : '' ?>">
                                <td>
                                    <span class="fw-semibold"><?= $jadwal['hari'] ?></span>
                                    <?php if($is_today): ?>
                                        <br><small class="badge badge-success">Hari ini</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-light">
                                        <i class="fas fa-clock me-1"></i>
                                        <?= $jam_mulai ?> - <?= $jam_selesai ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($jadwal['kelas']) ?></td>
                                <td class="fw-medium"><?= htmlspecialchars($jadwal['mapel']) ?></td>
                                <td class="text-center">
                                    <?php if($is_current): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-play-circle me-1"></i> Berlangsung
                                        </span>
                                    <?php elseif($is_upcoming): ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock me-1"></i> Akan Datang
                                        </span>
                                    <?php elseif($is_passed): ?>
                                        <span class="badge badge-light">
                                            <i class="fas fa-check-circle me-1"></i> Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-light">
                                            <i class="far fa-calendar me-1"></i> Terjadwal
                                        </span>
                                    <?php endif; ?>
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
                                Total <?= $total_jadwal; ?> jadwal • 
                                <?= $total_hari_ini; ?> jadwal hari ini
                            </small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <small class="text-muted">
                                <i class="fas fa-sync-alt me-1"></i>
                                Terakhir diperbarui: <?php echo date('H:i:s'); ?> • 
                                Sistem Jadwal © <?php echo date('Y'); ?>
                            </small>
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
        
        // Update total schedule for today
        document.addEventListener('DOMContentLoaded', function() {
            const totalElement = document.getElementById('totalJadwalHariIni');
            const todayCards = document.querySelectorAll('.day-card.today .class-item');
            const totalToday = todayCards.length;
            totalElement.textContent = `${totalToday} Jadwal`;
            
            if (totalToday > 0) {
                totalElement.innerHTML += ` <i class="fas fa-check-circle ms-1"></i>`;
            }
        });
        
        // Update time and check for current classes
        function updateCurrentTime() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const timeString = `${hours}:${minutes}`;
            
            // Update current time display
            const timeElements = document.querySelectorAll('.current-time');
            timeElements.forEach(el => {
                el.innerHTML = `<i class="fas fa-clock"></i> ${timeString}`;
            });
            
            // Update "sedang berlangsung" status
            const currentTime = now.getHours() * 60 + now.getMinutes();
            const classItems = document.querySelectorAll('.class-item');
            
            classItems.forEach(item => {
                const timeBadge = item.querySelector('.time-badge');
                if (timeBadge) {
                    const timeText = timeBadge.textContent;
                    const times = timeText.match(/(\d{2}):(\d{2})/g);
                    
                    if (times && times.length === 2) {
                        const startTime = times[0].split(':');
                        const endTime = times[1].split(':');
                        const startMinutes = parseInt(startTime[0]) * 60 + parseInt(startTime[1]);
                        const endMinutes = parseInt(endTime[0]) * 60 + parseInt(endTime[1]);
                        
                        const isCurrent = currentTime >= startMinutes && currentTime <= endMinutes;
                        
                        if (isCurrent && !item.classList.contains('current')) {
                            item.classList.add('current');
                            timeBadge.classList.add('current');
                            if (timeBadge.querySelector('small')) {
                                timeBadge.querySelector('small').textContent = 'Sedang berlangsung';
                            }
                        } else if (!isCurrent && item.classList.contains('current')) {
                            item.classList.remove('current');
                            timeBadge.classList.remove('current');
                            if (timeBadge.querySelector('small')) {
                                timeBadge.querySelector('small').textContent = '';
                            }
                        }
                    }
                }
            });
        }
        
        // Update every minute
        updateCurrentTime();
        setInterval(updateCurrentTime, 60000);
        
        // Add hover effects to day cards
        document.addEventListener('DOMContentLoaded', function() {
            const dayCards = document.querySelectorAll('.day-card');
            dayCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = '0 8px 20px rgba(0,0,0,0.1)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.05)';
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