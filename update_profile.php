<?php
// Start the session
session_start();

// memastikan user login sebagai dokter
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dokter') {
    header("Location: login_dokter.php");
    exit;
}

// mengambil ID dokter dari sesi
$id_dokter = isset($_SESSION['id_dokter']) ? $_SESSION['id_dokter'] : null;

// Check jika ID dokter tersedia
if (!$id_dokter) {
    die("Doctor ID is not set.");
}

// koneksi ke database
include('config.php');

// mengambil data dokter
$query_dokter = "SELECT username, password, nama, alamat, no_hp FROM dokter WHERE id = ?";
$stmt = $conn->prepare($query_dokter);
$stmt->bind_param("i", $id_dokter);
$stmt->execute();
$result = $stmt->get_result();
$dokter = $result->fetch_assoc();

// form submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $nama = htmlspecialchars($_POST['nama']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $no_hp = htmlspecialchars($_POST['no_hp']);

    // menset password diambil dari alamat
    $password = $alamat;

    // update dokter detail
    $update_query = "UPDATE dokter SET username = ?, password = ?, nama = ?, alamat = ?, no_hp = ? WHERE id = ?";
    $stmt_update = $conn->prepare($update_query);
    $stmt_update->bind_param("sssssi", $username, $password, $nama, $alamat, $no_hp, $id_dokter);

    if ($stmt_update->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile: " . $stmt_update->error;
    }
}

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
    <title>Update Profile</title>
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
                            <a href="update_profile.php" class="nav-link active">
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
            <div class="container mt-5">
                <h2 class="mb-4">Update Profile</h2>
                <?php if ($message): ?>
                    <div class="alert alert-info"> <?php echo $message; ?> </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $dokter['username']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo $dokter['nama']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="alamat" class="form-label">Address</label>
                        <input type="text" class="form-control" id="alamat" name="alamat" value="<?php echo $dokter['alamat']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="no_hp" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?php echo $dokter['no_hp']; ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <a href="dokter_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </form>
            </div>
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
