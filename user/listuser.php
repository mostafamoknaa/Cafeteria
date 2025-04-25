<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("../connect.php");

if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    try {
        mysqli_begin_transaction($conn);

        $query = "SELECT picture FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $picture_path);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        mysqli_stmt_execute($stmt);

        if (!empty($picture_path) && file_exists($picture_path)) {
            unlink($picture_path);
        }

        mysqli_commit($conn);

        header("Location: listuser.php?delete_success=1");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: listuser.php?delete_error=" . urlencode($e->getMessage()));
        exit();
    }
}

$sql = "SELECT id, name, email, picture, role, created_at FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);


?>
<?php include "../shared/navbar.php"; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
   
   <style>
        .table-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 5px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .action-btns .btn {
            margin-right: 5px;
        }
        .badge-admin { background-color: #dc3545; }
        .badge-customer { background-color: #198754; }
    </style>
</head>

<body>
    <div class="container table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">Manage Users</h1>
            <a href="adduser.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Add New User
            </a>
        </div>

        <?php if (isset($_GET['delete_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                User deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['delete_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                Error: <?= htmlspecialchars(urldecode($_GET['delete_error'])) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($user['picture'])): ?>
                                        <img src="<?= htmlspecialchars($user['picture']) ?>" alt="Profile" class="user-avatar">
                                    <?php else: ?>
                                        <div class="user-avatar bg-secondary d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge rounded-pill <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-customer' ?>">
                                        <?= htmlspecialchars(ucfirst($user['role'])) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y g:i A', strtotime($user['created_at'])) ?></td>
                                <td class="action-btns">
                                    <a href="edituser.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="listuser.php?delete_id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>

</body>
</html>
