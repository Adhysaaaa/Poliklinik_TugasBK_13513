<?php
// Start the session
session_start();

// Ensure the user is logged in as patient
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pasien') {
    header("Location: login.php");
    exit;
}

// Fetch username from the session securely
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "pasien";

// Include database connection (adjust according to your db config)
include('config.php');

// Handle logout action
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Fetch available Poli and Doctors
$query_poli = "SELECT * FROM poli";
$result_poli = $conn->query($query_poli);

$query_dokter = "SELECT dokter.*, poli.nama_poli FROM dokter JOIN poli ON dokter.id_poli = poli.id";
$result_dokter = $conn->query($query_dokter);

$query_jadwal = "SELECT jadwal_periksa.*, dokter.nama AS nama_dokter, poli.nama_poli 
                 FROM jadwal_periksa 
                 JOIN dokter ON jadwal_periksa.id_dokter = dokter.id 
                 JOIN poli ON dokter.id_poli = poli.id";
$result_jadwal = $conn->query($query_jadwal);

// Handle Poli Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_poli'])) {
    $id_pasien = $_SESSION['user_id'];
    $id_jadwal = $_POST['id_jadwal'];
    $keluhan = htmlspecialchars($_POST['keluhan']);

    // Generate a new queue number
    $query_antrian = "SELECT MAX(no_antrian) AS max_antrian FROM daftar_poli WHERE id_jadwal = ?";
    $stmt = $conn->prepare($query_antrian);
    $stmt->bind_param("i", $id_jadwal);
    $stmt->execute();
    $result_antrian = $stmt->get_result()->fetch_assoc();
    $no_antrian = $result_antrian['max_antrian'] + 1;

    // Insert the registration
    $query_register = "INSERT INTO daftar_poli (id_pasien, id_jadwal, keluhan, no_antrian) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query_register);
    $stmt->bind_param("iisi", $id_pasien, $id_jadwal, $keluhan, $no_antrian);

    if ($stmt->execute()) {
        $success_message = "You have successfully registered to Poli with Queue Number: " . $no_antrian;
    } else {
        $error_message = "Failed to register. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
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
            <a href="#" class="brand-link">
                <span class="brand-text font-weight-light">Patient Dashboard</span>
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
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Patient Dashboard</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <?php if (isset($success_message)) { ?>
                        <div class="alert alert-success"> <?php echo $success_message; ?> </div>
                    <?php } elseif (isset($error_message)) { ?>
                        <div class="alert alert-danger"> <?php echo $error_message; ?> </div>
                    <?php } ?>

                    <div class="card">
                        <div class="card-header bg-info text-white">Register for Poli</div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="keluhan" class="form-label">Keluhan/Gejala</label>
                                    <textarea class="form-control" id="keluhan" name="keluhan" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="id_jadwal" class="form-label">Choose Doctor and Schedule</label>
                                    <select class="form-control" id="id_jadwal" name="id_jadwal" required>
                                        <option value="">Select Schedule</option>
                                        <?php while ($row = $result_jadwal->fetch_assoc()) { ?>
                                            <option value="<?php echo $row['id']; ?>">
                                                <?php echo $row['nama_poli'] . " - " . $row['nama_dokter'] . " (" . $row['hari'] . ", " . $row['jam_mulai'] . " - " . $row['jam_selesai'] . ")"; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <button type="submit" name="register_poli" class="btn btn-primary">Register</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 3.2.0
            </div>
            <strong>&copy; 2024 <a href="#">Polyclinic Adhysaa</a>.</strong> All rights reserved.
        </footer>
    </div>
</body>
</html>
