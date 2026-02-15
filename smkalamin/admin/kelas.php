<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Cek login admin
redirectIfNotLoggedIn('admin');

// --- PROSES HAPUS DATA ---
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $delete = mysqli_query($conn, "DELETE FROM kelas WHERE id = '$id'");
    if ($delete) {
        echo "<script>alert('Data berhasil dihapus!'); window.location='kelas.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data! Mungkin kelas ini sedang digunakan oleh siswa.'); window.location='kelas.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kelas - Admin</title>
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
        
        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
            border: none;
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, var(--warning) 100%);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            border: none;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, var(--danger) 100%);
            transform: translateY(-2px);
        }
        
        /* Action buttons container */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
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
        
        /* Kelas icon styling */
        .kelas-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
        }
        
        /* Kelas badge */
        .kelas-badge {
            background: linear-gradient(135deg, var(--success) 0%, #0da271 100%);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        /* Student count badge */
        .student-count {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
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
            
            .card-header a {
                width: 100%;
                text-align: center;
            }
            
            .table-responsive {
                font-size: 0.85rem;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.6rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.3rem;
            }
            
            .kelas-info {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.5rem;
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
            
            .kelas-badge, .student-count {
                font-size: 0.7rem;
                padding: 0.2rem 0.5rem;
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
        
        /* Kelas card styling */
        .kelas-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .kelas-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        /* Kelas info row */
        .kelas-info {
            display: flex;
            align-items: center;
            gap: 1rem;
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
                <a class="nav-link active" href="kelas.php"><i class="fas fa-chalkboard"></i> Kelas</a>
                <a class="nav-link" href="laporan-absensi.php"><i class="fas fa-chart-bar"></i> Laporan Absensi</a>
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
                    <h5 class="mb-0 text-dark"><i class="fas fa-chalkboard me-2"></i>Manajemen Kelas</h5>
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
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="kelas-icon">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 text-primary fw-bold">Data Kelas</h5>
                        <small class="text-muted">Kelola semua kelas di SMK Al Amin Cibening</small>
                    </div>
                </div>
                <a href="kelas_tambah.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Kelas
                </a>
            </div>
            <div class="card-body">
                <!-- Stats Summary -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card border-start border-primary border-4 bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Kelas</h6>
                                        <h3 class="mb-0 fw-bold text-primary">
                                            <?php 
                                            $total = mysqli_query($conn, "SELECT COUNT(*) as total FROM kelas");
                                            echo mysqli_fetch_assoc($total)['total'];
                                            ?>
                                        </h3>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                        <i class="fas fa-chalkboard fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-info-circle text-primary me-2"></i>
                                    Anda dapat menambahkan, mengedit, atau menghapus kelas. Pastikan kelas yang dihapus tidak memiliki siswa yang terdaftar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="60%">Kelas</th>
                                <th width="20%" class="text-center">Jumlah Siswa</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = mysqli_query($conn, "
                                SELECT k.*, 
                                       (SELECT COUNT(*) FROM siswa WHERE kelas_id = k.id) as jumlah_siswa 
                                FROM kelas k 
                                ORDER BY k.nama ASC
                            ");
                            
                            if(mysqli_num_rows($query) > 0):
                                while ($row = mysqli_fetch_assoc($query)):
                            ?>
                            <tr class="kelas-card">
                                <td class="text-center fw-bold"><?php echo $no++; ?></td>
                                <td>
                                    <div class="kelas-info">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                                <i class="fas fa-chalkboard text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?php echo $row['nama']; ?></h6>
                                                <small class="text-muted">ID: KLS-<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></small>
                                            </div>
                                        </div>
                                        <span class="kelas-badge ms-auto">Kelas <?php echo $row['nama']; ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="student-count">
                                            <i class="fas fa-user-graduate me-1"></i>
                                            <?php echo $row['jumlah_siswa']; ?> Siswa
                                        </span>
                                        <?php if($row['jumlah_siswa'] > 0): ?>
                                        <small class="text-muted mt-1">
                                            <a href="users.php?kelas=<?php echo $row['id']; ?>" class="text-primary">
                                                Lihat Siswa
                                            </a>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="kelas_edit.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-warning btn-sm" 
                                           title="Edit">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <a href="kelas.php?hapus=<?php echo $row['id']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus kelas \'<?php echo $row['nama']; ?>\'?\n\n<?php echo $row['jumlah_siswa']; ?> siswa akan terpengaruh.')"
                                           title="Hapus">
                                            <i class="fas fa-trash me-1"></i>Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-chalkboard text-muted"></i>
                                        <h5 class="mt-3 mb-2">Belum ada data kelas</h5>
                                        <p class="text-muted mb-0">Mulai dengan menambahkan kelas pertama Anda</p>
                                        <a href="kelas_tambah.php" class="btn btn-primary mt-3">
                                            <i class="fas fa-plus me-2"></i>Tambah Kelas Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Info -->
                <?php if(mysqli_num_rows($query) > 0): ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Menampilkan <span class="fw-bold"><?php echo mysqli_num_rows($query); ?></span> kelas
                    </div>
                    <div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled"><a class="page-link" href="#">Sebelumnya</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">Berikutnya</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-5 pt-4 border-top text-center text-muted">
            <p class="mb-1">© <?php echo date('Y'); ?> SMK Al Amin Cibening. All rights reserved.</p>
            <p class="small">Manajemen Kelas v1.0</p>
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
        
        // Confirm before deleting with custom message
        const deleteButtons = document.querySelectorAll('a[onclick*="confirm"]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const match = this.getAttribute('onclick').match(/confirm\('(.*?)'\)/);
                if (match && !confirm(match[1])) {
                    e.preventDefault();
                }
            });
        });
        
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
        
        // Make kelas cards clickable (for future expansion)
        const kelasCards = document.querySelectorAll('.kelas-card');
        kelasCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Prevent click if clicking on buttons
                if (!e.target.closest('a') && !e.target.closest('button')) {
                    // Future functionality: view class details
                    console.log('View class details:', this);
                }
            });
        });
    </script>
</body>
</html>