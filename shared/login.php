<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cafeteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">


</head>

<body class="bg-light">
    <div class="login-container">
        <div class="login-card shadow">
            <div class="logo-container">
                <img src="https://t4.ftcdn.net/jpg/04/83/16/09/360_F_483160952_bYB2DOjUdsuB33gTXodCnnn8qDMxtSkl.jpg" alt="Logo" class="logo">
                <div class="welcome-text">Welcome Back</div>
            </div>

            <div class="card-body">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        Invalid email or password
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form id="loginForm" action="login_process.php" method="post" class="needs-validation" novalidate>
                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback">
                            Please enter a valid email address
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">
                            Please enter your password
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>


                   


                    <button type="submit" class="btn btn-primary w-100 py-2">Login</button><br>
                    <div class="text-center mt-3">
                        <a href="forgot_password.php" class="login-link">Forgot Password?</a>
                    </div>
                </form>
               

                <div class="mt-3 text-center">
                    <p>Don't have an account? <a href="registration.php" class="register-link">Register here</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict'

            const form = document.getElementById('loginForm')

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })()
    </script>
</body>

</html>