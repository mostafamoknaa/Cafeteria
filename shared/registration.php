
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
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #2e59d9;
            --light-color: #f8f9fc;
            --text-color: #5a5c69;
        }
        
        body {
            background-color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.9)), 
                              url('https://img.freepik.com/free-vector/pack-four-vintage-coffee-stickers_23-2147605356.jpg?t=st=1745440442~exp=1745444042~hmac=31b415b8f950130d5de03fe143a7dfbd7a695c9777e2007c6501745a82e5f463&w=740');
            background-size: cover;
            background-position: center;
        }
        
        .register-container {
            width: 100%;
            max-width: 600px; /* زيادة العرض إلى 600px */
            padding: 2rem;
        }
        
        .register-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
            padding: 3rem;
            border: 1px solid rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.2);
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .logo {
            width: 180px;
            height: 180px;
            object-fit: cover;
            margin-bottom: 1.5rem;
            border-radius: 50%;
            border: 6px solid #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        
        .welcome-text {
            color: var(--primary-color);
            font-weight: 600;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.2rem;
            line-height: 1.6;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
            border-radius: 15px 15px 0 0;
            margin-bottom: 2rem;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 0.85rem 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid #d1d3e2;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.3);
        }
        
        .form-select {
            padding: 0.85rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .btn-register {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            padding: 0.85rem;
            font-weight: 600;
            width: 100%;
            border-radius: 8px;
            transition: all 0.3s;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            margin-top: 1rem;
        }
        
        .btn-register:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .login-text {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-color);
            font-size: 1rem;
        }
        
        .login-link {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .login-link:hover {
            text-decoration: underline;
            color: var(--primary-dark);
        }
        
        .text-muted {
            font-size: 0.9rem;
            color: #6c757d !important;
            margin-top: -1rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: -1rem;
            margin-bottom: 1rem;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .alert ul {
            margin-bottom: 0;
        }
    </style>
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

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <!-- <select class="form-select <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>" 
                                id="role" name="role" required>
                            <option value="customer" <?php echo (($_POST['role'] ?? '') === 'customer' ? 'selected' : ''); ?>>Customer</option>
                            <option value="admin" <?php echo (($_POST['role'] ?? '') === 'admin' ? 'selected' : ''); ?>>Admin</option>
                        </select> -->
                        <select class="form-select <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>" 
        id="role" name="role" required>
    <option value="" disabled <?php echo empty($old['role']) ? 'selected' : ''; ?>>Choose role</option>
    <option value="customer" <?php echo ($old['role'] ?? '') === 'customer' ? 'selected' : ''; ?>>Customer</option>
    <option value="admin" <?php echo ($old['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
</select>


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


