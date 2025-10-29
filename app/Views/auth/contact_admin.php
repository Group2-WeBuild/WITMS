<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Contact Administrator - WITMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= base_url('public/assets/css/auth.css') ?>" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-xl-10">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="row g-0">                        <div class="col-lg-4 d-none d-lg-block">
                            <div class="bg-primary text-white p-5 h-100 d-flex flex-column justify-content-center rounded-start-4">
                                <div class="text-center">
                                    <i class="bi bi-boxes fs-1 mb-4"></i>
                                    <h2 class="fw-bold mb-3">WeBuild Company</h2>
                                    <p class="lead opacity-75 mb-4">Request access to our Warehouse Inventory and Tracking Management System</p>
                                    
                                    <div class="mt-5 text-start">
                                        <h5 class="fw-bold mb-3">📋 What You'll Need:</h5>
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Full Name</li>
                                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Email Address</li>
                                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Phone Number</li>
                                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Department</li>
                                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Role</li>
                                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Employee ID (optional)</li>
                                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Reason for Access</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Side - Contact Form -->
                        <div class="col-lg-8">
                            <div class="card-body p-5">
                                <div class="text-center mb-4">
                                    <i class="bi bi-person-plus text-primary fs-1 mb-3"></i>
                                    <h3 class="fw-bold text-dark mb-2">Request Account Access</h3>
                                    <p class="text-muted">Fill out the form below and our administrator will create your account within 24 hours</p>
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
                                  <!-- Validation Errors -->
                                <?php if (session()->getFlashdata('errors')): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Please check the following errors:</strong>
                                    <ul class="mb-0 mt-2">
                                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                            <li><?= esc($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php endif; ?>
                                
                                <form id="contactForm" action="<?= base_url('auth/contact-admin') ?>" method="POST">
                                    <?= csrf_field() ?>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="firstName" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-person me-2"></i>First Name *
                                            </label>                                            <input type="text" 
                                                   class="form-control form-control-lg border-2 <?= (validation_show_error('first_name')) ? 'is-invalid' : '' ?>" 
                                                   id="firstName" 
                                                   name="first_name" 
                                                   value="<?= old('first_name') ?>"
                                                   placeholder="Enter your first name"
                                                   required>
                                            <?php if (validation_show_error('first_name')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('first_name') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="lastName" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-person me-2"></i>Last Name *
                                            </label>                                            
                                            <input type="text" 
                                                   class="form-control form-control-lg border-2 <?= (validation_show_error('last_name')) ? 'is-invalid' : '' ?>" 
                                                   id="lastName" 
                                                   name="last_name" 
                                                   value="<?= old('last_name') ?>"
                                                   placeholder="Enter your last name"
                                                   required>
                                            <?php if (validation_show_error('last_name')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('last_name') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-envelope me-2"></i>Email Address *
                                            </label>                                            <input type="email" 
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
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-telephone me-2"></i>Phone Number
                                            </label>                                            <input type="tel" 
                                                   class="form-control form-control-lg border-2 <?= (validation_show_error('phone')) ? 'is-invalid' : '' ?>" 
                                                   id="phone" 
                                                   name="phone" 
                                                   value="<?= old('phone') ?>"
                                                   placeholder="Enter your phone number">
                                            <?php if (validation_show_error('phone')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('phone') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="department" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-building me-2"></i>Department *
                                            </label>                                            <select class="form-select form-select-lg border-2 <?= (validation_show_error('department')) ? 'is-invalid' : '' ?>" 
                                                    id="department" 
                                                    name="department" 
                                                    required>
                                                <option value="">Select your department</option>
                                                <?php if (isset($departments) && !empty($departments)): ?>
                                                    <?php foreach ($departments as $dept): ?>
                                                        <option value="<?= $dept['id'] ?>" <?= (old('department') == $dept['id']) ? 'selected' : '' ?>>
                                                            <?= esc($dept['name']) ?> 
                                                            <?php if (!empty($dept['warehouse_location']) && $dept['warehouse_location'] !== 'All Warehouses'): ?>
                                                                - <?= esc($dept['warehouse_location']) ?>
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <option value="">No departments available</option>
                                                <?php endif; ?>
                                            </select>
                                            <?php if (validation_show_error('department')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('department') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>                                        <div class="col-md-6 mb-3">
                                            <label for="role" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-person-badge me-2"></i>Requested Role *
                                            </label>
                                            <select class="form-select form-select-lg border-2 <?= (validation_show_error('role')) ? 'is-invalid' : '' ?>" 
                                                    id="role" 
                                                    name="role" 
                                                    required>
                                                <option value="">Select your requested role</option>
                                                <?php if (isset($roles) && !empty($roles)): ?>
                                                    <?php foreach ($roles as $r): ?>
                                                        <option value="<?= esc($r['name']) ?>" <?= (old('role') == $r['name']) ? 'selected' : '' ?>>
                                                            <?= esc($r['name']) ?>
                                                            <?php if (!empty($r['description'])): ?>
                                                                - <?= esc($r['description']) ?>
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <option value="">No roles available</option>
                                                <?php endif; ?>
                                            </select>
                                            <?php if (validation_show_error('role')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('role') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="employeeId" class="form-label fw-semibold text-dark">
                                            <i class="bi bi-card-text me-2"></i>Employee ID (if applicable)
                                        </label>                                        <input type="text" 
                                               class="form-control form-control-lg border-2 <?= (validation_show_error('employee_id')) ? 'is-invalid' : '' ?>" 
                                               id="employeeId" 
                                               name="employee_id" 
                                               value="<?= old('employee_id') ?>"
                                               placeholder="Enter your employee ID">
                                        <?php if (validation_show_error('employee_id')): ?>
                                            <div class="invalid-feedback">
                                                <?= validation_show_error('employee_id') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="reason" class="form-label fw-semibold text-dark">
                                            <i class="bi bi-chat-text me-2"></i>Reason for Access Request *
                                        </label>                                        <textarea class="form-control border-2 <?= (validation_show_error('reason')) ? 'is-invalid' : '' ?>" 
                                                  id="reason" 
                                                  name="reason" 
                                                  rows="4" 
                                                  placeholder="Please explain why you need access to the system and how you plan to use it..."
                                                  required><?= old('reason') ?></textarea>
                                        <?php if (validation_show_error('reason')): ?>
                                            <div class="invalid-feedback">
                                                <?= validation_show_error('reason') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="form-check">                                            <input class="form-check-input <?= (validation_show_error('agreement')) ? 'is-invalid' : '' ?>" type="checkbox" id="agreement" name="agreement" value="1" required>
                                            <label class="form-check-label" for="agreement">
                                                I agree to comply with company policies and understand that system access is subject to approval and monitoring.
                                            </label>
                                            <?php if (validation_show_error('agreement')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('agreement') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid mb-4">
                                        <button type="submit" class="btn btn-primary btn-lg py-3 fw-semibold">
                                            <i class="bi bi-send me-2"></i>
                                            Submit Request
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="text-center">
                                    <div class="border-top pt-4">
                                        <p class="text-muted mb-2">
                                            <small><strong>Processing Time:</strong> Account requests are typically processed within 24 hours during business days.</small>
                                        </p>
                                        <p class="text-muted mb-0">
                                            Questions? Contact IT Support at 
                                            <a href="mailto:it-support@webuild.com" class="text-decoration-none text-primary fw-semibold">
                                                it-support@webuild.com
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
        // Enhanced client-side validation before form submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            // Get form values
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            const department = document.getElementById('department').value;
            const role = document.getElementById('role').value;
            const reason = document.getElementById('reason').value.trim();
            const agreement = document.getElementById('agreement').checked;
            
            // Clear previous validation classes
            clearValidationErrors();
            
            let hasErrors = false;
            
            // Validation checks
            if (!firstName) {
                showFieldError('firstName', 'First name is required.');
                hasErrors = true;
            }
            
            if (!lastName) {
                showFieldError('lastName', 'Last name is required.');
                hasErrors = true;
            }
            
            if (!email) {
                showFieldError('email', 'Email address is required.');
                hasErrors = true;
            } else if (!isValidEmail(email)) {
                showFieldError('email', 'Please enter a valid email address.');
                hasErrors = true;
            }
            
            if (!department) {
                showFieldError('department', 'Please select your department.');
                hasErrors = true;
            }
            
            if (!role) {
                showFieldError('role', 'Please select your requested role.');
                hasErrors = true;
            }
            
            if (!reason || reason.length < 20) {
                showFieldError('reason', 'Please provide a detailed reason (at least 20 characters).');
                hasErrors = true;
            }
            
            if (!agreement) {
                showFieldError('agreement', 'You must agree to the terms and conditions.');
                hasErrors = true;
            }
            
            // Prevent submission if there are errors
            if (hasErrors) {
                e.preventDefault();
                
                // Scroll to first error
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstError.focus();
                }
                
                return false;
            }
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Submitting Request...';
            submitButton.disabled = true;
            
            // Allow form to submit normally to server
            return true;
        });
        
        function clearValidationErrors() {
            // Remove all validation classes
            document.querySelectorAll('.is-invalid').forEach(function(element) {
                element.classList.remove('is-invalid');
            });
            
            // Remove all error messages
            document.querySelectorAll('.invalid-feedback').forEach(function(element) {
                element.style.display = 'none';
            });
        }
        
        function showFieldError(fieldId, message) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.classList.add('is-invalid');
                
                // Find or create invalid feedback element
                let feedback = field.parentNode.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    field.parentNode.appendChild(feedback);
                }
                
                feedback.textContent = message;
                feedback.style.display = 'block';
            }
        }
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Remove error classes when user starts typing/selecting
        document.querySelectorAll('input, select, textarea').forEach(function(element) {
            element.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentNode.querySelector('.invalid-feedback');
                    if (feedback) {
                        feedback.style.display = 'none';
                    }
                }
            });
            
            element.addEventListener('change', function() {
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentNode.querySelector('.invalid-feedback');
                    if (feedback) {
                        feedback.style.display = 'none';
                    }
                }
            });
        });
        
        // Auto-dismiss success/error alerts after 10 seconds
        document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
            setTimeout(function() {
                const closeButton = alert.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.click();
                }
            }, 10000);
        });
    </script>
</body>
</html>