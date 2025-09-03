<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Sign In - WITMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= base_url(relativePath: 'public/assets/css/auth.css') ?>" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="row g-0">                        
                         <div class="col-lg-5 d-none d-lg-block">
                            <div class="bg-primary text-white p-5 h-100 d-flex flex-column justify-content-center rounded-start-4">
                                <div class="text-center">
                                    <i class="bi bi-boxes fs-1 mb-4"></i>
                                    <h2 class="fw-bold mb-3">WeBuild Company</h2>
                                    <p class="lead opacity-75">Warehouse Inventory and Tracking Management System</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Side - Login Form -->
                        <div class="col-lg-7">
                            <div class="card-body p-5">
                                <div class="text-center mb-5">
                                    <h3 class="fw-bold text-dark mb-2">Sign in your account</h3>
                                    <p class="text-muted">Welcome back! Please enter your credentials</p>
                                </div>
                                  <form>
                                    <div class="mb-3">
                                        <label for="email" class="form-label fw-semibold text-dark">
                                            <i class="bi bi-envelope me-2"></i>Email Address
                                        </label>
                                        <input type="email" 
                                               class="form-control form-control-lg border-2" 
                                               id="email" 
                                               name="email" 
                                               placeholder="Enter your email address">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="password" class="form-label fw-semibold text-dark">
                                            <i class="bi bi-lock me-2"></i>Password
                                        </label>
                                        <input type="password" 
                                               class="form-control form-control-lg border-2" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Enter your password">
                                    </div>
                                    
                                    <div class="row mb-4">
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                                <label class="form-check-label text-muted" for="remember">
                                                    Remember me
                                                </label>
                                            </div>
                                        </div>                                        
                                        <div class="col-6 text-end">
                                            <a href="<?= base_url(relativePath: 'auth/reset-password') ?>" class="text-decoration-none text-primary">
                                                Forgot Password?
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid mb-4">
                                        <button type="submit" class="btn btn-primary btn-lg py-3 fw-semibold">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>
                                            Log In
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="text-center">
                                    <p class="text-muted mb-0">
                                        Don't have an account? 
                                        <a href="<?= base_url(relativePath: 'auth/contact-admin') ?>" class="text-decoration-none text-primary fw-semibold">
                                            Contact Administrator
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOAE8ZvarG9voXn55vfS1sMG" crossorigin="anonymous"></script>
</body>
</html>