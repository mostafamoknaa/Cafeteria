<?php
session_start();

$errors = [];

if (isset($_POST["btn"])) {
    include_once '../connect.php'; 

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    // $role = $_POST["role"];
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


    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($result) > 0) {
        $errors['email'] = "Email already registered.";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header("Location: registration.php");
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password, picture) 
            VALUES ('$name', '$email', '$hashed_password', '$picture')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Registration successful! You can now log in.";
        header("Location: login.php");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    mysqli_close($conn);
}
