<?php
include 'config.php';
session_start();

// Handle form submission for login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($username) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        $table = ($role === 'admin') ? 'admin' : (($role === 'pasien') ? 'pasien' : 'dokter');
        
        // Prepare SQL statement
        $stmt = $conn->prepare("SELECT * FROM $table WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role === 'admin') {
                header("Location: admin_dashboard.php");
                exit;
            } elseif ($role === 'pasien') {
                header("Location: pasien_dashboard.php");
                exit;
            } elseif ($role === 'dokter') {
                header("Location: dokter_dashboard.php");
                exit;
            }
        } else {
            $error = "Invalid credentials.";
        }
    }
}

// Handle registration form submission for pasien
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_pasien'])) {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_ktp = $_POST['no_ktp'];
    $no_hp = $_POST['no_hp'];

    if (empty($nama) || empty($alamat) || empty($no_ktp) || empty($no_hp)) {
        $error = "All fields are required.";
    } else {
        // Check if patient already exists based on KTP
        $stmt = $conn->prepare("SELECT * FROM pasien WHERE no_ktp = ?");
        $stmt->bind_param("s", $no_ktp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Patient with this KTP already exists.";
        } else {
            // Generate No RM
            $month_year = date('Ym');
            $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pasien WHERE LEFT(no_rm, 6) = ?");
            $stmt->bind_param("s", $month_year);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $no_rm = $month_year . '-' . str_pad($row['total'] + 1, 3, '0', STR_PAD_LEFT);

            // Insert new patient into the database
            $username = strtolower(str_replace(' ', '', $nama)); // Generate username from name
            $password = strtolower(str_replace(' ', '', $alamat)); // Generate password from address
            $stmt = $conn->prepare("INSERT INTO pasien (nama, alamat, no_ktp, no_hp, no_rm, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $nama, $alamat, $no_ktp, $no_hp, $no_rm, $username, $password);
            $stmt->execute();

            // Redirect to login page after successful registration
            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Polyclinic Login</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger text-center"> <?php echo $error; ?> </div>
    <?php endif; ?>
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center">Admin Login</h5>
                    <form method="POST">
                        <input type="hidden" name="role" value="admin">
                        <div class="mb-3">
                            <label for="adminUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="adminUsername" name="username">
                        </div>
                        <div class="mb-3">
                            <label for="adminPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="adminPassword" name="password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center">Pasien Login</h5>
                    <form method="POST">
                        <input type="hidden" name="role" value="pasien">
                        <div class="mb-3">
                            <label for="pasienUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="pasienUsername" name="username">
                        </div>
                        <div class="mb-3">
                            <label for="pasienPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="pasienPassword" name="password">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Login</button>
                    </form>
                    <a href="#" class="mt-3 d-block text-center" data-bs-toggle="modal" data-bs-target="#registerModal">Register as Pasien</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center">Dokter Login</h5>
                    <form method="POST">
                        <input type="hidden" name="role" value="dokter">
                        <div class="mb-3">
                            <label for="dokterUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="dokterUsername" name="username">
                        </div>
                        <div class="mb-3">
                            <label for="dokterPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="dokterPassword" name="password">
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Registration -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registerModalLabel">Patient Registration</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
            <input type="hidden" name="register_pasien" value="1">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <input type="text" class="form-control" id="alamat" name="alamat" required>
            </div>
            <div class="mb-3">
                <label for="no_ktp" class="form-label">No KTP</label>
                <input type="text" class="form-control" id="no_ktp" name="no_ktp" required>
            </div>
            <div class="mb-3">
                <label for="no_hp" class="form-label">No HP</label>
                <input type="text" class="form-control" id="no_hp" name="no_hp" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
