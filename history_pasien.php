<?php
include('config.php');
session_start();

// memastikan login sebagai dokter
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dokter') {
    header("Location: login_dokter.php");
    exit;
}

// mengambil ID pasien
$id_pasien = $_GET['id_pasien'] ?? null;

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "dokter";
$id_dokter = isset($_SESSION['id_dokter']) ? $_SESSION['id_dokter'] : null; // mendapatkan ID dokter dasi sesi
if (!$id_pasien) {
    die("Patient ID is required.");
}

// mengambil detail pasien
$query_patient = "SELECT nama, alamat FROM pasien WHERE id = ?";
$stmt_patient = $conn->prepare($query_patient);
$stmt_patient->bind_param("i", $id_pasien);
$stmt_patient->execute();
$patient = $stmt_patient->get_result()->fetch_assoc();

if (!$patient) {
    die("Patient not found.");
}

// mengambil riwayat pemeriksaan untuk pasien
$query_history = "
    SELECT per.tgl_periksa, per.catatan, per.biaya_periksa, GROUP_CONCAT(o.nama_obat SEPARATOR ', ') AS obat_diberikan
    FROM periksa per
    JOIN daftar_poli dp ON per.id_daftar_poli = dp.id
    LEFT JOIN detail_periksa dp_obat ON per.id = dp_obat.id_periksa
    LEFT JOIN obat o ON dp_obat.id_obat = o.id
    WHERE dp.id_pasien = ?
    GROUP BY per.id
    ORDER BY per.tgl_periksa DESC";
$stmt_history = $conn->prepare($query_history);
$stmt_history->bind_param("i", $id_pasien);
$stmt_history->execute();
$history = $stmt_history->get_result();

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
    <title>Patient History</title>
    <!-- Include Bootstrap and AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        .nav-link {
            color: #ddd;
            font-size: 1rem;
            padding: 12px 15px;
        }
        .nav-link:hover {
            background: #006494;
            color: #fff;
        }
        .content-wrapper {
            padding: 20px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="#" class="brand-link">
            <span class="brand-text font-weight-light">Patient History</span>
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
                        <a href="history_pasien.php" class="nav-link active">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Patient History</p>
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
        <div class="container">
            <h3>Examination History for: <?php echo htmlspecialchars($patient['nama']); ?></h3>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($patient['alamat']); ?></p>
            <hr>
            <?php if ($history->num_rows > 0) { ?>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Health Notes</th>
                        <th>Prescribed Medications</th>
                        <th>Total Cost (IDR)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $history->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['tgl_periksa']); ?></td>
                            <td><?php echo htmlspecialchars($row['catatan'] ?? 'No Notes'); ?></td>
                            <td><?php echo htmlspecialchars($row['obat_diberikan'] ?? 'No Medications'); ?></td>
                            <td><?php echo number_format($row['biaya_periksa'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="alert alert-warning">No examination history found for this patient.</div>
            <?php } ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
</body>
</html>
