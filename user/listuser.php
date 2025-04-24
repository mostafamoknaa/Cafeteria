<?php
require "../connect.php";
session_start();

// Check if user is admin (you should add this security check)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_user"])) {
    $delete_id = intval($_POST["delete_id"]);
    
    // Prevent deleting the current admin user
    if ($delete_id == $_SESSION['user_id']) {
        $_SESSION["error_message"] = "You cannot delete your own account while logged in.";
    } else {
        $delete_query = "DELETE FROM users WHERE id = $delete_id";
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION["success_message"] = "User deleted successfully.";
        } else {
            $_SESSION["error_message"] = "Failed to delete user.";
        }
    }

    header("Location: listuser.php");
    exit();
}
?>
<?php include "../shared/navbar.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --border-color: #ddd;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--light-bg);
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        h1, h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid var(--border-color);
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: var(--primary-color);
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #777;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-buttons a {
            color: var(--primary-color);
            text-decoration: none;
            padding: 5px;
            border-radius: 4px;
        }

        .action-buttons a:hover {
            background-color: #f1f1f1;
        }

        .action-buttons a.delete {
            color: var(--danger-color);
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-admin {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .badge-user {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-staff {
            background-color: #fff3cd;
            color: #856404;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .pagination a {
            padding: 8px 12px;
            border: 1px solid var(--primary-color);
            border-radius: 4px;
            color: var(--primary-color);
            text-decoration: none;
        }

        .pagination a.active {
            background-color: var(--primary-color);
            color: white;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }

            .action-buttons a {
                margin-bottom: 5px;
            }

            th, td {
                padding: 8px;
            }

            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1><i class="fas fa-users"></i> User Management</h1>
        <a href="adduser.php" class="btn">Add User</a>
    </header>
    
    <!-- Display success/error messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
            <?= $_SESSION['success_message'] ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
            <?= $_SESSION['error_message'] ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <h2><i class="fas fa-list"></i> User List</h2>
    <div class="table-responsive">
        <table id="userTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Avatar</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td>
                            <div class="user-avatar">
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="../images/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="User Avatar" style="width:100%; height:100%; border-radius:50%;">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge badge-admin"><i class="fas fa-crown"></i> Admin</span>
                            <?php elseif ($user['role'] === 'staff'): ?>
                                <span class="badge badge-staff"><i class="fas fa-user-tie"></i> Staff</span>
                            <?php else: ?>
                                <span class="badge badge-user"><i class="fas fa-user"></i> User</span>
                            <?php endif; ?>
                        </td>
                        <td class="action-buttons">
                            <a href="edituser.php?id=<?= $user['id'] ?>" title="Edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $user['id'] ?>">
                                <button type="submit" name="delete_user" class="btn" style="background-color: var(--danger-color); color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;" 
                                    <?= ($user['id'] == $_SESSION['user_id']) ? 'disabled title="Cannot delete your own account"' : '' ?>>
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center;">No users found. Add your first user above.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <div class="pagination" id="pagination"></div>
    </div>
</div>
<script>
    const table = document.getElementById('userTable');
    const rows = Array.from(table.getElementsByTagName('tbody')[0].rows);
    const pagination = document.getElementById('pagination');
    const rowsPerPage = 5;
    const pageCount = Math.ceil(rows.length / rowsPerPage);

    function displayPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        rows.forEach((row, index) => {
            row.style.display = index >= start && index < end ? '' : 'none';
        });

        pagination.innerHTML = '';
        for (let i = 1; i <= pageCount; i++) {
            const link = document.createElement('a');
            link.textContent = i;
            link.href = "#";
            link.className = i === page ? 'active' : '';
            link.addEventListener('click', (e) => {
                e.preventDefault();
                displayPage(i);
            });
            pagination.appendChild(link);
        }
    }

    displayPage(1);
</script>
</body>
</html>