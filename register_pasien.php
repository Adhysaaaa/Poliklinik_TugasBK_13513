<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_ktp = $_POST['no_ktp'];
    $no_hp = $_POST['no_hp'];

    if (empty($nama) || empty($alamat) || empty($no_ktp) || empty($no_hp)) {
        $error = "All fields are required.";
    } else {
        // Check if patient already exists
        $stmt = $conn->prepare("SELECT * FROM pasien WHERE no_ktp = ?");
        $stmt->bind_param("s", $no_ktp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Patient with this KTP number is already registered.";
        } else {
            // Generate No RM
            $currentYearMonth = date("Ym");
            $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pasien WHERE no_rm LIKE ?");
            $likePattern = $currentYearMonth . '%';
            $stmt->bind_param("s", $likePattern);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['total'];
            $newNoRM = $currentYearMonth . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

            // Insert new patient into database
            $stmt = $conn->prepare("INSERT INTO pasien (no_rm, nama, alamat, no_ktp, no_hp) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $newNoRM, $nama, $alamat, $no_ktp, $no_hp);
            if ($stmt->execute()) {
                $success = "Patient registered successfully with No RM: $newNoRM.";
            } else {
                $error = "Failed to register patient. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Patient</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Register New Patient</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger text-center"> <?php echo $error; ?> </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success text-center"> <?php echo $success; ?> </div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="nama" class="form-label">Name</label>
            <input type="text" class="form-control" id="nama" name="nama" required>
        </div>
        <div class="mb-3">
            <label for="alamat" class="form-label">Address</label>
            <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="no_ktp" class="form-label">KTP Number</label>
            <input type="text" class="form-control" id="no_ktp" name="no_ktp" required>
        </div>
        <div class="mb-3">
            <label for="no_hp" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="no_hp" name="no_hp" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
</div>
</body>
</html>
