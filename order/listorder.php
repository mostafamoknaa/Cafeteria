<?php
session_start();
require "../connect.php";

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$orders = [];
$user_id = $_SESSION['user_id'] ?? null;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$sql = "SELECT o.*, u.name AS user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE 1=1";
if ($startDate) {
    $sql .= " AND o.created_at >= '$startDate 00:00:00'";
}
if ($endDate) {
    $sql .= " AND o.created_at <= '$endDate 23:59:59'";
}
if ($status) {
    $sql .= " AND o.status = '$status'";
}
$sql .= " ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orderId = $row['id'];
        $productSql = "SELECT p.*, op.quantity FROM order_products op JOIN products p ON op.product_id = p.id WHERE op.order_id = $orderId";
        $productResult = mysqli_query($conn, $productSql);
        $products = [];
        if ($productResult) {
            while ($productRow = mysqli_fetch_assoc($productResult)) {
                $products[] = $productRow;
            }
        }
        $row['products'] = $products;
        $orders[] = $row;
    }
} else {
    die("Query failed: " . mysqli_error($conn));
}

// Get total orders for pagination
$countSql = "SELECT COUNT(*) as total FROM orders o WHERE 1=1";
if ($startDate) {
    $countSql .= " AND o.created_at >= '$startDate 00:00:00'";
}
if ($endDate) {
    $countSql .= " AND o.created_at <= '$endDate 23:59:59'";
}
if ($status) {
    $countSql .= " AND o.status = '$status'";
}
$countResult = mysqli_query($conn, $countSql);
$totalOrders = mysqli_fetch_assoc($countResult)['total'] ?? 0;
$totalPages = ceil($totalOrders / $limit);
?>

<?php include "../shared/navbar.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order List</title>
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
    .card {
      border: 1px solid #D8AC9F;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .card-header {
      background-color: #D8AC9F !important;
      color: #5d4037 !important;
      font-weight: bold;
      border-bottom: 1px solid #C28D7A;
    }
    .btn-primary {
      background-color: #D8AC9F;
      border-color: #D8AC9F;
      color: #5d4037;
      font-weight: 500;
    }
    .btn-primary:hover {
      background-color: #C28D7A;
      border-color: #C28D7A;
      color: #5d4037;
    }
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
    .btn-info {
      background-color: #D8AC9F;
      border-color: #D8AC9F;
      color: #5d4037;
    }
    .btn-info:hover {
      background-color: #C28D7A;
      border-color: #C28D7A;
      color: #5d4037;
    }
    .btn-warning {
      background-color: #f8f9fa;
      border-color: #D8AC9F;
      color: #5d4037;
    }
    .btn-warning:hover {
      background-color: #C28D7A;
      border-color: #C28D7A;
      color: white;
    }
    .status-badge {
      padding: 0.35rem 0.65rem;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 500;
      text-transform: capitalize;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-processing { background: #cce5ff; color: #004085; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
    .table {
      border-color: #D8AC9F;
    }
    .table thead th {
      background-color: #D8AC9F;
      color: #5d4037;
      border-color: #C28D7A;
    }
    .table-striped tbody tr:nth-of-type(odd) {
      background-color: rgba(216, 172, 159, 0.1);
    }
    .table-hover tbody tr:hover {
      background-color: rgba(216, 172, 159, 0.2);
    }
    .page-item.active .page-link {
      background-color: #D8AC9F;
      border-color: #D8AC9F;
      color: #5d4037;
    }
    .page-link {
      color: #5d4037;
      border-color: #D8AC9F;
    }
    .page-link:hover {
      background-color: #C28D7A;
      border-color: #C28D7A;
      color: white;
    }
    .form-control:focus, .form-select:focus {
      border-color: #D8AC9F;
      box-shadow: 0 0 0 0.25rem rgba(216, 172, 159, 0.25);
    }
    .alert-info {
      background-color: rgba(216, 172, 159, 0.2);
      border-color: #D8AC9F;
      color: #5d4037;
    }
  </style>
</head>
<body>
<div class="container mt-4">
  <div class="row">
    <div class="col-md-3">
      <div class="card mb-4">
        <div class="card-header">
          <h5>Order Filters</h5>
        </div>
        <div class="card-body">
          <form method="get">
            <div class="mb-3">
              <label for="start_date" class="form-label">From Date</label>
              <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="mb-3">
              <label for="end_date" class="form-label">To Date</label>
              <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Status</label>
              <select class="form-select" id="status" name="status">
                <option value="">All Statuses</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Processing</option>
                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
              </select>
            </div>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">Apply Filters</button>
              <a href="listorder.php" class="btn btn-secondary">Reset</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-9">
      <div class="card">
        <div class="card-header">
          <h5>Order History</h5>
        </div>
        <div class="card-body">
          <?php if (empty($orders)): ?>
            <div class="alert alert-info text-center">No orders found</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($orders as $order): ?>
                    <tr>
                      <td>#<?= $order['id'] ?></td>
                      <td><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                      <td><?= htmlspecialchars($order['user_name'] ?? 'Guest') ?></td>
                      <td><?= array_sum(array_column($order['products'], 'quantity')) ?> Products</td>
                      <td>₱<?= number_format($order['total'], 2) ?></td>
                      <td>
                        <span class="status-badge status-<?= $order['status'] ?>">
                          <?= ucfirst($order['status']) ?>
                        </span>
                      </td>
                      <td class="d-flex gap-2">
                        <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-info">
                          <i class="fas fa-eye"></i> View
                        </a>
                        <a href="edit_order.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-warning">
                          <i class="fas fa-edit"></i> Edit
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <?php if ($totalPages > 1): ?>
              <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mt-4">
                  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                      <a class="page-link" href="?page=<?= $i ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&status=<?= urlencode($status) ?>">
                        <?= $i ?>
                      </a>
                    </li>
                  <?php endfor; ?>
                </ul>
              </nav>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>