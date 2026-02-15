<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('guru');

$id_guru = $_SESSION['user_id'];

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM tugas WHERE id='$id'");
    echo "<script>alert('Tugas berhasil dihapus'); window.location='tugas.php';</script>";
    exit();
}

// Hitung statistik tugas
$tgl_sekarang = date('Y-m-d H:i:s');

// Total tugas
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE guru_id = '$id_guru'");
$total_tugas = mysqli_fetch_assoc($q_total)['total'];

// Tugas aktif (belum lewat deadline)
$q_active = mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE guru_id = '$id_guru' AND deadline >= '$tgl_sekarang'");
$active_tugas = mysqli_fetch_assoc($q_active)['total'];

// Tugas lewat deadline
$q_overdue = mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE guru_id = '$id_guru' AND deadline < '$tgl_sekarang'");
$overdue_tugas = mysqli_fetch_assoc($q_overdue)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tugas - Dashboard Guru</title>
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
        
        /* Task card */
        .task-card {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.08);
            background: white;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
        }
        
        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-color: var(--primary);
        }
        
        .task-icon {
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
        
        .task-icon.overdue { background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%); }
        .task-icon.warning { background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%); }
        .task-icon.success { background: linear-gradient(135deg, var(--success) 0%, #059669 100%); }
        
        .task-info h6 {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--dark);
        }
        
        .task-info p {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        
        /* Deadline styling */
        .deadline-soon {
            color: var(--danger) !important;
            font-weight: 600;
        }
        
        .deadline-future {
            color: var(--success) !important;
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
            
            .table thead th,
            .table tbody td {
                padding: 0.75rem;
            }
            
            .btn-admin {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
            
            .task-card {
                flex-direction: column;
                text-align: center;
            }
            
            .task-icon {
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
            
            .action-buttons {
                justify-content: flex-start;
                margin-top: 0.5rem;
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
        
        /* Deadline warning */
        .deadline-warning {
            position: relative;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(239, 68, 68, 0.02) 100%);
        }
        
        .deadline-warning:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--danger);
            border-radius: 0 4px 4px 0;
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
                <a class="nav-link active" href="tugas.php">
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
                        <i class="fas fa-tasks me-2"></i>
                        Manajemen Tugas
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
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="stat-card fade-in">
                    <div class="stat-icon text-primary">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3><?php echo $total_tugas; ?></h3>
                    <p class="text-muted mb-0">Total Tugas</p>
                </div>
            </div>
            
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="stat-card border-success fade-in">
                    <div class="stat-icon text-success">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3><?php echo $active_tugas; ?></h3>
                    <p class="text-muted mb-0">Tugas Aktif</p>
                </div>
            </div>
            
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="stat-card border-danger fade-in">
                    <div class="stat-icon text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3><?php echo $overdue_tugas; ?></h3>
                    <p class="text-muted mb-0">Lewat Deadline</p>
                </div>
            </div>
        </div>
        
        <!-- Main Card -->
        <div class="card fade-in">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>Daftar Tugas
                    </h5>
                    <p class="mb-0 opacity-90 small">Kelola semua tugas yang telah dibuat untuk siswa</p>
                </div>
                <a href="tugas_tambah.php" class="btn btn-admin">
                    <i class="fas fa-plus me-2"></i>Buat Tugas Baru
                </a>
            </div>
            
            <div class="card-body">
                <?php
                $q = mysqli_query($conn, "SELECT t.*, k.nama as kelas, mp.nama as mapel FROM tugas t 
                    JOIN kelas k ON t.kelas_id = k.id 
                    JOIN mata_pelajaran mp ON t.mapel_id = mp.id 
                    WHERE t.guru_id = '$id_guru' ORDER BY t.deadline DESC");
                $total_tugas_list = mysqli_num_rows($q);
                ?>
                
                <?php if($total_tugas_list > 0): ?>
                    <div class="mb-4">
                        <div class="alert alert-info border-0 d-flex align-items-center" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%); border-left: 4px solid var(--primary);">
                            <i class="fas fa-info-circle me-3" style="color: var(--primary); font-size: 1.2rem;"></i>
                            <div>
                                <strong class="d-block">Total <?= $total_tugas_list; ?> Tugas</strong>
                                <small class="text-muted">Semua tugas yang telah dibuat akan ditampilkan di sini</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th>Judul Tugas</th>
                                    <th>Kelas & Mapel</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                    <th class="text-center" width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                mysqli_data_seek($q, 0);
                                while($r = mysqli_fetch_assoc($q)):
                                    $deadline = strtotime($r['deadline']);
                                    $now = time();
                                    $diff = $deadline - $now;
                                    $hours_left = floor($diff / 3600);
                                    
                                    // Tentukan status dan styling
                                    if ($diff < 0) {
                                        $status = 'Lewat Deadline';
                                        $status_class = 'badge-danger';
                                        $deadline_class = 'deadline-soon';
                                        $row_class = 'deadline-warning';
                                        $icon_class = 'overdue';
                                    } elseif ($diff < 86400) { // kurang dari 24 jam
                                        $status = 'Deadline Dekat';
                                        $status_class = 'badge-warning';
                                        $deadline_class = 'deadline-soon';
                                        $row_class = '';
                                        $icon_class = 'warning';
                                    } else {
                                        $status = 'Aktif';
                                        $status_class = 'badge-success';
                                        $deadline_class = 'deadline-future';
                                        $row_class = '';
                                        $icon_class = 'success';
                                    }
                                ?>
                                <tr class="<?= $row_class ?>" data-label="Tugas">
                                    <td data-label="No" class="text-center fw-semibold"><?= $no++; ?></td>
                                    <td data-label="Judul Tugas">
                                        <a href="tugas_detail.php?id=<?= $r['id'] ?>" class="text-decoration-none text-dark">
                                            <div class="task-card">
                                                <div class="task-icon <?= $icon_class ?>">
                                                    <i class="fas fa-tasks"></i>
                                                </div>
                                                <div class="task-info">
                                                    <h6 class="mb-1"><?= htmlspecialchars($r['judul']); ?></h6>
                                                    <?php if(!empty($r['deskripsi'])): ?>
                                                        <p class="mb-1"><?= htmlspecialchars(substr($r['deskripsi'], 0, 60)); ?>...</p>
                                                    <?php endif; ?>
                                                    <small class="text-muted">
                                                        <i class="far fa-calendar me-1"></i>
                                                        Dibuat: <?= date('d/m/Y', strtotime($r['created_at'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td data-label="Kelas & Mapel">
                                        <div class="mb-2">
                                            <span class="badge badge-primary mb-1">
                                                <?= htmlspecialchars($r['kelas']); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-book me-1"></i>
                                                <?= htmlspecialchars($r['mapel']); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td data-label="Deadline">
                                        <div class="<?= $deadline_class ?>">
                                            <i class="far fa-clock me-1"></i>
                                            <?= date('d/m/Y H:i', $deadline); ?>
                                        </div>
                                        <?php if($diff > 0 && $diff < 86400): ?>
                                            <small class="text-muted d-block mt-1">
                                                <i class="fas fa-hourglass-half me-1"></i>
                                                <?= $hours_left ?> jam lagi
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Status">
                                        <span class="badge <?= $status_class ?>">
                                            <i class="fas fa-circle fa-xs me-1"></i>
                                            <?= $status ?>
                                        </span>
                                    </td>
                                    <td data-label="Aksi" class="text-center">
                                        <div class="action-buttons">
                                            <a href="tugas_detail.php?id=<?= $r['id'] ?>" 
                                               class="action-btn btn btn-outline-primary btn-sm" 
                                               title="Detail Tugas">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="tugas_edit.php?id=<?= $r['id'] ?>" 
                                               class="action-btn btn btn-outline-warning btn-sm" 
                                               title="Edit Tugas">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="tugas.php?hapus=<?= $r['id']; ?>" 
                                               class="action-btn btn btn-danger btn-sm" 
                                               onclick="return confirm('Yakin ingin menghapus tugas ini?')" 
                                               title="Hapus Tugas">
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
                                    Tugas terakhir dibuat: 
                                    <?php 
                                    $last_task = mysqli_query($conn, "SELECT MAX(created_at) as last_date FROM tugas WHERE guru_id='$id_guru'");
                                    $last_date = mysqli_fetch_assoc($last_task)['last_date'];
                                    echo $last_date ? date('d F Y, H:i', strtotime($last_date)) : '-';
                                    ?>
                                </small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <small class="text-muted">
                                    Total: <?= $total_tugas_list; ?> tugas • 
                                    Sistem Tugas © <?php echo date('Y'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state fade-in">
                        <i class="fas fa-tasks"></i>
                        <h4 class="text-muted mt-3 mb-2">Belum Ada Tugas</h4>
                        <p class="text-muted mb-4">Anda belum membuat tugas untuk siswa.</p>
                        <a href="tugas_tambah.php" class="btn btn-admin btn-lg px-5">
                            <i class="fas fa-plus me-2"></i>Buat Tugas Pertama
                        </a>
                        <div class="mt-4">
                            <div class="alert alert-info border-0" style="max-width: 500px; margin: 0 auto;">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Tips:</strong> Buat tugas dengan deadline yang jelas untuk memudahkan siswa dalam mengatur waktu pengerjaan.
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
                    if (!confirm('Yakin ingin menghapus tugas ini? Tugas yang dihapus tidak dapat dikembalikan.')) {
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
            
            // Update deadline countdown
            function updateDeadlines() {
                const now = new Date();
                const deadlineElements = document.querySelectorAll('[data-label="Deadline"]');
                
                deadlineElements.forEach(element => {
                    const deadlineText = element.querySelector('.deadline-soon, .deadline-future')?.textContent;
                    if (deadlineText) {
                        // Parse deadline date from text
                        const deadlineParts = deadlineText.match(/(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})/);
                        if (deadlineParts) {
                            const deadlineDate = new Date(
                                parseInt(deadlineParts[3]),
                                parseInt(deadlineParts[2]) - 1,
                                parseInt(deadlineParts[1]),
                                parseInt(deadlineParts[4]),
                                parseInt(deadlineParts[5])
                            );
                            
                            const diffMs = deadlineDate - now;
                            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                            const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                            
                            // Update countdown text if within 24 hours
                            if (diffMs > 0 && diffMs < 24 * 60 * 60 * 1000) {
                                const countdownElement = element.querySelector('small.text-muted');
                                if (countdownElement) {
                                    countdownElement.innerHTML = `<i class="fas fa-hourglass-half me-1"></i> ${diffHours} jam ${diffMinutes} menit lagi`;
                                }
                            }
                        }
                    }
                });
            }
            
            // Update deadlines every minute
            setInterval(updateDeadlines, 60000);
            updateDeadlines(); // Initial call
        });
    </script>
</body>
</html>