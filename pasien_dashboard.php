<?php
// Start the session
session_start();

// memastikan user login sebagai pasien
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pasien') {
    header("Location: login_pasien.php");
    exit;
}

// memastikan user_id ada di sesi
if (!isset($_SESSION['user_id'])) {
    header("Location: login_pasien.php");
    exit;
}

// ambil ID pasien dari sesi
$id_pasien = $_SESSION['user_id'];

// mengambil nama pengguna dari sesi dengan aman
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Pasien";

// koneksi ke database
include('config.php');

// logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: home.html");
    exit;
}

// ambil Poli dan Dokter yang tersedia
$query_poli = "SELECT * FROM poli";
$result_poli = $conn->query($query_poli);

$query_dokter = "SELECT dokter.*, poli.nama_poli FROM dokter JOIN poli ON dokter.id_poli = poli.id";
$result_dokter = $conn->query($query_dokter);

// ambil jadwal yang aktif saja
$query_jadwal = "SELECT jadwal_periksa.*, dokter.nama AS nama_dokter, poli.nama_poli 
                 FROM jadwal_periksa 
                 JOIN dokter ON jadwal_periksa.id_dokter = dokter.id 
                 JOIN poli ON dokter.id_poli = poli.id
                 WHERE jadwal_periksa.active = 1";
$result_jadwal = $conn->query($query_jadwal);

// Poli Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_poli'])) {
    // dapatkan data form dengan aman
    $id_jadwal = $_POST['id_jadwal'];
    $keluhan = htmlspecialchars($_POST['keluhan']);

    // membuat nomor antrian baru
    $query_antrian = "SELECT MAX(no_antrian) AS max_antrian FROM daftar_poli WHERE id_jadwal = ?";
    $stmt = $conn->prepare($query_antrian);
    $stmt->bind_param("i", $id_jadwal);
    $stmt->execute();
    $result_antrian = $stmt->get_result()->fetch_assoc();
    $no_antrian = $result_antrian['max_antrian'] + 1;

    // masukkan untuk registrasi
    $query_register = "INSERT INTO daftar_poli (id_pasien, id_jadwal, keluhan, no_antrian) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query_register);
    $stmt->bind_param("iisi", $id_pasien, $id_jadwal, $keluhan, $no_antrian);

    if ($stmt->execute()) {
        $success_message = "You have successfully registered to Poli with Queue Number: " . $no_antrian;
    } else {
        $error_message = "Failed to register. Please try again.";
    }
}

// mengambil pendaftaran Poli untuk pasien
$query_registrations = "
    SELECT dp.id AS id_daftar_poli, dp.tgl_daftar, dp.keluhan, dp.no_antrian, 
           per.id AS periksa_id, per.tgl_periksa, per.catatan, per.biaya_periksa, 
           dok.nama AS nama_dokter, 
           GROUP_CONCAT(obat.nama_obat SEPARATOR ', ') AS nama_obat,
           GROUP_CONCAT(obat.harga SEPARATOR ', ') AS harga_obat
    FROM daftar_poli dp
    LEFT JOIN periksa per ON dp.id = per.id_daftar_poli
    LEFT JOIN jadwal_periksa jp ON dp.id_jadwal = jp.id
    LEFT JOIN dokter dok ON jp.id_dokter = dok.id
    LEFT JOIN detail_periksa dpk ON per.id = dpk.id_periksa
    LEFT JOIN obat ON dpk.id_obat = obat.id
    WHERE dp.id_pasien = ? 
    GROUP BY dp.id
    ORDER BY dp.tgl_daftar DESC";

$stmt = $conn->prepare($query_registrations);
$stmt->bind_param("i", $id_pasien);
$stmt->execute();
$poli_registrations = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
    <style>
        .status-belum {
            color: #fff;
            background-color: #dc3545;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .status-sudah {
            color: #fff;
            background-color: #28a745;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .modal-header.bg-primary {
        background-color: #007bff;
        color: #fff;
        border-bottom: 2px solid #0056b3;
        }
        .modal-body h6 {
            font-weight: bold;
            color: #343a40;
            margin-bottom: 5px;
        }
        .modal-body p.text-muted {
            font-size: 1rem;
            margin: 0;
            color: #6c757d;
        }
        .modal-footer button {
            font-size: 0.9rem;
        }

    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="#" class="brand-link">
                <span class="brand-text font-weight-light">Patient Dashboard</span>
            </a>
            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://via.placeholder.com/150" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block"><?php echo $username; ?></a>
                    </div>
                </div>
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
            <section class="content">
                <div class="container-fluid">
                    <?php if (isset($success_message)) { ?>
                        <div class="alert alert-success"> <?php echo $success_message; ?> </div>
                    <?php } elseif (isset($error_message)) { ?>
                        <div class="alert alert-danger"> <?php echo $error_message; ?> </div>
                    <?php } ?>
                    <div class="card mb-4">
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
                                        <?php if ($result_jadwal->num_rows > 0) { ?>
                                            <?php while ($row = $result_jadwal->fetch_assoc()) { ?>
                                                <option value="<?php echo $row['id']; ?>">
                                                    <?php echo $row['nama_poli'] . " - " . $row['nama_dokter'] . " (" . $row['hari'] . ", " . $row['jam_mulai'] . " - " . $row['jam_selesai'] . ")"; ?>
                                                </option>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <option disabled>No active schedules available</option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <button type="submit" name="register_poli" class="btn btn-primary">Register</button>
                            </form>
                        </div>
                    </div>
                    <div class="card">
            <div class="card-header bg-primary text-white">Poli Registrations</div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Daftar</th>
                            <th>Keluhan</th>
                            <th>No Antrian</th>
                            <th>Status Pemeriksaan</th>
                            <th>Hasil</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($poli_registrations->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = $poli_registrations->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['tgl_daftar']); ?></td>
                                    <td><?php echo htmlspecialchars($row['keluhan']); ?></td>
                                    <td><?php echo htmlspecialchars($row['no_antrian']); ?></td>
                                    <td>
                                        <?php if ($row['periksa_id']): ?>
                                            <span class="status-sudah">Sudah Diperiksa</span>
                                        <?php else: ?>
                                            <span class="status-belum">Belum Diperiksa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['periksa_id']): ?>
                                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#hasilModal<?php echo $row['periksa_id']; ?>">Hasil</button>
                                            <div class="modal fade" id="hasilModal<?php echo $row['periksa_id']; ?>" tabindex="-1" aria-labelledby="hasilModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-primary text-white">
                                                            <h5 class="modal-title" id="hasilModalLabel">
                                                                <i class="fas fa-file-medical-alt"></i> Hasil Pemeriksaan
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <p><strong>Nama Dokter:</strong> <?php echo htmlspecialchars($row['nama_dokter']); ?></p>
                                                                    <p><strong>Catatan Pemeriksaan:</strong> <?php echo htmlspecialchars($row['catatan']); ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p><strong>Biaya Pemeriksaan:</strong> Rp<?php echo number_format($row['biaya_periksa'], 0, ',', '.'); ?></p>
                                                                    <p><strong>Harga Obat:</strong> Rp<?php echo number_format($row['harga_obat'], 0, ',', '.'); ?></p>
                                                                </div>
                                                            </div>
                                                            <p><strong>Obat yang diberikan:</strong> <?php echo htmlspecialchars($row['nama_obat']); ?></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                <i class="fas fa-times"></i> Close
                                                            </button>
                                                            <button type="button" class="btn btn-success">
                                                                <i class="fas fa-print"></i> Print Result
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada pendaftaran poli.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
