<?php
include 'config.php'; // Koneksi ke database

// Ambil data poli
$query = $conn->query("SELECT * FROM poli");

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "admin1";


// Tambah data poli
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_poli'])) {
    $nama_poli = $_POST['nama_poli'];
    $keterangan = $_POST['keterangan'];

    $insert = $conn->prepare("INSERT INTO poli (nama_poli, keterangan) VALUES (?, ?)");
    $insert->bind_param("ss", $nama_poli, $keterangan);
    $insert->execute();
    header("Location: manage_poli.php");
    exit;
}

// Hapus poli
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Cek apakah poli masih digunakan di tabel dokter
    $check = $conn->query("SELECT COUNT(*) AS count FROM dokter WHERE id_poli = $id");
    $result = $check->fetch_assoc();

    if ($result['count'] > 0) {
        // Jika masih ada data terkait di tabel dokter
        echo "<script>
            alert('Tidak dapat menghapus poli karena masih digunakan oleh data dokter.');
            window.location.href = 'manage_poli.php';
        </script>";
    } else {
        // Jika tidak ada data terkait, hapus poli
        $conn->query("DELETE FROM poli WHERE id = $id");
        header("Location: manage_poli.php");
        exit;
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
    <title>Manajemen Poli</title>
    <!-- Bootstrap and AdminLTE CSS -->
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
        .nav-link:hover, .nav-link.active {
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
                            <a href="manage_poli.php" class="nav-link active">
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
                            <h1 class="m-0">Manajemen Poli</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addPoliModal">Tambah Poli</button>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Poli</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $query->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= $row['nama_poli'] ?></td>
                                    <td><?= $row['keterangan'] ?></td>
                                    <td>
                                        <a href="edit_poli.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="manage_poli.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- Modal Tambah Poli -->
    <div class="modal fade" id="addPoliModal" tabindex="-1" aria-labelledby="addPoliModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPoliModalLabel">Tambah Poli</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama_poli" class="form-label">Nama Poli</label>
                            <input type="text" name="nama_poli" id="nama_poli" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_poli" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 and AdminLTE JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
