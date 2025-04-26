<?php
session_start();
require "../connect.php";

if (!isset($_GET['id'])) {
    die('Order ID not provided.');
}

$orderId = (int)$_GET['id'];


$orderSql = "SELECT o.*, u.name AS user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = $orderId";
$orderResult = mysqli_query($conn, $orderSql);

if (!$orderResult || mysqli_num_rows($orderResult) == 0) {
    die('Order not found.');
}

$order = mysqli_fetch_assoc($orderResult);

$productSql = "SELECT p.*, c.name AS category_name, op.quantity 
               FROM order_products op 
               JOIN products p ON op.product_id = p.id 
               LEFT JOIN categories c ON p.category_id = c.id
               WHERE op.order_id = $orderId";
$productResult = mysqli_query($conn, $productSql);
$products = [];

if ($productResult) {
    while ($productRow = mysqli_fetch_assoc($productResult)) {
        $products[] = $productRow;
    }
}
?>

<?php include "../shared/navbar.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #5d4037;
      --secondary-color: #2ecc71;
      --danger-color: #e74c3c;
    }
    body {
      background-color: #f8f9fa;
    }
    .order-card {
        
      background: #fff;
      border: 1px solid #ddd;
      padding: 20px;
      border-radius: 10px;
      margin-top: 30px;
    }
    .product-item {
      border-bottom: 1px solid #eee;
      padding: 15px 0;
    }
    .product-item:last-child {
      border-bottom: none;
    }
    .product-img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 10px;
      border: 1px solid #ddd;
      margin-right: 15px;
    }
    .status-badge {
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      text-transform: capitalize;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-processing { background: #cce5ff; color: #004085; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body>

<div class="container">
  <div class="order-card">
    <h3>Order #<?= $order['id'] ?> Details</h3>
    <hr>

    <div class="row mb-4">
      <div class="col-md-6">
        <h5>Customer Info</h5>
        <p><strong>Name:</strong> <?= htmlspecialchars($order['user_name'] ?? 'Guest') ?></p>
        <p><strong>Order Date:</strong> <?= date('M d, Y H:i', strtotime($order['created_at'])) ?></p>

      </div>
      <div class="col-md-6">
        <h5>Order Summary</h5>
        <p><strong>Total:</strong> <strong>₱<?= number_format($order['total'], 2) ?></strong></p>
        <p>
          <strong>Status:</strong> 
          <span class="status-badge status-<?= $order['status'] ?>">
            <?= ucfirst($order['status']) ?>
          </span>
        </p>
      </div>
    </div>

    <h5>Ordered Products</h5>
    <div class="row g-3 mb-4">
    <?php foreach ($products as $product): ?>
        <div class="col-md-4">
        <div class="card h-100">
            <img src="../images/product/<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="height: 200px; object-fit: cover;">
            <div class="card-body d-flex flex-column">
            <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
            <p class="card-text mb-1"><strong>Category:</strong> <?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></p>
            <p class="card-text mb-1"><strong>Price:</strong> ₱<?= number_format($product['price'], 2) ?></p>
            <p class="card-text mb-1"><strong>Quantity:</strong> <?= $product['quantity'] ?> pcs</p>
            <p class="card-text mb-2"><strong>Subtotal:</strong> ₱<?= number_format($product['price'] * $product['quantity'], 2) ?></p>
            <p class="card-text small text-muted"><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</p>
            </div>
        </div>
        </div>
    <?php endforeach; ?>
    </div>
    </div>

    <a href="listorder.php" class="btn btn-secondary mt-2"><i class="fas fa-arrow-left"></i> Back to Orders</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
