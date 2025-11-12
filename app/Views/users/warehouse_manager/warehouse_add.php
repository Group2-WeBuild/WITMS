<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .main-content {
            margin-left: 250px;
            padding-top: 90px;
            padding-right: 20px;
            padding-left: 20px;
            padding-bottom: 30px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .required::after {
            content: " *";
            color: red;
        }    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user]) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user]) ?>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-10 col-xl-8 mx-auto">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="bi bi-plus-circle"></i> Add New Warehouse</h2>
                        <p class="text-muted">Create a new warehouse location</p>
                    </div>
                    <a href="<?=base_url('/warehouse-manager/warehouse-management')?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>

                <!-- Alert Messages -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>                <div class="form-container">
                    <form action="<?= base_url('/warehouse-manager/warehouse/store') ?>" method="POST">
                        <?= csrf_field() ?>

                        <!-- Basic Information -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="bi bi-info-circle"></i> Basic Information
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label required">Warehouse Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= old('name') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="code" class="form-label required">Warehouse Code</label>
                                    <input type="text" class="form-control" id="code" name="code" 
                                           placeholder="e.g., WH-001" value="<?= old('code') ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="capacity" class="form-label required">Capacity (m²)</label>
                                    <input type="number" class="form-control" id="capacity" name="capacity" 
                                           step="0.01" value="<?= old('capacity') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="manager_id" class="form-label">Manager (Optional)</label>
                                    <input type="number" class="form-control" id="manager_id" name="manager_id" 
                                           value="<?= old('manager_id') ?>" placeholder="User ID">
                                    <small class="text-muted">Leave empty to assign later</small>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="bi bi-geo-alt"></i> Location Information
                            </div>
                            <div class="mb-3">
                                <label for="street_address" class="form-label">Street Address</label>
                                <input type="text" class="form-control" id="street_address" name="street_address" 
                                       value="<?= old('street_address') ?>" placeholder="House/Building No., Street Name">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="barangay" class="form-label">Barangay</label>
                                    <input type="text" class="form-control" id="barangay" name="barangay" 
                                           value="<?= old('barangay') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label required">City/Municipality</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?= old('city') ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="province" class="form-label required">Province</label>
                                    <input type="text" class="form-control" id="province" name="province" 
                                           value="<?= old('province') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="region" class="form-label required">Region</label>
                                    <select class="form-select" id="region" name="region" required>
                                        <option value="">Select Region</option>
                                        <option value="NCR (National Capital Region)">NCR (National Capital Region)</option>
                                        <option value="Region I (Ilocos Region)">Region I (Ilocos Region)</option>
                                        <option value="CAR (Cordillera Administrative Region)">CAR (Cordillera Administrative Region)</option>
                                        <option value="Region II (Cagayan Valley)">Region II (Cagayan Valley)</option>
                                        <option value="Region III (Central Luzon)">Region III (Central Luzon)</option>
                                        <option value="Region IV-A (CALABARZON)">Region IV-A (CALABARZON)</option>
                                        <option value="MIMAROPA Region">MIMAROPA Region</option>
                                        <option value="Region V (Bicol Region)">Region V (Bicol Region)</option>
                                        <option value="Region VI (Western Visayas)">Region VI (Western Visayas)</option>
                                        <option value="Region VII (Central Visayas)">Region VII (Central Visayas)</option>
                                        <option value="Region VIII (Eastern Visayas)">Region VIII (Eastern Visayas)</option>
                                        <option value="Region IX (Zamboanga Peninsula)">Region IX (Zamboanga Peninsula)</option>
                                        <option value="Region X (Northern Mindanao)">Region X (Northern Mindanao)</option>
                                        <option value="Region XI (Davao Region)">Region XI (Davao Region)</option>
                                        <option value="Region XII (SOCCSKSARGEN)">Region XII (SOCCSKSARGEN)</option>
                                        <option value="Region XIII (Caraga)">Region XIII (Caraga)</option>
                                        <option value="BARMM (Bangsamoro Autonomous Region in Muslim Mindanao)">BARMM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                           value="<?= old('postal_code') ?>" maxlength="10">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" 
                                           value="<?= old('country', 'Philippines') ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Coordinates (Optional) -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="bi bi-pin-map"></i> GPS Coordinates (Optional)
                            </div>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> <strong>Optional:</strong> Adding GPS coordinates allows instant map plotting. 
                                Without coordinates, the system will use geocoding (slower).
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <input type="number" class="form-control" id="latitude" name="latitude" 
                                           step="0.00000001" value="<?= old('latitude') ?>" placeholder="e.g., 14.5995">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <input type="number" class="form-control" id="longitude" name="longitude" 
                                           step="0.00000001" value="<?= old('longitude') ?>" placeholder="e.g., 120.9842">
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?=base_url('/warehouse-manager/warehouse-management')?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Create Warehouse

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
