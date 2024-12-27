<?php
include 'config.php';
session_start();

// login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username and Password are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM pasien WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // menyimpan detail pengguna dalam sesi
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id']; // Ensure 'id' is the primary key in your table
            $_SESSION['role'] = 'pasien';

            // mengarahkan ke dashboard pasien
            header("Location: pasien_dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}

// registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_pasien'])) {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_ktp = $_POST['no_ktp'];
    $no_hp = $_POST['no_hp'];

    if (empty($nama) || empty($alamat) || empty($no_ktp) || empty($no_hp)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM pasien WHERE no_ktp = ?");
        $stmt->bind_param("s", $no_ktp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Patient with this KTP already exists.";
        } else {
            $month_year = date('Ym');
            $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pasien WHERE LEFT(no_rm, 6) = ?");
            $stmt->bind_param("s", $month_year);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $no_rm = $month_year . '-' . str_pad($row['total'] + 1, 3, '0', STR_PAD_LEFT);

            $username = strtolower(str_replace(' ', '', $nama));
            $password = strtolower(str_replace(' ', '', $alamat));
            $stmt = $conn->prepare("INSERT INTO pasien (nama, alamat, no_ktp, no_hp, no_rm, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $nama, $alamat, $no_ktp, $no_hp, $no_rm, $username, $password);
            $stmt->execute();

            header("Location: login_pasien.php");
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
    <title>Login Pasien</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right, #83a4d4, #b6fbff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, #83a4d4, #b6fbff, #004d7a);
            animation: rotateBg 10s linear infinite;
            z-index: -1;
        }

        @keyframes rotateBg {
            0% { transform: rotate(0); }
            100% { transform: rotate(360deg); }
        }

        .login-card {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .login-card h1 {
            color: #004d7a;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .login-card .btn-success {
            background: linear-gradient(to right, #00b09b, #96c93d);
            border: none;
            transition: all 0.3s;
        }

        .login-card .btn-success:hover {
            background: linear-gradient(to right, #008a6e, #78a92d);
        }

        .login-card .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 77, 122, 0.25);
            border-color: #004d7a;
        }

        .login-card a {
            text-decoration: none;
            color: #004d7a;
            transition: color 0.3s;
        }

        .login-card a:hover {
            color: #006494;
        }

        .modal-content {
            border-radius: 15px;
            padding: 20px;
        }

        .modal-header {
            background: linear-gradient(to right, #004d7a, #007aa6);
            color: #ffffff;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .modal-header h5 {
            font-weight: bold;
        }

        .modal-header .btn-close {
            color: #ffffff;
        }

        .modal-body .btn-primary {
            background: linear-gradient(to right, #004d7a, #007aa6);
            border: none;
        }

        .modal-body .btn-primary:hover {
            background: linear-gradient(to right, #00395a, #005f84);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1><i class="fas fa-user-injured"></i> Pasien Login</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"> <?php echo $error; ?> </div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="role" value="pasien">
            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-success w-100"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
        <div class="text-center mt-3">
            <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal"><i class="fas fa-user-plus"></i> Register as Pasien</a>
        </div>
        <div class="text-center mt-3">
            <a href="home.html"><i class="fas fa-arrow-left"></i> Back to Home</a>
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
