<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <title>Reset Password - WITMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="../public/assets/css/auth.css" rel="stylesheet">
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
                            <div class="card-body p-5">
                                <div class="text-center mb-5">
                                    <i class="bi bi-shield-lock text-primary fs-1 mb-3"></i>
                                    <h3 class="fw-bold text-dark mb-2">Reset your password</h3>
                                    <p class="text-muted">Enter your email address and we'll send you a link to reset your password</p>
                                </div>
                                
                                <!-- Success Message -->
                                <div class="alert alert-success d-none" id="successAlert">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    Reset link sent! Please check your email.
                                </div>
                                
                                <!-- Error Message -->
                                <div class="alert alert-danger d-none" id="errorAlert">
                                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                                    <span id="errorMessage"></span>
                                </div>
                                
                                <form id="resetForm">
                                    <div class="mb-4">
                                        <label for="email" class="form-label fw-semibold text-dark">
                                            <i class="bi bi-envelope me-2"></i>Email Address
                                        </label>
                                        <input type="email" 
                                               class="form-control form-control-lg border-2" 
                                               id="email" 
                                               name="email" 
                                               placeholder="Enter your email address"
                                               required>
                                        <div class="invalid-feedback">
                                            Please enter a valid email address.
                                        </div>
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
                                            <a href="#" class="text-decoration-none text-primary fw-semibold">
                                                Contact our support team
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <a href="<?= base_url(relativePath: '/') ?>" class="text-decoration-none text-primary">
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
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email');
            const emailValue = email.value.trim();
            
            // Hide previous alerts
            document.getElementById('successAlert').classList.add('d-none');
            document.getElementById('errorAlert').classList.add('d-none');
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailValue) {
                showError('Please enter your email address.');
                email.classList.add('is-invalid');
                return;
            }
            
            if (!emailRegex.test(emailValue)) {
                showError('Please enter a valid email address.');
                email.classList.add('is-invalid');
                return;
            }
            
            // Remove invalid class if validation passes
            email.classList.remove('is-invalid');
            
            // Simulate sending reset link (replace with actual AJAX call)
            setTimeout(function() {
                document.getElementById('successAlert').classList.remove('d-none');
                document.getElementById('resetForm').reset();
            }, 1000);
        });
        
        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorAlert').classList.remove('d-none');
        }
        
        // Remove validation classes on input
        document.getElementById('email').addEventListener('input', function() {
            this.classList.remove('is-invalid');
            document.getElementById('errorAlert').classList.add('d-none');
        });
    </script>
</body>
</html>