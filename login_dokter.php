<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Ubah kueri untuk mengambil detail dokter dan menyertakan ID
        $stmt = $conn->prepare("SELECT id, username FROM dokter WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // mengambil data dokter (termasuk id_dokter)
            $doctor = $result->fetch_assoc();

            // menyimpan nama pengguna dan ID dokter dalam sesi
            $_SESSION['username'] = $username;
            $_SESSION['id_dokter'] = $doctor['id']; // menyimpan ID dokter dalam sesi
            $_SESSION['role'] = 'dokter'; // menyimpan role dokter dalam sesi

            // Mengalihkan ke dashboard dokter
            header("Location: dokter_dashboard.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokter Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(120deg, #3a7bd5, #3a6073);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
        }
        .login-card {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }
        .login-card h1 {
            color: #2c3e50;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
        }
        .login-card .btn-primary {
            background: linear-gradient(120deg, #ff9a00, #ff5200);
            border: none;
            transition: all 0.3s;
        }
        .login-card .btn-primary:hover {
            background: linear-gradient(120deg, #ff5200, #ff9a00);
        }
        .login-card .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(255, 82, 0, 0.25);
            border-color: #ff5200;
        }
        .login-card .alert {
            border-radius: 5px;
        }
        .login-card .icon {
            font-size: 60px;
            color: #ff5200;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="icon">
        <i class="fas fa-user-md"></i>
    </div>
    <h1>Dokter Login</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger text-center"> <?php echo $error; ?> </div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username Anda" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password Anda" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <div class="text-center mt-3">
        <a href="home.html" class="text-muted">Back to Home</a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
