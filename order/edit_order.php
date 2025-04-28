<?php
session_start();
require "../connect.php";
if (!isset($_SESSION['user_id'] ) || $_SESSION['user_role'] != 'admin') {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page.';
    header('Location: ../shared/login.php');
    exit();
}

if (!isset($_GET['id'])) {
    die("Order ID missing!");
}
$orderId = intval($_GET['id']);


$sql = "SELECT * FROM orders WHERE id = $orderId";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Order not found!");
}

$order = mysqli_fetch_assoc($result);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newStatus = mysqli_real_escape_string($conn, $_POST['status']);
    
    $updateSql = "UPDATE orders SET status = '$newStatus' WHERE id = $orderId";
    if (mysqli_query($conn, $updateSql)) {
        header("Location: listorder.php?success=1");
        exit();
    } else {
        die("Failed to update order: " . mysqli_error($conn));
    }
}
?>

<?php include "../shared/navbar.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Order #<?= $order['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Edit Order #<?= $order['id'] ?></h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="status" class="form-label">Order Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Update Order</button>
                <a href="listorder.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
