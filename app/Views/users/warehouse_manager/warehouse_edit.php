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
                        <h2><i class="bi bi-pencil"></i> Edit Warehouse</h2>
                        <p class="text-muted">Update warehouse information</p>
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
                    <form action="<?= base_url('/warehouse-manager/warehouse/update/' . $warehouse['id']) ?>" method="POST">
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
                                           value="<?= old('name', $warehouse['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="code" class="form-label required">Warehouse Code</label>
                                    <input type="text" class="form-control" id="code" name="code" 
                                           value="<?= old('code', $warehouse['code']) ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="capacity" class="form-label required">Capacity (m²)</label>
                                    <input type="number" class="form-control" id="capacity" name="capacity" 
                                           step="0.01" value="<?= old('capacity', $warehouse['capacity']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="manager_id" class="form-label">Manager (Optional)</label>
                                    <input type="number" class="form-control" id="manager_id" name="manager_id" 
                                           value="<?= old('manager_id', $warehouse['manager_id']) ?>" placeholder="User ID">
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
                                       value="<?= old('street_address', $warehouse['street_address'] ?? '') ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="barangay" class="form-label">Barangay</label>
                                    <input type="text" class="form-control" id="barangay" name="barangay" 
                                           value="<?= old('barangay', $warehouse['barangay'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label required">City/Municipality</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?= old('city', $warehouse['city'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="province" class="form-label required">Province</label>
                                    <input type="text" class="form-control" id="province" name="province" 
                                           value="<?= old('province', $warehouse['province'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="region" class="form-label required">Region</label>
                                    <select class="form-select" id="region" name="region" required>
                                        <option value="">Select Region</option>
                                        <?php 
                                        $regions = [
                                            "NCR (National Capital Region)",
                                            "Region I (Ilocos Region)",
                                            "CAR (Cordillera Administrative Region)",
                                            "Region II (Cagayan Valley)",
                                            "Region III (Central Luzon)",
                                            "Region IV-A (CALABARZON)",
                                            "MIMAROPA Region",
                                            "Region V (Bicol Region)",
                                            "Region VI (Western Visayas)",
                                            "Region VII (Central Visayas)",
                                            "Region VIII (Eastern Visayas)",
                                            "Region IX (Zamboanga Peninsula)",
                                            "Region X (Northern Mindanao)",
                                            "Region XI (Davao Region)",
                                            "Region XII (SOCCSKSARGEN)",
                                            "Region XIII (Caraga)",
                                            "BARMM (Bangsamoro Autonomous Region in Muslim Mindanao)"
                                        ];
                                        $selectedRegion = old('region', $warehouse['region'] ?? '');
                                        foreach ($regions as $region): ?>
                                            <option value="<?= $region ?>" <?= $selectedRegion === $region ? 'selected' : '' ?>>
                                                <?= $region ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                           value="<?= old('postal_code', $warehouse['postal_code'] ?? '') ?>" maxlength="10">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" 
                                           value="<?= old('country', $warehouse['country'] ?? 'Philippines') ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Coordinates -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="bi bi-pin-map"></i> GPS Coordinates (Optional)
                            </div>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> <strong>Optional:</strong> Adding GPS coordinates allows instant map plotting.
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <input type="number" class="form-control" id="latitude" name="latitude" 
                                           step="0.00000001" value="<?= old('latitude', $warehouse['latitude'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <input type="number" class="form-control" id="longitude" name="longitude" 
                                           step="0.00000001" value="<?= old('longitude', $warehouse['longitude'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?=base_url('/warehouse-manager/warehouse-management')?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="bi bi-check-circle"></i> Update Warehouse
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
