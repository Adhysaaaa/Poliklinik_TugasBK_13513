<?php
include 'config.php'; // Koneksi ke database

// ambil data pasien
$query = $conn->query("SELECT * FROM pasien");

// tambah data pasien
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_pasien'])) {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_ktp = $_POST['no_ktp'];
    $no_hp = $_POST['no_hp'];

    // cek apakah pasien sudah ada
    $check = $conn->prepare("SELECT * FROM pasien WHERE no_ktp = ?");
    $check->bind_param("s", $no_ktp);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Pasien dengan No KTP ini sudah terdaftar.');</script>";
    } else {
        // Generate No RM
        $year_month = date("Ym");
        $count = $conn->query("SELECT COUNT(*) AS total FROM pasien WHERE no_rm LIKE '$year_month%'")->fetch_assoc()['total'];
        $no_rm = $year_month . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        $insert = $conn->prepare("INSERT INTO pasien (nama, alamat, no_ktp, no_hp, no_rm) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("sssss", $nama, $alamat, $no_ktp, $no_hp, $no_rm);
        $insert->execute();
        header("Location: manage_pasien.php");
        exit;
    }
}

// Hapus pasien
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM pasien WHERE id = $id");
    header("Location: manage_pasien.php");
    exit;
}

// mengambil pengguna sesi dan memeriksa status admin
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: home.html");
    exit;
}
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pasien</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
    <style>
        body {
            background: #f8f9fa;
        }

        table {
        background-color: #ffffff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    th {
        background-color: #006494;
        color: #ffffff;
        text-align: left;
        padding: 12px;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
        color: #495057;
    }

    tr:hover {
        background-color: #f1f8ff;
        transition: background-color 0.3s;
    }

    .btn {
        font-size: 0.9rem;
        padding: 5px 10px;
        border-radius: 5px;
    }

    .btn-warning {
        background-color: #f1c40f;
        color: #ffffff;
        border: none;
    }

    .btn-warning:hover {
        background-color: #d4ac0d;
    }

    .btn-danger {
        background-color: #e74c3c;
        color: #ffffff;
        border: none;
    }

    .btn-danger:hover {
        background-color: #c0392b;
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
        .nav-link.active {
            background: #006494;
            color: #fff;
        }
        .nav-link:hover {
            background: #006494;
            color: #fff;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="#" class="brand-link">
                <span class="brand-text">Admin Dashboard</span>
            </a>
            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="1.jpg" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block"><?php echo $username; ?></a>
                    </div>
                </div>
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
                            <a href="manage_pasien.php" class="nav-link active">
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
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Manajemen Pasien</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
    <div class="container-fluid">
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addPatientModal">Tambah Pasien</button>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>No KTP</th>
                    <th>No HP</th>
                    <th>No RM</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $query->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['nama'] ?></td>
                        <td><?= $row['alamat'] ?></td>
                        <td><?= $row['no_ktp'] ?></td>
                        <td><?= $row['no_hp'] ?></td>
                        <td><?= $row['no_rm'] ?></td>
                        <td>
                            <a href="edit_pasien.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="manage_pasien.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>
        </div>

        <!-- Modal Tambah Pasien -->
        <div class="modal fade" id="addPatientModal" tabindex="-1" aria-labelledby="addPatientModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addPatientModalLabel">Tambah Pasien</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" name="nama" id="nama" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <input type="text" name="alamat" id="alamat" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="no_ktp" class="form-label">No KTP</label>
                                <input type="text" name="no_ktp" id="no_ktp" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="no_hp" class="form-label">No HP</label>
                                <input type="text" name="no_hp" id="no_hp" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="add_pasien" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
