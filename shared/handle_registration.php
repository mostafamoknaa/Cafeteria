<?php
session_start();

$errors = [];

if (isset($_POST["btn"])) {
    include_once '../connect.php'; // نرجع خطوة لفوق عشان نجيب ملف الاتصال

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = $_POST["role"];
    $picture_name = '';

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    // التحقق من الإيميل إذا كان متكرر
    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($result) > 0) {
        $errors['email'] = "Email already registered.";
    }

    // ✅ لو فيه أخطاء، احفظها وأرجع لـ registration.php
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header("Location: registration.php");
        exit;
    }

    // ✅ لو مفيش أخطاء، سجل المستخدم
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password, picture, role) 
            VALUES ('$name', '$email', '$hashed_password', '$picture_name', '$role')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Registration successful! You can now log in.";
        header("Location: login.php");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    mysqli_close($conn);
}
