




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cafeteria</title>
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
        
        .login-container {
            width: 100%;
            max-width: 500px; /* زيادة العرض من 400px إلى 500px */
            padding: 2rem;
        }
        
        .login-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
            padding: 3rem; /* زيادة المساحة الداخلية */
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 2.5rem; /* زيادة المسافة */
        }
        
        .logo {
            width: 180px; /* زيادة حجم اللوجو */
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
            padding: 0 1rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 0.85rem 1.25rem; /* زيادة حجم الحقول */
            margin-bottom: 1.5rem;
            border: 1px solid #d1d3e2;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.3);
        }
        
        .form-check {
            margin-bottom: 2rem;
        }
        
        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            margin-top: 0.25em;
        }
        
        .form-check-label {
            font-size: 1rem;
            margin-left: 0.5rem;
        }
        
        .btn-login {
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
            margin-bottom: 1.5rem;
        }
        
        .btn-login:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .register-text {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-color);
            font-size: 1rem;
        }
        
        .register-link {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .register-link:hover {
            text-decoration: underline;
            color: var(--primary-dark);
        }
        
        /* تأثيرات إضافية */
        .login-card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.2);
        }
    </style>
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

                    <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
                </form>

                <div class="mt-3 text-center">
                    <p>Don't have an account? <a href="registration.php" class="register-link">Register here</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            'use strict'

            const form = document.getElementById('loginForm')

            form.addEventListener('submit', function (event) {
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



