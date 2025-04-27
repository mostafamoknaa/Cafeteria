<?php
require_once('../connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../shared/login.php');
    exit();
}

$selectedCurrency = 'EGP';

function formatCurrency($amount) {
    global $selectedCurrency;
    $symbols = [
        'EGP' => 'EGP',
        'USD' => '$',
        'EUR' => '€'
    ];
    $symbol = isset($symbols[$selectedCurrency]) ? $symbols[$selectedCurrency] : $selectedCurrency;
    return $symbol . ' ' . number_format($amount, 2);
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

$items_per_page = 5;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$query = "SELECT * FROM orders WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if (!empty($date_from)) {
    $query .= " AND DATE(created_at) >= ?";
    $params[] = $date_from;
    $types .= "s";
}
if (!empty($date_to)) {
    $query .= " AND DATE(created_at) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$count_query = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
$count_params = [$user_id];
$count_types = "i";

if (!empty($date_from)) {
    $count_query .= " AND DATE(created_at) >= ?";
    $count_params[] = $date_from;
    $count_types .= "s";
}
if (!empty($date_to)) {
    $count_query .= " AND DATE(created_at) <= ?";
    $count_params[] = $date_to;
    $count_types .= "s";
}

$stmt = $conn->prepare($count_query);
$stmt->bind_param($count_types, ...$count_params);
$stmt->execute();
$total_orders = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_orders / $items_per_page);

$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $items_per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function getOrderItems($conn, $order_id) {
    $query = "SELECT op.*, p.name as product_name FROM order_products op JOIN products p ON op.product_id = p.id WHERE op.order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $items;
}

// Helper to rebuild query string
function buildQuery($overrides = []) {
    $params = array_merge($_GET, $overrides);
    return http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        
        .order-card { border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); margin-bottom: 20px; overflow: hidden; }
        .order-header { background-color: #f8f9fa; padding: 15px; border-bottom: 1px solid #eee; }
        .order-body { padding: 15px; }
        .status-processing { color: #ffc107; font-weight: bold; }
        .status-out { color: #0dcaf0; font-weight: bold; }
        .status-completed { color: #198754; font-weight: bold; }
        .status-cancelled { color: #dc3545; font-weight: bold; }
        .item-list { list-style-type: none; padding-left: 0; }
        .item-list li { padding: 5px 0; border-bottom: 1px dashed #eee; }
        .pagination .page-item.active .page-link { background-color: #5d4037; border-color: #5d4037; }
        .navbar { background-color: #5d4037 !important; }
        .user-image { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Cafe Order System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="userhome.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="my_orders.php">My Orders</a></li>
                </ul>
            </div>
            <div class="dropdown">
                <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php if (!empty($user['picture'])): ?>
                        <img src="../images/<?= htmlspecialchars($user['picture']) ?>" class="user-image me-2">
                    <?php endif; ?>
                    <span><?= htmlspecialchars($user['name']) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../shared/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">My Orders</h2>

        <form method="get" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">Date from</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">Date to</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark me-2">Filter</button>
                    <a href="my_orders.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">No orders found.</div>
        <?php else: ?>
            <?php $totalPriceThisPage = 0; ?>
            <?php foreach ($orders as $order): ?>
                <?php $items = getOrderItems($conn, $order['id']); ?>
                <?php $totalPriceThisPage += $order['total']; ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Order Date</strong>
                                <p><?= date('Y/m/d h:i A', strtotime($order['created_at'])) ?></p>
                            </div>
                            <div class="col-md-4">
                                <strong>Status</strong>
                                <p class="status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>">
                                    <?= $order['status'] ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <strong>Amount</strong>
                                <p><?= formatCurrency($order['total']) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="order-body">
                        <?php if (!empty($order['notes'])): ?>
                            <div class="mb-3">
                                <strong>Notes:</strong>
                                <p><?= htmlspecialchars($order['notes']) ?></p>
                            </div>
                        <?php endif; ?>
                        <ul class="item-list">
                            <?php foreach ($items as $item): ?>
                            <li>
                                <div class="d-flex justify-content-between">
                                    <span><?= htmlspecialchars($item['product_name']) ?></span>
                                    <span><?= $item['quantity'] ?> x <?= formatCurrency($item['price']) ?></span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if ($order['status'] == 'Processing'): ?>
                            <div class="text-end mt-3">
                                <form method="post" action="cancel_order.php" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">CANCEL ORDER</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="text-end mt-4">
                <h5>Total This Page: <?= formatCurrency($totalPriceThisPage) ?></h5>
            </div>
        <?php endif; ?>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= buildQuery(['page' => $current_page - 1]) ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= buildQuery(['page' => $i]) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= buildQuery(['page' => $current_page + 1]) ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</body>
</html>
