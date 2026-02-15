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
    <title>Nilai Saya</title>
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
        
        /* Nilai Card */
        .nilai-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: none;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .nilai-header {
            background: linear-gradient(135deg, var(--primary) 0%, #3a56d4 100%);
            color: white;
            padding: 20px;
        }
        
        .nilai-body {
            padding: 25px;
        }
        
        .badge-nilai {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        /* Stats Card */
        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .stats-card h6 {
            color: var(--gray);
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .stats-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
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
        
        /* Grade Colors */
        .grade-a {
            background: rgba(76, 201, 240, 0.1);
            color: #4cc9f0;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }
        
        .grade-b {
            background: rgba(67, 97, 238, 0.1);
            color: #4361ee;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }
        
        .grade-c {
            background: rgba(248, 150, 30, 0.1);
            color: #f8961e;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }
        
        .grade-d {
            background: rgba(247, 37, 133, 0.1);
            color: #f72585;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
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
            
            .nilai-body {
                padding: 15px;
            }
            
            .stats-card {
                padding: 15px;
            }
            
            .stats-card .value {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px 10px;
            }
            
            .navbar-custom {
                padding: 12px 15px;
            }
            
            .custom-table {
                display: block;
                overflow-x: auto;
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
                <a class="nav-link" href="tugas.php">
                    <i class="fas fa-tasks"></i>
                    <span>Tugas</span>
                </a>
                <a class="nav-link" href="absensi.php">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Riwayat Absen</span>
                </a>
                <a class="nav-link active" href="nilai.php">
                    <i class="fas fa-star"></i>
                    <span>Lihat Nilai</span>
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
                    <i class="fas fa-star text-primary fs-4 me-3"></i>
                    <div>
                        <h5 class="page-title mb-0">Nilai Akademik</h5>
                        <small class="text-muted">Rekap nilai tugas, UTS, dan UAS</small>
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
        
        <!-- Nilai Content -->
        <div class="nilai-card">
            <div class="nilai-header">
                <h5 class="mb-2"><i class="fas fa-star me-2"></i>Daftar Nilai Akademik</h5>
                <span class="badge-nilai">Total: 
                    <?php 
                    $count_query = "SELECT COUNT(*) as total FROM nilai WHERE siswa_id = '$id_siswa'";
                    $count_result = mysqli_query($conn, $count_query);
                    $count_data = mysqli_fetch_assoc($count_result);
                    echo $count_data['total'];
                    ?> Mata Pelajaran
                </span>
            </div>
            
            <div class="nilai-body">
                <?php
                // Hitung statistik
                $total_nilai = 0;
                $count_nilai = 0;
                $total_mapel = 0;
                
                $q_stats = "SELECT nilai_tugas, nilai_uts, nilai_uas FROM nilai WHERE siswa_id = '$id_siswa'";
                $res_stats = mysqli_query($conn, $q_stats);
                
                if($res_stats && mysqli_num_rows($res_stats) > 0) {
                    while($row = mysqli_fetch_assoc($res_stats)) {
                        $avg = ($row['nilai_tugas'] + $row['nilai_uts'] + $row['nilai_uas']) / 3;
                        $total_nilai += $avg;
                        $count_nilai++;
                        $total_mapel++;
                    }
                    
                    $rata_rata = $count_nilai > 0 ? $total_nilai / $count_nilai : 0;
                    ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="stats-card text-center">
                                <h6>Rata-rata Nilai</h6>
                                <div class="value"><?= number_format($rata_rata, 2); ?></div>
                                <small class="text-muted">Skala 0-100</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stats-card text-center">
                                <h6>Total Mata Pelajaran</h6>
                                <div class="value"><?= $total_mapel; ?></div>
                                <small class="text-muted">Yang sudah dinilai</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stats-card text-center">
                                <h6>Status Akademik</h6>
                                <?php
                                $status_class = 'grade-c';
                                $status_text = 'Cukup';
                                
                                if ($rata_rata >= 85) {
                                    $status_class = 'grade-a';
                                    $status_text = 'Sangat Baik';
                                } elseif ($rata_rata >= 75) {
                                    $status_class = 'grade-b';
                                    $status_text = 'Baik';
                                } elseif ($rata_rata >= 65) {
                                    $status_class = 'grade-c';
                                    $status_text = 'Cukup';
                                } else {
                                    $status_class = 'grade-d';
                                    $status_text = 'Perlu Perbaikan';
                                }
                                ?>
                                <div class="value <?= $status_class; ?> mt-2"><?= $status_text; ?></div>
                                <small class="text-muted">Berdasarkan rata-rata</small>
                            </div>
                        </div>
                    </div>
                    
                <?php } ?>
                
                <div class="table-responsive">
                    <table class="table custom-table">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengajar</th>
                                <th class="text-center">Tugas</th>
                                <th class="text-center">UTS</th>
                                <th class="text-center">UAS</th>
                                <th class="text-center">Rata-rata</th>
                                <th class="text-center">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q_simpel = "SELECT n.*, g.nama_lengkap as guru, g.mata_pelajaran as mapel 
                                         FROM nilai n JOIN guru g ON n.guru_id = g.id WHERE n.siswa_id = '$id_siswa'";

                            $res = mysqli_query($conn, $q_simpel);
                            
                            if($res && mysqli_num_rows($res) > 0) {
                                while($row = mysqli_fetch_assoc($res)):
                                    $avg = ($row['nilai_tugas'] + $row['nilai_uts'] + $row['nilai_uas']) / 3;
                                    
                                    // Determine grade
                                    $grade_class = '';
                                    $grade_text = '';
                                    
                                    if ($avg >= 85) {
                                        $grade_class = 'grade-a';
                                        $grade_text = 'A';
                                    } elseif ($avg >= 75) {
                                        $grade_class = 'grade-b';
                                        $grade_text = 'B';
                                    } elseif ($avg >= 65) {
                                        $grade_class = 'grade-c';
                                        $grade_text = 'C';
                                    } elseif ($avg >= 55) {
                                        $grade_class = 'grade-c';
                                        $grade_text = 'D';
                                    } else {
                                        $grade_class = 'grade-d';
                                        $grade_text = 'E';
                                    }
                            ?>
                            <tr>
                                <td class="fw-bold"><?= $no++; ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['mapel']); ?></div>
                                    <small class="text-muted">Kode: NIL<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['guru']); ?></td>
                                <td class="text-center">
                                    <span class="fw-bold <?= $row['nilai_tugas'] >= 75 ? 'text-success' : 'text-warning'; ?>">
                                        <?= $row['nilai_tugas']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold <?= $row['nilai_uts'] >= 75 ? 'text-success' : 'text-warning'; ?>">
                                        <?= $row['nilai_uts']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold <?= $row['nilai_uas'] >= 75 ? 'text-success' : 'text-warning'; ?>">
                                        <?= $row['nilai_uas']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-primary">
                                        <?= number_format($avg, 2); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="<?= $grade_class; ?>">
                                        <?= $grade_text; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; } else { ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="fas fa-chart-line"></i>
                                        <h5 class="mb-2">Belum Ada Data Nilai</h5>
                                        <p class="text-muted">Data nilai akan ditampilkan di sini setelah guru memberikan nilai.</p>
                                        <small class="text-muted">Silakan cek kembali nanti.</small>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if(mysqli_num_rows($res) > 0): ?>
                <div class="mt-4 p-3 bg-light bg-opacity-25 rounded">
                    <h6 class="mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Keterangan Nilai:</h6>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <span class="grade-a me-2">A</span> <small class="text-muted">85-100 (Sangat Baik)</small>
                        </div>
                        <div class="col-md-3 mb-2">
                            <span class="grade-b me-2">B</span> <small class="text-muted">75-84 (Baik)</small>
                        </div>
                        <div class="col-md-3 mb-2">
                            <span class="grade-c me-2">C</span> <small class="text-muted">65-74 (Cukup)</small>
                        </div>
                        <div class="col-md-3 mb-2">
                            <span class="grade-d me-2">D/E</span> <small class="text-muted">≤64 (Perlu Perbaikan)</small>
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
            
            // Highlight best and worst scores
            const scoreCells = document.querySelectorAll('td .text-success');
            scoreCells.forEach(function(cell) {
                if (parseFloat(cell.textContent) >= 90) {
                    cell.innerHTML = '<i class="fas fa-trophy me-1"></i>' + cell.textContent;
                }
            });
        });
    </script>
</body>
</html>