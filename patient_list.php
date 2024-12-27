<?php
// Start session
session_start();

// memastikan user login sebagai dokter
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dokter') {
    header("Location: login_dokter.php");
    exit;
}

// ambil ID dokter yang masuk 
$id_dokter = isset($_SESSION['id_dokter']) ? $_SESSION['id_dokter'] : null;

if (!$id_dokter) {
    die("Doctor ID is not set in the session.");
}

include('config.php');


$query_patients = "
    SELECT dp.id, p.nama, dp.keluhan, dp.no_antrian, per.tgl_periksa, dp.id_pasien
    FROM daftar_poli dp
    JOIN jadwal_periksa jp ON dp.id_jadwal = jp.id
    JOIN pasien p ON dp.id_pasien = p.id
    LEFT JOIN periksa per ON per.id_daftar_poli = dp.id
    WHERE jp.id_dokter = ?";
$stmt = $conn->prepare($query_patients);
$stmt->bind_param("i", $id_dokter);
$stmt->execute();
$patients = $stmt->get_result();

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
    <title>Patient List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
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
        .container {
            margin-top: 50px;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
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
                        <a href="#" class="d-block"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
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
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Patient List</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="container">
                                <h2>Patient List</h2>
                                <?php if ($patients->num_rows > 0) { ?>
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Patient Name</th>
                                            <th>Complaint</th>
                                            <th>Queue Number</th>
                                            <th>Examination Date</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php while ($row = $patients->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                                <td><?php echo htmlspecialchars($row['keluhan']); ?></td>
                                                <td><?php echo htmlspecialchars($row['no_antrian']); ?></td>
                                                <td><?php echo htmlspecialchars($row['tgl_periksa'] ?? 'Not Set'); ?></td>
                                                <td>
                                                    <!-- Buttons for managing records -->
                                                    <a href="manage_pasienn.php?id_daftar_poli=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Manage</a>
                                                    <a href="history_pasien.php?id_pasien=<?php echo $row['id_pasien']; ?>" class="btn btn-secondary btn-sm">History</a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                <?php } else { ?>
                                    <div class="alert alert-warning">No patients found for your schedule.</div>
                                <?php } ?>
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
