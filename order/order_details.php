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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  <style>
    .navbar { 
      background-color: #D8AC9F !important;
      color: #5d4037 !important;
    }
    body {
      background-color: #f8f9fa;
      color: #5d4037;
    }
    .order-card {
      background: #fff;
      border: 1px solid #D8AC9F;
      padding: 25px;
      border-radius: 10px;
      margin-top: 30px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .card {
      border: 1px solid #D8AC9F;
      transition: transform 0.2s;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    .card-img-top {
      border-bottom: 1px solid #D8AC9F;
    }
    .product-item {
      border-bottom: 1px solid rgba(216, 172, 159, 0.3);
      padding: 15px 0;
    }
    .product-item:last-child {
      border-bottom: none;
    }
    .status-badge {
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
      text-transform: capitalize;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-processing { background: #cce5ff; color: #004085; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
    .btn-secondary {
      background-color: #f8f9fa;
      border-color: #D8AC9F;
      color: #5d4037;
      font-weight: 500;
    }
    .btn-secondary:hover {
      background-color: #C28D7A;
      border-color: #C28D7A;
      color: white;
    }
    h3, h5 {
      color: #5d4037;
      font-weight: 600;
    }
    hr {
      border-color: #D8AC9F;
      opacity: 0.5;
    }
    strong {
      color: #5d4037;
    }
    .text-muted {
      color: #8d6e63 !important;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="order-card">
    <h3><i class="fas fa-receipt me-2"></i>Order #<?= $order['id'] ?> Details</h3>
    <hr>

    <div class="row mb-4">
      <div class="col-md-6">
        <h5><i class="fas fa-user me-2"></i>Customer Info</h5>
        <p><strong>Name:</strong> <?= htmlspecialchars($order['user_name'] ?? 'Guest') ?></p>
        <p><strong>Order Date:</strong> <?= date('M d, Y H:i', strtotime($order['created_at'])) ?></p>
        <?php if (!empty($order['notes'])): ?>
          <p><strong>Notes:</strong> <?= htmlspecialchars($order['notes']) ?></p>
        <?php endif; ?>
      </div>
      <div class="col-md-6">
        <h5><i class="fas fa-clipboard-list me-2"></i>Order Summary</h5>
        <p><strong>Total:</strong> <strong>₱<?= number_format($order['total'], 2) ?></strong></p>
        <p>
          <strong>Status:</strong> 
          <span class="status-badge status-<?= $order['status'] ?>">
            <?= ucfirst($order['status']) ?>
          </span>
        </p>
      </div>
    </div>

    <h5><i class="fas fa-box-open me-2"></i>Ordered Products</h5>
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
              <p class="card-text small text-muted mt-auto"><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <a href="listorder.php" class="btn btn-secondary mt-2"><i class="fas fa-arrow-left me-2"></i>Back to Orders</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>