<?php
include 'config.php'; // Koneksi ke database

// Ambil data dokter
$query = $conn->query("SELECT dokter.*, poli.nama_poli FROM dokter LEFT JOIN poli ON dokter.id_poli = poli.id");

// Tambah data dokter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_dokter'])) {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $id_poli = $_POST['id_poli'];

    $insert = $conn->prepare("INSERT INTO dokter (nama, alamat, no_hp, id_poli) VALUES (?, ?, ?, ?)");
    $insert->bind_param("sssi", $nama, $alamat, $no_hp, $id_poli);
    $insert->execute();
    header("Location: manage_dokter.php");
    exit;
}

// Hapus dokter
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM dokter WHERE id = $id");
    header("Location: manage_dokter.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Dokter</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
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
                <img src="https://via.placeholder.com/150" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Admin Dashboard</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- User Panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://via.placeholder.com/150" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">Admin</a>
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
                            <a href="manage_dokter.php" class="nav-link active">
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
                            <h1 class="m-0">Manajemen Dokter</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addDoctorModal">Tambah Dokter</button>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Alamat</th>
                                <th>No HP</th>
                                <th>Poli</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $query->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= $row['nama'] ?></td>
                                    <td><?= $row['alamat'] ?></td>
                                    <td><?= $row['no_hp'] ?></td>
                                    <td><?= $row['nama_poli'] ?></td>
                                    <td>
                                        <a href="edit_dokter.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="manage_dokter.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Modal Tambah Dokter -->
        <div class="modal fade" id="addDoctorModal" tabindex="-1" aria-labelledby="addDoctorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addDoctorModalLabel">Tambah Dokter</h5>
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
                                <label for="no_hp" class="form-label">No HP</label>
                                <input type="text" name="no_hp" id="no_hp" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="id_poli" class="form-label">Poli</label>
                                <select name="id_poli" id="id_poli" class="form-select" required>
                                    <?php
                                    $poli = $conn->query("SELECT * FROM poli");
                                    while ($p = $poli->fetch_assoc()):
                                    ?>
                                        <option value="<?= $p['id'] ?>"><?= $p['nama_poli'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="add_dokter" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap 5 and AdminLTE JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
