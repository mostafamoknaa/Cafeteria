<?php
include "../connect.php";
session_start();

if (!isset($_SESSION['user_id'] ) || $_SESSION['user_role'] != 'admin') {
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

$orders_query = "SELECT o.id, o.created_at, o.total, u.name AS user_name 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 WHERE $where
                 ORDER BY o.created_at DESC";
$orders_result = $conn->query($orders_query);

$totals_query = "SELECT u.name, SUM(o.total) as total 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 WHERE $where
                 GROUP BY u.id 
                 ORDER BY total DESC";
$totals_result = $conn->query($totals_query);

?>
<?php include "../shared/navbar.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <title>Cafe Ordering System - Admin Orders</title>
    <style>
         .navbar { background-color: bisque !important; }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
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
        button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .amount {
            text-align: right;
            font-weight: bold;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            color: black;
            margin: 0 4px;
            border-radius: 5px;
        }
        .pagination a:hover {
            background-color: #ddd;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Checks</h1>
        
        <form method="POST" action="">
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
                
                <button type="submit">Apply Filters</button>
            </div>
        </form>

        <?php if (!empty($date_from) && !empty($date_to)): ?>
            <p style="text-align:center;">Showing results from <strong><?php echo htmlspecialchars($date_from); ?></strong> to <strong><?php echo htmlspecialchars($date_to); ?></strong></p>
        <?php endif; ?>
        
        <div class="users-section">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Total amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                   
                    $totals_query = "SELECT u.name,u.id, SUM(o.total) as total 
                                     FROM orders o 
                                     JOIN users u ON o.user_id = u.id 
                                     WHERE $where
                                     GROUP BY u.id 
                                     ORDER BY total DESC";
                    $totals_result = $conn->query($totals_query);

                    while ($row = $totals_result->fetch_assoc()): 
                    ?>
                    <tr>
                    <td>
                        <a href="user_details.php?id=<?= $row['id']; ?> " class="text-decoration-none text-dark fw-bold text-decoration-none">
                            <?= htmlspecialchars($row['name']); ?>
                        </a>
                    </td>
                    <td class="amount">
                        <?= number_format($row['total'], 2); ?> EGP
                    </td>
                </tr>

                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
