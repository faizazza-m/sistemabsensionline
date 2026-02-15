<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn('siswa');

$id_siswa = $_SESSION['user_id'];
$tgl_hari_ini = date('Y-m-d');

// --- PENGATURAN LOKASI SEKOLAH ---
$latitude_sekolah  = -6.6346317; 
$longitude_sekolah = 106.6719455; 
$radius_diizinkan  = 200; // Meter untuk status "Hadir"

// Ambil data kelas
$q_siswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT kelas_id FROM siswa WHERE id='$id_siswa'"));
$kelas_id = $q_siswa['kelas_id'];

// Cek Sesi Absensi
$q_sesi = mysqli_query($conn, "SELECT s.*, m.nama as mapel_nama, g.nama_lengkap as nama_guru
                               FROM sesi_absensi s
                               JOIN mata_pelajaran m ON s.mapel_id = m.id
                               JOIN guru g ON s.guru_id = g.id
                               WHERE s.kelas_id='$kelas_id' AND s.tanggal='$tgl_hari_ini' AND s.status='buka'");

// PROSES SIMPAN
if (isset($_POST['kirim_absen'])) {
    $mapel_id = $_POST['mapel_id'];
    $status_kehadiran = $_POST['status_kehadiran']; // Hadir, Sakit, Izin
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    
    // Validasi Geolocation Wajib Ada
    if (empty($lat) || empty($lng)) {
        echo "<script>alert('Gagal mendeteksi lokasi! Pastikan GPS aktif.'); window.location='absensi_input.php';</script>";
        exit;
    }

    // Hitung Jarak
    function distance($lat1, $lon1, $lat2, $lon2) {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        return ($dist * 60 * 1.1515 * 1.609344) * 1000;
    }
    $jarak = distance($lat, $lng, $latitude_sekolah, $longitude_sekolah);

    // --- LOGIKA VALIDASI STATUS ---
    $boleh_absen = false;
    $pesan_error = "";

    if ($status_kehadiran == 'Hadir') {
        // Jika HADIR, Wajib dalam radius
        if ($jarak <= $radius_diizinkan) {
            $boleh_absen = true;
        } else {
            $boleh_absen = false;
            $pesan_error = "Untuk status HADIR, Anda harus berada di sekolah! Jarak Anda: " . round($jarak) . "m.";
        }
    } else {
        // Jika SAKIT atau IZIN, Bebas radius (Boleh dari rumah)
        $boleh_absen = true;
    }

    // Eksekusi Simpan
    if ($boleh_absen) {
        // Cek Duplikat
        $cek = mysqli_query($conn, "SELECT * FROM absensi_siswa WHERE siswa_id='$id_siswa' AND mapel_id='$mapel_id' AND tanggal LIKE '$tgl_hari_ini%'");
        
        if (mysqli_num_rows($cek) == 0) {
            $q = "INSERT INTO absensi_siswa (siswa_id, mapel_id, tanggal, status, lat, lng) 
                  VALUES ('$id_siswa', '$mapel_id', NOW(), '$status_kehadiran', '$lat', '$lng')";
            
            if(mysqli_query($conn, $q)){
                echo "<script>alert('Absensi Berhasil ($status_kehadiran)!'); window.location='absensi.php';</script>";
            }
        } else {
            echo "<script>alert('Anda sudah melakukan absensi untuk mapel ini!');</script>";
        }
    } else {
        echo "<script>alert('$pesan_error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
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
        
        /* Map Styles */
        #map {
            height: 300px;
            width: 100%;
            border-radius: var(--border-radius);
            border: 2px solid #e9ecef;
            margin: 20px 0;
            z-index: 1;
        }
        
        .map-container {
            position: relative;
            margin-bottom: 25px;
        }
        
        .map-info {
            position: absolute;
            top: 10px;
            right: 10px;
            background: white;
            padding: 10px 15px;
            border-radius: 6px;
            box-shadow: var(--shadow);
            z-index: 1000;
            font-size: 0.85rem;
        }
        
        .map-info .info-circle {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .info-blue { background-color: #4361ee; }
        .info-red { background-color: #f72585; }
        
        /* Form Styles */
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            display: block;
        }
        
        .form-select, .form-control {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: var(--transition);
        }
        
        .form-select:focus, .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        /* Status Info */
        .status-info {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .status-hadir {
            background: rgba(67, 97, 238, 0.1);
            border-left: 4px solid var(--primary);
        }
        
        .status-izin {
            background: rgba(114, 9, 183, 0.1);
            border-left: 4px solid var(--secondary);
        }
        
        .status-sakit {
            background: rgba(248, 150, 30, 0.1);
            border-left: 4px solid var(--warning);
        }
        
        /* Button Styles */
        .btn-lokasi {
            background: var(--warning);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 600;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .btn-lokasi:hover {
            background: #e68a00;
            color: white;
        }
        
        .btn-lokasi:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }
        
        .btn-kirim {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 600;
            width: 100%;
        }
        
        .btn-kirim:hover {
            background: var(--primary-dark);
            color: white;
        }
        
        .btn-kirim:disabled {
            background: rgba(67, 97, 238, 0.5);
            cursor: not-allowed;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 3.5rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
        
        /* Alert Styles */
        .alert-custom {
            border-radius: var(--border-radius);
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        
        .alert-success-custom {
            background: rgba(76, 201, 240, 0.1);
            color: #4cc9f0;
            border-left: 4px solid #4cc9f0;
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
            .absensi-body {
                padding: 15px;
            }
            
            #map {
                height: 250px;
            }
            
            .map-info {
                position: relative;
                top: 0;
                right: 0;
                margin-bottom: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px 10px;
            }
            
            .navbar-custom {
                padding: 12px 15px;
            }
            
            .btn-lokasi, .btn-kirim {
                padding: 12px 15px;
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
                <a class="nav-link" href="absensi.php">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Riwayat Absen</span>
                </a>
                <a class="nav-link active" href="absensi_input.php">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Absen Sekarang</span>
                </a>
                <a class="nav-link" href="materi.php">
                    <i class="fas fa-book-reader"></i>
                    <span>Materi</span>
                </a>
                <a class="nav-link" href="tugas.php">
                    <i class="fas fa-tasks"></i>
                    <span>Tugas</span>
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
                    <i class="fas fa-map-marker-alt text-primary fs-4 me-3"></i>
                    <div>
                        <h5 class="page-title mb-0">Input Absensi</h5>
                        <small class="text-muted">Absen dengan lokasi GPS untuk hari ini</small>
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
                <h5 class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Form Absensi</h5>
                <small class="opacity-75">Tanggal: <?= date('d F Y'); ?></small>
            </div>
            
            <div class="absensi-body">
                
                <?php if(mysqli_num_rows($q_sesi) > 0): ?>
                    <div class="alert alert-success-custom alert-custom">
                        <i class="fas fa-check-circle me-2"></i> 
                        <strong>Sesi absensi tersedia!</strong> Silakan isi form di bawah ini untuk melakukan absensi.
                    </div>
                    
                    <form method="POST" id="formAbsen">
                        <!-- Mata Pelajaran -->
                        <div class="mb-4">
                            <label class="form-label">Mata Pelajaran</label>
                            <select name="mapel_id" class="form-select" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <?php while($sesi = mysqli_fetch_assoc($q_sesi)): ?>
                                    <option value="<?= $sesi['mapel_id'] ?>">
                                        <?= htmlspecialchars($sesi['mapel_nama']) ?> 
                                        <small>(Guru: <?= htmlspecialchars($sesi['nama_guru']) ?>)</small>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Status Kehadiran -->
                        <div class="mb-4">
                            <label class="form-label">Status Kehadiran</label>
                            <select name="status_kehadiran" id="status_kehadiran" class="form-select" required onchange="cekStatus()">
                                <option value="Hadir">Hadir (Wajib di Sekolah)</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                            </select>
                            <div id="infoLokasi" class="status-hadir status-info mt-2">
                                <i class="fas fa-info-circle me-2"></i>
                                Status <strong>Hadir</strong> wajib berada dalam radius <strong><?= $radius_diizinkan ?> meter</strong> dari sekolah.
                            </div>
                        </div>

                        <!-- Map Section -->
                        <div class="map-container">
                            <div id="map"></div>
                            <div class="map-info">
                                <div class="mb-1">
                                    <span class="info-circle info-blue"></span> 
                                    <span class="text-muted">Area Sekolah</span>
                                </div>
                                <div>
                                    <span class="info-circle info-red"></span> 
                                    <span class="text-muted">Lokasi Anda</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden Inputs for Coordinates -->
                        <input type="hidden" name="lat" id="lat">
                        <input type="hidden" name="lng" id="lng">
                        
                        <!-- Action Buttons -->
                        <div class="d-grid gap-3">
                            <button type="button" onclick="getLocation()" class="btn-lokasi" id="btnLokasi">
                                <i class="fas fa-search-location me-2"></i>1. Deteksi Lokasi
                            </button>
                            <button type="submit" name="kirim_absen" id="btnKirim" class="btn-kirim" disabled>
                                <i class="fas fa-paper-plane me-2"></i>2. Kirim Absensi
                            </button>
                        </div>
                    </form>

                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-coffee"></i>
                        <h5 class="mb-3 text-muted">Tidak ada sesi absensi yang dibuka</h5>
                        <p class="text-muted mb-4">Saat ini tidak ada jadwal absensi untuk hari ini. Silakan hubungi guru untuk informasi lebih lanjut.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <button onclick="location.reload()" class="btn btn-outline-primary">
                                <i class="fas fa-sync me-2"></i>Refresh Halaman
                            </button>
                            <a href="absensi.php" class="btn btn-primary">
                                <i class="fas fa-history me-2"></i>Lihat Riwayat
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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

        // Initialize Map
        var map = L.map('map').setView([<?= $latitude_sekolah ?>, <?= $longitude_sekolah ?>], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // School Area Circle
        var circle = L.circle([<?= $latitude_sekolah ?>, <?= $longitude_sekolah ?>], {
            color: '#4361ee',
            fillColor: '#4361ee',
            fillOpacity: 0.1,
            radius: <?= $radius_diizinkan ?>
        }).addTo(map);
        
        // School Marker
        var schoolMarker = L.marker([<?= $latitude_sekolah ?>, <?= $longitude_sekolah ?>]).addTo(map)
            .bindPopup("<strong>SMK Al Amin</strong><br>Area Absensi Sekolah")
            .openPopup();

        var userMarker = null;

        function cekStatus() {
            var status = document.getElementById('status_kehadiran').value;
            var info = document.getElementById('infoLokasi');
            
            if(status == 'Hadir') {
                info.innerHTML = '<i class="fas fa-info-circle me-2"></i>Status <strong>Hadir</strong> wajib berada dalam radius <strong><?= $radius_diizinkan ?> meter</strong> dari sekolah.';
                info.className = 'status-hadir status-info mt-2';
            } else {
                info.innerHTML = '<i class="fas fa-check-circle me-2 text-success"></i>Status <strong>' + status + '</strong> diperbolehkan absen dari luar sekolah.';
                info.className = 'status-izin status-info mt-2';
            }
        }

        function getLocation() {
            var btn = document.getElementById("btnLokasi");
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mendeteksi Lokasi...';
            btn.disabled = true;
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            } else { 
                alert("Browser Anda tidak mendukung Geolocation.");
                btn.innerHTML = '<i class="fas fa-search-location me-2"></i>1. Deteksi Lokasi';
                btn.disabled = false;
            }
        }

        function showPosition(pos) {
            var lat = pos.coords.latitude;
            var lng = pos.coords.longitude;
            
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            
            // Remove previous user marker
            if (userMarker) {
                map.removeLayer(userMarker);
            }
            
            // Add new user marker
            userMarker = L.marker([lat, lng], {
                icon: L.icon({
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-red.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41]
                })
            }).addTo(map)
            .bindPopup("Lokasi Anda<br>Lat: " + lat.toFixed(6) + "<br>Lng: " + lng.toFixed(6))
            .openPopup();
            
            // Center map on user location
            map.setView([lat, lng], 18);
            
            // Enable submit button
            document.getElementById('btnKirim').disabled = false;
            
            // Update location button
            var btn = document.getElementById("btnLokasi");
            btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Lokasi Berhasil Ditemukan';
            btn.className = "btn btn-success py-3";
            btn.disabled = true;
            
            // Calculate distance
            var distance = calculateDistance(lat, lng, <?= $latitude_sekolah ?>, <?= $longitude_sekolah ?>);
            var status = document.getElementById('status_kehadiran').value;
            
            if (status === 'Hadir' && distance > <?= $radius_diizinkan ?>) {
                alert("Peringatan: Anda berada " + Math.round(distance) + "m dari sekolah. Status 'Hadir' memerlukan Anda berada dalam radius <?= $radius_diizinkan ?>m.");
            }
        }

        function showError(error) {
            var btn = document.getElementById("btnLokasi");
            btn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Gagal Mendeteksi';
            btn.className = "btn btn-danger py-3";
            btn.disabled = false;
            
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    alert("Akses lokasi ditolak. Harap izinkan akses lokasi di pengaturan browser Anda.");
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert("Informasi lokasi tidak tersedia.");
                    break;
                case error.TIMEOUT:
                    alert("Waktu permintaan lokasi habis. Coba lagi.");
                    break;
                default:
                    alert("Terjadi kesalahan yang tidak diketahui.");
                    break;
            }
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            var R = 6371e3; // Earth's radius in meters
            var φ1 = lat1 * Math.PI/180;
            var φ2 = lat2 * Math.PI/180;
            var Δφ = (lat2-lat1) * Math.PI/180;
            var Δλ = (lon2-lon1) * Math.PI/180;

            var a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                    Math.cos(φ1) * Math.cos(φ2) *
                    Math.sin(Δλ/2) * Math.sin(Δλ/2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

            return R * c;
        }

        // Initialize status info
        cekStatus();
    </script>
</body>
</html>