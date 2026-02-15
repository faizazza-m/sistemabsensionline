<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn('guru');
$id_guru = $_SESSION['user_id'];

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
        $stmt = $conn->prepare("SELECT password FROM guru WHERE id = ?");
        $stmt->bind_param("i", $id_guru);
        $stmt->execute();
        $result = $stmt->get_result();
        $guru = $result->fetch_assoc();
        
        if ($guru) {
            $current_password_db = $guru['password'];
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
                $update_stmt = $conn->prepare("UPDATE guru SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_password_hash, $id_guru);
                
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
            $error = 'Data guru tidak ditemukan!';
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
    <title>Ganti Password - Dashboard Guru</title>
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
        
        /* Password Card */
        .password-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.08);
        }
        
        .password-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .password-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            font-size: 2.5rem;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
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
        
        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .input-group {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }
        
        .input-group:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        
        .input-group-text {
            background: #f8fafc;
            border: none;
            color: #64748b;
            padding: 0.75rem 1rem;
        }
        
        .form-control {
            border: none;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            background: white;
        }
        
        .form-control:focus {
            box-shadow: none;
            background: white;
        }
        
        .toggle-password {
            cursor: pointer;
            background: #f8fafc;
            border: none;
            color: #64748b;
            transition: color 0.2s;
        }
        
        .toggle-password:hover {
            color: var(--primary);
        }
        
        /* Password Strength */
        .password-strength {
            margin-top: 0.5rem;
        }
        
        .strength-meter {
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 0.25rem;
        }
        
        .strength-fill {
            height: 100%;
            width: 0;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        /* Alert Styles */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1.2rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            color: var(--danger);
            border-left: 4px solid var(--danger);
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
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 0.8rem;
            }
            
            .card-header {
                padding: 1rem 1.2rem;
            }
            
            .password-card {
                padding: 1.5rem;
            }
            
            .password-icon {
                width: 70px;
                height: 70px;
                font-size: 2rem;
            }
            
            .btn-admin, .btn-outline-admin {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
            }
            
            .password-card {
                padding: 1.25rem;
            }
            
            .alert {
                padding: 1rem;
            }
            
            .btn-admin, .btn-outline-admin {
                width: 100%;
                margin-bottom: 0.5rem;
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
        
        /* Validation Styles */
        .is-invalid {
            border-color: var(--danger) !important;
        }
        
        .is-valid {
            border-color: var(--success) !important;
        }
        
        .invalid-feedback {
            color: var(--danger);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .form-text {
            color: #64748b;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        /* Security Tips */
        .security-tips {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.05) 0%, rgba(37, 99, 235, 0.02) 100%);
            border-left: 4px solid var(--primary);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
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
                <a class="nav-link" href="absensi_saya.php">
                    <i class="fas fa-fingerprint"></i> Absensi Saya
                </a>
                <div class="mt-auto pt-3">
                    <a class="nav-link active" href="ganti_password.php">
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
                        <i class="fas fa-key me-2"></i>
                        Keamanan Akun
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
        <div class="fade-in">
            <div class="password-card">
                <div class="password-header">
                    <div class="password-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3 class="text-primary fw-bold mb-2">Ganti Password</h3>
                    <p class="text-muted mb-0">Lindungi akun Anda dengan password yang kuat dan aman</p>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success fade-in" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle fa-lg me-3"></i>
                            <div>
                                <h6 class="mb-1 fw-bold">Password Berhasil Diubah!</h6>
                                <p class="mb-0"><?php echo htmlspecialchars($success); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger fade-in" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle fa-lg me-3"></i>
                            <div>
                                <h6 class="mb-1 fw-bold">Terjadi Kesalahan</h6>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="passwordForm" class="needs-validation" novalidate>
                    <div class="form-group">
                        <label for="password_lama" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password Lama
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   name="password_lama" 
                                   id="password_lama" 
                                   placeholder="Masukkan password lama Anda" 
                                   required>
                            <button type="button" class="input-group-text toggle-password" onclick="togglePassword('password_lama', 'toggle_password_lama')">
                                <i class="fas fa-eye" id="toggle_password_lama"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Password lama wajib diisi</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_baru" class="form-label">
                            <i class="fas fa-key me-2"></i>Password Baru
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   name="password_baru" 
                                   id="password_baru" 
                                   placeholder="Masukkan password baru minimal 6 karakter" 
                                   required 
                                   minlength="6"
                                   oninput="checkPasswordStrength(this.value)">
                            <button type="button" class="input-group-text toggle-password" onclick="togglePassword('password_baru', 'toggle_password_baru')">
                                <i class="fas fa-eye" id="toggle_password_baru"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <small class="text-muted" id="password-strength-text">Kekuatan password: lemah</small>
                            <div class="strength-meter">
                                <div class="strength-fill" id="password-strength-bar"></div>
                            </div>
                        </div>
                        <div class="invalid-feedback">Password baru minimal 6 karakter</div>
                        <div class="form-text">Gunakan kombinasi huruf, angka, dan simbol untuk password yang lebih aman</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="konfirmasi_password" class="form-label">
                            <i class="fas fa-check-double me-2"></i>Konfirmasi Password Baru
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   name="konfirmasi_password" 
                                   id="konfirmasi_password" 
                                   placeholder="Ketik ulang password baru" 
                                   required
                                   oninput="checkPasswordMatch()">
                            <button type="button" class="input-group-text toggle-password" onclick="togglePassword('konfirmasi_password', 'toggle_konfirmasi_password')">
                                <i class="fas fa-eye" id="toggle_konfirmasi_password"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Konfirmasi password wajib diisi</div>
                        <div class="form-text" id="password-match-feedback"></div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-admin btn-lg">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                        <a href="index.php" class="btn btn-outline-admin">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </form>
                
                <!-- Security Tips -->
                <div class="security-tips mt-4">
                    <h6 class="fw-bold mb-3 text-primary">
                        <i class="fas fa-shield-alt me-2"></i>Tips Keamanan Password
                    </h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Gunakan minimal 8 karakter
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Kombinasikan huruf besar, kecil, angka, dan simbol
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Hindari penggunaan informasi pribadi
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Jangan gunakan password yang sama untuk akun lain
                        </li>
                    </ul>
                </div>
                
                <!-- Footer -->
                <div class="mt-4 pt-3 border-top text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Sistem keamanan menggunakan enkripsi modern untuk melindungi data Anda
                    </small>
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
        
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
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
            let strength = 0;
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            
            // Length check
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Character variety checks
            if (/[a-z]/.test(password)) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
            
            // Update strength bar
            let width = 0;
            let color = '#ef4444';
            let text = 'Sangat Lemah';
            
            if (strength >= 6) {
                width = 100;
                color = '#10b981';
                text = 'Sangat Kuat';
            } else if (strength >= 5) {
                width = 80;
                color = '#10b981';
                text = 'Kuat';
            } else if (strength >= 4) {
                width = 60;
                color = '#f59e0b';
                text = 'Cukup';
            } else if (strength >= 3) {
                width = 40;
                color = '#f59e0b';
                text = 'Lemah';
            } else if (strength >= 2) {
                width = 20;
                color = '#ef4444';
                text = 'Sangat Lemah';
            }
            
            strengthBar.style.width = width + '%';
            strengthBar.style.backgroundColor = color;
            strengthBar.style.boxShadow = `0 2px 8px ${color}40`;
            strengthText.textContent = 'Kekuatan password: ' + text;
            strengthText.style.color = color;
            
            // Check password match
            checkPasswordMatch();
        }
        
        // Check password match
        function checkPasswordMatch() {
            const password = document.getElementById('password_baru').value;
            const confirm = document.getElementById('konfirmasi_password').value;
            const feedback = document.getElementById('password-match-feedback');
            
            if (confirm.length === 0) {
                feedback.textContent = '';
                feedback.style.color = '';
                return;
            }
            
            if (password === confirm) {
                feedback.innerHTML = '<i class="fas fa-check-circle me-1"></i> Password cocok';
                feedback.style.color = '#10b981';
            } else {
                feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i> Password tidak cocok';
                feedback.style.color = '#ef4444';
            }
        }
        
        // Form validation
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    const password = document.getElementById('password_baru').value;
                    const confirm = document.getElementById('konfirmasi_password').value;
                    
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else if (password !== confirm) {
                        event.preventDefault();
                        event.stopPropagation();
                        const feedback = document.getElementById('password-match-feedback');
                        feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i> Password baru dan konfirmasi password tidak cocok!';
                        feedback.style.color = '#ef4444';
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        })();
        
        // Add hover effects to buttons
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.btn-admin, .btn-outline-admin');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                    this.style.boxShadow = '0 6px 15px rgba(37, 99, 235, 0.3)';
                });
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 4px 10px rgba(37, 99, 235, 0.2)';
                });
            });
        });
    </script>
</body>
</html>