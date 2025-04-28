<?php
include "../connect.php";
session_start();


if (!isset($_SESSION['user_id'] ) || $_SESSION['user_role'] != 'admin') {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page.';
    header('Location: ../shared/login.php');
    exit();
}
$user_id = $_GET['id'] ?? null;


if (!$user_id) {
    echo "No user selected.";
    exit;
}

$user_query = "SELECT name FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit;
}


$orders_query = "SELECT id, created_at, total 
                 FROM orders 
                 WHERE user_id = $user_id 
                 AND status = 'completed'
                 ORDER BY created_at DESC";
$orders_result = $conn->query($orders_query);

?>
<?php include "../shared/navbar.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Completed Orders for <?= htmlspecialchars($user['name']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <style>
        .navbar { background-color: bisque !important; }
        body {
            padding: 20px;
            background-color: #f5f5f5;
        }
        .card {
            margin-bottom: 20px;
        }
        .card-header {
            background-color: bisque;
            color: black;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Completed Orders for <?= htmlspecialchars($user['name']); ?></h1>

    <?php if ($orders_result->num_rows > 0): ?>
        <?php while ($order = $orders_result->fetch_assoc()): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Order #<?= $order['id']; ?> - <?= $order['created_at']; ?> - Total: <?= number_format($order['total'], 2); ?> EGP</h5>
                </div>
                <div class="card-body">
                    <h6>Products:</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $order_id = $order['id'];
                        $products_query = "SELECT p.name, p.price, oi.quantity 
                                           FROM order_products oi 
                                           JOIN products p ON oi.product_id = p.id 
                                           WHERE oi.order_id = $order_id";
                        $products_result = $conn->query($products_query);

                        $order_subtotal = 0;

                        while ($product = $products_result->fetch_assoc()): 
                            $subtotal = $product['price'] * $product['quantity'];
                            $order_subtotal += $subtotal;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($product['name']); ?></td>
                                <td><?= $product['quantity']; ?></td>
                                <td><?= number_format($product['price'], 2); ?> EGP</td>
                                <td><?= number_format($subtotal, 2); ?> EGP</td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>

                    <div class="text-end fw-bold">
                        Order Total: <?= number_format($order['total'], 2); ?> EGP
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="alert alert-warning text-center">No completed orders for this user.</p>
    <?php endif; ?>

</div>

</body>
</html>
