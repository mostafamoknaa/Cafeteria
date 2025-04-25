

<?php
require '../connect.php';

$email = $_GET['email'] ?? '';
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $errorMessage = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $errorMessage = "Password must be at least 6 characters.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);

        if ($stmt->execute()) {
            $successMessage = "Password updated successfully. <a href='login.php'>Click here to login</a>";
        } else {
            $errorMessage = "Error updating password.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set New Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css" >

 
           
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card p-4 shadow" style="max-width: 400px;">
        <h3 class="text-center mb-4">Set New Password</h3>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php elseif ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <form action="" method="post" class="needs-validation" novalidate>
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" name="password" id="password" class="form-control" required minlength="6">
                <div class="invalid-feedback">
                    Please enter a password with at least 6 characters.
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                <div class="invalid-feedback">
                    Please confirm your password.
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100">Reset Password</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bootstrap validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    var pass = form.querySelector('#password');
                    var confirm = form.querySelector('#confirm_password');

                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    if (pass.value !== confirm.value) {
                        event.preventDefault();
                        event.stopPropagation();
                        confirm.setCustomValidity("Passwords do not match");
                        confirm.classList.add("is-invalid");
                        confirm.nextElementSibling.textContent = "Passwords do not match";
                    } else {
                        confirm.setCustomValidity("");
                    }

                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
