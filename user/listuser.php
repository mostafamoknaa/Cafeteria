<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("../connect.php");
session_start();

if (!isset($_SESSION['user_id'] ) || $_SESSION['user_role'] != 'admin') {
  $_SESSION['error'] = 'You must be logged in as an admin to access this page.';
  header('Location: ../shared/login.php');
  exit();
}

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

$users_per_page = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $users_per_page;

$sql = "SELECT id, name, email, picture, role, created_at FROM users ORDER BY created_at DESC LIMIT $offset, $users_per_page";
$result = mysqli_query($conn, $sql);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get total users count
$count_query = "SELECT COUNT(*) as total FROM users";
$count_result = mysqli_query($conn, $count_query);
$total_users = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_users / $users_per_page);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-color: #f5f5f5;
        }
        
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        
        .navbar {
            background-color: #D8AC9F !important;
        }
        
        .navbar-brand, .nav-link {
            color: #5d4037 !important;
        }
        
        .btn-primary, .btn-add {
            background-color: #D8AC9F;
            border-color: #D8AC9F;
            color: #5d4037;
        }
        
        .btn-primary:hover, .btn-add:hover {
            background-color: #C28D7A;
            border-color: #C28D7A;
        }
        
        .table thead {
            background-color: #D8AC9F  !important;
            color: white;
        }
        
        .pagination .page-link {
            color: #5d4037;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #D8AC9F;
            border-color: #D8AC9F;
            color: white;
        }
    </style>

</head>

<body>
    <?php include "../shared/navbar.php"; ?>
    <div class="container table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 style="color: #5d4037;">Manage Users</h1>
            <a href="adduser.php" class="btn btn-add">
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
                <thead >
                    <tr >
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
                                        <img src="<?= htmlspecialchars($user['picture']) ?>" width="50" height="50" class="rounded-circle">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle fa-2x"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-success' ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <a href="edituser.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="listuser.php?delete_id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" >
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    Are you sure you want to delete this user?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a id="confirmDeleteButton" href="#" class="btn btn-danger">Yes, Delete</a>
                </div>

                </div>
            </div>
            </div>

        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center mt-4">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <script>
  const confirmDeleteModal = document.getElementById('confirmDeleteModal');
  confirmDeleteModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const deleteUrl = button.getAttribute('data-delete-url');
    const confirmButton = document.getElementById('confirmDeleteButton');
    confirmButton.setAttribute('href', deleteUrl);
  });
</script>
</body>
</html>