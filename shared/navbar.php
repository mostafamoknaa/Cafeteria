<?php

// session_start();

// $user_id1 = $_SESSION['user_id'];
// $sql1 = "SELECT * FROM users WHERE id = $user_id1";
// $result1 = mysqli_query($conn, $sql1);
// $user1 = mysqli_fetch_assoc($result1);

// 1. Always start session first
session_start();

// 2. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 3. Verify database connection exists
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection error");
}

try {
    // 4. Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // 5. Bind parameters securely
    $stmt->bind_param("i", $_SESSION['user_id']);
    
    // 6. Execute and check result
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // 7. Verify user exists
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
} catch (Exception $e) {
    // 8. Proper error handling
    error_log("Navbar error: " . $e->getMessage());
    die("A system error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <title>Dashboard</title>
    <style>
        .navbar {
            background-color: #5d4037 !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand" href="../admin/adminhome.php">Cafeteria</a>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="../product/listproduct.php">Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../category/listcategory.php">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../order/listorder.php">Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../check/listcheck.php">Checks</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../user/listuser.php">Users</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="../images/<?= htmlspecialchars($user1['picture']) ?>" alt="User Avatar" width="30" height="30" class="rounded-circle me-2">
                                <span><?php echo htmlspecialchars($user1['name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="../user/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="../shared/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>

</body>
</html>