<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Warehouse Management') ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <!-- Include Mobile Styles -->
    <?= view('templates/mobile_styles') ?>
    
    <style>
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
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }
        .table-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 120px;
            position: relative;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .stat-card.primary { border-left-color: #0d6efd; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
        .stat-card.info { border-left-color: #0dcaf0; }
        .stat-card p {
            margin: 0 0 8px 0;
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
            line-height: 1.2;
        }
        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            position: absolute;
            top: 20px;
            right: 20px;
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
        }
    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Warehouse Management']) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1"><i class="bi bi-building"></i> Warehouse Management</h2>
                        <p class="text-muted mb-0">Create and manage warehouse locations</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWarehouseModal">
                            <i class="bi bi-plus-circle"></i> Create Warehouse
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card primary">
                        <p>Total Warehouses</p>
                        <h3><?= $stats['total'] ?? 0 ?></h3>
                        <div class="icon bg-primary text-white">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card success">
                        <p>Active Warehouses</p>
                        <h3><?= $stats['active'] ?? 0 ?></h3>
                        <div class="icon bg-success text-white">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card warning">
                        <p>Inactive Warehouses</p>
                        <h3><?= $stats['inactive'] ?? 0 ?></h3>
                        <div class="icon bg-warning text-white">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card info">
                        <p>With Managers</p>
                        <h3><?= $stats['with_managers'] ?? 0 ?></h3>
                        <div class="icon bg-info text-white">
                            <i class="bi bi-person-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warehouses Table -->
            <div class="card table-card">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0"><i class="bi bi-list-ul"></i> All Warehouses</h5>
                        </div>
                        <div class="col-auto">
                            <div class="input-group" style="width: 250px;">
                                <input type="text" class="form-control" placeholder="Search warehouses..." id="searchWarehouses">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="warehousesTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Location</th>
                                    <th>Capacity (m²)</th>
                                    <th>Manager</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($warehouses as $warehouse): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($warehouse['name']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= esc($warehouse['code']) ?></span>
                                        </td>
                                        <td>
                                            <?php
                                                $warehouseModel = new \App\Models\WarehouseModel();
                                                $location = $warehouseModel->getWarehouseWithLocation($warehouse['id']);
                                                $locationText = 'N/A';
                                                if ($location && isset($location['city'])) {
                                                    $parts = array_filter([
                                                        $location['city'] ?? '',
                                                        $location['province'] ?? '',
                                                        $location['region'] ?? ''
                                                    ]);
                                                    $locationText = !empty($parts) ? implode(', ', $parts) : 'N/A';
                                                }
                                            ?>
                                            <small><?= esc($locationText) ?></small>
                                        </td>
                                        <td><?= $warehouse['capacity'] ? number_format($warehouse['capacity'], 2) : 'N/A' ?></td>
                                        <td>
                                            <?php if ($warehouse['manager_id'] && isset($warehouse['first_name'])): ?>
                                                <?= esc(trim($warehouse['first_name'] . ' ' . $warehouse['last_name'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">No Manager</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($warehouse['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-primary" onclick="editWarehouse(<?= $warehouse['id'] ?>)" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteWarehouse(<?= $warehouse['id'] ?>, '<?= esc($warehouse['name']) ?>')" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Warehouse Modal -->
    <div class="modal fade" id="createWarehouseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Create New Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createWarehouseForm">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="bi bi-info-circle"></i> Basic Information
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="createName" class="form-label required">Warehouse Name</label>
                                    <input type="text" class="form-control" id="createName" 
                                           pattern="[A-Za-z\s]+" 
                                           title="Warehouse name can only contain letters and spaces. Special characters are not allowed." 
                                           required>
                                    <small class="text-muted">Letters and spaces only. No special characters allowed.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="createCode" class="form-label required">Warehouse Code</label>
                                    <input type="text" class="form-control" id="createCode" 
                                           pattern="WH-?\d{3,}" 
                                           title="Warehouse code must be in format WH-001 or WH001 (e.g., WH-001, WH001, WH-123). Special characters are not allowed." 
                                           placeholder="e.g., WH-001 or WH001" required>
                                    <small class="text-muted">Format: WH-001 or WH001. No special characters allowed.</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="createCapacity" class="form-label">Capacity (m²)</label>
                                    <input type="number" class="form-control" id="createCapacity" 
                                           step="0.01" min="0" 
                                           title="Capacity must be a positive number or zero.">
                                    <small class="text-muted">Numbers only. Must be positive or zero.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="createManager" class="form-label required">Manager</label>
                                    <select class="form-select" id="createManager" required>
                                        <option value="">-- Select Manager --</option>
                                        <?php if (!empty($managers)): ?>
                                            <?php foreach ($managers as $manager): ?>
                                                <option value="<?= $manager['id'] ?>">
                                                    <?= esc(trim($manager['first_name'] . ' ' . $manager['last_name'])) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <small class="form-text text-muted">The selected manager will be automatically assigned to this warehouse</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="createIsActive" checked>
                                    <label class="form-check-label" for="createIsActive">
                                        Active Warehouse
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="bi bi-geo-alt"></i> Location Information
                            </div>
                            <div class="mb-3">
                                <label for="createStreetAddress" class="form-label">Street Address</label>
                                <input type="text" class="form-control" id="createStreetAddress" placeholder="House/Building No., Street Name">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="createBarangay" class="form-label">Barangay</label>
                                    <input type="text" class="form-control" id="createBarangay">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="createRegion" class="form-label">Region</label>
                                    <select class="form-select" id="createRegion" onchange="updateProvinces('create')">
                                        <option value="">-- Select Region --</option>
                                        <?php if (!empty($regions)): ?>
                                            <?php foreach ($regions as $region): ?>
                                                <option value="<?= esc($region['region']) ?>"><?= esc($region['region']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="createProvince" class="form-label">Province</label>
                                    <select class="form-select" id="createProvince" onchange="updateCities('create')" disabled>
                                        <option value="">-- Select Province --</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="createCity" class="form-label required">City/Municipality</label>
                                    <select class="form-select" id="createCity" onchange="updateLocationDetails('create')" required disabled>
                                        <option value="">-- Select City --</option>
                                    </select>
                                    <small class="text-muted">Or enter manually below</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="createCityManual" class="form-label">City (Manual Entry)</label>
                                    <input type="text" class="form-control" id="createCityManual" placeholder="Enter city if not in list">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="createPostalCode" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="createPostalCode" 
                                           pattern="\d+" 
                                           title="Postal code must contain numbers only. Special characters and letters are not allowed.">
                                    <small class="text-muted">Numbers only. No special characters or letters allowed.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="createCountry" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="createCountry" value="Philippines">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="createLatitude" class="form-label">Latitude</label>
                                    <input type="number" class="form-control" id="createLatitude" step="any">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="createLongitude" class="form-label">Longitude</label>
                                    <input type="number" class="form-control" id="createLongitude" step="any">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitCreateWarehouse()">
                        <i class="bi bi-check-circle"></i> Create Warehouse
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Warehouse Modal -->
    <div class="modal fade" id="editWarehouseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="editWarehouseContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Location dropdown data from PHP
        const locationData = <?= $locationDropdownData ?? '[]' ?>;
        
        // Initialize DataTables
        $(document).ready(function() {
            $('#warehousesTable').DataTable({
                order: [[0, 'asc']],
                pageLength: 25,
                responsive: true
            });

            // Search functionality
            $('#searchWarehouses').on('keyup', function() {
                $('#warehousesTable').DataTable().search(this.value).draw();
            });
        });

        // Cascading dropdown functions
        function updateProvinces(prefix) {
            const regionSelect = document.getElementById(prefix + 'Region');
            const provinceSelect = document.getElementById(prefix + 'Province');
            const citySelect = document.getElementById(prefix + 'City');
            
            const selectedRegion = regionSelect.value;
            
            // Clear and disable dependent dropdowns
            provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
            citySelect.innerHTML = '<option value="">-- Select City --</option>';
            provinceSelect.disabled = !selectedRegion;
            citySelect.disabled = true;
            
            if (!selectedRegion) return;
            
            // Find the region in locationData
            const regionData = locationData.find(r => r.region === selectedRegion);
            if (regionData && regionData.provinces) {
                regionData.provinces.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.province;
                    option.textContent = province.province;
                    provinceSelect.appendChild(option);
                });
                provinceSelect.disabled = false;
            }
        }

        function updateCities(prefix) {
            const regionSelect = document.getElementById(prefix + 'Region');
            const provinceSelect = document.getElementById(prefix + 'Province');
            const citySelect = document.getElementById(prefix + 'City');
            
            const selectedRegion = regionSelect.value;
            const selectedProvince = provinceSelect.value;
            
            // Clear city dropdown
            citySelect.innerHTML = '<option value="">-- Select City --</option>';
            citySelect.disabled = !selectedProvince;
            
            if (!selectedRegion || !selectedProvince) return;
            
            // Find the region and province in locationData
            const regionData = locationData.find(r => r.region === selectedRegion);
            if (regionData && regionData.provinces) {
                const provinceData = regionData.provinces.find(p => p.province === selectedProvince);
                if (provinceData && provinceData.cities) {
                    provinceData.cities.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.id;
                        option.textContent = city.city;
                        option.setAttribute('data-city-name', city.city);
                        citySelect.appendChild(option);
                    });
                    citySelect.disabled = false;
                }
            }
        }

        function updateLocationDetails(prefix) {
            const citySelect = document.getElementById(prefix + 'City');
            const cityManualInput = document.getElementById(prefix + 'CityManual');
            const selectedOption = citySelect.options[citySelect.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const cityName = selectedOption.getAttribute('data-city-name') || selectedOption.textContent;
                cityManualInput.value = cityName;
                
                // Optionally fetch and populate other location details via AJAX
                // For now, user can manually enter province/region or we can enhance this later
            }
        }

        function submitCreateWarehouse() {
            const form = document.getElementById('createWarehouseForm');
            
            // Validate warehouse name
            const nameInput = document.getElementById('createName');
            const namePattern = /^[A-Za-z\s]+$/;
            if (!namePattern.test(nameInput.value.trim())) {
                alert('Warehouse name can only contain letters and spaces. Special characters are not allowed.');
                nameInput.focus();
                return;
            }
            
            // Validate warehouse code
            const codeInput = document.getElementById('createCode');
            const codePattern = /^WH-?\d{3,}$/;
            if (!codePattern.test(codeInput.value.trim())) {
                alert('Warehouse code must be in format WH-001 or WH001 (e.g., WH-001, WH001, WH-123). Special characters are not allowed.');
                codeInput.focus();
                return;
            }
            
            // Validate capacity if provided
            const capacityInput = document.getElementById('createCapacity');
            if (capacityInput.value && (isNaN(capacityInput.value) || parseFloat(capacityInput.value) < 0)) {
                alert('Capacity must be a positive number or zero.');
                capacityInput.focus();
                return;
            }
            
            // Validate postal code if provided
            const postalCodeInput = document.getElementById('createPostalCode');
            if (postalCodeInput.value) {
                const postalPattern = /^\d+$/;
                if (!postalPattern.test(postalCodeInput.value.trim())) {
                    alert('Postal code must contain numbers only. Special characters and letters are not allowed.');
                    postalCodeInput.focus();
                    return;
                }
            }
            
            // Validate manager is selected
            const managerInput = document.getElementById('createManager');
            if (!managerInput.value) {
                alert('Please select a Warehouse Manager. This field is required.');
                managerInput.focus();
                return;
            }
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Get city from dropdown or manual input
            const cityDropdown = document.getElementById('createCity');
            const cityManual = document.getElementById('createCityManual');
            const selectedCityOption = cityDropdown.options[cityDropdown.selectedIndex];
            const cityValue = selectedCityOption && selectedCityOption.value 
                ? selectedCityOption.getAttribute('data-city-name') || selectedCityOption.textContent
                : (cityManual.value || '');

            const formData = {
                name: document.getElementById('createName').value,
                code: document.getElementById('createCode').value,
                capacity: document.getElementById('createCapacity').value || null,
                manager_id: document.getElementById('createManager').value || null,
                is_active: document.getElementById('createIsActive').checked ? '1' : '0',
                street_address: document.getElementById('createStreetAddress').value || null,
                barangay: document.getElementById('createBarangay').value || null,
                city: cityValue,
                province: document.getElementById('createProvince').value || null,
                region: document.getElementById('createRegion').value || null,
                postal_code: document.getElementById('createPostalCode').value || null,
                country: document.getElementById('createCountry').value || 'Philippines',
                latitude: document.getElementById('createLatitude').value || null,
                longitude: document.getElementById('createLongitude').value || null
            };

            $.ajax({
                url: '<?= base_url('it-administrator/warehouse-management/create') ?>',
                method: 'POST',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Warehouse created successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('createWarehouseModal')).hide();
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to create warehouse'));
                        if (response.errors) {
                            console.error('Validation errors:', response.errors);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        function editWarehouse(warehouseId) {
            const modal = new bootstrap.Modal(document.getElementById('editWarehouseModal'));
            document.getElementById('editWarehouseContent').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            modal.show();

            $.ajax({
                url: '<?= base_url('it-administrator/warehouse-management/get') ?>',
                method: 'POST',
                data: { warehouse_id: warehouseId },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        const warehouse = response.warehouse;
                        const location = warehouse.location || {};
                        
                        // Get managers for dropdown
                        const managers = <?= json_encode($managers ?? []) ?>;
                        let managersOptions = '<option value="">-- Select Manager --</option>';
                        if (managers && managers.length > 0) {
                            managers.forEach(manager => {
                                managersOptions += `<option value="${manager.id}" ${manager.id == warehouse.manager_id ? 'selected' : ''}>${manager.first_name} ${manager.last_name}</option>`;
                            });
                        }
                        
                        // Initialize location dropdowns after a short delay to ensure DOM is ready
                        setTimeout(function() {
                            if (location.region) {
                                updateProvinces('edit');
                                // Set province after provinces are loaded
                                setTimeout(function() {
                                    if (location.province) {
                                        const provinceSelect = document.getElementById('editProvince');
                                        provinceSelect.value = location.province;
                                        updateCities('edit');
                                        // Set city after cities are loaded
                                        setTimeout(function() {
                                            if (location.city) {
                                                const citySelect = document.getElementById('editCity');
                                                // Try to find city by name
                                                for (let i = 0; i < citySelect.options.length; i++) {
                                                    if (citySelect.options[i].getAttribute('data-city-name') === location.city) {
                                                        citySelect.value = citySelect.options[i].value;
                                                        break;
                                                    }
                                                }
                                            }
                                        }, 100);
                                    }
                                }, 100);
                            }
                        }, 100);

                        // Build region options
                        const regions = <?= json_encode($regions ?? []) ?>;
                        let regionOptions = '<option value="">-- Select Region --</option>';
                        regions.forEach(region => {
                            const selected = location.region === region.region ? 'selected' : '';
                            regionOptions += `<option value="${region.region}" ${selected}>${region.region}</option>`;
                        });

                        document.getElementById('editWarehouseContent').innerHTML = `
                            <form id="editWarehouseForm">
                                <input type="hidden" id="editWarehouseId" value="${warehouse.id}">
                                
                                <!-- Basic Information -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="bi bi-info-circle"></i> Basic Information
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="editName" class="form-label required">Warehouse Name</label>
                                            <input type="text" class="form-control" id="editName" 
                                                   pattern="[A-Za-z\s]+" 
                                                   title="Warehouse name can only contain letters and spaces. Special characters are not allowed." 
                                                   value="${warehouse.name || ''}" required>
                                            <small class="text-muted">Letters and spaces only. No special characters allowed.</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editCode" class="form-label required">Warehouse Code</label>
                                            <input type="text" class="form-control" id="editCode" 
                                                   pattern="WH-?\d{3,}" 
                                                   title="Warehouse code must be in format WH-001 or WH001 (e.g., WH-001, WH001, WH-123). Special characters are not allowed." 
                                                   value="${warehouse.code || ''}" required>
                                            <small class="text-muted">Format: WH-001 or WH001. No special characters allowed.</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="editCapacity" class="form-label">Capacity (m²)</label>
                                            <input type="number" class="form-control" id="editCapacity" 
                                                   step="0.01" min="0" 
                                                   title="Capacity must be a positive number or zero." 
                                                   value="${warehouse.capacity || ''}">
                                            <small class="text-muted">Numbers only. Must be positive or zero.</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editManager" class="form-label required">Manager</label>
                                            <select class="form-select" id="editManager" required>
                                                ${managersOptions}
                                            </select>
                                            <small class="form-text text-muted">The selected manager will be automatically assigned to this warehouse</small>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editIsActive" ${warehouse.is_active ? 'checked' : ''}>
                                            <label class="form-check-label" for="editIsActive">
                                                Active Warehouse
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Location Information -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="bi bi-geo-alt"></i> Location Information
                                    </div>
                                    <div class="mb-3">
                                        <label for="editStreetAddress" class="form-label">Street Address</label>
                                        <input type="text" class="form-control" id="editStreetAddress" value="${location.street_address || ''}">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="editBarangay" class="form-label">Barangay</label>
                                            <input type="text" class="form-control" id="editBarangay" value="${location.barangay || ''}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editRegion" class="form-label">Region</label>
                                            <select class="form-select" id="editRegion" onchange="updateProvinces('edit')">
                                                ${regionOptions}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="editProvince" class="form-label">Province</label>
                                            <select class="form-select" id="editProvince" onchange="updateCities('edit')" disabled>
                                                <option value="">-- Select Province --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editCity" class="form-label required">City/Municipality</label>
                                            <select class="form-select" id="editCity" onchange="updateLocationDetails('edit')" required disabled>
                                                <option value="">-- Select City --</option>
                                            </select>
                                            <small class="text-muted">Or enter manually below</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="editCityManual" class="form-label">City (Manual Entry)</label>
                                            <input type="text" class="form-control" id="editCityManual" value="${location.city || ''}" placeholder="Enter city if not in list">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="editPostalCode" class="form-label">Postal Code</label>
                                            <input type="text" class="form-control" id="editPostalCode" 
                                                   pattern="\d+" 
                                                   title="Postal code must contain numbers only. Special characters and letters are not allowed." 
                                                   value="${location.postal_code || ''}">
                                            <small class="text-muted">Numbers only. No special characters or letters allowed.</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editCountry" class="form-label">Country</label>
                                            <input type="text" class="form-control" id="editCountry" value="${location.country || 'Philippines'}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="editLatitude" class="form-label">Latitude</label>
                                            <input type="number" class="form-control" id="editLatitude" step="any" value="${location.latitude || ''}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editLongitude" class="form-label">Longitude</label>
                                            <input type="number" class="form-control" id="editLongitude" step="any" value="${location.longitude || ''}">
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="submitEditWarehouse()">
                                    <i class="bi bi-check-circle"></i> Update Warehouse
                                </button>
                            </div>
                        `;
                    } else {
                        alert('Error: ' + (response.message || 'Failed to load warehouse'));
                        bootstrap.Modal.getInstance(document.getElementById('editWarehouseModal')).hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    bootstrap.Modal.getInstance(document.getElementById('editWarehouseModal')).hide();
                }
            });
        }

        function submitEditWarehouse() {
            const form = document.getElementById('editWarehouseForm');
            
            // Validate warehouse name
            const nameInput = document.getElementById('editName');
            const namePattern = /^[A-Za-z\s]+$/;
            if (!namePattern.test(nameInput.value.trim())) {
                alert('Warehouse name can only contain letters and spaces. Special characters are not allowed.');
                nameInput.focus();
                return;
            }
            
            // Validate warehouse code
            const codeInput = document.getElementById('editCode');
            const codePattern = /^WH-?\d{3,}$/;
            if (!codePattern.test(codeInput.value.trim())) {
                alert('Warehouse code must be in format WH-001 or WH001 (e.g., WH-001, WH001, WH-123). Special characters are not allowed.');
                codeInput.focus();
                return;
            }
            
            // Validate capacity if provided
            const capacityInput = document.getElementById('editCapacity');
            if (capacityInput.value && (isNaN(capacityInput.value) || parseFloat(capacityInput.value) < 0)) {
                alert('Capacity must be a positive number or zero.');
                capacityInput.focus();
                return;
            }
            
            // Validate postal code if provided
            const postalCodeInput = document.getElementById('editPostalCode');
            if (postalCodeInput.value) {
                const postalPattern = /^\d+$/;
                if (!postalPattern.test(postalCodeInput.value.trim())) {
                    alert('Postal code must contain numbers only. Special characters and letters are not allowed.');
                    postalCodeInput.focus();
                    return;
                }
            }
            
            // Validate manager is selected
            const managerInput = document.getElementById('editManager');
            if (!managerInput.value) {
                alert('Please select a Warehouse Manager. This field is required.');
                managerInput.focus();
                return;
            }
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Get city from dropdown or manual input
            const cityDropdown = document.getElementById('editCity');
            const cityManual = document.getElementById('editCityManual');
            const selectedCityOption = cityDropdown.options[cityDropdown.selectedIndex];
            const cityValue = selectedCityOption && selectedCityOption.value 
                ? selectedCityOption.getAttribute('data-city-name') || selectedCityOption.textContent
                : (cityManual.value || '');

            const formData = {
                warehouse_id: document.getElementById('editWarehouseId').value,
                name: document.getElementById('editName').value,
                code: document.getElementById('editCode').value,
                capacity: document.getElementById('editCapacity').value || null,
                manager_id: document.getElementById('editManager').value || null,
                is_active: document.getElementById('editIsActive').checked ? '1' : '0',
                street_address: document.getElementById('editStreetAddress').value || null,
                barangay: document.getElementById('editBarangay').value || null,
                city: cityValue,
                province: document.getElementById('editProvince').value || null,
                region: document.getElementById('editRegion').value || null,
                postal_code: document.getElementById('editPostalCode').value || null,
                country: document.getElementById('editCountry').value || 'Philippines',
                latitude: document.getElementById('editLatitude').value || null,
                longitude: document.getElementById('editLongitude').value || null
            };

            $.ajax({
                url: '<?= base_url('it-administrator/warehouse-management/update') ?>',
                method: 'POST',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Warehouse updated successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('editWarehouseModal')).hide();
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to update warehouse'));
                        if (response.errors) {
                            console.error('Validation errors:', response.errors);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        function deleteWarehouse(warehouseId, warehouseName) {
            if (!confirm(`Are you sure you want to delete "${warehouseName}"?\n\nThis will soft delete the warehouse. It can be restored later if needed.`)) {
                return;
            }

            $.ajax({
                url: '<?= base_url('it-administrator/warehouse-management/delete') ?>',
                method: 'POST',
                data: { warehouse_id: warehouseId },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Warehouse deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to delete warehouse'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }
    </script>
</body>
</html>



