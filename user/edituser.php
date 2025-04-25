<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("../connect.php");

$success = '';
$error = '';
$user = [];

// Fetch user data if ID is provided
if (isset($_GET['id'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT id, name, email, picture, role FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        $error = "User not found!";
        header("Location: read_users.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $id = mysqli_real_escape_string($conn, $_POST["id"]);
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $role = mysqli_real_escape_string($conn, $_POST["role"]);
    $current_picture = mysqli_real_escape_string($conn, $_POST["current_picture"]);

    // Initialize variables
    $picture = $current_picture;
    $password_changed = false;
    $new_password = '';

    // Check if password is being changed
    if (!empty($_POST["password"])) {
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];

        if ($password !== $confirm_password) {
            $error = "Passwords do not match!";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters!";
        } else {
            $new_password = password_hash($password, PASSWORD_DEFAULT);
            $password_changed = true;
        }
    }

    // Process image upload if no errors
    if (empty($error) && isset($_FILES["picture"]) && $_FILES["picture"]["size"] > 0) {
        $targetDir = "../images/";
        $fileExt = strtolower(pathinfo($_FILES["picture"]["name"], PATHINFO_EXTENSION));
        $fileName = time() . '_' . uniqid() . '.' . $fileExt;
        $targetFile = $targetDir . $fileName;

        // Validate image
        $check = getimagesize($_FILES["picture"]["tmp_name"]);
        if ($check === false) {
            $error = "File is not a valid image!";
        } elseif ($_FILES["picture"]["size"] > 5000000) {
            $error = "Image size too large (max 5MB)!";
        }

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileExt, $allowedTypes)) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed!";
        }

        if (empty($error)) {
            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $targetFile)) {
                // Delete old picture if it exists and is not the default
                if (!empty($current_picture) && file_exists($current_picture)) {
                    unlink($current_picture);
                }
                $picture = $targetFile;
            } else {
                $error = "Error uploading image!";
            }
        }
    }

    // Update user if no errors
    if (empty($error)) {
        if ($password_changed) {
            $sql = "UPDATE users SET name = ?, email = ?, password = ?, picture = ?, role = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $new_password, $picture, $role, $id);
        } else {
            $sql = "UPDATE users SET name = ?, email = ?, picture = ?, role = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $picture, $role, $id);
        }

        if (mysqli_stmt_execute($stmt)) {
            $success = "User updated successfully!";
            // Refresh user data
            $sql = "SELECT id, name, email, picture, role FROM users WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
        } else {
            $error = "Error updating user: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .form-title {
            color: #0d6efd;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            display: block;
        }
        .picture-container {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container form-container">
        <div class="text-center">
            <h1 class="form-title">Edit User</h1>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form class="needs-validation" method="post" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id" value="<?= htmlspecialchars($user['id'] ?? '') ?>">
            <input type="hidden" name="current_picture" value="<?= htmlspecialchars($user['picture'] ?? '') ?>">

            <div class="picture-container">
                <?php if (!empty($user['picture'])): ?>
                    <img src="<?= htmlspecialchars($user['picture']) ?>" alt="Profile Picture" class="profile-picture">
                <?php else: ?>
                    <div class="profile-picture bg-secondary d-flex align-items-center justify-content-center">
                        <i class="fas fa-user fa-3x text-white"></i>
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="picture" name="picture" accept="image/*">
                <small class="text-muted">Leave blank to keep current image</small>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                <div class="invalid-feedback">Please enter your name.</div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                <div class="invalid-feedback">Please enter a valid email.</div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" class="form-control" id="password" name="password" minlength="6">
                <div class="invalid-feedback">Password must be at least 6 characters.</div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                <div class="invalid-feedback">Passwords must match.</div>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="admin" <?= (isset($user['role']) && $user['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="customer" <?= (isset($user['role']) && $user['role'] === 'customer') ? 'selected' : '' ?>>Customer</option>
                </select>
                <div class="invalid-feedback">Please select a role.</div>
            </div>

            <div class="text-center">
                <button class="btn btn-primary" type="submit" name="submit">Update User</button>
                <a href="listuser.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');

            // Password confirmation validation
            const password = document.getElementById('password');
            const confirm_password = document.getElementById('confirm_password');

            function validatePassword() {
                if (password.value && password.value !== confirm_password.value) {
                    confirm_password.setCustomValidity("Passwords don't match");
                } else {
                    confirm_password.setCustomValidity('');
                }
            }

            password.onchange = validatePassword;
            confirm_password.onkeyup = validatePassword;

            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>

</html>