<?php
// Start the session
session_start();

//role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}


$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Admin";

// koneksi ke database
include('config.php');

// menampilkan data yg dimasukkan
$query_pasien = "SELECT COUNT(*) AS total_pasien FROM pasien";
$query_dokter = "SELECT COUNT(*) AS total_dokter FROM dokter";
$query_poli = "SELECT COUNT(*) AS total_poli FROM poli";
$query_obat = "SELECT COUNT(*) AS total_obat FROM obat";

$result_pasien = $conn->query($query_pasien);
$result_dokter = $conn->query($query_dokter);
$result_poli = $conn->query($query_poli);
$result_obat = $conn->query($query_obat);

$total_pasien = $result_pasien->fetch_assoc()['total_pasien'];
$total_dokter = $result_dokter->fetch_assoc()['total_dokter'];
$total_poli = $result_poli->fetch_assoc()['total_poli'];
$total_obat = $result_obat->fetch_assoc()['total_obat'];

// logout 
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: home.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap and AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
    <style>
        body {
            background: #f8f9fa;
        }
        .main-sidebar {
            background: #1e2d3b;
        }
        .brand-link {
            background: #004d7a;
            color: #fff;
            text-align: center;
            font-size: 1.25rem;
            padding: 15px 0;
        }
        .user-panel .info a {
            color: #fff;
            font-size: 1.1rem;
            font-weight: bold;
        }
        .card-header {
            background: #004d7a;
            color: #fff;
        }
        .card-body {
            background: #fff;
            color: #333;
        }
        .nav-link {
            color: #ddd;
            font-size: 1rem;
            padding: 12px 15px;
        }
        .nav-link:hover {
            background: #006494;
            color: #fff;
        }
        .card {
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-10px);
        }
        .card-title {
            font-size: 2rem;
            font-weight: bold;
        }
        footer {
            background: #1e2d3b;
            color: #fff;
        }
        footer a {
            color: #f39c12;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="#" class="brand-link">
                <span class="brand-text">Admin Dashboard</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- User Panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="1.jpg" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block"><?php echo $username; ?></a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="admin_dashboard.php" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_dokter.php" class="nav-link">
                                <i class="nav-icon fas fa-user-md"></i>
                                <p>Manage Dokter</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_pasien.php" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Manage Pasien</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_poli.php" class="nav-link">
                                <i class="nav-icon fas fa-hospital"></i>
                                <p>Manage Poli</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_obat.php" class="nav-link">
                                <i class="nav-icon fas fa-pills"></i>
                                <p>Manage Obat</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="?logout=true" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Admin Dashboard</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <!-- Card for Pasien Count -->
                        <div class="col-md-3">
                            <div class="card bg-info text-white mb-3">
                                <div class="card-header">Total Pasien</div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $total_pasien; ?></h5>
                                </div>
                            </div>
                        </div>

                        <!-- Card untuk Dokter -->
                        <div class="col-md-3">
                            <div class="card bg-success text-white mb-3">
                                <div class="card-header">Total Dokter</div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $total_dokter; ?></h5>
                                </div>
                            </div>
                        </div>

                        <!-- Card untuk Poli -->
                        <div class="col-md-3">
                            <div class="card bg-warning text-white mb-3">
                                <div class="card-header">Total Poli</div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $total_poli; ?></h5>
                                </div>
                            </div>
                        </div>

                        <!-- Card untuk Obat -->
                        <div class="col-md-3">
                            <div class="card bg-danger text-white mb-3">
                                <div class="card-header">Total Obat</div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $total_obat; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Welcome, <?php echo $username; ?>!</h5>
                                    <p class="card-text">You can manage doctors, patients, poliklinik, and medicines from the sidebar.</p>
                                    <?php
                                    if (isset($_GET['page']) && $_GET['page'] == 'manage_pasien') {
                                        include 'manage_pasien.php';
                                    } elseif (isset($_GET['page']) && $_GET['page'] == 'manage_dokter') {
                                        include 'manage_dokter.php';
                                    } else {
                                        echo '<p>Choose an option from the sidebar to manage resources.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Main Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 3.2.0
            </div>
            <strong>&copy; 2024 <a href="#">Polyclinic Adhysaa</a>.</strong> All rights reserved.
        </footer>
    </div>
</body>
</html>

