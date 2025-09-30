<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Reset Password - WITMS</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
                          <!-- Right Side - Reset Password Form -->
                        <div class="col-lg-7">
                            <div class="card-body p-5">                                <div class="text-center mb-5">
                                    <h3 class="fw-bold text-dark mb-2">Reset your password</h3>
                                    <p class="text-muted">Enter your email address and we'll send you a link to reset your password</p>
                                </div>

                                <!-- Success Message -->
                                <?php if (session()->getFlashdata('success')): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <?= session()->getFlashdata('success') ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Error Message -->
                                <?php if (session()->getFlashdata('error')): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <?= session()->getFlashdata('error') ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php endif; ?>

                                <form action="<?= base_url('auth/reset-password') ?>" method="POST" id="resetForm">
                                    <?= csrf_field() ?>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label fw-semibold text-dark">
                                            <i class="bi bi-envelope me-2"></i>Email Address
                                        </label>
                                        <input type="email" 
                                               class="form-control form-control-lg border-2 <?= (validation_show_error('email')) ? 'is-invalid' : '' ?>" 
                                               id="email" 
                                               name="email" 
                                               value="<?= old('email') ?>"
                                               placeholder="Enter your email address"
                                               required>
                                        <?php if (validation_show_error('email')): ?>
                                            <div class="invalid-feedback">
                                                <?= validation_show_error('email') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-grid mb-4">
                                        <button type="submit" class="btn btn-primary btn-lg py-3 fw-semibold">
                                            <i class="bi bi-send me-2"></i>
                                            Send reset link
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="text-center">
                                    <div class="border-top pt-4">
                                        <p class="text-muted mb-2">
                                            <small>Didn't receive the email? Check your spam folder or try again.</small>
                                        </p>
                                        <p class="text-muted mb-0">
                                            Need help? 
                                            <a href="<?= base_url('auth/contact-admin') ?>" class="text-decoration-none text-primary fw-semibold">
                                                Contact our support team
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <a href="<?= base_url('auth/login') ?>" class="text-decoration-none text-primary">
                                        <i class="bi bi-arrow-left me-2"></i>
                                        Back to Sign In
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5.3.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOAE8ZvarG9voXn55vfS1sMG" crossorigin="anonymous"></script>
      <script>
        // Basic client-side email validation before form submission
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const emailValue = email.value.trim();
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailValue) {
                e.preventDefault();
                alert('Please enter your email address.');
                email.focus();
                return false;
            }
            
            if (!emailRegex.test(emailValue)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                email.focus();
                return false;
            }
            
            // Allow form to submit normally to the server
            return true;
        });
        
        // Remove validation classes on input
        document.getElementById('email').addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    </script>
</body>
</html>