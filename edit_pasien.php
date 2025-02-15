<?php
include 'config.php'; // Koneksi ke database

// Ambil data pasien berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = $conn->query("SELECT * FROM pasien WHERE id = $id");
    $pasien = $query->fetch_assoc();
}

// Edit pasien
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_pasien'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_ktp = $_POST['no_ktp'];
    $no_hp = $_POST['no_hp'];
    $no_rm = $_POST['no_rm'];

    $update = $conn->prepare("UPDATE pasien SET nama = ?, alamat = ?, no_ktp = ?, no_hp = ?, no_rm = ? WHERE id = ?");
    $update->bind_param("sssssi", $nama, $alamat, $no_ktp, $no_hp, $no_rm, $id);
    $update->execute();
    header("Location: manage_pasien.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pasien</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="#" class="brand-link">
                <span class="brand-text font-weight-light">Admin Dashboard</span>
            </a>
            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://via.placeholder.com/150" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">Admin</a>
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
                            <h1 class="m-0">Edit Pasien</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Data Pasien</h5>
                            <a href="manage_pasien.php" class="btn-close" aria-label="Close"></a>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <input type="hidden" name="id" value="<?= $pasien['id'] ?>">
                                <label for="edit_nama" class="form-label">Nama</label>
                                <input type="text" name="nama" id="edit_nama" class="form-control" value="<?= $pasien['nama'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_alamat" class="form-label">Alamat</label>
                                <input type="text" name="alamat" id="edit_alamat" class="form-control" value="<?= $pasien['alamat'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_no_ktp" class="form-label">No KTP</label>
                                <input type="text" name="no_ktp" id="edit_no_ktp" class="form-control" value="<?= $pasien['no_ktp'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_no_hp" class="form-label">No HP</label>
                                <input type="text" name="no_hp" id="edit_no_hp" class="form-control" value="<?= $pasien['no_hp'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_no_rm" class="form-label">No RM</label>
                                <input type="text" name="no_rm" id="edit_no_rm" class="form-control" value="<?= $pasien['no_rm'] ?>" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="edit_pasien" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <!-- Bootstrap 5 and AdminLTE JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
