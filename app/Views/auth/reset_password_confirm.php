<!DOCTYPE html>
<html lang="en">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Set New Password - WITMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= base_url('public/assets/css/auth.css') ?>" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="row g-0">                        
                        <!-- Left Side - Branding -->
                        <div class="col-lg-5 d-none d-lg-block">
                            <div class="bg-primary text-white p-5 h-100 d-flex flex-column justify-content-center rounded-start-4">
                                <div class="text-center">
                                    <i class="bi bi-boxes fs-1 mb-4"></i>
                                    <h2 class="fw-bold mb-3">WeBuild Company</h2>
                                    <p class="lead opacity-75">Warehouse Inventory and Tracking Management System</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Side - Password Reset Form -->
                        <div class="col-lg-7">                            <div class="card-body p-5">
                                <div class="text-center mb-5">
                                    <h3 class="fw-bold text-dark mb-2">Set New Password</h3>
                                    <p class="text-muted">Create a strong password for your WeBuild WITMS account</p>
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
                                
                                <!-- User Info -->
                                <div class="bg-light p-3 rounded mb-4">
                                    <h6 class="mb-2">Resetting password for:</h6>
                                    <p class="mb-1"><strong><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></strong></p>
                                    <p class="mb-1 text-muted"><?= esc($user['email']) ?></p>
                                    <small class="text-muted">Role: <?= esc(ucwords(str_replace('-', ' ', $user['role']))) ?></small>
                                </div>
                                  <form action="<?= base_url('auth/reset-password-confirm/' . $token) ?>" method="POST" id="resetPasswordForm">
                                    <?= csrf_field() ?>
                                    
                                    <div class="mb-4">
                                        <label for="password" class="form-label fw-semibold text-dark">
                                            <i class="bi bi-lock me-2"></i>New Password
                                        </label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control form-control-lg border-2 <?= (validation_show_error('password')) ? 'is-invalid' : '' ?>" 
                                                   id="password" 
                                                   name="password" 
                                                   placeholder="Enter your new password"
                                                   required>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="bi bi-eye" id="passwordIcon"></i>
                                            </button>
                                        </div>
                                        <?php if (validation_show_error('password')): ?>
                                            <div class="invalid-feedback">
                                                <?= validation_show_error('password') ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="form-text">
                                            <small>Password must be at least 8 characters and contain: uppercase, lowercase, number, and special character (@$!%*?&)</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="password_confirm" class="form-label fw-semibold text-dark">
                                            <i class="bi bi-shield-check me-2"></i>Confirm Password
                                        </label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control form-control-lg border-2 <?= (validation_show_error('password_confirm')) ? 'is-invalid' : '' ?>" 
                                                   id="password_confirm" 
                                                   name="password_confirm" 
                                                   placeholder="Confirm your new password"
                                                   required>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                                <i class="bi bi-eye" id="passwordConfirmIcon"></i>
                                            </button>
                                        </div>
                                        <?php if (validation_show_error('password_confirm')): ?>
                                            <div class="invalid-feedback">
                                                <?= validation_show_error('password_confirm') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Password Strength Indicator -->
                                    <div class="mb-4">
                                        <label class="form-label small text-muted">Password Strength:</label>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar" role="progressbar" id="passwordStrength" style="width: 0%"></div>
                                        </div>
                                        <small id="strengthText" class="text-muted"></small>
                                    </div>
                                    
                                    <div class="d-grid mb-4">
                                        <button type="submit" class="btn btn-primary btn-lg py-3 fw-semibold">
                                            <i class="bi bi-check-circle me-2"></i>
                                            Update Password
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="text-center">
                                    <div class="border-top pt-4">                                        <p class="text-muted mb-2">
                                            <small><i class="bi bi-info-circle me-1"></i>This password reset link expires in 1 hour</small>
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
        // Password visibility toggles
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = document.getElementById('passwordIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
            const passwordConfirm = document.getElementById('password_confirm');
            const icon = document.getElementById('passwordConfirmIcon');
            
            if (passwordConfirm.type === 'password') {
                passwordConfirm.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordConfirm.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let strengthLabel = '';
            let strengthClass = '';
            
            // Check password criteria
            if (password.length >= 8) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/\d/.test(password)) strength += 1;
            if (/[@$!%*?&]/.test(password)) strength += 1;
            
            // Set strength level
            switch (strength) {
                case 0:
                case 1:
                    strengthLabel = 'Very Weak';
                    strengthClass = 'bg-danger';
                    break;
                case 2:
                    strengthLabel = 'Weak';
                    strengthClass = 'bg-warning';
                    break;
                case 3:
                    strengthLabel = 'Fair';
                    strengthClass = 'bg-info';
                    break;
                case 4:
                    strengthLabel = 'Good';
                    strengthClass = 'bg-primary';
                    break;
                case 5:
                    strengthLabel = 'Strong';
                    strengthClass = 'bg-success';
                    break;
            }
            
            const percentage = (strength / 5) * 100;
            strengthBar.style.width = percentage + '%';
            strengthBar.className = 'progress-bar ' + strengthClass;
            strengthText.textContent = strengthLabel;
        });

        // Password confirmation validation
        document.getElementById('password_confirm').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        // Form validation
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirm').value;
            
            // Check password requirements
            const hasMinLength = password.length >= 8;
            const hasLowercase = /[a-z]/.test(password);
            const hasUppercase = /[A-Z]/.test(password);
            const hasNumber = /\d/.test(password);
            const hasSpecialChar = /[@$!%*?&]/.test(password);
            
            if (!hasMinLength || !hasLowercase || !hasUppercase || !hasNumber || !hasSpecialChar) {
                e.preventDefault();
                alert('Password must be at least 8 characters and contain uppercase, lowercase, number, and special character.');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                return false;
            }
        });
    </script>
</body>
</html>
