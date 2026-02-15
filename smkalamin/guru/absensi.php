<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('guru');

$id_guru = $_SESSION['user_id'];
$tgl_hari_ini = date('Y-m-d');

$selected_kelas = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '';
$selected_mapel = isset($_GET['mapel_id']) ? $_GET['mapel_id'] : '';

// --- LOGIKA BUKA / TUTUP ABSENSI ---
if (isset($_POST['aksi_absen'])) {
    $kelas = $_POST['kelas_id'];
    $mapel = $_POST['mapel_id'];
    $aksi  = $_POST['aksi_absen']; 
    
    // Cek sesi (sekarang per MAPEL juga)
    $cek = mysqli_query($conn, "SELECT * FROM sesi_absensi WHERE kelas_id='$kelas' AND mapel_id='$mapel' AND guru_id='$id_guru' AND tanggal='$tgl_hari_ini'");
    
    if (mysqli_num_rows($cek) > 0) {
        $waktu = ($aksi == 'buka') ? "waktu_buka=NOW()" : "waktu_tutup=NOW()";
        mysqli_query($conn, "UPDATE sesi_absensi SET status='$aksi', $waktu WHERE kelas_id='$kelas' AND mapel_id='$mapel' AND guru_id='$id_guru' AND tanggal='$tgl_hari_ini'");
    } else {
        if ($aksi == 'buka') {
            mysqli_query($conn, "INSERT INTO sesi_absensi (kelas_id, mapel_id, guru_id, tanggal, status, waktu_buka) VALUES ('$kelas', '$mapel', '$id_guru', '$tgl_hari_ini', 'buka', NOW())");
        }
    }
    header("Location: absensi.php?kelas_id=$kelas&mapel_id=$mapel");
    exit();
}

// Cek Status Sesi
$status_sesi = 'tutup';
if ($selected_kelas && $selected_mapel) {
    $q_sesi = mysqli_query($conn, "SELECT status FROM sesi_absensi WHERE kelas_id='$selected_kelas' AND mapel_id='$selected_mapel' AND guru_id='$id_guru' AND tanggal='$tgl_hari_ini'");
    if ($d_sesi = mysqli_fetch_assoc($q_sesi)) {
        $status_sesi = $d_sesi['status'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Mata Pelajaran - Dashboard Guru</title>
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
        
        .card-header.bg-success {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%) !important;
            color: white;
        }
        
        .card-header.bg-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%) !important;
            color: white;
        }
        
        /* Filter Card */
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .form-select, .form-control {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 1rem;
            transition: all 0.3s;
            font-size: 0.95rem;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
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
        
        /* Status badges */
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
        
        .badge-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
            color: white;
        }
        
        .badge-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
        }
        
        .badge-info {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
        }
        
        .badge-secondary {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white;
        }
        
        /* Session Status Card */
        .session-status-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left: 5px solid var(--primary);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(37, 99, 235, 0.15);
        }
        
        .live-badge {
            animation: pulse 1.5s infinite;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
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
            
            .filter-card {
                padding: 1rem;
            }
            
            .session-status-card {
                padding: 1rem;
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
            
            .form-select, .form-control {
                font-size: 0.9rem;
                padding: 0.5rem 0.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
            }
            
            .filter-card {
                padding: 1rem;
            }
            
            .form-select, .form-control {
                margin-bottom: 0.5rem;
            }
            
            .btn-admin, .btn-outline-admin {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
                width: 100%;
                margin-top: 0.5rem;
            }
            
            .table {
                font-size: 0.85rem;
            }
            
            .badge {
                font-size: 0.8rem;
                padding: 0.3rem 0.6rem;
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
            padding: 3rem 1rem;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
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
                <a class="nav-link active" href="absensi.php">
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
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-calendar-check me-2"></i>
                        Kontrol Absensi Mata Pelajaran
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
                <h5 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>Absensi Per Mata Pelajaran
                </h5>
                <span class="badge bg-light text-dark">
                    <i class="fas fa-calendar-day me-1"></i>
                    <?php echo date('d F Y'); ?>
                </span>
            </div>
            
            <div class="card-body">
                <!-- Filter Section -->
                <div class="filter-card fade-in">
                    <h6 class="mb-3 fw-bold text-dark">
                        <i class="fas fa-filter me-2"></i>Filter Data
                    </h6>
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-lg-5 col-md-6">
                            <label class="form-label fw-semibold mb-2">Pilih Kelas</label>
                            <select name="kelas_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php
                                $q_kelas = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama ASC");
                                while($k = mysqli_fetch_assoc($q_kelas)){
                                    $sel = ($k['id'] == $selected_kelas) ? 'selected' : '';
                                    echo "<option value='{$k['id']}' $sel>{$k['nama']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-lg-5 col-md-6">
                            <label class="form-label fw-semibold mb-2">Pilih Mata Pelajaran</label>
                            <select name="mapel_id" class="form-select" required>
                                <option value="">-- Pilih Mapel --</option>
                                <?php
                                $q_mapel = mysqli_query($conn, "SELECT * FROM mata_pelajaran ORDER BY nama ASC");
                                while($m = mysqli_fetch_assoc($q_mapel)){
                                    $sel = ($m['id'] == $selected_mapel) ? 'selected' : '';
                                    echo "<option value='{$m['id']}' $sel>{$m['nama']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-12">
                            <button type="submit" class="btn btn-admin w-100">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>

                <?php if($selected_kelas && $selected_mapel): ?>
                    <?php
                    // Ambil nama kelas dan mapel untuk ditampilkan
                    $kelas_nama = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM kelas WHERE id='$selected_kelas'"))['nama'];
                    $mapel_nama = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM mata_pelajaran WHERE id='$selected_mapel'"))['nama'];
                    ?>
                    
                    <!-- Session Status -->
                    <div class="session-status-card fade-in">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-3 fw-bold text-primary">
                                    <i class="fas fa-info-circle me-2"></i>Status Sesi Absensi
                                </h5>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block">Mata Pelajaran</small>
                                        <span class="fw-semibold"><?= $mapel_nama ?></span>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block">Kelas</small>
                                        <span class="fw-semibold"><?= $kelas_nama ?></span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Tanggal</small>
                                        <span class="fw-semibold"><?= date('d/m/Y'); ?></span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Status</small>
                                        <?php if($status_sesi == 'buka'): ?>
                                            <span class="badge badge-success live-badge">
                                                <i class="fas fa-play-circle me-1"></i> DIBUKA
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-stop-circle me-1"></i> DITUTUP
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <form method="POST" class="d-inline-block">
                                    <input type="hidden" name="kelas_id" value="<?= $selected_kelas; ?>">
                                    <input type="hidden" name="mapel_id" value="<?= $selected_mapel; ?>">
                                    <?php if($status_sesi == 'tutup'): ?>
                                        <button type="submit" name="aksi_absen" value="buka" class="btn btn-admin w-100">
                                            <i class="fas fa-play me-2"></i>BUKA ABSEN
                                        </button>
                                        <p class="small text-muted mt-2 mb-0 text-center">
                                            Klik untuk membuka sesi absensi
                                        </p>
                                    <?php else: ?>
                                        <button type="submit" name="aksi_absen" value="tutup" class="btn btn-danger w-100" style="background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);">
                                            <i class="fas fa-stop me-2"></i>TUTUP ABSEN
                                        </button>
                                        <p class="small text-muted mt-2 mb-0 text-center">
                                            Sesi absensi sedang aktif
                                        </p>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Students List -->
                    <div class="fade-in">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-users me-2"></i>Daftar Siswa - <?= $kelas_nama ?>
                            </h5>
                            <span class="badge bg-light text-dark border">
                                Total: 
                                <?php
                                $total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa WHERE kelas_id='$selected_kelas'");
                                $total_data = mysqli_fetch_assoc($total_query);
                                echo $total_data['total'];
                                ?>
                                Siswa
                            </span>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th width="60" class="text-center">No</th>
                                        <th>Nama Siswa</th>
                                        <th width="100" class="text-center">NIS</th>
                                        <th width="120" class="text-center">Waktu Absen</th>
                                        <th width="100" class="text-center">Lokasi</th>
                                        <th width="120" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $query = "SELECT s.nama, s.nis, a.status, a.tanggal, a.lat, a.lng 
                                              FROM siswa s 
                                              LEFT JOIN absensi_siswa a ON s.id = a.siswa_id 
                                                AND DATE(a.tanggal) = '$tgl_hari_ini'
                                                AND a.mapel_id = '$selected_mapel'
                                              WHERE s.kelas_id = '$selected_kelas' 
                                              ORDER BY s.nama ASC";
                                    
                                    $res = mysqli_query($conn, $query);
                                    if(mysqli_num_rows($res) > 0) {
                                        while($row = mysqli_fetch_assoc($res)):
                                            $status = $row['status'] ?? 'Belum';
                                            $jam = $row['tanggal'] ? date('H:i', strtotime($row['tanggal'])) : '-';
                                            
                                            // Tentukan warna badge berdasarkan status
                                            if($status == 'Hadir') {
                                                $badge_class = 'badge-success';
                                            } elseif($status == 'Belum') {
                                                $badge_class = 'badge-secondary';
                                            } elseif($status == 'Izin') {
                                                $badge_class = 'badge-warning';
                                            } elseif($status == 'Sakit') {
                                                $badge_class = 'badge-info';
                                            } elseif($status == 'Alpa') {
                                                $badge_class = 'badge-danger';
                                            } else {
                                                $badge_class = 'badge-secondary';
                                            }
                                    ?>
                                    <tr>
                                        <td class="text-center fw-semibold"><?= $no++; ?></td>
                                        <td class="fw-medium"><?= htmlspecialchars($row['nama']); ?></td>
                                        <td class="text-center text-muted"><?= $row['nis']; ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"><?= $jam; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if(!empty($row['lat']) && !empty($row['lng'])): ?>
                                                <a href="http://maps.google.com/?q=<?= $row['lat'] ?>,<?= $row['lng'] ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   title="Lihat lokasi"
                                                   style="border-radius: 8px;">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $badge_class; ?> px-3">
                                                <?= $status; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; } else { ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-user-slash"></i>
                                                <h5 class="text-muted mt-3">Tidak ada siswa di kelas ini</h5>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if($status_sesi == 'buka'): ?>
                        <div class="alert alert-info mt-3 fade-in" role="alert">
                            <i class="fas fa-sync-alt me-2"></i>
                            <strong>Auto-refresh aktif:</strong> Halaman akan diperbarui otomatis setiap 15 detik selama sesi absensi dibuka.
                        </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state fade-in">
                        <i class="fas fa-hand-point-up"></i>
                        <h4 class="text-muted mt-3">Pilih Kelas dan Mata Pelajaran</h4>
                        <p class="text-muted mb-4">Silakan pilih kelas dan mata pelajaran terlebih dahulu untuk melihat dan mengontrol absensi.</p>
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            Pilih kelas dan mata pelajaran yang Anda ajar untuk memulai sesi absensi.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Footer -->
            <div class="card-footer bg-light">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Terakhir diperbarui: <?php echo date('H:i:s'); ?>
                        </small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">
                            Sistem Absensi Digital © <?php echo date('Y'); ?>
                        </small>
                    </div>
                </div>
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
        
        // Auto Refresh jika sesi dibuka
        <?php if($status_sesi == 'buka'): ?>
        setTimeout(function() {
            location.reload();
        }, 15000); // Refresh setiap 15 detik saat sesi dibuka
        <?php endif; ?>
        
        // Add hover effects to table rows
        document.addEventListener('DOMContentLoaded', function() {
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