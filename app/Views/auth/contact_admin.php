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
                                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i>First Name</li>
                                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Middle Name (optional)</li>
                                            <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Last Name</li>
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
                                        <div class="col-md-4 mb-3">
                                            <label for="firstName" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-person me-2"></i>First Name *
                                            </label>                                            <input type="text" 
                                                   class="form-control form-control-lg border-2 <?= (validation_show_error('first_name')) ? 'is-invalid' : '' ?>" 
                                                   id="firstName" 
                                                   name="first_name" 
                                                   value="<?= old('first_name') ?>"
                                                   placeholder="Enter your first name"
                                                   pattern="^[a-zA-ZñÑ\s.'-]+$"
                                                   title="Only letters, spaces, ñ/Ñ, dots, hyphens, and apostrophes are allowed"
                                                   required>
                                            <?php if (validation_show_error('first_name')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('first_name') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="middleName" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-person me-2"></i>Middle Name
                                            </label>                                              <input type="text" 
                                                   class="form-control form-control-lg border-2 <?= (validation_show_error('middle_name')) ? 'is-invalid' : '' ?>" 
                                                   id="middleName" 
                                                   name="middle_name" 
                                                   value="<?= old('middle_name') ?>"
                                                   placeholder="Enter your middle name"
                                                   pattern="^[a-zA-ZñÑ\s.'-]*$"
                                                   title="Only letters, spaces, ñ/Ñ, dots, hyphens, and apostrophes are allowed">
                                            <?php if (validation_show_error('middle_name')): ?>
                                                <div class="invalid-feedback">
                                                    <?= validation_show_error('middle_name') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="lastName" class="form-label fw-semibold text-dark">
                                                <i class="bi bi-person me-2"></i>Last Name *
                                            </label>                                              <input type="text" 
                                                   class="form-control form-control-lg border-2 <?= (validation_show_error('last_name')) ? 'is-invalid' : '' ?>" 
                                                   id="lastName" 
                                                   name="last_name" 
                                                   value="<?= old('last_name') ?>"
                                                   placeholder="Enter your last name"
                                                   pattern="^[a-zA-ZñÑ\s.'-]+$"
                                                   title="Only letters, spaces, ñ/Ñ, dots, hyphens, and apostrophes are allowed (e.g., Jr., Sr.)"
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
                                                   placeholder="yourname@gmail.com"
                                                   pattern="^[a-zA-Z][a-zA-Z0-9._]*@gmail\.com$"
                                                   title="Gmail address must start with a letter, followed by letters, numbers, dots, or underscores"
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
                                                   placeholder="+639XXXXXXXXX or 09XXXXXXXXX"
                                                   pattern="^(\+639|09)\d{9}$"
                                                   title="Phone number must be in Philippine format: +639XXXXXXXXX or 09XXXXXXXXX"
                                                   maxlength="13">
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
                                            </label>                                            
                                            <select class="form-select form-select-lg border-2 <?= (validation_show_error('department')) ? 'is-invalid' : '' ?>" 
                                                    id="department" 
                                                    name="department" 
                                                    required
                                                    onchange="filterRolesByDepartment()">
                                                <option value="">-- Select Your Department --</option>
                                                <?php if (isset($departments) && !empty($departments)): ?>
                                                    <?php foreach ($departments as $dept): ?>
                                                        <option value="<?= $dept['id'] ?>" 
                                                                data-department-name="<?= esc($dept['name']) ?>"
                                                                <?= (old('department') == $dept['id']) ? 'selected' : '' ?>>
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
                                                    required
                                                    disabled>
                                                <option value="">-- Select Department First --</option>
                                                <?php if (isset($roles) && !empty($roles)): ?>
                                                    <?php foreach ($roles as $r): ?>
                                                        <option value="<?= esc($r['name']) ?>" 
                                                                data-role-name="<?= esc($r['name']) ?>"
                                                                <?= (old('role') == $r['name']) ? 'selected' : '' ?>>
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
                                            <small class="text-muted" id="roleHint">Please select a department to see available roles</small>
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
                                               placeholder="e.g., EMP12345"
                                               pattern="^[a-zA-Z0-9]+$"
                                               title="Only letters and numbers are allowed (no spaces or special characters)"
                                               maxlength="50">
                                        <?php if (validation_show_error('employee_id')): ?>
                                            <div class="invalid-feedback">
                                                <?= validation_show_error('employee_id') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="reason" class="form-label fw-semibold text-dark">
                                            <i class="bi bi-chat-text me-2"></i>Reason for Access Request *
                                        </label>                                          <textarea class="form-control border-2 <?= (validation_show_error('reason')) ? 'is-invalid' : '' ?>" 
                                                  id="reason" 
                                                  name="reason" 
                                                  rows="4" 
                                                  placeholder="Please explain why you need access to the system and how you plan to use it..."
                                                  minlength="20"
                                                  maxlength="1000"
                                                  required><?= old('reason') ?></textarea>
                                        <small class="text-muted">
                                            <span id="reasonCharCount">0</span>/1000 characters (minimum 20 characters)
                                        </small>
                                        <?php if (validation_show_error('reason')): ?>
                                            <div class="invalid-feedback">
                                                <?= validation_show_error('reason') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="form-check">                                            
                                            <input class="form-check-input <?= (validation_show_error('agreement')) ? 'is-invalid' : '' ?>" type="checkbox" id="agreement" name="agreement" value="1" required>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOAE8ZvarG9voXn55vfS1sMG" crossorigin="anonymous"></script>      <script>
        // Validation patterns for real-time input restriction
        const validationPatterns = {
            name: /^[a-zA-ZñÑ\s.'\-]*$/,                    // Letters, spaces, ñ/Ñ, dots, hyphens, apostrophes
            emailLocal: /^[a-zA-Z][a-zA-Z0-9._]*$/,         // Email must start with letter
            emailChar: /^[a-zA-Z0-9._@]*$/,                 // Valid email characters
            phone: /^[\+0-9]*$/,                             // Phone numbers (digits and +)
            employeeId: /^[a-zA-Z0-9]*$/,                   // Alphanumeric only
            reason: /^[a-zA-Z0-9\s.,;:!?()\-'"ñÑ]*$/,      // Safe characters for reason text
        };
        
        /**
         * Validate and restrict input in real-time
         */
        function setupInputValidation() {
            // Name fields validation (first, middle, last)
            const nameFields = ['firstName', 'middleName', 'lastName'];
            nameFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', function(e) {
                        const value = e.target.value;
                        if (!validationPatterns.name.test(value)) {
                            e.target.value = value.slice(0, -1);
                            showValidationHint(fieldId, 'Only letters, spaces, ñ/Ñ, dots, hyphens, and apostrophes are allowed', 'warning');
                        } else {
                            hideValidationHint(fieldId);
                        }
                    });
                    
                    field.addEventListener('blur', function(e) {
                        const value = e.target.value.trim();
                        if (value && fieldId !== 'middleName') {
                            if (value.length < 2) {
                                showValidationHint(fieldId, 'Name must be at least 2 characters long', 'danger');
                            } else if (!/^[a-zA-ZñÑ]/.test(value)) {
                                showValidationHint(fieldId, 'Name must start with a letter', 'danger');
                            }
                        }
                    });
                }
            });
              // Email field validation
            const emailField = document.getElementById('email');
            if (emailField) {
                emailField.addEventListener('input', function(e) {
                    const value = e.target.value;
                    
                    // Check if trying to start with a number
                    if (value.length === 1 && /^\d/.test(value)) {
                        e.target.value = '';
                        showValidationHint('email', 'Email must start with a letter', 'warning');
                        return;
                    }
                    
                    // Validate characters before @
                    if (!value.includes('@')) {
                        if (!validationPatterns.emailLocal.test(value) && value !== '') {
                            e.target.value = value.slice(0, -1);
                            showValidationHint('email', 'Only letters, numbers, dots, and underscores allowed', 'warning');
                        } else {
                            hideValidationHint('email');
                        }
                    } else {
                        // After @ is typed, validate full pattern
                        if (!validationPatterns.emailChar.test(value)) {
                            e.target.value = value.slice(0, -1);
                            showValidationHint('email', 'Invalid character detected', 'warning');
                        } else {
                            hideValidationHint('email');
                        }
                    }
                });
                
                emailField.addEventListener('blur', function(e) {
                    const value = e.target.value.trim();
                    if (value) {
                        if (!value.includes('@gmail.com')) {
                            showValidationHint('email', 'Only Gmail addresses (@gmail.com) are accepted', 'danger');
                        } else if (!/^[a-zA-Z][a-zA-Z0-9._]*@gmail\.com$/.test(value)) {
                            showValidationHint('email', 'Invalid email format. Must start with a letter.', 'danger');
                        }
                    }
                });
            }
            
            // Phone field validation
            const phoneField = document.getElementById('phone');
            if (phoneField) {
                phoneField.addEventListener('input', function(e) {
                    const value = e.target.value;
                    if (!validationPatterns.phone.test(value)) {
                        e.target.value = value.slice(0, -1);
                        showValidationHint('phone', 'Only numbers and + are allowed', 'warning');
                    } else {
                        hideValidationHint('phone');
                    }
                });
                
                phoneField.addEventListener('blur', function(e) {
                    const value = e.target.value.trim();
                    if (value) {
                        if (!/^(\+639|09)\d{9}$/.test(value)) {
                            showValidationHint('phone', 'Format: +639XXXXXXXXX or 09XXXXXXXXX (11 digits)', 'danger');
                        }
                    }
                });
            }
              // Employee ID validation
            const employeeIdField = document.getElementById('employeeId');
            if (employeeIdField) {
                employeeIdField.addEventListener('input', function(e) {
                    const value = e.target.value;
                    if (!validationPatterns.employeeId.test(value)) {
                        e.target.value = value.slice(0, -1);
                        showValidationHint('employeeId', 'Only letters and numbers are allowed (no spaces or special characters)', 'warning');
                    } else {
                        hideValidationHint('employeeId');
                    }
                });
            }
            
            // Reason field validation with character counter
            const reasonField = document.getElementById('reason');
            const charCountElement = document.getElementById('reasonCharCount');
            
            if (reasonField && charCountElement) {
                // Update character count on input
                reasonField.addEventListener('input', function(e) {
                    const value = e.target.value;
                    const length = value.length;
                    
                    // Update character count
                    charCountElement.textContent = length;
                    
                    // Validate against pattern (prevent malicious characters)
                    if (!validationPatterns.reason.test(value)) {
                        e.target.value = value.slice(0, -1);
                        showValidationHint('reason', 'Invalid character detected. Only letters, numbers, and basic punctuation allowed.', 'warning');
                    } else {
                        hideValidationHint('reason');
                    }
                    
                    // Update character count color based on length
                    if (length < 20) {
                        charCountElement.className = 'text-danger fw-bold';
                    } else if (length >= 20 && length <= 1000) {
                        charCountElement.className = 'text-success fw-bold';
                    } else {
                        charCountElement.className = 'text-danger fw-bold';
                    }
                });
                
                // Validate minimum length on blur
                reasonField.addEventListener('blur', function(e) {
                    const value = e.target.value.trim();
                    if (value && value.length < 20) {
                        showValidationHint('reason', 'Please provide at least 20 characters to explain your reason', 'danger');
                    } else if (value.length > 1000) {
                        showValidationHint('reason', 'Maximum 1000 characters allowed', 'danger');
                    }
                });
                
                // Initialize character count if there's existing content
                const initialLength = reasonField.value.length;
                if (initialLength > 0) {
                    charCountElement.textContent = initialLength;
                    charCountElement.className = initialLength >= 20 ? 'text-success fw-bold' : 'text-danger fw-bold';
                }
            }
        }
        
        /**
         * Show validation hint below field
         */
        function showValidationHint(fieldId, message, type = 'warning') {
            const field = document.getElementById(fieldId);
            if (!field) return;
            
            // Remove existing hint
            hideValidationHint(fieldId);
            
            // Create hint element
            const hint = document.createElement('small');
            hint.className = `validation-hint text-${type} d-block mt-1`;
            hint.id = `${fieldId}ValidationHint`;
            hint.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i>${message}`;
            
            // Insert after field or after invalid-feedback if exists
            const invalidFeedback = field.parentNode.querySelector('.invalid-feedback');
            if (invalidFeedback) {
                invalidFeedback.insertAdjacentElement('afterend', hint);
            } else {
                field.insertAdjacentElement('afterend', hint);
            }
            
            // Auto-hide warning hints after 3 seconds
            if (type === 'warning') {
                setTimeout(() => hideValidationHint(fieldId), 3000);
            }
        }
        
        /**
         * Hide validation hint
         */
        function hideValidationHint(fieldId) {
            const hint = document.getElementById(`${fieldId}ValidationHint`);
            if (hint) {
                hint.remove();
            }
        }
        
        // Department to Role mapping based on WeBuild company structure
        const departmentRoleMapping = {
            'Warehouse Operations': ['Warehouse Manager', 'Warehouse Staff'],
            'Quality Control': ['Inventory Auditor'],
            'Procurement': ['Procurement Officer'],
            'Finance': ['Accounts Payable Clerk', 'Accounts Receivable Clerk'],
            'Information Technology': ['IT Administrator'],
            'Executive': ['Top Management']
        };
        
        // Store all role options on page load to prevent losing them
        let allRoleOptions = [];
        
        /**
         * Filter roles based on selected department
         */
        function filterRolesByDepartment() {
            const departmentSelect = document.getElementById('department');
            const roleSelect = document.getElementById('role');
            const roleHint = document.getElementById('roleHint');
            
            // Get selected department name
            const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
            const departmentName = selectedOption.getAttribute('data-department-name');
            
            console.log('Selected department:', departmentName); // Debug log
            
            // Clear and reset role select
            roleSelect.innerHTML = '<option value="">-- Select Your Role --</option>';
            
            if (!departmentName || !departmentRoleMapping[departmentName]) {
                roleSelect.disabled = true;
                roleHint.textContent = 'Please select a department to see available roles';
                roleHint.className = 'text-muted';
                return;
            }
            
            // Get applicable roles for selected department
            const applicableRoles = departmentRoleMapping[departmentName];
            console.log('Applicable roles:', applicableRoles); // Debug log
            
            // Filter and add applicable roles
            let rolesAdded = 0;
            allRoleOptions.forEach(option => {
                const roleName = option.getAttribute('data-role-name');
                console.log('Checking role:', roleName); // Debug log
                
                if (applicableRoles.includes(roleName)) {
                    const newOption = document.createElement('option');
                    newOption.value = option.value;
                    newOption.textContent = option.textContent;
                    newOption.setAttribute('data-role-name', roleName);
                    roleSelect.appendChild(newOption);
                    rolesAdded++;
                    console.log('Added role:', roleName); // Debug log
                }
            });
            
            // Enable role select and update hint
            if (rolesAdded > 0) {
                roleSelect.disabled = false;
                roleHint.textContent = `${rolesAdded} role(s) available for ${departmentName}`;
                roleHint.className = 'text-success';
            } else {
                roleSelect.disabled = true;
                roleHint.textContent = 'No roles available for this department';
                roleHint.className = 'text-warning';
            }
        }
          // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Setup real-time input validation
            setupInputValidation();
            
            // Store all role options before any filtering
            const roleSelect = document.getElementById('role');
            allRoleOptions = Array.from(roleSelect.querySelectorAll('option[data-role-name]'));
            console.log('Total roles stored:', allRoleOptions.length); // Debug log
            
            // If department is pre-selected (validation errors), filter roles
            const departmentSelect = document.getElementById('department');
            if (departmentSelect.value) {
                filterRolesByDepartment();
            }
        });
          // Track if form is being submitted to prevent double submissions
        let isSubmitting = false;
        
        // Enhanced AJAX form submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Prevent double submission
            if (isSubmitting) {
                return false;
            }
            
            // Get form values
            const firstName = document.getElementById('firstName').value.trim();
            const middleName = document.getElementById('middleName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            const department = document.getElementById('department').value;
            const role = document.getElementById('role').value;
            const reason = document.getElementById('reason').value.trim();
            const agreement = document.getElementById('agreement').checked;
            const employeeId = document.getElementById('employeeId').value.trim();
            const phone = document.getElementById('phone').value.trim();
            
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
            } else if (!isValidGmailEmail(email)) {
                showFieldError('email', 'Only Gmail addresses (@gmail.com) are accepted.');
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
            
            // Validate phone format if provided
            if (phone && !/^(\+639|09)\d{9}$/.test(phone)) {
                showFieldError('phone', 'Phone number must be in format +639XXXXXXXXX or 09XXXXXXXXX');
                hasErrors = true;
            }
            
            // Validate employee ID format if provided
            if (employeeId && !/^[a-zA-Z0-9]+$/.test(employeeId)) {
                showFieldError('employeeId', 'Employee ID can only contain letters and numbers');
                hasErrors = true;
            }
            
            if (!agreement) {
                showFieldError('agreement', 'You must agree to the terms and conditions.');
                hasErrors = true;
            }
            
            // If there are validation errors, stop here
            if (hasErrors) {
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
            
            // Mark as submitting
            isSubmitting = true;
            
            // Get submit button
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            // Show loading state
            submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Submitting Request...';
            submitButton.disabled = true;
            
            // Prepare form data
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch('<?= base_url('auth/contact-admin') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showAlert('success', data.message || 'Your request has been submitted successfully! Our IT Administrator will review your request and contact you within 24 hours during business days.');
                    
                    // Reset form
                    document.getElementById('contactForm').reset();
                    
                    // Reset role select
                    document.getElementById('role').disabled = true;
                    document.getElementById('role').innerHTML = '<option value="">-- Select Department First --</option>';
                    document.getElementById('roleHint').textContent = 'Please select a department to see available roles';
                    document.getElementById('roleHint').className = 'text-muted';
                    
                    // Scroll to top to show success message
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                } else {
                    // Show error message
                    showAlert('danger', data.message || 'An error occurred while submitting your request. Please try again.');
                    
                    // If there are field-specific errors, show them
                    if (data.errors) {
                        Object.keys(data.errors).forEach(fieldName => {
                            showFieldError(fieldName, data.errors[fieldName]);
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'An unexpected error occurred. Please try again or contact IT support directly at it-support@webuild.com');
            })
            .finally(() => {
                // Reset submitting state
                isSubmitting = false;
                
                // Restore button
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
            
            return false;
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
        }          function isValidGmailEmail(email) {
            // Only accept Gmail addresses
            const gmailRegex = /^[a-zA-Z0-9._%+\-]+@gmail\.com$/;
            return gmailRegex.test(email);
        }
        
        /**
         * Show alert message at the top of the form
         */
        function showAlert(type, message) {
            // Remove any existing alerts
            const existingAlerts = document.querySelectorAll('.dynamic-alert');
            existingAlerts.forEach(alert => alert.remove());
            
            // Create alert element
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show dynamic-alert`;
            alertDiv.setAttribute('role', 'alert');
            
            // Determine icon based on type
            let icon = 'bi-check-circle-fill';
            if (type === 'danger') {
                icon = 'bi-exclamation-triangle-fill';
            } else if (type === 'warning') {
                icon = 'bi-exclamation-circle-fill';
            } else if (type === 'info') {
                icon = 'bi-info-circle-fill';
            }
            
            alertDiv.innerHTML = `
                <i class="bi ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Insert at the top of the form container
            const formContainer = document.querySelector('.card-body');
            const firstElement = formContainer.querySelector('.text-center');
            firstElement.insertAdjacentElement('afterend', alertDiv);
            
            // Auto-dismiss after 10 seconds
            setTimeout(() => {
                const closeButton = alertDiv.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.click();
                }
            }, 10000);
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