<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('siswa');

$id_siswa = $_SESSION['user_id'];
$d_siswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT kelas_id FROM siswa WHERE id='$id_siswa'"));
$kelas_id = $d_siswa['kelas_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas Sekolah</title>
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
        
        /* Tugas Card */
        .tugas-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: none;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .tugas-card:hover {
            box-shadow: var(--shadow-hover);
        }
        
        .tugas-header {
            background: linear-gradient(135deg, var(--primary) 0%, #3a56d4 100%);
            color: white;
            padding: 20px;
        }
        
        .tugas-body {
            padding: 25px;
        }
        
        .badge-tugas {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        /* Button Styles */
        .btn-tugas {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        
        .btn-tugas:hover {
            background: var(--primary-dark);
            color: white;
        }
        
        .btn-download {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            border: 1px solid rgba(67, 97, 238, 0.2);
            padding: 8px 15px;
            border-radius: 6px;
            transition: var(--transition);
        }
        
        .btn-download:hover {
            background: rgba(67, 97, 238, 0.2);
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
        }
        
        .badge-selesai {
            background: rgba(76, 201, 240, 0.1);
            color: #4cc9f0;
        }
        
        .badge-belum {
            background: rgba(248, 150, 30, 0.1);
            color: #f8961e;
        }
        
        /* Deadline Warning */
        .deadline-warning {
            color: var(--danger);
            font-weight: 500;
        }
        
        .deadline-normal {
            color: var(--gray);
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
            
            .tugas-body {
                padding: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px 10px;
            }
            
            .navbar-custom {
                padding: 12px 15px;
            }
            
            .btn-tugas, .btn-download {
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
        
        /* Badge for notifications */
        .badge-notification {
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            margin-left: auto;
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
                <a class="nav-link active" href="tugas.php">
                    <i class="fas fa-tasks"></i>
                    <span>Tugas</span>
                </a>
                <a class="nav-link" href="absensi.php">
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
            <a class="nav-link text-white bg-danger bg-opacity-25 rounded py-2 text-center" href="../includes/logout.php">
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
                    <i class="fas fa-tasks text-primary fs-4 me-3"></i>
                    <div>
                        <h5 class="page-title mb-0">Tugas Sekolah</h5>
                        <small class="text-muted">Daftar tugas dan status pengerjaan</small>
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
        
        <!-- Tugas Content -->
        <div class="tugas-card">
            <div class="tugas-header">
                <h5 class="mb-2"><i class="fas fa-tasks me-2"></i>Daftar Tugas Sekolah</h5>
                <span class="badge-tugas">Total: 
                    <?php 
                    $count_query = "SELECT COUNT(*) as total FROM tugas WHERE kelas_id = '$kelas_id'";
                    $count_result = mysqli_query($conn, $count_query);
                    $count_data = mysqli_fetch_assoc($count_result);
                    echo $count_data['total'];
                    ?> Tugas
                </span>
            </div>
            
            <div class="tugas-body">
                <div class="table-responsive">
                    <table class="table custom-table">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Judul Tugas</th>
                                <th>Guru Pengajar</th>
                                <th>Deadline</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q = mysqli_query($conn, "SELECT t.*, g.nama_lengkap as guru 
                                                      FROM tugas t 
                                                      JOIN guru g ON t.guru_id = g.id 
                                                      WHERE t.kelas_id = '$kelas_id' ORDER BY t.deadline DESC");
                            
                            if($q && mysqli_num_rows($q) > 0) {
                                while($row = mysqli_fetch_assoc($q)):
                                    $id_tgs = $row['id'];
                                    $cek = mysqli_query($conn, "SELECT * FROM tugas_siswa WHERE tugas_id='$id_tgs' AND siswa_id='$id_siswa'");
                                    $sudah = (mysqli_num_rows($cek) > 0);
                                    
                                    // Cek deadline
                                    $deadline = strtotime($row['deadline']);
                                    $now = time();
                                    $is_overdue = ($deadline < $now && !$sudah);
                            ?>
                            <tr>
                                <td class="fw-bold"><?= $no++; ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['judul']); ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($row['deskripsi'] ?? 'Tidak ada deskripsi'); ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['guru']); ?></td>
                                <td>
                                    <i class="far fa-calendar me-2 text-muted"></i>
                                    <?= date('d/m/Y', strtotime($row['deadline'])); ?>
                                    <br>
                                    <small class="<?= $is_overdue ? 'deadline-warning' : 'deadline-normal'; ?>">
                                        <i class="far fa-clock me-1"></i>
                                        <?= date('H:i', strtotime($row['deadline'])); ?>
                                        <?php if($is_overdue): ?>
                                            <span class="badge bg-danger ms-2">Terlambat</span>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <?php if($sudah): ?>
                                        <span class="badge-status badge-selesai">
                                            <i class="fas fa-check-circle me-1"></i>Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-status badge-belum">
                                            <i class="fas fa-clock me-1"></i>Belum
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if(!empty($row['file_tugas'])): ?>
                                            <a href="../assets/uploads/<?= $row['file_tugas']; ?>" target="_blank" class="btn-download" title="Download Soal">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if(!$sudah): ?>
                                            <a href="tugas_kirim.php?id=<?= $row['id']; ?>" class="btn-tugas">
                                                <i class="fas fa-paper-plane me-1"></i>Kerjakan
                                            </a>
                                        <?php else: ?>
                                            <a href="#" class="btn-tugas disabled" style="background: rgba(0,0,0,0.1); color: #6c757d; cursor: default;">
                                                <i class="fas fa-check me-1"></i>Sudah Dikerjakan
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; } else { ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-tasks"></i>
                                        <h5 class="mb-2">Belum Ada Tugas</h5>
                                        <p class="text-muted">Tugas sekolah akan ditampilkan di sini ketika tersedia.</p>
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
            
            // Highlight overdue tasks
            const deadlineCells = document.querySelectorAll('td .deadline-warning');
            deadlineCells.forEach(function(cell) {
                const row = cell.closest('tr');
                if (row) {
                    row.style.backgroundColor = 'rgba(247, 37, 133, 0.05)';
                }
            });
        });
    </script>
</body>
</html>