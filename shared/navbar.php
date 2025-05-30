<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id1 = $_SESSION['user_id'];
$sql1 = "SELECT * FROM users WHERE id = $user_id1";
$result1 = mysqli_query($conn, $sql1);
$user1 = mysqli_fetch_assoc($result1);

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
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
            background-color:bisque !important;
            color: white !important;
        }
    </style>
</head>
<body>
    <div class="container text-white">
        <nav class="navbar navbar-expand-lg navbar-light bg-light text-white">
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