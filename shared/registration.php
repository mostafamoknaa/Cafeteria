
<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

unset($_SESSION['errors'], $_SESSION['old']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Cafeteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css" >
   
</head>

<body>
    <div class="register-container">
        <div class="register-card">
            <div class="logo-container">
                <img src="https://img.freepik.com/vetores-premium/cracha-de-cafeteria-em-estilo-vintage_476121-79.jpg" alt="Cafeteria Logo" class="logo">
                <div class="welcome-text">Create your account to enjoy our services</div>
            </div>

            
            
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form id="registrationForm" action="handle_registration.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                               id="name" name="name" value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>"
                               required>
                        <div class="invalid-feedback">
                            <?php echo $errors['name'] ?? 'Please enter your name'; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" required>
                        <div class="invalid-feedback">
                            <?php echo $errors['email'] ?? 'Please enter a valid email'; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <!-- <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                               id="password" name="password" required> -->
                               <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                   id="password" name="password" value="<?php echo htmlspecialchars($old['password'] ?? ''); ?>" required>

                        <small class="text-muted">At least 6 characters</small>
                        <div class="invalid-feedback">
                            <?php echo $errors['password'] ?? 'Password must be at least 6 characters'; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <!-- <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                               id="confirm_password" name="confirm_password" required> -->
                               <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
       id="confirm_password" name="confirm_password" value="<?php echo htmlspecialchars($old['confirm_password'] ?? ''); ?>" required>

                        <div class="invalid-feedback">
                            <?php echo $errors['confirm_password'] ?? 'Passwords do not match'; ?>
                        </div>
                    </div>

                    <!-- <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                       
                        <select class="form-select <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>" 
        id="role" name="role" required>
    <option value="" disabled <?php echo empty($old['role']) ? 'selected' : ''; ?>>Choose role</option>
    <option value="customer" <?php echo ($old['role'] ?? '') === 'customer' ? 'selected' : ''; ?>>Customer</option>
    <option value="admin" <?php echo ($old['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
</select> -->


                        <div class="invalid-feedback">
                            <?php echo $errors['role'] ?? 'Please select a role'; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="picture" class="form-label">Profile Picture</label>
                        <!-- <input type="file" class="form-control <?php echo isset($errors['picture']) ? 'is-invalid' : ''; ?>" 
                               id="picture" name="picture" accept="image/*"> -->

                               <input type="file" class="form-control <?php echo isset($errors['picture']) ? 'is-invalid' : ''; ?>" 
       id="picture" name="picture" accept="image/*">

       
                        <small class="text-muted">Optional (Max 2MB)</small>
                        <div class="invalid-feedback">
                            <?php echo $errors['picture'] ?? 'Invalid image file'; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-register" name="btn">Register</button>
                </form>

                <div class="login-text">
                    Already have an account? <a href="login.php" class="login-link">Login here</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            'use strict'

            var forms = document.querySelectorAll('.needs-validation')

            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
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


