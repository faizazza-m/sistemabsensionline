<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn('siswa');
$id_siswa = $_SESSION['user_id'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
    
    // Validasi input
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        $error = 'Semua kolom harus diisi!';
    } elseif (strlen($password_baru) < 6) {
        $error = 'Password baru minimal 6 karakter!';
    } elseif ($password_baru !== $konfirmasi_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        // Ambil password saat ini dari database
        $stmt = $conn->prepare("SELECT password FROM siswa WHERE id = ?");
        $stmt->bind_param("i", $id_siswa);
        $stmt->execute();
        $result = $stmt->get_result();
        $siswa = $result->fetch_assoc();
        
        if ($siswa) {
            $current_password_db = $siswa['password'];
            $is_valid = false;
            
            // Cek password lama (support hash dan plain text untuk legacy)
            if (strlen($current_password_db) > 50) {
                $is_valid = password_verify($password_lama, $current_password_db);
            } else {
                $is_valid = ($password_lama == $current_password_db);
            }
            
            if ($is_valid) {
                // Hash password baru dan update
                $new_password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE siswa SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_password_hash, $id_siswa);
                
                if ($update_stmt->execute()) {
                    $success = 'Password berhasil diubah!';
                } else {
                    $error = 'Gagal mengubah password. Silakan coba lagi.';
                }
                $update_stmt->close();
            } else {
                $error = 'Password lama tidak benar!';
            }
        } else {
            $error = 'Data siswa tidak ditemukan!';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password - Dashboard Siswa</title>
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
        
        /* Password Card */
        .password-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 30px;
            max-width: 500px;
            margin: 0 auto;
            transition: var(--transition);
        }
        
        .password-card:hover {
            box-shadow: var(--shadow-hover);
        }
        
        .password-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .password-icon i {
            font-size: 2rem;
            color: white;
        }
        
        /* Form Styles */
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            display: block;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-right: none;
            padding: 12px 15px;
        }
        
        .form-control {
            border: 1px solid #e9ecef;
            border-left: none;
            padding: 12px 15px;
            border-radius: 0 8px 8px 0;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .toggle-password {
            cursor: pointer;
            color: var(--gray);
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-left: none;
            padding: 12px 15px;
            border-radius: 0 8px 8px 0;
        }
        
        .toggle-password:hover {
            background: #e9ecef;
        }
        
        /* Button Styles */
        .btn-password {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 600;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .btn-password:hover {
            background: var(--primary-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.2);
        }
        
        .btn-back {
            background: transparent;
            color: var(--gray);
            border: 1px solid #e9ecef;
            padding: 12px 25px;
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 500;
            width: 100%;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .btn-back:hover {
            background: #f8f9fa;
            color: var(--dark);
        }
        
        /* Alert Styles */
        .alert-custom {
            border-radius: var(--border-radius);
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }
        
        .alert-success-custom {
            background: rgba(76, 201, 240, 0.1);
            color: #4cc9f0;
            border-left: 4px solid #4cc9f0;
        }
        
        .alert-danger-custom {
            background: rgba(247, 37, 133, 0.1);
            color: #f72585;
            border-left: 4px solid #f72585;
        }
        
        .alert-custom i {
            font-size: 1.2rem;
            margin-right: 10px;
        }
        
        /* Password Strength */
        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
        }
        
        .strength-weak {
            color: var(--danger);
        }
        
        .strength-medium {
            color: var(--warning);
        }
        
        .strength-strong {
            color: var(--success);
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
            .password-card {
                padding: 20px;
            }
            
            .password-icon {
                width: 70px;
                height: 70px;
            }
            
            .password-icon i {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px 10px;
            }
            
            .navbar-custom {
                padding: 12px 15px;
            }
            
            .btn-password, .btn-back {
                padding: 10px 20px;
                font-size: 0.9rem;
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
                <a class="nav-link" href="absensi.php">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Riwayat Absen</span>
                </a>
                <a class="nav-link" href="nilai.php">
                    <i class="fas fa-star"></i>
                    <span>Lihat Nilai</span>
                </a>
                <a class="nav-link active" href="ganti_password.php">
                    <i class="fas fa-key"></i>
                    <span>Ganti Password</span>
                </a>
            </nav>
        </div>
        
        <div class="sidebar-footer">
            <a class="nav-link text-white bg-danger bg-opacity-25 rounded py-2 text-center" href="logout.php">
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
                    <i class="fas fa-key text-primary fs-4 me-3"></i>
                    <div>
                        <h5 class="page-title mb-0">Ganti Password</h5>
                        <small class="text-muted">Perbarui kata sandi akun Anda</small>
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
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Password Form -->
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="password-card">
                    <div class="password-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    
                    <h4 class="text-center mb-4">Ganti Password</h4>
                    <p class="text-center text-muted mb-4">
                        Pastikan password baru Anda kuat dan mudah diingat
                    </p>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success-custom alert-custom">
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo $success; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger-custom alert-custom">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo $error; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="passwordForm">
                        <!-- Password Lama -->
                        <div class="mb-4">
                            <label class="form-label">Password Lama</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       name="password_lama" 
                                       id="password_lama" 
                                       placeholder="Masukkan password lama" 
                                       required>
                                <span class="toggle-password" onclick="togglePassword('password_lama', 'toggle_password_lama')">
                                    <i class="fas fa-eye" id="toggle_password_lama"></i>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Password Baru -->
                        <div class="mb-4">
                            <label class="form-label">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       name="password_baru" 
                                       id="password_baru" 
                                       placeholder="Minimal 6 karakter" 
                                       required 
                                       minlength="6"
                                       oninput="checkPasswordStrength(this.value)">
                                <span class="toggle-password" onclick="togglePassword('password_baru', 'toggle_password_baru')">
                                    <i class="fas fa-eye" id="toggle_password_baru"></i>
                                </span>
                            </div>
                            <div id="passwordStrength" class="password-strength"></div>
                        </div>
                        
                        <!-- Konfirmasi Password -->
                        <div class="mb-4">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-check-double"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       name="konfirmasi_password" 
                                       id="konfirmasi_password" 
                                       placeholder="Ulangi password baru" 
                                       required
                                       oninput="checkPasswordMatch()">
                                <span class="toggle-password" onclick="togglePassword('konfirmasi_password', 'toggle_konfirmasi_password')">
                                    <i class="fas fa-eye" id="toggle_konfirmasi_password"></i>
                                </span>
                            </div>
                            <div id="passwordMatch" class="password-strength"></div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-grid gap-3">
                            <button type="submit" class="btn-password">
                                <i class="fas fa-save me-2"></i>Simpan Password Baru
                            </button>
                            <a href="index.php" class="btn-back">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                            </a>
                        </div>
                    </form>
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
        });

        // Toggle password visibility
        function togglePassword(inputId, toggleId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(toggleId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Check password strength
        function checkPasswordStrength(password) {
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            let strength = 0;
            let feedback = '';
            let strengthClass = '';
            
            // Length check
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Complexity checks
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Determine strength level
            if (strength <= 2) {
                feedback = 'Lemah';
                strengthClass = 'strength-weak';
            } else if (strength <= 4) {
                feedback = 'Sedang';
                strengthClass = 'strength-medium';
            } else {
                feedback = 'Kuat';
                strengthClass = 'strength-strong';
            }
            
            strengthDiv.innerHTML = `<span class="${strengthClass}">Kekuatan: ${feedback}</span>`;
            
            // Check password match
            checkPasswordMatch();
        }

        // Check password match
        function checkPasswordMatch() {
            const password = document.getElementById('password_baru').value;
            const confirm = document.getElementById('konfirmasi_password').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirm.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirm) {
                matchDiv.innerHTML = '<span class="strength-strong"><i class="fas fa-check-circle me-1"></i>Password cocok</span>';
            } else {
                matchDiv.innerHTML = '<span class="strength-weak"><i class="fas fa-times-circle me-1"></i>Password tidak cocok</span>';
            }
        }

        // Form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password_baru').value;
            const confirm = document.getElementById('konfirmasi_password').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok!');
                document.getElementById('konfirmasi_password').focus();
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password baru minimal 6 karakter!');
                document.getElementById('password_baru').focus();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>