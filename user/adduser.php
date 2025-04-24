<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("../connect.php");

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = mysqli_real_escape_string($conn, $_POST["role"]);


    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    }

    if (empty($error)) {
        $check_email = "SELECT email FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_email);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Email already exists!";
        }
        mysqli_stmt_close($stmt);
    }


    $picture = '';
    if (empty($error) && isset($_FILES["picture"])) {
        $targetDir = "../images/";
        $fileExt = strtolower(pathinfo($_FILES["picture"]["name"], PATHINFO_EXTENSION));
        $fileName = time() . '_' . uniqid() . '.' . $fileExt;
        $targetFile = $targetDir . $fileName;


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
            if (!move_uploaded_file($_FILES["picture"]["tmp_name"], $targetFile)) {
                $error = "Error uploading image!";
            } else {
                $picture = $targetFile;
            }
        }
    }


    if (empty($error)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password, picture, role) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashed_password, $picture, $role);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: listuser.php");
            exit;
        } else {
            $error = "Error: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create User</title>
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
    </style>
</head>

<body>
    <div class="container form-container">
        <div class="text-center">
            <h1 class="form-title">Add User</h1>
        </div>



        <form class="needs-validation" method="post" enctype="multipart/form-data" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback">Please enter your name.</div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback">Please enter a valid email.</div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                <div class="invalid-feedback">Password must be at least 6 characters.</div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <div class="invalid-feedback">Passwords must match.</div>
            </div>

            <div class="mb-3">
                <label for="picture" class="form-label">Profile Picture</label>
                <input type="file" class="form-control" id="picture" name="picture" accept="image/*" required>
                <div class="invalid-feedback">Please upload an image.</div>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="">Choose role...</option>
                    <option value="admin">Admin</option>
                    <option value="customer">Customer</option>


                </select>
                <div class="invalid-feedback">Please select a role.</div>
            </div>

            <div class="text-center">
                <button class="btn btn-primary" type="submit" name="submit">Submit</button>
            </div>
        </form>
    </div>

    <script>
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');


            const password = document.getElementById('password');
            const confirm_password = document.getElementById('confirm_password');

            function validatePassword() {
                if (password.value !== confirm_password.value) {
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