<?php
include 'config.php'; // Koneksi ke database

// Ambil data obat berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = $conn->prepare("SELECT * FROM obat WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $obat = $result->fetch_assoc();
    
    // Jika obat tidak ditemukan
    if (!$obat) {
        header("Location: manage_obat.php");
        exit;
    }
}

// Update data obat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_obat'])) {
    $nama_obat = $_POST['nama_obat'];
    $kemasan = $_POST['kemasan'];
    $harga = $_POST['harga'];

    $update = $conn->prepare("UPDATE obat SET nama_obat = ?, kemasan = ?, harga = ? WHERE id = ?");
    $update->bind_param("ssii", $nama_obat, $kemasan, $harga, $id);
    $update->execute();
    header("Location: manage_obat.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Obat</title>
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
                            <a href="manage_poli.php" class="nav-link">
                                <i class="nav-icon fas fa-hospital"></i>
                                <p>Manage Poli</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_obat.php" class="nav-link active">
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
                            <h1 class="m-0">Edit Obat</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nama_obat" class="form-label">Nama Obat</label>
                            <input type="text" name="nama_obat" id="nama_obat" class="form-control" value="<?= htmlspecialchars($obat['nama_obat']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="kemasan" class="form-label">Kemasan</label>
                            <input type="text" name="kemasan" id="kemasan" class="form-control" value="<?= htmlspecialchars($obat['kemasan']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="harga" class="form-label">Harga</label>
                            <input type="number" name="harga" id="harga" class="form-control" value="<?= htmlspecialchars($obat['harga']) ?>" required>
                        </div>
                        <button type="submit" name="update_obat" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </section>
        </div>

    </div>

    <!-- Bootstrap 5 and AdminLTE JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
