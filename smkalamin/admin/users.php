<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Cek login admin
redirectIfNotLoggedIn('admin');

// --- LOGIKA HAPUS DATA ---
if (isset($_GET['hapus']) && isset($_GET['role'])) {
    $id = intval($_GET['hapus']); // Cast ke integer untuk keamanan
    $role = $_GET['role'];
    
    // Debug: Tampilkan informasi hapus
    error_log("DELETE ATTEMPT: Role=$role, ID=$id");
    
    if ($role == 'siswa') { 
        $table = 'siswa'; 
    } elseif ($role == 'guru') { 
        $table = 'guru'; 
    } elseif ($role == 'admin') { 
        $table = 'admin'; 
    }

    if (isset($table)) {
        // Cek koneksi database
        if (!$conn) {
            echo "<script>alert('Koneksi database gagal!'); window.location='users.php';</script>";
            exit();
        }
        
        // Cek apakah data ada sebelum dihapus
        $check_query = "SELECT * FROM $table WHERE id = '$id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) == 0) {
            echo "<script>alert('Data tidak ditemukan!'); window.location='users.php';</script>";
            exit();
        }
        
        $data = mysqli_fetch_assoc($check_result);
        $nama = isset($data['nama']) ? $data['nama'] : 
                (isset($data['nama_lengkap']) ? $data['nama_lengkap'] : 'User');
        
        // Hapus data
        $delete = mysqli_query($conn, "DELETE FROM $table WHERE id = '$id'");
        
        if ($delete) {
            // Log aktivitas hapus
            $user_id = $_SESSION['user_id'];
            $aksi = "Menghapus $role: $nama (ID: $id)";
            $log_query = "INSERT INTO aktivitas (user_id, aksi) VALUES ('$user_id', '$aksi')";
            mysqli_query($conn, $log_query);
            
            echo "<script>alert('Data berhasil dihapus!'); window.location='users.php';</script>";
        } else {
            $error_msg = mysqli_error($conn);
            error_log("DELETE ERROR: $error_msg");
            
            // Cek jika ada foreign key constraint
            if (strpos($error_msg, 'foreign key constraint') !== false) {
                echo "<script>alert('Gagal menghapus! Data ini terkait dengan data lain (mungkin ada absensi atau tugas).'); window.location='users.php';</script>";
            } else {
                echo "<script>alert('Gagal menghapus data! Error: " . addslashes($error_msg) . "'); window.location='users.php';</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Admin</title>
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
        
        /* Badge Styles */
        .badge {
            font-weight: 500;
            padding: 0.4em 0.8em;
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            padding: 0.7rem 1.8rem;
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
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            border: none;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, var(--danger) 100%);
            transform: translateY(-2px);
        }
        
        .btn-edit {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
            border: none;
            color: white;
        }
        
        .btn-edit:hover {
            background: linear-gradient(135deg, #d97706 0%, var(--warning) 100%);
            transform: translateY(-2px);
            color: white;
        }
        
        /* Tabs */
        .nav-tabs {
            border-bottom: 2px solid rgba(0,0,0,0.08);
        }
        
        .nav-tabs .nav-link {
            color: var(--dark);
            font-weight: 500;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px 8px 0 0;
            margin-bottom: -2px;
            transition: all 0.3s;
            background: transparent;
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--primary);
            background: rgba(37, 99, 235, 0.05);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary);
            background: white;
            border-bottom: 3px solid var(--primary);
            font-weight: 600;
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
            
            .nav-tabs .nav-link {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 0.8rem;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 1rem;
            }
            
            .d-flex.justify-content-between a {
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
            
            .nav-tabs .nav-link {
                padding: 0.5rem 0.8rem;
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
        
        /* Loading overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

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
                <a class="nav-link active" href="users.php"><i class="fas fa-users"></i> Manajemen User</a>
                <a class="nav-link" href="mapel.php"><i class="fas fa-book"></i> Mata Pelajaran</a>
                <a class="nav-link" href="kelas.php"><i class="fas fa-chalkboard"></i> Kelas</a>
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
                    <h5 class="mb-0 text-dark"><i class="fas fa-users me-2"></i>Manajemen User</h5>
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
        
        <!-- Header and Add Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-users me-2"></i>Manajemen User</h4>
            <a href="users_tambah.php" class="btn btn-primary px-4">
                <i class="fas fa-plus me-2"></i>Tambah User Baru
            </a>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="userTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="siswa-tab" data-bs-toggle="tab" data-bs-target="#siswa" type="button" role="tab">
                    <i class="fas fa-user-graduate me-2"></i>Data Siswa
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guru-tab" data-bs-toggle="tab" data-bs-target="#guru" type="button" role="tab">
                    <i class="fas fa-chalkboard-teacher me-2"></i>Data Guru
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab">
                    <i class="fas fa-user-shield me-2"></i>Data Admin
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="userTabsContent">
            
            <!-- Siswa Tab -->
            <div class="tab-pane fade show active" id="siswa" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>NIS</th>
                                        <th>Nama Lengkap</th>
                                        <th>Kelas</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $q_siswa = mysqli_query($conn, "SELECT siswa.*, kelas.nama as nama_kelas FROM siswa LEFT JOIN kelas ON siswa.kelas_id = kelas.id ORDER BY siswa.nama ASC");
                                    
                                    if(mysqli_num_rows($q_siswa) > 0):
                                        while($s = mysqli_fetch_assoc($q_siswa)):
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td>
                                            <span class="badge bg-secondary px-3 py-2"><?= $s['nis']; ?></span>
                                        </td>
                                        <td class="fw-bold"><?= $s['nama']; ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= $s['nama_kelas'] ?: '-'; ?></span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="users_edit.php?role=siswa&id=<?= $s['id']; ?>" class="btn btn-edit btn-sm" title="Edit">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm delete-btn" 
                                                        data-id="<?= $s['id']; ?>" 
                                                        data-role="siswa" 
                                                        data-nama="<?= htmlspecialchars($s['nama']); ?>"
                                                        title="Hapus">
                                                    <i class="fas fa-trash me-1"></i>Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="empty-state">
                                                <i class="fas fa-user-graduate text-muted"></i>
                                                <p class="mb-0">Belum ada data siswa</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guru Tab -->
            <div class="tab-pane fade" id="guru" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>NIP</th>
                                        <th>Nama Guru</th>
                                        <th>Mapel Diampu</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $q_guru = mysqli_query($conn, "SELECT * FROM guru ORDER BY nama_lengkap ASC");
                                    
                                    if(mysqli_num_rows($q_guru) > 0):
                                        while($g = mysqli_fetch_assoc($q_guru)):
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td>
                                            <span class="badge bg-info text-dark px-3 py-2"><?= $g['nip']; ?></span>
                                        </td>
                                        <td class="fw-bold"><?= $g['nama_lengkap']; ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= $g['mata_pelajaran']; ?></span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="users_edit.php?role=guru&id=<?= $g['id']; ?>" class="btn btn-edit btn-sm" title="Edit">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm delete-btn" 
                                                        data-id="<?= $g['id']; ?>" 
                                                        data-role="guru" 
                                                        data-nama="<?= htmlspecialchars($g['nama_lengkap']); ?>"
                                                        title="Hapus">
                                                    <i class="fas fa-trash me-1"></i>Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="empty-state">
                                                <i class="fas fa-chalkboard-teacher text-muted"></i>
                                                <p class="mb-0">Belum ada data guru</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Tab -->
            <div class="tab-pane fade" id="admin" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Username</th>
                                        <th>Nama Admin</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $q_admin = mysqli_query($conn, "SELECT * FROM admin ORDER BY nama ASC");
                                    
                                    if(mysqli_num_rows($q_admin) > 0):
                                        while($a = mysqli_fetch_assoc($q_admin)):
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td>
                                            <span class="badge bg-dark px-3 py-2"><?= $a['username']; ?></span>
                                        </td>
                                        <td class="fw-bold"><?= $a['nama']; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="users_edit.php?role=admin&id=<?= $a['id']; ?>" class="btn btn-edit btn-sm" title="Edit">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm delete-btn" 
                                                        data-id="<?= $a['id']; ?>" 
                                                        data-role="admin" 
                                                        data-nama="<?= htmlspecialchars($a['nama']); ?>"
                                                        title="Hapus">
                                                    <i class="fas fa-trash me-1"></i>Hapus
                                                </button>
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
                                                <i class="fas fa-user-shield text-muted"></i>
                                                <p class="mb-0">Belum ada data admin</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        
        <!-- Footer -->
        <footer class="mt-5 pt-4 border-top text-center text-muted">
            <p class="mb-1">© <?php echo date('Y'); ?> SMK Al Amin Cibening. All rights reserved.</p>
            <p class="small">Manajemen User v1.0</p>
        </footer>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus <span id="deleteUserName" class="fw-bold"></span>?</p>
                    <p class="text-danger small">
                        <i class="fas fa-info-circle me-1"></i>
                        Data yang dihapus tidak dapat dikembalikan.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Ya, Hapus
                    </a>
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
        const loadingOverlay = document.getElementById('loadingOverlay');
        
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
        
        // Tab click handler for mobile
        const tabLinks = document.querySelectorAll('.nav-tabs .nav-link');
        tabLinks.forEach(tab => {
            tab.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    // Scroll to top of tab content on mobile
                    document.getElementById('userTabsContent').scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Delete functionality
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const deleteUserName = document.getElementById('deleteUserName');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const role = this.getAttribute('data-role');
                const nama = this.getAttribute('data-nama');
                
                // Set modal content
                deleteUserName.textContent = nama;
                
                // Set delete URL
                const deleteUrl = `users.php?hapus=${id}&role=${role}`;
                confirmDeleteBtn.href = deleteUrl;
                
                // Show modal
                deleteModal.show();
            });
        });
        
        // Show loading when deleting
        confirmDeleteBtn.addEventListener('click', function(e) {
            loadingOverlay.classList.add('active');
        });
        
        // Hide loading if user cancels
        document.querySelector('#deleteModal .btn-close').addEventListener('click', () => {
            loadingOverlay.classList.remove('active');
        });
        
        document.querySelector('#deleteModal .btn-secondary').addEventListener('click', () => {
            loadingOverlay.classList.remove('active');
        });
        
        // Auto-hide loading after 5 seconds (safety)
        setTimeout(() => {
            loadingOverlay.classList.remove('active');
        }, 5000);
    </script>
</body>
</html>