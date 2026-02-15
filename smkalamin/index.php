<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMK Al Amin Cibening - E-Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #1e40af;
            --accent: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
        }
        
        /* Navbar */
        .navbar {
            background: var(--primary);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            margin: 0 0.3rem;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: white !important;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 6rem 0 4rem;
            position: relative;
            overflow: hidden;
        }
        
       
        
        .hero h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .teacher-card img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid var(--light);
        }
        
        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--primary);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            padding-left: 1.5rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -0.5rem;
            top: 0.5rem;
            width: 1rem;
            height: 1rem;
            background: var(--primary);
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px var(--primary);
        }
        
        /* Footer */
        .footer {
            background: var(--dark);
            color: white;
            padding: 3rem 0 2rem;
        }
        
        .footer h5 {
            color: white;
            margin-bottom: 1.5rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 4rem 0 3rem;
                text-align: center;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .teacher-card img {
                width: 100px;
                height: 100px;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>SMK Al Amin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#home">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#sejarah">Sejarah</a></li>
                    <li class="nav-item"><a class="nav-link" href="#visi">Visi & Misi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#guru">Guru</a></li>
                    <li class="nav-item"><a class="nav-link" href="#fasilitas">Fasilitas</a></li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-light text-primary ms-2" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
<section id="home" class="hero text-center">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-8 text-white fade-in">
                <h1>Selamat Datang di E-Learning SMK Al Amin Cibening</h1>
                <p class="lead mb-4">
                    Platform pembelajaran digital untuk meningkatkan kualitas pendidikan 
                    dan memudahkan proses belajar mengajar.
                </p>
                <div class="d-flex justify-content-center flex-wrap gap-3">
                    <a href="login.php" class="btn btn-light btn-lg">
                        <i class="fas fa-rocket me-2"></i>Mulai Belajar
                    </a>
                    <a href="#sejarah" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle me-2"></i>Tentang Kami
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

   <!-- Sejarah -->
<section id="sejarah" class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4">
                <img src="assets/image/smk.jpeg" 
                     alt="Sejarah sekolah" 
                     class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6">
                <h2 class="mb-4">
                    <i class="fas fa-history text-primary me-2"></i>Sejarah SMK Al Amin
                </h2>

                <!-- Teks Sejarah dengan rata kanan kiri -->
                <p class="text-muted mb-4" style="text-align: justify;">
                    SMKS AL AMIN Cibening berdiri pada 11 Juli 2000 di bawah naungan Yayasan Haji Muhammad Amin sebagai upaya menyediakan pendidikan kejuruan bagi masyarakat Desa Cibening dan sekitarnya. Pada masa awal, sekolah ini beroperasi dengan fasilitas yang sederhana, namun perlahan berkembang berkat dukungan yayasan, masyarakat, serta dedikasi para guru.
                    <br><br>
                    Seiring waktu, SMKS AL AMIN membuka beberapa kompetensi keahlian — Teknik Komputer dan Jaringan, Bisnis Daring dan Pemasaran, serta Otomatisasi dan Tata Kelola Perkantoran — untuk menyesuaikan pendidikan dengan kebutuhan dunia kerja. Berbagai peningkatan mutu pembelajaran dan sarana prasarana menjadikan sekolah ini memperoleh akreditasi B.
                    <br><br>
                    Hingga kini, SMKS AL AMIN terus berkomitmen memberikan pendidikan yang berkualitas, religius, dan berbasis keterampilan guna membentuk generasi muda yang mandiri serta siap menghadapi tantangan masa depan.
                </p>

            </div>
        </div>
    </div>
</section>


    <!-- Visi & Misi -->
    <section id="visi" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white rounded-circle p-3 me-3">
                                    <i class="fas fa-eye fa-2x"></i>
                                </div>
                                <h3 class="mb-0">Visi</h3>
                            </div>
                            <p class="card-text fs-5"> "Menjadikan sekolah tempat mendidik dan melatih peserta didik dengan menitik beratkan pada pembentukan pribadi yang berkarakter mulia berwawasan dan berpengetahuan luas, yang dapat menjawab sumber daya manusia yang berkualitas dan berdayaguna serta dapat diserap oleh dunia usaha/dunia industri."</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white rounded-circle p-3 me-3">
                                    <i class="fas fa-bullseye fa-2x"></i>
                                </div>
                                <h3 class="mb-0">Misi</h3>
                            </div>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Mendidik dan melatih peserta didik agar menjadi warga negara yang disiplin, jujur, tanggung jawab, beriman dan bertaqwa serta memiliki ilmu pengetahuan dan keterampilan sebagai bekal untuk mengisi kebutuhan tenaga kerja tingkat menengah baik saat ini maupun di masa mendatang.</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Mempersiapkan peserta didik agar menjadi manusia yang produktif mampu bekerja mandiri, mengisi lowongan pekerjaan di DU/Di sebagai tenaga kerja tingkat menengah sesuai dengan kompetensi dalam program keahlian masing-masing.</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Meningkatkan kualitas tamatan yang sesuai dengan Standar Kompetensi Nasional (SKN) dalam Menghadapi era globalisasi.</li>
                                <li><i class="fas fa-check text-primary me-2"></i>Meningkatkan mutu sumber daya manusia melalui dukungan IPTEK/IMTAQ.</li>
                                <li><i class="fas fa-check text-primary me-2"></i>Melaksankan KBM dan kegiatan ekstrakulikuler untuk mengembangkan minat dan bakat peserta didik dalam meraih prestasi.</li>
                                
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Data Guru -->
    <section id="guru" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5"><i class="fas fa-chalkboard-teacher text-primary me-2"></i>Data Guru Kami</h2>
            <div class="row" id="guruContainer">
                <!-- Data guru akan dimuat via JS -->
            </div>
        </div>
    </section>

    <!-- Fasilitas -->
    <section id="fasilitas" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5"><i class="fas fa-building text-primary me-2"></i>Fasilitas Sekolah</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 text-center">
                        <div class="card-body p-4">
                            <div class="bg-primary text-white rounded-circle p-3 d-inline-flex mb-3">
                                <i class="fas fa-laptop-code fa-2x"></i>
                            </div>
                            <h5>Lab Komputer</h5>
                            <p class="text-muted">Laboratorium komputer dengan 30 unit PC modern dan koneksi internet cepat</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 text-center">
                        <div class="card-body p-4">
                            <div class="bg-primary text-white rounded-circle p-3 d-inline-flex mb-3">
                                <i class="fas fa-book fa-2x"></i>
                            </div>
                            <h5>Perpustakaan Digital</h5>
                            <p class="text-muted">Perpustakaan dengan koleksi buku fisik yang lengkap</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 text-center">
                        <div class="card-body p-4">
                            <div class="bg-primary text-white rounded-circle p-3 d-inline-flex mb-3">
                                <i class="fas fa-wifi fa-2x"></i>
                            </div>
                            <h5>WiFi sekolah</h5>
                            <p class="text-muted">Akses internet gratis di seluruh area sekolah untuk mendukung pembelajaran</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><i class="fas fa-graduation-cap me-2"></i>SMK Al-Amin Cibening</h5>
                    <p class="mt-3">Jl. Tamrin No.1 Desa. Cibening, Kecamatan. Pamijahan, Kabupaten. Bogor - Jawa Barat<br>
                    Telp: +62 856-9439-7071<br>
                    Email: smkalaminbogor@gmail.com</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Menu Cepat</h5>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2"><a href="#home" class="text-light text-decoration-none">Beranda</a></li>
                        <li class="mb-2"><a href="#sejarah" class="text-light text-decoration-none">Sejarah</a></li>
                        <li class="mb-2"><a href="#guru" class="text-light text-decoration-none">Data Guru</a></li>
                        <li><a href="#fasilitas" class="text-light text-decoration-none">Fasilitas</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Login</h5>
                    <p class="mt-3">Akses sistem e-learning dengan login sesuai peran Anda:</p>
                    <a href="login.php" class="btn btn-light">
                        <i class="fas fa-sign-in-alt me-2"></i>Login Sekarang
                    </a>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="text-center">
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Data guru
        const guruData = [
            {
                nama: "Aria Suryadinata,S.Pt,MM ",
                nip: "198003122005011001",
                mapel: "Ketua Yayasan",
                email: "ahmad.fauzi@smk-alamin.sch.id"
            },
            {
                nama: "Endah Setia Febriawan, S.Pt.M.Pd",
                nip: "198512182010012002",
                mapel: "Kepala Sekola ",
                email: "siti.rahmawati@smk-alamin.sch.id"
            },
            {
                nama: "H.Asep Saepudin Z.S.Pd. M.Pd",
                nip: "197904052003121003",
                mapel: "Multimedia",
                email: "budi.santoso@smk-alamin.sch.id"
            },
            {
                nama: "KM. Jafar Siddiq. BA.",
                nip: "199005152015032004",
                mapel: "Bahasa Indonesia",
                email: "dewi.anggraini@smk-alamin.sch.id"
            },
            {
                nama: "Rusmiati. S.Pd",
                nip: "199210102018011005",
                mapel: "Pemrograman Web",
                email: "rizki.pratama@smk-alamin.sch.id"
            },
            {
                nama: "Sushanti. SE. ME",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
             {
                nama: "Sukarni. S.Pdi. MM",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
             {
                nama: "Drs. H. Enjay Sujaya. MBA. MM",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
             {
                nama: "Iis Sukersih. S.Pdi. MM",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
             {
                nama: "Ade Gusman Al Katiri. S.Pd",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
             {
                nama: "Mohammad Iqbal. S.Pd",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
             {
                nama: "Aseph Choiruddin. S.Pd",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
             {
                nama: "Heri Ahmad Sobari. S.Pd",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
             {
                nama: "Ato Sugiarto. S.Ag",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
             {
                nama: "Siti Kamila. S.Pd",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
             {
                nama: "Ade Fifin Muflihah. S.Pdi",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Muhammad Sopian Sauri",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Saktiaji Dhany Prasetyo. S.Pd",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Irpan Maulana Yusuf. S.Pd",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Nurapiah. S.Pd",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Syarah Nopiyani. S.Pd",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Ramdani Syukur",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Amar Ludy Yahya",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Asep Syamsul Badar",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Mad Huri",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Memen Rohmatul Ummah.S.Pdi",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Mira Humaeroh",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Muhamad Ilham Alfiansyah",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Eka Safitri",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            {
                nama: "Fiqri Yawan",
                nip: "198703252012012006",
                mapel: "Pendidikan Agama Islam",
                email: "maya.indah@smk-alamin.sch.id"

            },
            
            
            
            
            
            
            
            
            
            
            
        ];

        // Load data guru
        function loadGuruData() {
            const container = document.getElementById('guruContainer');
            
            guruData.forEach(guru => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4 mb-4';
                
                // Generate avatar
                const avatarName = guru.nama.split(' ')[0];
                const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(avatarName)}&background=2563eb&color=fff&size=200&bold=true`;
                
                col.innerHTML = `
                    <div class="card teacher-card text-center p-4">
                        <img src="${avatarUrl}" alt="${guru.nama}" class="mx-auto mb-3">
                        <h5 class="mb-2">${guru.nama}</h5>
                        <p class="text-muted small mb-2"><i class="fas fa-id-card me-1"></i>${guru.nip}</p>
                        <p class="text-primary mb-3"><i class="fas fa-book me-1"></i>${guru.mapel}</p>
                        <p class="text-muted"><i class="fas fa-envelope me-1"></i>${guru.email}</p>
                    </div>
                `;
                
                container.appendChild(col);
            });
        }

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 70,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadGuruData();
        });
    </script>
</body>
</html>