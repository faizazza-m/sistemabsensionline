<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('siswa');

$id_siswa = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Variabel CSS */
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --card-bg: #ffffff;
            --sidebar-bg: #1a237e;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
            --transition: all 0.3s ease;
        }
        
        /* Reset dan Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: var(--sidebar-bg);
            color: white;
            height: 100vh;
            position: fixed;
            width: 260px;
            left: 0;
            top: 0;
            transition: var(--transition);
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-header h4 {
            font-weight: 600;
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        
        .sidebar-header small {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
            border-left: 3px solid transparent;
            margin: 2px 0;
            text-decoration: none;
        }
        
        .sidebar-menu .nav-link:hover, 
        .sidebar-menu .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }
        
        .sidebar-menu .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        .sidebar-footer {
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
            transition: var(--transition);
        }
        
        /* Navbar */
        .navbar-custom {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        
        .page-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1.5rem;
        }
        
        /* Absensi Card */
        .absensi-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: none;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .absensi-header {
            background: linear-gradient(135deg, var(--primary) 0%, #3a56d4 100%);
            color: white;
            padding: 20px;
        }
        
        .absensi-body {
            padding: 25px;
        }
        
        .badge-absensi {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        /* Button Styles */
        .btn-absensi {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-absensi:hover {
            background: var(--primary-dark);
            color: white;
        }
        
        .btn-map {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border: 1px solid rgba(247, 37, 133, 0.2);
            padding: 6px 15px;
            border-radius: 6px;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }
        
        .btn-map:hover {
            background: rgba(247, 37, 133, 0.2);
        }
        
        /* Table Styles */
        .custom-table {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .custom-table thead {
            background: var(--primary);
            color: white;
        }
        
        .custom-table th {
            border: none;
            padding: 15px;
            font-weight: 500;
        }
        
        .custom-table td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f3f4;
        }
        
        .custom-table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        /* Status Badges */
        .badge-status {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
            min-width: 100px;
            text-align: center;
        }
        
        .badge-hadir {
            background: rgba(76, 201, 240, 0.1);
            color: #4cc9f0;
        }
        
        .badge-sakit {
            background: rgba(248, 150, 30, 0.1);
            color: #f8961e;
        }
        
        .badge-izin {
            background: rgba(114, 9, 183, 0.1);
            color: #7209b7;
        }
        
        .badge-alpha {
            background: rgba(247, 37, 133, 0.1);
            color: #f72585;
        }
        
        /* Date Styling */
        .date-cell {
            font-weight: 600;
            color: var(--dark);
        }
        
        .time-cell {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
        
        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .navbar-custom {
                margin-top: 10px;
                margin-bottom: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .custom-table {
                font-size: 0.9rem;
            }
            
            .custom-table th,
            .custom-table td {
                padding: 10px;
            }
            
            .absensi-body {
                padding: 15px;
            }
            
            .badge-status {
                min-width: 80px;
                padding: 6px 10px;
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px 10px;
            }
            
            .navbar-custom {
                padding: 12px 15px;
            }
            
            .btn-absensi, .btn-map {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
        }
        
        /* Toggle Button */
        #sidebarToggle {
            background: var(--primary);
            border: none;
            color: white;
            border-radius: 6px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        /* Utility Classes */
        .text-primary { color: var(--primary) !important; }
        .bg-primary { background-color: var(--primary) !important; }
        .btn-primary { 
            background-color: var(--primary) !important; 
            border-color: var(--primary) !important; 
        }
        .btn-primary:hover { 
            background-color: var(--primary-dark) !important; 
            border-color: var(--primary-dark) !important; 
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h4>Panel Siswa</h4>
            <small><?php echo htmlspecialchars($_SESSION['nama']); ?></small>
        </div>
        
        <div class="sidebar-menu">
            <nav class="nav flex-column">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a class="nav-link" href="materi.php">
                    <i class="fas fa-book-reader"></i>
                    <span>Materi</span>
                </a>
                <a class="nav-link" href="tugas.php">
                    <i class="fas fa-tasks"></i>
                    <span>Tugas</span>
                </a>
                <a class="nav-link active" href="absensi.php">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Riwayat Absen</span>
                </a>
                <a class="nav-link" href="ganti_password.php">
                    <i class="fas fa-key"></i>
                    <span>Ganti Password</span>
                </a>
            </nav>
        </div>
        
        <div class="sidebar-footer">
            <a class="nav-link text-white bg-danger bg-opacity-25 rounded py-2 text-center" href="../includes/logout.php" onclick="return confirm('Yakin ingin keluar?')">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </div>
    
    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <nav class="navbar-custom">
            <div class="container-fluid">
                <button class="btn btn-primary me-3 d-lg-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="d-flex align-items-center">
                    <i class="fas fa-history text-primary fs-4 me-3"></i>
                    <div>
                        <h5 class="page-title mb-0">Riwayat Absensi</h5>
                        <small class="text-muted">Catatan kehadiran dan lokasi absensi</small>
                    </div>
                </div>
                
                <div class="ms-auto">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i>
                            <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <div class="dropdown-header">
                                    <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong>
                                    <div class="text-muted small">Siswa</div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="ganti_password.php"><i class="fas fa-key me-2"></i>Ganti Password</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Pengaturan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../includes/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Absensi Content -->
        <div class="absensi-card">
            <div class="absensi-header">
                <h5 class="mb-2"><i class="fas fa-history me-2"></i>Riwayat Absensi Saya</h5>
                <span class="badge-absensi">Total: 
                    <?php 
                    $count_query = "SELECT COUNT(*) as total FROM absensi_siswa WHERE siswa_id = '$id_siswa'";
                    $count_result = mysqli_query($conn, $count_query);
                    $count_data = mysqli_fetch_assoc($count_result);
                    echo $count_data['total'];
                    ?> Catatan
                </span>
            </div>
            
            <div class="absensi-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="absensi_input.php" class="btn-absensi">
                            <i class="fas fa-map-marker-alt me-2"></i>Absen Sekarang
                        </a>
                    </div>
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>Klik tombol peta untuk melihat lokasi absensi
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table custom-table">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Tanggal & Waktu</th>
                                <th>Hari</th>
                                <th>Lokasi Absensi</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = mysqli_query($conn, "SELECT * FROM absensi_siswa WHERE siswa_id = '$id_siswa' ORDER BY tanggal DESC");
                            
                            if(mysqli_num_rows($query) > 0) {
                                while($row = mysqli_fetch_assoc($query)):
                                    $hari = date('l', strtotime($row['tanggal']));
                                    $hari_indo = [
                                        'Sunday' => 'Minggu',
                                        'Monday' => 'Senin', 
                                        'Tuesday' => 'Selasa',
                                        'Wednesday' => 'Rabu',
                                        'Thursday' => 'Kamis',
                                        'Friday' => 'Jumat',
                                        'Saturday' => 'Sabtu'
                                    ];
                                    
                                    // Determine badge class
                                    $badge_class = '';
                                    switch($row['status']) {
                                        case 'Hadir': $badge_class = 'badge-hadir'; break;
                                        case 'Sakit': $badge_class = 'badge-sakit'; break;
                                        case 'Izin': $badge_class = 'badge-izin'; break;
                                        case 'Alpha': $badge_class = 'badge-alpha'; break;
                                        default: $badge_class = 'badge-hadir';
                                    }
                            ?>
                            <tr>
                                <td class="fw-bold"><?= $no++; ?></td>
                                <td>
                                    <div class="date-cell">
                                        <?= date('d F Y', strtotime($row['tanggal'])); ?>
                                    </div>
                                    <div class="time-cell">
                                        <i class="far fa-clock me-1"></i>
                                        <?= date('H:i', strtotime($row['tanggal'])); ?> WIB
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium"><?= $hari_indo[$hari]; ?></span>
                                </td>
                                <td>
                                    <?php if(!empty($row['lat']) && !empty($row['lng'])): ?>
                                        <a href="https://www.google.com/maps?q=<?= $row['lat'] ?>,<?= $row['lng'] ?>" 
                                           target="_blank" 
                                           class="btn-map"
                                           title="Lihat lokasi di Google Maps">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            Lihat Peta
                                        </a>
                                        <div class="text-muted small mt-1">
                                            Koordinat: <?= round($row['lat'], 4) ?>, <?= round($row['lng'], 4) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">Tidak tersedia</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge-status <?= $badge_class ?>">
                                        <?php if($row['status'] == 'Hadir'): ?>
                                            <i class="fas fa-check-circle me-1"></i>
                                        <?php elseif($row['status'] == 'Sakit'): ?>
                                            <i class="fas fa-thermometer me-1"></i>
                                        <?php elseif($row['status'] == 'Izin'): ?>
                                            <i class="fas fa-envelope me-1"></i>
                                        <?php elseif($row['status'] == 'Alpha'): ?>
                                            <i class="fas fa-times-circle me-1"></i>
                                        <?php endif; ?>
                                        <?= $row['status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; } else { ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-clipboard-list"></i>
                                        <h5 class="mb-2">Belum Ada Data Absensi</h5>
                                        <p class="text-muted">Riwayat absensi akan ditampilkan di sini.</p>
                                        <a href="absensi_input.php" class="btn-absensi mt-3">
                                            <i class="fas fa-map-marker-alt me-2"></i>Absen Sekarang
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    sidebarOverlay.classList.toggle('active');
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                });
            }
            
            // Close sidebar when clicking on a link in mobile view
            const sidebarLinks = document.querySelectorAll('.sidebar-menu .nav-link');
            sidebarLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        sidebar.classList.remove('active');
                        sidebarOverlay.classList.remove('active');
                    }
                });
            });
            
            // Update active link based on current page
            const currentPage = window.location.pathname.split('/').pop();
            sidebarLinks.forEach(function(link) {
                const linkPage = link.getAttribute('href');
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>