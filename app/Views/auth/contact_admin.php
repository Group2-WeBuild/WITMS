<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Contact Administrator - WITMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="../public/assets/css/auth.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-xl-10">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="row g-0">                   
                         <div class="col-lg-4 d-none d-lg-block">
                            <div class="bg-primary text-white p-5 h-100 d-flex flex-column justify-content-center rounded-start-4">
                                <div class="text-center">
                                    <i class="bi bi-boxes fs-1 mb-4"></i>
                                    <h2 class="fw-bold mb-3">WeBuild Company</h2>
                                    <p class="lead opacity-75 mb-4">Request access to our Warehouse Inventory and Tracking Management System</p>
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
                                <div class="alert alert-success d-none" id="successAlert">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    Request submitted successfully! You will receive an email confirmation shortly.
                                </div>
                                
                                <!-- Error Message -->
                                <div class="alert alert-danger d-none" id="errorAlert">
                                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                                    <span id="errorMessage"></span>
                                </div>
                                
                                <form id="contactForm" action="<?= base_url('auth/submit-request') ?>" method="post">
                                    <?= csrf_field() ?>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="firstName" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-person me-2"></i>First Name *
                                            </label>
                                            <input type="text" 
                                                   class="form-control form-control-lg border-2" 
                                                   id="firstName" 
                                                   name="first_name" 
                                                   placeholder="Enter your first name"
                                                   required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="lastName" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-person me-2"></i>Last Name *
                                            </label>
                                            <input type="text" 
                                                   class="form-control form-control-lg border-2" 
                                                   id="lastName" 
                                                   name="last_name" 
                                                   placeholder="Enter your last name"
                                                   required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-envelope me-2"></i>Email Address *
                                            </label>
                                            <input type="email" 
                                                   class="form-control form-control-lg border-2" 
                                                   id="email" 
                                                   name="email" 
                                                   placeholder="Enter your email address"
                                                   required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-telephone me-2"></i>Phone Number
                                            </label>
                                            <input type="tel" 
                                                   class="form-control form-control-lg border-2" 
                                                   id="phone" 
                                                   name="phone" 
                                                   placeholder="Enter your phone number">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="department" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-building me-2"></i>Department *
                                            </label>
                                            <select class="form-select form-select-lg border-2" 
                                                    id="department" 
                                                    name="department" 
                                                    required>
                                                <option value="">Select your department</option>
                                                <option value="construction">Construction</option>
                                                <option value="project_management">Project Management</option>
                                                <option value="site_supervision">Site Supervision</option>
                                                <option value="administration">Administration</option>
                                                <option value="logistics">Logistics</option>
                                                <option value="safety">Safety & Compliance</option>
                                                <option value="finance">Finance</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="role" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-person-badge me-2"></i>Requested Role *
                                            </label>
                                            <select class="form-select form-select-lg border-2" 
                                                    id="role" 
                                                    name="role" 
                                                    required>
                                                <option value="">Select your role</option>
                                                <option value="worker">Construction Worker</option>
                                                <option value="supervisor">Site Supervisor</option>
                                                <option value="manager">Project Manager</option>
                                                <option value="coordinator">Project Coordinator</option>
                                                <option value="admin">Administrative Staff</option>
                                                <option value="viewer">View Only Access</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="employeeId" class="form-label fw-semibold text-dark">
                                            <i class="bi bi-card-text me-2"></i>Employee ID (if applicable)
                                        </label>
                                        <input type="text" 
                                               class="form-control form-control-lg border-2" 
                                               id="employeeId" 
                                               name="employee_id" 
                                               placeholder="Enter your employee ID">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="reason" class="form-label fw-semibold text-dark">
                                            <i class="bi bi-chat-text me-2"></i>Reason for Access Request *
                                        </label>
                                        <textarea class="form-control border-2" 
                                                  id="reason" 
                                                  name="reason" 
                                                  rows="4" 
                                                  placeholder="Please explain why you need access to the system and how you plan to use it..."
                                                  required></textarea>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agreement" name="agreement" required>
                                            <label class="form-check-label" for="agreement">
                                                I agree to comply with company policies and understand that system access is subject to approval and monitoring.
                                            </label>
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
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Hide previous alerts
            document.getElementById('successAlert').classList.add('d-none');
            document.getElementById('errorAlert').classList.add('d-none');
            
            // Get form values
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            const department = document.getElementById('department').value;
            const role = document.getElementById('role').value;
            const reason = document.getElementById('reason').value.trim();
            const agreement = document.getElementById('agreement').checked;
            
            // Validation
            if (!firstName || !lastName || !email || !department || !role || !reason) {
                showError('Please fill in all required fields marked with *');
                return;
            }
            
            if (!agreement) {
                showError('You must agree to the terms and conditions to proceed.');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('Please enter a valid email address.');
                return;
            }
            
            // Simulate form submission (replace with actual AJAX call)
            setTimeout(function() {
                document.getElementById('successAlert').classList.remove('d-none');
                document.getElementById('contactForm').reset();
                
                // Scroll to success message
                document.getElementById('successAlert').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 1000);
        });
        
        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorAlert').classList.remove('d-none');
            
            // Scroll to error message
            document.getElementById('errorAlert').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
        
        // Remove error alerts when user starts typing
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(function(input) {
            input.addEventListener('input', function() {
                document.getElementById('errorAlert').classList.add('d-none');
            });
        });
    </script>
</body>
</html>