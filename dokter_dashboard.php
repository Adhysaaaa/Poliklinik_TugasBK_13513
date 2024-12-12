<?php
// Start the session
session_start();

// Ensure the user is logged in as a doctor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dokter') {
    header("Location: login.php");
    exit;
}

// Fetch username and doctor ID from the session securely
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "dokter";

// Include database connection
include('config.php');

// Fetch data for the dashboard
$query_appointments = "SELECT COUNT(*) AS total_appointments FROM daftar_poli dp
                        JOIN jadwal_periksa jp ON dp.id_jadwal = jp.id
                        WHERE jp.id_dokter = ?";
$query_history = "SELECT COUNT(*) AS total_history FROM periksa p
                   JOIN daftar_poli dp ON p.id_daftar_poli = dp.id
                   JOIN jadwal_periksa jp ON dp.id_jadwal = jp.id
                   WHERE jp.id_dokter = ?";

$stmt_appointments = $conn->prepare($query_appointments);
$stmt_appointments->bind_param("i", $id);
$stmt_appointments->execute();
$total_appointments = $stmt_appointments->get_result()->fetch_assoc()['total_appointments'];

$stmt_history = $conn->prepare($query_history);
$stmt_history->bind_param("i", $id);
$stmt_history->execute();
$total_history = $stmt_history->get_result()->fetch_assoc()['total_history'];

// Handle logout 
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <!-- Include Bootstrap and AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <!-- Include FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="#" class="brand-link">
                <span class="brand-text font-weight-light">Doctor Dashboard</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- User Panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://via.placeholder.com/150" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block"><?php echo $username; ?></a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="dokter_dashboard.php" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_schedule.php" class="nav-link">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Manage Schedule</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="patient_list.php" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Patient List</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="update_profile.php" class="nav-link">
                                <i class="nav-icon fas fa-user-edit"></i>
                                <p>Update Profile</p>
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
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Doctor Dashboard</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <!-- Card for Appointments Count -->
                        <div class="col-md-3">
                            <div class="card text-white bg-info mb-3">
                                <div class="card-header">Upcoming Appointments</div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $total_appointments; ?></h5>
                                </div>
                            </div>
                        </div>

                        <!-- Card for History Count -->
                        <div class="col-md-3">
                            <div class="card text-white bg-success mb-3">
                                <div class="card-header">Patient History</div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $total_history; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Welcome, <?php echo $username; ?>!</h5>
                                    <p class="card-text">Use the sidebar to manage your schedule, view patients, update your profile, and record patient data.</p>
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