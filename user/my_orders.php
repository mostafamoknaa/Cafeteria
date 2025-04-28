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
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

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
if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Count total for pagination
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
if (!empty($status_filter)) {
    $count_query .= " AND status = ?";
    $count_params[] = $status_filter;
    $count_types .= "s";
}

// Get total pages
$stmt = $conn->prepare($count_query);
$stmt->bind_param($count_types, ...$count_params);
$stmt->execute();
$total_orders = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_orders / $items_per_page);

// Apply order and pagination
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

function buildQuery($overrides = []) {
    $params = array_merge($_GET, $overrides);
    return http_build_query($params);
}

// Available statuses
$order_statuses = ['Processing', 'Out for Delivery', 'Completed', 'Cancelled'];
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
        :root {
            --primary-color: #D8AC9F;
            --primary-hover: #C28D7A;
            --text-color: #5d4037;
            --light-bg: #f8f9fa;
            --border-color: #D8AC9F;
            --danger-color: #e74c3c;
            --success-color: #27ae60;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --shadow-inset: inset 0 1px 2px rgba(0,0,0,0.1);
        }

        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar { 
            background-color: var(--primary-color) !important;
            color: var(--text-color) !important;
            box-shadow: var(--shadow-md);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color) !important;
        }

        .nav-link {
            color: var(--text-color) !important;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: #3e2723 !important;
            font-weight: 500;
        }

        .dropdown-menu {
            border: none;
            box-shadow: var(--shadow-md);
        }

        .user-image {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: var(--shadow-sm);
        }

        .order-card {
            border-radius: 10px;
            box-shadow: var(--shadow-md);
            margin-bottom: 20px;
            overflow: hidden;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .order-header {
            background-color: var(--primary-color);
            padding: 15px;
            color: white;
        }

        .order-body {
            padding: 20px;
            background-color: white;
        }

        .status-processing { color: #ffc107; font-weight: bold; }
        .status-out-for-delivery { color: #0dcaf0; font-weight: bold; }
        .status-completed { color: var(--success-color); font-weight: bold; }
        .status-cancelled { color: var(--danger-color); font-weight: bold; }

        .item-list {
            list-style-type: none;
            padding-left: 0;
        }

        .item-list li {
            padding: 8px 0;
            border-bottom: 1px dashed rgba(0,0,0,0.1);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--text-color);
            border-color: var(--text-color);
        }

        .pagination .page-link {
            color: var(--text-color);
        }

        .pagination .page-link:hover {
            color: var(--primary-hover);
        }

        .btn-outline-danger {
            color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .btn-outline-danger:hover {
            background-color: var(--danger-color);
            color: white;
        }

        .filter-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
        }

        .form-control, .form-select {
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-inset);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-hover);
            box-shadow: 0 0 0 0.2rem rgba(216, 172, 159, 0.25);
        }

        .btn-dark {
            background-color: var(--text-color);
            border-color: var(--text-color);
        }

        .btn-dark:hover {
            background-color: #3e2723;
            border-color: #3e2723;
        }

        @media (max-width: 768px) {
            .order-header .row > div {
                margin-bottom: 10px;
            }
            
            .order-header .text-end {
                text-align: left !important;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="userhome.php">Cafeteria</a>
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
                <li><a class="dropdown-item text-danger" href="../shared/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4">My Orders</h2>

    <div class="filter-container">
        <form method="get">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Date from</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Date to</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All</option>
                        <?php foreach ($order_statuses as $status): ?>
                            <option value="<?= $status ?>" <?= $status == $status_filter ? 'selected' : '' ?>>
                                <?= $status ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark me-2">Filter</button>
                    <a href="my_orders.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>

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
                                <?= htmlspecialchars($order['status']) ?>
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
                                <button type="submit" class="btn btn-sm btn-outline-danger" >
                                    <i class="fas fa-times-circle"></i> Cancel Order
                                </button>
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