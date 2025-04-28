<?php
session_start();
require "../connect.php";

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
    <style>
        .navbar { 
            background-color: #D8AC9F !important;
            color: #5d4037 !important;
        }
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: 1px solid #D8AC9F;
            border-radius: 8px;
        }
        .card-header {
            background-color: #D8AC9F !important;
            color: #5d4037 !important;
            font-weight: bold;
        }
        .btn-update {
            background-color: #D8AC9F;
            border-color: #D8AC9F;
            color: #5d4037;
            font-weight: 500;
        }
        .btn-update:hover {
            background-color: #C28D7A;
            border-color: #C28D7A;
            color: #5d4037;
        }
        .btn-cancel {
            background-color: #f8f9fa;
            border-color: #D8AC9F;
            color: #5d4037;
            font-weight: 500;
        }
        .btn-cancel:hover {
            background-color: #C28D7A;
            border-color: #C28D7A;
            color: white;
        }
        .form-select:focus {
            border-color: #D8AC9F;
            box-shadow: 0 0 0 0.25rem rgba(216, 172, 159, 0.25);
        }
        .form-label {
            color: #5d4037;
            font-weight: 500;
        }
        .container {
            max-width: 600px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">Edit Order #<?= $order['id'] ?></h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-4">
                    <label for="status" class="form-label">Order Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-update me-md-2">Update Order</button>
                    <a href="listorder.php" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>