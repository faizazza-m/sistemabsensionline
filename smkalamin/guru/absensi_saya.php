<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('guru');

$id_guru = $_SESSION['user_id'];
$today = date('Y-m-d');

// --- LOKASI SEKOLAH (SMK AL AMIN CIBENING) ---
$latitude_sekolah  = -6.6346317; 
$longitude_sekolah = 106.6719455; 
$radius_diizinkan  = 200; // Meter

// Cek apakah guru sudah absen hari ini?
$cek_absen = mysqli_query($conn, "SELECT * FROM absensi_guru WHERE guru_id='$id_guru' AND tanggal LIKE '$today%'");
$sudah_absen = (mysqli_num_rows($cek_absen) > 0);
$data_absen_hari_ini = $sudah_absen ? mysqli_fetch_assoc($cek_absen) : null;

// Ambil riwayat absensi 7 hari terakhir
$riwayat_query = "SELECT tanggal, status, TIME(tanggal) as waktu, lat, lng 
                  FROM absensi_guru 
                  WHERE guru_id='$id_guru' 
                  ORDER BY tanggal DESC 
                  LIMIT 7";
$riwayat_result = mysqli_query($conn, $riwayat_query);

// Hitung statistik
$total_hadir_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM absensi_guru WHERE guru_id='$id_guru' AND status='hadir'");
$total_hadir = mysqli_fetch_assoc($total_hadir_query)['total'];

$bulan_ini = date('Y-m');
$hadir_bulan_ini_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM absensi_guru WHERE guru_id='$id_guru' AND status='hadir' AND DATE(tanggal) LIKE '$bulan_ini%'");
$hadir_bulan_ini = mysqli_fetch_assoc($hadir_bulan_ini_query)['total'];

$streak_query = mysqli_query($conn, "SELECT DATE(tanggal) as tgl FROM absensi_guru WHERE guru_id='$id_guru' AND status='hadir' ORDER BY tanggal DESC");
$current_streak = 0;
$dates = [];
while($row = mysqli_fetch_assoc($streak_query)) {
    $dates[] = $row['tgl'];
}

// Hitung streak beruntun
$current_date = date('Y-m-d');
for($i = 0; $i < count($dates); $i++) {
    $expected_date = date('Y-m-d', strtotime($current_date . " -{$i} days"));
    if($dates[$i] == $expected_date) {
        $current_streak++;
    } else {
        break;
    }
}

// --- PROSES SIMPAN ABSEN ---
if (isset($_POST['absen_masuk'])) {
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    
    if (empty($lat) || empty($lng)) {
        echo "<script>alert('Gagal mendeteksi lokasi! Pastikan GPS aktif.'); window.location='absensi_saya.php';</script>";
        exit;
    }

    // Hitung Jarak
    function distance($lat1, $lon1, $lat2, $lon2) {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        return ($dist * 60 * 1.1515 * 1.609344) * 1000; // Meter
    }

    $jarak = distance($lat, $lng, $latitude_sekolah, $longitude_sekolah);

    if ($jarak <= $radius_diizinkan) {
        $q = "INSERT INTO absensi_guru (guru_id, tanggal, status, lat, lng) 
              VALUES ('$id_guru', NOW(), 'hadir', '$lat', '$lng')";
        
        if (mysqli_query($conn, $q)) {
            echo "<script>alert('Absensi Masuk Berhasil! Selamat Mengajar.'); window.location='absensi_saya.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('Gagal! Anda berada di luar jangkauan sekolah (" . round($jarak) . "m).');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Saya - Dashboard Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
        .stat-card.border-danger { border-left-color: var(--danger); }
        
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
        
        /* Today Status Card */
        .today-status-card {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%);
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.2);
        }
        
        .today-status-card.already {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        }
        
        /* Map Container */
        #map {
            height: 400px;
            width: 100%;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 2px solid #e2e8f0;
            z-index: 1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
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
        
        .btn-admin:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%);
            color: white;
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }
        
        .btn-success:hover:not(:disabled) {
            background: linear-gradient(135deg, #0da271 0%, var(--success) 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
            color: white;
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2);
        }
        
        .btn-warning:hover:not(:disabled) {
            background: linear-gradient(135deg, #d97706 0%, var(--warning) 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(245, 158, 11, 0.3);
            color: white;
        }
        
        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
            border-left: 4px solid var(--primary);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        /* History Cards */
        .history-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-top: 1.5rem;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .history-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .history-item:hover {
            background-color: #f8fafc;
            transform: translateX(5px);
        }
        
        .history-item:last-child {
            border-bottom: none;
        }
        
        .history-icon {
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
        
        .history-icon.today {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%);
        }
        
        /* Badge styles */
        .badge {
            padding: 0.4rem 0.8rem;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        
        .badge-success {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%);
            color: white;
        }
        
        .badge-light {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--dark);
            border: 1px solid #dee2e6;
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
            
            .today-status-card {
                padding: 1.5rem;
            }
            
            #map {
                height: 300px;
            }
            
            .btn-admin, .btn-success, .btn-warning {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .info-box {
                padding: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
            }
            
            #map {
                height: 250px;
            }
            
            .history-card {
                padding: 1rem;
            }
            
            .history-item {
                padding: 0.75rem;
            }
            
            .history-icon {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
                margin-right: 0.75rem;
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
        
        /* Pulse animation */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        /* Streak indicator */
        .streak-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            color: var(--warning);
            border-radius: 8px;
            font-weight: 600;
            border: 1px solid rgba(245, 158, 11, 0.2);
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
                <a class="nav-link" href="jadwal.php">
                    <i class="fas fa-calendar-alt"></i> Jadwal Mengajar
                </a>
                <a class="nav-link active" href="absensi_saya.php">
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
                        <i class="fas fa-fingerprint me-2"></i>
                        Absensi Saya
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
        
        <!-- Statistics Cards -->
        <div class="row mb-4 g-3">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card fade-in">
                    <div class="stat-icon text-primary">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3><?php echo $total_hadir; ?></h3>
                    <p class="text-muted mb-0">Total Kehadiran</p>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card border-success fade-in">
                    <div class="stat-icon text-success">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3><?php echo $hadir_bulan_ini; ?></h3>
                    <p class="text-muted mb-0">Hadir Bulan Ini</p>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card border-warning fade-in">
                    <div class="stat-icon text-warning">
                        <i class="fas fa-fire"></i>
                    </div>
                    <h3><?php echo $current_streak; ?></h3>
                    <p class="text-muted mb-0">Streak Beruntun</p>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card border-danger fade-in">
                    <div class="stat-icon text-danger">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3><?php echo number_format($radius_diizinkan); ?>m</h3>
                    <p class="text-muted mb-0">Radius Sekolah</p>
                </div>
            </div>
        </div>
        
        <!-- Main Card -->
        <div class="card fade-in">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-fingerprint me-2"></i>Presensi Kehadiran Hari Ini
                    </h5>
                    <p class="mb-0 opacity-90 small"><?php echo date('l, d F Y'); ?></p>
                </div>
                <div class="streak-indicator">
                    <i class="fas fa-fire"></i>
                    Streak: <?php echo $current_streak; ?> hari
                </div>
            </div>
            
            <div class="card-body">
                <?php if($sudah_absen): ?>
                    <div class="today-status-card already fade-in">
                        <div class="pulse">
                            <i class="fas fa-check-circle fa-4x mb-3"></i>
                        </div>
                        <h3 class="mb-2">Presensi Berhasil!</h3>
                        <p class="mb-0 opacity-90">
                            Anda telah melakukan presensi hari ini pukul 
                            <strong><?php echo date('H:i', strtotime($data_absen_hari_ini['tanggal'])); ?> WIB</strong>
                        </p>
                    </div>
                    
                    <div class="info-box fade-in">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle fa-2x text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-2 text-primary">Detail Presensi</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted d-block">Status</small>
                                        <span class="badge badge-success">Hadir</span>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted d-block">Waktu</small>
                                        <span class="fw-semibold"><?php echo date('H:i:s', strtotime($data_absen_hari_ini['tanggal'])); ?></span>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted d-block">Tanggal</small>
                                        <span class="fw-semibold"><?php echo date('d/m/Y', strtotime($data_absen_hari_ini['tanggal'])); ?></span>
                                    </div>
                                </div>
                                <?php if($data_absen_hari_ini['lat'] && $data_absen_hari_ini['lng']): ?>
                                <div class="mt-2">
                                    <small class="text-muted d-block">Lokasi</small>
                                    <a href="https://maps.google.com/?q=<?php echo $data_absen_hari_ini['lat']; ?>,<?php echo $data_absen_hari_ini['lng']; ?>" 
                                       target="_blank" 
                                       class="text-primary">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        Lihat di Google Maps
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    
                    <div class="info-box fade-in">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle fa-2x text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-2 text-primary">Informasi Presensi</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted d-block">Lokasi Sekolah</small>
                                        <span class="fw-semibold">SMK Al Amin Cibening</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted d-block">Radius Diizinkan</small>
                                        <span class="fw-semibold"><?= number_format($radius_diizinkan) ?> meter</span>
                                    </div>
                                </div>
                                <p class="mb-0 text-muted small">
                                    <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                    Pastikan GPS aktif dan Anda berada di lingkungan sekolah.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div id="map" class="fade-in"></div>

                    <form method="POST" class="fade-in">
                        <input type="hidden" name="lat" id="lat">
                        <input type="hidden" name="lng" id="lng">
                        
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <button type="button" onclick="getLocation()" class="btn btn-warning w-100" id="btnLokasi">
                                    <i class="fas fa-search-location me-2"></i>
                                    <span id="btnLokasiText">Deteksi Lokasi Saya</span>
                                </button>
                            </div>
                            <div class="col-lg-6">
                                <button type="submit" name="absen_masuk" class="btn btn-success w-100" id="btnKirim" disabled>
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Presensi
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                Klik "Deteksi Lokasi" terlebih dahulu sebelum mengirim presensi
                            </small>
                        </div>
                    </form>

                <?php endif; ?>

                <!-- Riwayat Absensi -->
                <div class="history-card fade-in">
                    <h6 class="mb-3 fw-bold text-dark">
                        <i class="fas fa-history me-2"></i>Riwayat 7 Hari Terakhir
                    </h6>
                    
                    <?php if(mysqli_num_rows($riwayat_result) > 0): ?>
                        <div class="history-list">
                            <?php 
                            mysqli_data_seek($riwayat_result, 0);
                            $day_counter = 0;
                            while($riwayat = mysqli_fetch_assoc($riwayat_result)): 
                                $is_today = date('Y-m-d', strtotime($riwayat['tanggal'])) == $today;
                                $day_counter++;
                            ?>
                                <div class="history-item">
                                    <div class="history-icon <?= $is_today ? 'today' : '' ?>">
                                        <i class="fas <?= $day_counter == 1 ? 'fa-calendar-day' : 'fa-calendar-check' ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 fw-semibold">
                                                    <?= date('l, d F Y', strtotime($riwayat['tanggal'])) ?>
                                                    <?php if($day_counter == 1): ?>
                                                        <span class="badge badge-success ms-2">TERBARU</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <p class="mb-1 small text-muted">
                                                    <i class="far fa-clock me-1"></i>
                                                    <?= $riwayat['waktu'] ?> WIB
                                                </p>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge badge-success mb-1">Hadir</span>
                                                <?php if($riwayat['lat'] && $riwayat['lng']): ?>
                                                <br>
                                                <a href="https://maps.google.com/?q=<?= $riwayat['lat']; ?>,<?= $riwayat['lng']; ?>" 
                                                   target="_blank" 
                                                   class="small text-primary">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="padding: 2rem 1rem;">
                            <i class="fas fa-calendar-times"></i>
                            <p class="text-muted mt-2 mb-0">Belum ada riwayat presensi</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Summary -->
                <div class="mt-4 pt-3 border-top">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Presensi menggunakan sistem geolocation berbasis GPS
                            </small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <small class="text-muted">
                                <i class="fas fa-sync-alt me-1"></i>
                                Sistem Absensi © <?php echo date('Y'); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
        
        <?php if(!$sudah_absen): ?>
        // Initialize Map
        var map = L.map('map').setView([<?= $latitude_sekolah ?>, <?= $longitude_sekolah ?>], 17);
        
        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Add school marker with custom icon
        var schoolIcon = L.divIcon({
            html: '<div style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.2);"><i class="fas fa-school"></i></div>',
            className: 'custom-school-icon',
            iconSize: [40, 40],
            iconAnchor: [20, 40]
        });
        
        var schoolMarker = L.marker([<?= $latitude_sekolah ?>, <?= $longitude_sekolah ?>], {
            icon: schoolIcon
        }).addTo(map);
        
        schoolMarker.bindPopup(`
            <div class="text-center p-2">
                <h6 class="fw-bold mb-1">SMK Al Amin Cibening</h6>
                <p class="mb-1 small text-muted">Lokasi Sekolah</p>
                <div class="badge badge-light mt-1">
                    <i class="fas fa-ruler me-1"></i>
                    Radius: <?= number_format($radius_diizinkan) ?>m
                </div>
            </div>
        `).openPopup();
        
        // Add school area circle
        var schoolArea = L.circle([<?= $latitude_sekolah ?>, <?= $longitude_sekolah ?>], {
            color: '#2563eb',
            fillColor: '#2563eb',
            fillOpacity: 0.1,
            weight: 2,
            radius: <?= $radius_diizinkan ?>
        }).addTo(map);
        
        schoolArea.bindPopup(`
            <div class="text-center p-2">
                <h6 class="fw-bold mb-1">Area Sekolah</h6>
                <p class="mb-0 small text-muted">Radius <?= number_format($radius_diizinkan) ?> meter dari titik pusat</p>
            </div>
        `);
        
        // User marker variable
        var userMarker = null;
        var accuracyCircle = null;
        
        function getLocation() {
            var btn = document.getElementById("btnLokasi");
            var btnText = document.getElementById("btnLokasiText");
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mendeteksi Lokasi...';
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            } else {
                alert("Browser tidak mendukung geolocation.");
                resetLocationButton();
            }
        }

        function showPosition(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            var accuracy = position.coords.accuracy;
            
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            
            // Remove existing user marker and accuracy circle
            if (userMarker) {
                map.removeLayer(userMarker);
            }
            if (accuracyCircle) {
                map.removeLayer(accuracyCircle);
            }
            
            // Add user marker with custom icon
            var userIcon = L.divIcon({
                html: '<div style="background: linear-gradient(135deg, var(--success) 0%, #0da271 100%); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.2);"><i class="fas fa-user"></i></div>',
                className: 'custom-user-icon',
                iconSize: [40, 40],
                iconAnchor: [20, 40]
            });
            
            userMarker = L.marker([lat, lng], {
                icon: userIcon
            }).addTo(map);
            
            // Add accuracy circle
            accuracyCircle = L.circle([lat, lng], {
                color: '#10b981',
                fillColor: '#10b981',
                fillOpacity: 0.1,
                weight: 1,
                radius: accuracy
            }).addTo(map);
            
            // Calculate distance to school
            var distance = calculateDistance(lat, lng, <?= $latitude_sekolah ?>, <?= $longitude_sekolah ?>);
            
            // Update marker popup
            userMarker.bindPopup(`
                <div class="text-center p-2">
                    <h6 class="fw-bold mb-1">Lokasi Anda</h6>
                    <div class="mb-2">
                        <span class="badge ${distance <= <?= $radius_diizinkan ?> ? 'badge-success' : 'badge-danger'}">
                            <i class="fas fa-ruler me-1"></i>
                            ${Math.round(distance)}m dari sekolah
                        </span>
                    </div>
                    <p class="mb-1 small text-muted">${lat.toFixed(6)}, ${lng.toFixed(6)}</p>
                    <p class="mb-0 small text-muted">Akurasi: ±${Math.round(accuracy)}m</p>
                </div>
            `).openPopup();
            
            // Center map on user location
            map.setView([lat, lng], 18);
            
            // Enable submit button
            document.getElementById('btnKirim').disabled = false;
            
            // Update location button
            var btn = document.getElementById("btnLokasi");
            btn.innerHTML = '<i class="fas fa-check-circle me-2"></i><span id="btnLokasiText">Lokasi Berhasil Dideteksi</span>';
            btn.className = "btn btn-success w-100";
            
            // Show distance alert if outside radius
            if (distance > <?= $radius_diizinkan ?>) {
                alert(`Peringatan: Anda berada ${Math.round(distance - <?= $radius_diizinkan ?>)} meter di luar radius sekolah!`);
            }
        }

        function showError(error) {
            var errorMessage = "Gagal mendapatkan lokasi: ";
            
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage += "Izin lokasi ditolak. Harap izinkan akses lokasi di browser Anda.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage += "Informasi lokasi tidak tersedia.";
                    break;
                case error.TIMEOUT:
                    errorMessage += "Permintaan lokasi timeout. Coba lagi.";
                    break;
                default:
                    errorMessage += "Terjadi kesalahan yang tidak diketahui.";
                    break;
            }
            
            alert(errorMessage);
            resetLocationButton();
        }

        function resetLocationButton() {
            var btn = document.getElementById("btnLokasi");
            btn.innerHTML = '<i class="fas fa-search-location me-2"></i><span id="btnLokasiText">Coba Lagi Deteksi Lokasi</span>';
            btn.className = "btn btn-warning w-100";
            btn.disabled = false;
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; // Earth radius in meters
            const φ1 = lat1 * Math.PI/180;
            const φ2 = lat2 * Math.PI/180;
            const Δφ = (lat2-lat1) * Math.PI/180;
            const Δλ = (lon2-lon1) * Math.PI/180;

            const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                      Math.cos(φ1) * Math.cos(φ2) *
                      Math.sin(Δλ/2) * Math.sin(Δλ/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

            return R * c;
        }
        <?php endif; ?>
        
        // Add hover effects to history items
        document.addEventListener('DOMContentLoaded', function() {
            const historyItems = document.querySelectorAll('.history-item');
            historyItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                    this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
                });
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>
</body>
</html>