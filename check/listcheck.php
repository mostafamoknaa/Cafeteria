<?php
include "../connect.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page.';
    header('Location: ../shared/login.php');
    exit();
}

$date_from = $_POST['date_from'] ?? '';
$date_to = $_POST['date_to'] ?? '';
$selected_user = $_POST['user'] ?? 'all';

$where = "o.status = 'completed'";

if (!empty($date_from) && !empty($date_to)) {
    $where .= " AND o.created_at BETWEEN '$date_from' AND '$date_to'";
}
if ($selected_user != 'all') {
    $where .= " AND u.id = $selected_user";
}


$users_query = "SELECT id, name FROM users ORDER BY name";
$users_result = $conn->query($users_query);
$users = [];
while ($user = $users_result->fetch_assoc()) {
    $users[] = $user;
}


$totals_query = "SELECT u.name, u.id, SUM(o.total) as total 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 WHERE $where
                 GROUP BY u.id 
                 ORDER BY total DESC";
$totals_result = $conn->query($totals_query);


$overall_query = "SELECT SUM(o.total) as grand_total
                  FROM orders o
                  JOIN users u ON o.user_id = u.id
                  WHERE $where";
$overall_result = $conn->query($overall_query);
$overall_row = $overall_result->fetch_assoc();
$grand_total = $overall_row['grand_total'] ?? 0;

?>
<?php include "../shared/navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe Ordering System - Admin Orders</title>

 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <style>
        .navbar { background-color: bisque !important; }
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .filters {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .filter-group label {
            margin-right: 10px;
            font-weight: bold;
        }
        .filter-group input, .filter-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button[type="submit"], .reset-btn {
            padding: 8px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            margin-top: 10px;
            margin-left: 10px;
        }
        button[type="submit"]:hover, .reset-btn:hover {
            background-color: #45a049;
        }
        .total-summary {
            margin-top: 30px;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            color: #333;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Checks</h1>

    
    <form method="POST" action="" id="filterForm">
        <div class="filters">
            <div class="filter-group">
                <label for="date_from">Date from:</label>
                <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>

            <div class="filter-group">
                <label for="date_to">Date to:</label>
                <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>

            <div class="filter-group">
                <label for="user">User:</label>
                <select id="user" name="user">
                    <option value="all" <?php echo ($selected_user == 'all') ? 'selected' : ''; ?>>All Users</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo ($selected_user == $user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <button type="submit">Apply Filters</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='<?= $_SERVER['PHP_SELF'] ?>'">Reset</button>
            </div>
        </div>
    </form>

    <?php if (!empty($date_from) && !empty($date_to)): ?>
        <p style="text-align:center;">Showing results from <strong><?php echo htmlspecialchars($date_from); ?></strong> to <strong><?php echo htmlspecialchars($date_to); ?></strong></p>
    <?php endif; ?>

   
    <div class="accordion mt-4" id="usersAccordion">
        <?php
        $counter = 0;
        while ($row = $totals_result->fetch_assoc()):
            $userId = $row['id'];
            $userName = htmlspecialchars($row['name']);
            $userTotal = number_format($row['total'], 2);

           
            $orders_query = "SELECT id, created_at, total 
                             FROM orders 
                             WHERE user_id = $userId AND status = 'completed'
                             ORDER BY created_at DESC";
            $user_orders_result = $conn->query($orders_query);
        ?>
        <div class="accordion-item mb-3">
            <h2 class="accordion-header" id="heading<?= $counter ?>">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $counter ?>" aria-expanded="false" aria-controls="collapse<?= $counter ?>">
                    <?= $userName ?> - <?= $userTotal ?> EGP
                </button>
            </h2>
            <div id="collapse<?= $counter ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $counter ?>" data-bs-parent="#usersAccordion">
                <div class="accordion-body">
                    <?php if ($user_orders_result->num_rows > 0): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total (EGP)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $user_orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $order['id'] ?></td>
                                        <td><?= htmlspecialchars($order['created_at']) ?></td>
                                        <td><?= number_format($order['total'], 2) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No orders found for this user.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        $counter++;
        endwhile;
        ?>
    </div>

 
    <div class="total-summary">
        Total Sales: <?= number_format($grand_total, 2) ?> EGP
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>


<script>
function resetForm() {
    document.getElementById('filterForm').reset();
}
</script>

</body>
</html>
