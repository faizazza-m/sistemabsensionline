<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    redirectBasedOnRole();
    exit();
}

$error = '';
$identifier = '';
$role = 'siswa'; // Default

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (empty($identifier) || empty($password)) {
        $error = 'Harap isi semua kolom!';
    } else {
        $result = login($identifier, $password, $role);
        
        if ($result['success']) {
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['role'] = $role;
            $_SESSION['nama'] = $result['nama'];
            
            redirectBasedOnRole();
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMK Al Amin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #2c3e50; --secondary: #3498db; }
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            font-family: 'Segoe UI', sans-serif; 
        }
        .login-card { 
            background: white; 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.2); 
            overflow: hidden; 
            max-width: 400px; 
            width: 100%; 
            margin: 0 auto; 
        }
        .login-header { 
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); 
            color: white; 
            padding: 30px; 
            text-align: center; 
        }
        .school-logo { 
            width: 80px; 
            height: 80px; 
            background: white; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0 auto 15px; 
            color: var(--primary); 
            font-size: 40px; 
        }
        .login-body { padding: 30px; }
        .role-selector { display: flex; gap: 10px; margin-bottom: 25px; }
        .role-btn { 
            flex: 1; 
            padding: 10px; 
            border: 2px solid #eee; 
            border-radius: 10px; 
            text-align: center; 
            cursor: pointer; 
            transition: 0.3s; 
        }
        .role-btn:hover, .role-btn.active { 
            border-color: var(--secondary); 
            background: #e3f2fd; 
            color: var(--secondary); 
        }
        .btn-login { 
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); 
            color: white; 
            border: none; 
            padding: 12px; 
            border-radius: 10px; 
            width: 100%; 
            font-weight: bold; 
            transition: 0.3s;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3); }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .back-link:hover { color: white; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="login-header">
                <div class="school-logo"><i class="fas fa-graduation-cap"></i></div>
                <h4 class="mb-0">SMK Al Amin</h4>
                <small>Sistem E-Learning</small>
            </div>
            <div class="login-body">
                <?php if($error): ?>
                    <div class="alert alert-danger py-2"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="role" id="role" value="siswa">
                    
                    <label class="form-label fw-bold">Login Sebagai:</label>
                    <div class="role-selector">
                        <div class="role-btn active" data-role="siswa" onclick="selectRole('siswa')">
                            <i class="fas fa-user-graduate d-block mb-1"></i> Siswa
                        </div>
                        <div class="role-btn" data-role="guru" onclick="selectRole('guru')">
                            <i class="fas fa-chalkboard-teacher d-block mb-1"></i> Guru
                        </div>
                        <div class="role-btn" data-role="admin" onclick="selectRole('admin')">
                            <i class="fas fa-user-shield d-block mb-1"></i> Admin
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" id="labelID">NIS</label>
                        <input type="text" name="identifier" class="form-control" placeholder="Masukkan NIS" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
                    </div>

                    <button type="submit" class="btn-login">LOGIN</button>
                </form>
            </div>
        </div>

        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Utama
        </a>

    </div>

    <script>
        function selectRole(role) {
            document.getElementById('role').value = role;
            document.querySelectorAll('.role-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`[data-role="${role}"]`).classList.add('active');
            
            const label = document.getElementById('labelID');
            const input = document.querySelector('input[name="identifier"]');
            
            if(role === 'siswa') { label.innerText = 'NIS'; input.placeholder = 'Contoh: 10111'; }
            else if(role === 'guru') { label.innerText = 'NIP'; input.placeholder = 'Contoh: 198001...'; }
            else { label.innerText = 'Username'; input.placeholder = 'Contoh: admin'; }
        }
    </script>
</body>
</html>