<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bootstrap Validation Form</title>
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

        .header-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 15px;
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
            <div class="header-icon">
                <i class="fa-solid fa-mug-saucer"></i>
            </div>
            <h1 class="form-title">Cafeteria</h1>
        </div>
        <form class="needs-validation" method="post" enctype="multipart/form-data" novalidate>

            <div class="mb-3">
                <label for="name" class="form-label" >Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback">Please enter your name.</div>
            </div>


            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback">Please enter a valid email address.</div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                <div class="invalid-feedback">Password must be at least 6 characters.</div>
            </div>


            <div class="mb-3">
                <label for="picture" class="form-label">Profile Picture</label>
                <input type="file" class="form-control" id="picture" name="picture" accept="../images/" required>
                <div class="invalid-feedback">Please upload a picture.</div>
            </div>


            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="">Choose a role...</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
                <div class="invalid-feedback">Please select a role.</div>
            </div>

            <div class="text-center">
                <button class="btn  btn-primary" type="submit" name="submit">Submit</button>
            </div>
        </form>
    </div>


    <script>
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>

</html>




<?php
if (isset($_POST["submit"])) {
    include_once "connect.php";

    $name     = $_POST["name"];
    $email    = $_POST["email"];
    $password = $_POST["password"];
    $role     = $_POST["role"];

    // Handle uploaded image
    $picture = $_FILES["picture"]["name"];
    $tmp_name = $_FILES["picture"]["tmp_name"];
    $upload_dir = "uploads/";
    $target_path = $upload_dir . basename($picture);
    
    move_uploaded_file($tmp_name, $target_path);

    
    $query = "INSERT INTO users (name, email, password, picture, role)
              VALUES ('$name', '$email', '$password', '$picture', '$role')";

    if (mysqli_query($conn, $query)) {
        echo "User added successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
