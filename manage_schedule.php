<?php
// Start session
session_start();

// memastikan user login sebagai  dokter
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dokter') {
    header("Location: login_dokter.php");
    exit;
}

include('config.php');

// dapatkan ID dokter yang masuk dari sesi
$id_dokter = $_SESSION['id_dokter'];

// pembuatan jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    $hari = htmlspecialchars($_POST['hari']);
    $jam_mulai = htmlspecialchars($_POST['jam_mulai']);
    $jam_selesai = htmlspecialchars($_POST['jam_selesai']);

    if (empty($hari) || empty($jam_mulai) || empty($jam_selesai)) {
        $error_message = "All fields are required.";
    } else {
        // Check jika ada jadwal yang bertabrakan
        $query_check_conflict = "
            SELECT * FROM jadwal_periksa 
            WHERE id_dokter = ? 
              AND hari = ? 
              AND (
                  (jam_mulai <= ? AND jam_selesai > ?) OR 
                  (jam_mulai < ? AND jam_selesai >= ?)
              )
        ";
        $stmt_conflict = $conn->prepare($query_check_conflict);
        $stmt_conflict->bind_param("isssss", $id_dokter, $hari, $jam_mulai, $jam_mulai, $jam_selesai, $jam_selesai);
        $stmt_conflict->execute();
        $result_conflict = $stmt_conflict->get_result();

        if ($result_conflict->num_rows > 0) {
            $error_message = "Schedule conflicts with an existing schedule.";
        } else {
            // menonaktifkan jadwal aktif sebelumnya
            $query_deactivate = "UPDATE jadwal_periksa SET active = 0 WHERE id_dokter = ?";
            $stmt_deactivate = $conn->prepare($query_deactivate);
            $stmt_deactivate->bind_param("i", $id_dokter);
            $stmt_deactivate->execute();

            // tambah jadwal baru
            $query_add_schedule = "INSERT INTO jadwal_periksa (id_dokter, hari, jam_mulai, jam_selesai, active) VALUES (?, ?, ?, ?, 1)";
            $stmt = $conn->prepare($query_add_schedule);
            $stmt->bind_param("isss", $id_dokter, $hari, $jam_mulai, $jam_selesai);

            if ($stmt->execute()) {
                $success_message = "Schedule added successfully.";
            } else {
                $error_message = "Failed to add schedule. Please try again.";
            }
        }
    }
}

// Menangani status jadwal (Aktifkan/Nonaktifkan)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $schedule_id = $_POST['schedule_id'];

    // active status
    $query_toggle = "UPDATE jadwal_periksa SET active = NOT active WHERE id = ?";
    $stmt_toggle = $conn->prepare($query_toggle);
    $stmt_toggle->bind_param("i", $schedule_id);

    if ($stmt_toggle->execute()) {
        $success_message = "Schedule status updated successfully.";
    } else {
        $error_message = "Failed to update schedule status. Please try again.";
    }
}

// Mengambil jadwal yang ada untuk dokter yang login
$query_schedules = "SELECT id, hari, jam_mulai, jam_selesai, active FROM jadwal_periksa WHERE id_dokter = ?";
$stmt = $conn->prepare($query_schedules);
$stmt->bind_param("i", $id_dokter);
$stmt->execute();
$schedules = $stmt->get_result();

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
    <title>Manage Schedule</title>
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
        footer {
            background: #1e2d3b;
            color: #fff;
        }
        footer a {
            color: #f39c12;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const startTimeInput = document.getElementById('jam_mulai');
            const endTimeInput = document.getElementById('jam_selesai');

            startTimeInput.addEventListener('change', () => {
                const startTime = new Date(`1970-01-01T${startTimeInput.value}:00`);
                const endTime = new Date(`1970-01-01T${endTimeInput.value}:00`);

                if (startTime >= endTime) {
                    alert("Start time must be earlier than end time.");
                }
            });
        });
    </script>
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
                            <h1 class="m-0"></h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="container">
                                <h2>Manage Schedule</h2>
                                <?php if (isset($success_message)) { ?>
                                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                                <?php } elseif (isset($error_message)) { ?>
                                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                                <?php } ?>
                                <form method="POST" class="mt-4">
                                    <div class="mb-3">
                                        <label for="hari" class="form-label">Day</label>
                                        <select class="form-select" id="hari" name="hari" required>
                                            <option value="">Select Day</option>
                                            <option value="Senin">Senin</option>
                                            <option value="Selasa">Selasa</option>
                                            <option value="Rabu">Rabu</option>
                                            <option value="Kamis">Kamis</option>
                                            <option value="Jumat">Jumat</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="jam_mulai" class="form-label">Start Time</label>
                                        <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                                    </div>
                                    <div class="mb-3">
                                    <label for="jam_selesai" class="form-label">End Time</label>
                                        <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                                    </div>
                                    <button type="submit" name="add_schedule" class="btn btn-primary">Add Schedule</button>
                                </form>

                                <h3 class="mt-5">Existing Schedules</h3>
                                <table class="table table-bordered mt-3">
                                    <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($schedule = $schedules->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($schedule['hari']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['jam_mulai']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['jam_selesai']); ?></td>
                                                <td>
                                                    <?php 
                                                    $status = isset($schedule['active']) && $schedule['active'] == 1 ? 'Active' : 'Inactive'; 
                                                    echo $status;
                                                    ?>
                                                </td>
                                                <td>
                                                    <form method="POST">
                                                        <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                                        <button type="submit" name="toggle_status" class="btn btn-warning">
                                                            <?php echo $status == 'Active' ? 'Deactivate' : 'Activate'; ?>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
