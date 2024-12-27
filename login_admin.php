<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'admin';
            header("Location: admin_dashboard.php");
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
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right, #4facfe, #00f2fe);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-family: 'Arial', sans-serif;
        }

        /* Background Animation */
        body:before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, #4facfe, #00f2fe, #004d7a);
            animation: move-bg 8s linear infinite;
            z-index: -1;
        }

        @keyframes move-bg {
            0% { transform: translate(0, 0); }
            50% { transform: translate(-50%, -50%); }
            100% { transform: translate(0, 0); }
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .login-card:before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            background: rgba(0, 77, 122, 0.1);
            width: 100px;
            height: 100px;
            border-radius: 50%;
        }

        .login-card h1 {
            color: #004d7a;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        .login-card .icon {
            font-size: 4rem;
            color: #00f2fe;
            margin-bottom: 20px;
            animation: bounce 1.5s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .login-card .form-control {
            border-radius: 30px;
            padding: 10px 20px;
            box-shadow: none;
            transition: all 0.3s ease-in-out;
        }

        .login-card .form-control:focus {
            box-shadow: 0 0 5px rgba(0, 77, 122, 0.3);
            border-color: #004d7a;
        }

        .login-card .btn {
            border-radius: 30px;
            padding: 10px 20px;
            font-weight: bold;
            font-size: 1rem;
            transition: background 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(to right, #004d7a, #007aa6);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #00395a, #005f84);
        }

        .login-card .alert {
            border-radius: 5px;
            margin-top: 10px;
        }

        .login-card .footer {
            margin-top: 20px;
            text-align: center;
        }

        .login-card .footer a {
            color: #004d7a;
            text-decoration: none;
            transition: color 0.3s;
        }

        .login-card .footer a:hover {
            color: #007aa6;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center">
            <i class="fas fa-user-shield icon"></i>
        </div>
        <h1>Admin Login</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"> <?php echo $error; ?> </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="footer">
            <a href="home.html">Back to Home</a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>