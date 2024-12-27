<?php
include('config.php');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dokter') {
    header("Location: login_dokter.php");
    exit;
}

$id_daftar_poli = $_GET['id_daftar_poli'] ?? null;

if (!$id_daftar_poli) {
    die("Invalid request.");
}

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "dokter";
$id_dokter = isset($_SESSION['id_dokter']) ? $_SESSION['id_dokter'] : null; // mendapatkan ID dokter dari sesi

// mengambil detail pasien
$query_patient = "
    SELECT dp.id, p.nama, dp.keluhan, dp.no_antrian, per.catatan, per.biaya_periksa, per.id AS periksa_id
    FROM daftar_poli dp
    JOIN pasien p ON dp.id_pasien = p.id
    LEFT JOIN periksa per ON per.id_daftar_poli = dp.id
    WHERE dp.id = ?";
$stmt = $conn->prepare($query_patient);
$stmt->bind_param("i", $id_daftar_poli);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catatan = $_POST['catatan'] ?? '';
    $obat_ids = $_POST['obat_ids'] ?? [];
    $total_obat_cost = 0;

    // kalkulasi biaya obat
    if (!empty($obat_ids)) {
        $placeholders = implode(',', array_fill(0, count($obat_ids), '?'));
        $query_obat = "SELECT SUM(harga) AS total_harga FROM obat WHERE id IN ($placeholders)";
        $stmt_obat = $conn->prepare($query_obat);
        $stmt_obat->bind_param(str_repeat('i', count($obat_ids)), ...$obat_ids);
        $stmt_obat->execute();
        $obat_data = $stmt_obat->get_result()->fetch_assoc();
        $total_obat_cost = $obat_data['total_harga'] ?? 0;
    }

    // kalkulasi total pemeriksaan (termasuk fee dokter)
    $biaya_periksa = 150000 + $total_obat_cost;

    // Insert atau update the catatan pemeriksaan
    if (empty($patient['periksa_id'])) {
        // tidak ada catatan pemeriksaan, buat baru
        $query_insert = "
            INSERT INTO periksa (id_daftar_poli, tgl_periksa, catatan, biaya_periksa)
            VALUES (?, NOW(), ?, ?)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param("isi", $id_daftar_poli, $catatan, $biaya_periksa);
        $stmt_insert->execute();
        $periksa_id = $stmt_insert->insert_id;  // Dapatkan ID periksa yang baru
    } else {
        // Update pemeriksaan yang tersedia
        $query_update = "
            UPDATE periksa SET catatan = ?, biaya_periksa = ? WHERE id = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("sii", $catatan, $biaya_periksa, $patient['periksa_id']);
        $stmt_update->execute();
        $periksa_id = $patient['periksa_id'];  // Use the existing periksa ID
    }

    // menghapus obat yang ada untuk pemeriksaan saat ini (jika ada)
    $query_delete = "DELETE FROM detail_periksa WHERE id_periksa = ?";
    $stmt_delete = $conn->prepare($query_delete);
    $stmt_delete->bind_param("i", $periksa_id);
    $stmt_delete->execute();

    // masukkan obat baru yang diresepkan
    foreach ($obat_ids as $id_obat) {
        $query_insert_obat = "INSERT INTO detail_periksa (id_periksa, id_obat) VALUES (?, ?)";
        $stmt_insert_obat = $conn->prepare($query_insert_obat);
        $stmt_insert_obat->bind_param("ii", $periksa_id, $id_obat);
        $stmt_insert_obat->execute();
    }

    // mengarahkan ke dashboard setelah menyimpan
    header("Location: dokter_dashboard.php");
    exit;
}

// mengambil obat yang tersedia
$query_obat = "SELECT * FROM obat";
$obat_result = $conn->query($query_obat);

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
    <title>Manage Patient</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
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
        .nav-link {
            color: #ddd;
            font-size: 1rem;
            padding: 12px 15px;
        }
        .nav-link:hover {
            background: #006494;
            color: #fff;
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
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Manage Patient</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Patient Details</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="catatan" class="form-label">Health Notes</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="4"><?php echo htmlspecialchars($patient['catatan'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="obat_ids" class="form-label">Prescribe Medications</label>
                                <select class="form-select" id="obat_ids" name="obat_ids[]" multiple>
                                    <?php while ($obat = $obat_result->fetch_assoc()) { ?>
                                        <option value="<?php echo $obat['id']; ?>">
                                            <?php echo htmlspecialchars($obat['nama_obat']) . " (" . $obat['harga'] . " IDR)"; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
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

