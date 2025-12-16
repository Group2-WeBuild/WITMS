<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Items - WITMS</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
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
        
        #qr-reader {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: white;
            min-height: 300px;
        }
        
        .scanned-items {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .scanned-item {
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .scanned-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-input {
            width: 80px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding-left: 15px;
                padding-right: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Scan Items']) ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6">
                    <!-- Scanner Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-qr-code-scan"></i> Scan Items
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="qr-reader"></div>
                            
                            <div class="d-flex justify-content-center gap-2 mt-3">
                                <button id="stop-camera" class="btn btn-danger" style="display:none;">
                                    <i class="bi bi-stop-circle"></i> Stop Camera
                                </button>
                            </div>

                            <!-- Manual Input -->
                            <div class="mt-3">
                                <div class="input-group">
                                    <input type="text" id="manual-input" class="form-control" placeholder="Enter material code...">
                                    <button class="btn btn-outline-primary" id="manual-submit">
                                        <i class="bi bi-search"></i> Add
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Upload QR Image -->
                            <div class="mt-3">
                                <label class="form-label"><i class="bi bi-upload"></i> Or Upload QR Code Image</label>
                                <input type="file" id="qr-upload" class="form-control" accept="image/*" capture="environment">
                                <small class="text-muted">Take a photo of the QR code or upload from gallery</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <!-- Scanned Items List -->
                    <div class="scanned-items">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="bi bi-list-check"></i> Scanned Items
                                <span class="badge bg-primary ms-2" id="item-count">0</span>
                            </h5>
                            <button id="clear-all" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i> Clear All
                            </button>
                        </div>
                        
                        <div id="scanned-items-list">
                            <p class="text-muted text-center">No items scanned yet</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4" id="action-buttons" style="display:none;">
                            <div class="d-grid gap-2">
                                <button id="issue-stock-btn" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#issueStockModal">
                                    <i class="bi bi-box-arrow-up"></i> Issue Stock
                                </button>
                                <button id="transfer-stock-btn" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#transferStockModal">
                                    <i class="bi bi-arrow-left-right"></i> Transfer Stock
                                </button>
                                <button id="adjust-stock-btn" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
                                    <i class="bi bi-pencil-square"></i> Adjust Stock
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Html5 QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    
    <script>
        let html5QrcodeScanner = null;
        let scannedItems = [];
        let lastScannedCode = '';
        let scanCooldown = false;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateItemCount();
            startScanner();
        });

        // Stop camera
        document.getElementById('stop-camera').addEventListener('click', function() {
            stopScanner();
        });

        // Manual input
        document.getElementById('manual-submit').addEventListener('click', function() {
            const input = document.getElementById('manual-input').value.trim();
            if (input) {
                processScanResult(input);
                document.getElementById('manual-input').value = '';
            }
        });

        document.getElementById('manual-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const input = this.value.trim();
                if (input) {
                    processScanResult(input);
                    this.value = '';
                }
            }
        });

        // QR Code Upload
        document.getElementById('qr-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const html5QrCode = new Html5Qrcode("qr-reader");
                html5QrCode.scanFile(file, true)
                    .then(decodedText => {
                        showNotification('QR Code detected!', 'success');
                        processScanResult(decodedText);
                        document.getElementById('qr-upload').value = '';
                    })
                    .catch(err => {
                        console.error('Error reading QR from image:', err);
                        alert('Could not detect QR code in image. Please try again.');
                        document.getElementById('qr-upload').value = '';
                    });
            }
        });

        // Clear all items
        document.getElementById('clear-all').addEventListener('click', function() {
            if (confirm('Clear all scanned items?')) {
                scannedItems = [];
                updateScannedItemsList();
            }
        });

        // Action buttons now trigger modals (handled by Bootstrap data attributes)

        function startScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(err => console.log(err));
            }
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader",
                { 
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                    rememberLastUsedCamera: true
                },
                false
            );
            
            html5QrcodeScanner.render(onScanSuccess, onScanError);
            
            const stopBtn = document.getElementById('stop-camera');
            if (stopBtn) stopBtn.style.display = 'inline-block';
        }
        
        function onScanSuccess(decodedText, decodedResult) {
            // Prevent duplicate scans
            if (scanCooldown || decodedText === lastScannedCode) {
                return;
            }
            
            console.log(`QR Code detected: ${decodedText}`);
            lastScannedCode = decodedText;
            scanCooldown = true;
            
            // Reset cooldown after 2 seconds
            setTimeout(() => {
                scanCooldown = false;
            }, 2000);
            
            processScanResult(decodedText);
        }
        
        function onScanError(error) {
            // Ignore continuous scan errors
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(err => console.log(err));
                html5QrcodeScanner = null;
            }
            
            const stopBtn = document.getElementById('stop-camera');
            if (stopBtn) stopBtn.style.display = 'none';
            
            document.getElementById('qr-reader').innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-camera-video-off display-1 text-muted"></i>
                    <p class="text-muted mt-3">Camera stopped. Use manual input or file upload.</p>
                </div>
            `;
        }

        function processScanResult(decodedText) {
            let scanData;
            
            // Clean the decoded text - remove any whitespace or extra characters
            decodedText = decodedText.trim();
            
            // Check if the text contains multiple JSON objects (common QR scanner issue)
            // Try to extract the first valid JSON object
            if (decodedText.includes('}{')) {
                // Multiple JSON objects concatenated - extract the first one
                const firstJsonEnd = decodedText.indexOf('}');
                if (firstJsonEnd > 0) {
                    decodedText = decodedText.substring(0, firstJsonEnd + 1);
                    console.log('Extracted first QR code:', decodedText);
                }
            }
            
            try {
                scanData = JSON.parse(decodedText);
                
                // Validate that it's a proper QR code format
                if (scanData.t || scanData.type) {
                    // Valid QR code format
                    console.log('Parsed QR code data:', scanData);
                } else {
                    // Not a valid QR code format, treat as material code
                    throw new Error('Invalid QR format');
                }
            } catch (e) {
                // If JSON parsing fails, treat as plain material code
                console.log('Failed to parse as JSON, treating as material code:', decodedText);
                scanData = {
                    type: 'material_code',
                    code: decodedText
                };
            }
            
            fetchItemData(scanData);
        }

        function fetchItemData(scanData) {
            fetch('<?= base_url('warehouse-staff/qr/scan-data') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(scanData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addItemToList(data);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        function addItemToList(itemData) {
            const existingIndex = scannedItems.findIndex(item => 
                item.material.id === itemData.material.id
            );
            
            if (existingIndex >= 0) {
                scannedItems[existingIndex].quantity++;
                showNotification('Item quantity updated', 'success');
            } else {
                scannedItems.push({
                    ...itemData,
                    quantity: 1
                });
                showNotification('Item added to list', 'success');
            }
            
            updateScannedItemsList();
        }

        function updateScannedItemsList() {
            const listDiv = document.getElementById('scanned-items-list');
            
            if (scannedItems.length === 0) {
                listDiv.innerHTML = '<p class="text-muted text-center">No items scanned yet</p>';
                document.getElementById('action-buttons').style.display = 'none';
            } else {
                let html = '';
                scannedItems.forEach((item, index) => {
                    html += `
                        <div class="scanned-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${item.material.name}</h6>
                                    <small class="text-muted">Code: ${item.material.code}</small><br>
                                    <small class="text-muted">Category: ${item.material.category_name || item.material.category || 'N/A'}</small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${index})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="quantity-control mt-2">
                                <label class="form-label mb-0">Quantity:</label>
                                <input type="number" class="form-control quantity-input" 
                                       value="${item.quantity}" min="1" 
                                       onchange="updateQuantity(${index}, this.value)">
                                <span class="text-muted">${item.material.unit_abbreviation || item.material.unit_name || item.material.unit || 'units'}</span>
                            </div>
                        </div>
                    `;
                });
                
                listDiv.innerHTML = html;
                document.getElementById('action-buttons').style.display = 'block';
            }
            
            updateItemCount();
        }

        function updateItemCount() {
            document.getElementById('item-count').textContent = scannedItems.length;
        }

        function removeItem(index) {
            scannedItems.splice(index, 1);
            updateScannedItemsList();
            showNotification('Item removed', 'info');
        }

        function updateQuantity(index, value) {
            const quantity = parseInt(value) || 1;
            scannedItems[index].quantity = quantity;
            showNotification('Quantity updated', 'success');
        }

        // processScannedItems function removed - now using modals

        function showNotification(message, type) {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
            toast.style.zIndex = '9999';
            toast.innerHTML = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Remove old event listeners and add new ones for modals
        document.getElementById('issue-stock-btn')?.removeEventListener('click', processScannedItems);
        document.getElementById('transfer-stock-btn')?.removeEventListener('click', processScannedItems);
        document.getElementById('adjust-stock-btn')?.removeEventListener('click', processScannedItems);
    </script>

    <!-- Issue Stock Modal -->
    <div class="modal fade" id="issueStockModal" tabindex="-1" aria-labelledby="issueStockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="issueStockModalLabel">
                        <i class="bi bi-box-arrow-up"></i> Issue Stock
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="issue-items-list" class="mb-3">
                        <h6>Items to Issue:</h6>
                        <div id="issue-items-container"></div>
                    </div>
                    <form id="issue-stock-form">
                        <div class="mb-3">
                            <label for="issue-warehouse" class="form-label">From Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select" id="issue-warehouse" required>
                                <option value="">Select Warehouse</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="issue-to" class="form-label">Issue To Department <span class="text-danger">*</span></label>
                            <select class="form-select" id="issue-to" required>
                                <option value="">Select Department</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="issue-reference" class="form-label">Requisition Number</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="issue-reference" placeholder="Auto-generated" readonly>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="generate-requisition-modal-btn" title="Generate new requisition number">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Auto-generated requisition number</small>
                        </div>
                        <div class="mb-3">
                            <label for="issue-notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="issue-notes" rows="3" placeholder="Additional notes..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirm-issue-stock">
                        <i class="bi bi-check-circle"></i> Issue Stock
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Stock Modal -->
    <div class="modal fade" id="transferStockModal" tabindex="-1" aria-labelledby="transferStockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="transferStockModalLabel">
                        <i class="bi bi-arrow-left-right"></i> Transfer Stock
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="transfer-items-list" class="mb-3">
                        <h6>Items to Transfer:</h6>
                        <div id="transfer-items-container"></div>
                    </div>
                    <form id="transfer-stock-form">
                        <div class="mb-3">
                            <label for="transfer-from-warehouse" class="form-label">From Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select" id="transfer-from-warehouse" required>
                                <option value="">Select Warehouse</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="transfer-to-warehouse" class="form-label">To Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select" id="transfer-to-warehouse" required>
                                <option value="">Select Warehouse</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="transfer-reference" class="form-label">Transfer Reference</label>
                            <input type="text" class="form-control" id="transfer-reference" placeholder="Optional">
                        </div>
                        <div class="mb-3">
                            <label for="transfer-notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="transfer-notes" rows="3" placeholder="Additional notes..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" id="confirm-transfer-stock">
                        <i class="bi bi-check-circle"></i> Transfer Stock
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Adjust Stock Modal -->
    <div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title" id="adjustStockModalLabel">
                        <i class="bi bi-pencil-square"></i> Adjust Stock
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="adjust-items-list" class="mb-3">
                        <h6>Items to Adjust:</h6>
                        <div id="adjust-items-container"></div>
                    </div>
                    <form id="adjust-stock-form">
                        <div class="mb-3">
                            <label for="adjust-warehouse" class="form-label">Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select" id="adjust-warehouse" required>
                                <option value="">Select Warehouse</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="adjust-reason" class="form-label">Adjustment Reason <span class="text-danger">*</span></label>
                            <select class="form-select" id="adjust-reason" required>
                                <option value="">Select Reason</option>
                                <option value="Physical Count">Physical Count</option>
                                <option value="Damaged Goods">Damaged Goods</option>
                                <option value="Expired Items">Expired Items</option>
                                <option value="Theft/Loss">Theft/Loss</option>
                                <option value="Found Items">Found Items</option>
                                <option value="System Correction">System Correction</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="adjust-notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="adjust-notes" rows="3" placeholder="Additional notes about the adjustment..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-secondary" id="confirm-adjust-stock">
                        <i class="bi bi-check-circle"></i> Adjust Stock
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load warehouses for modals
        let warehouses = [];
        fetch('<?= base_url('warehouse-staff/ajax/materials-by-warehouse') ?>?warehouse_id=1')
            .then(response => response.json())
            .then(data => {
                // Get warehouses from a different endpoint or hardcode for now
                loadWarehouses();
            })
            .catch(err => {
                console.error('Error loading warehouses:', err);
                loadWarehouses();
            });

        function loadWarehouses() {
            // We'll load warehouses when modals open
        }

        // Issue Stock Modal - Use Bootstrap modal event only
        const issueStockModal = document.getElementById('issueStockModal');
        if (issueStockModal) {
            issueStockModal.addEventListener('show.bs.modal', function() {
                populateIssueModal();
            });
        }

        // Remove duplicate button click handler - Bootstrap modal event is sufficient

        function populateIssueModal() {
            const container = document.getElementById('issue-items-container');
            if (!container) return;
            
            let html = '<div class="table-responsive"><table class="table table-sm">';
            html += '<thead><tr><th>Material</th><th>Code</th><th>Quantity</th></tr></thead><tbody>';
            
            scannedItems.forEach(item => {
                html += `<tr>
                    <td>${item.material.name}</td>
                    <td>${item.material.code}</td>
                    <td>${item.quantity} ${item.material.unit_abbreviation || item.material.unit_name || 'units'}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
            
            // Load warehouses and departments
            loadWarehousesIntoSelect('issue-warehouse');
            loadDepartmentsIntoSelect('issue-to');
            
            // Generate requisition number
            generateRequisitionNumberForModal();
        }

        function generateRequisitionNumberForModal() {
            const requisitionInput = document.getElementById('issue-reference');
            const generateBtn = document.getElementById('generate-requisition-modal-btn');
            
            if (!requisitionInput) return;
            
            if (generateBtn) {
                generateBtn.disabled = true;
                generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            }
            
            fetch('<?= base_url('warehouse-staff/ajax/generate-requisition') ?>', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (generateBtn) {
                    generateBtn.disabled = false;
                    generateBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
                }
                
                if (data.success && data.requisition_number) {
                    requisitionInput.value = data.requisition_number;
                } else {
                    console.error('Error generating requisition number:', data.message);
                }
            })
            .catch(error => {
                if (generateBtn) {
                    generateBtn.disabled = false;
                    generateBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
                }
                console.error('Error generating requisition number:', error);
            });
        }

        // Generate requisition number button click handler
        document.getElementById('generate-requisition-modal-btn')?.addEventListener('click', function() {
            generateRequisitionNumberForModal();
        });

        // Transfer Stock Modal
        document.getElementById('transfer-stock-btn')?.addEventListener('click', function() {
            populateTransferModal();
        });

        function populateTransferModal() {
            const container = document.getElementById('transfer-items-container');
            let html = '<div class="table-responsive"><table class="table table-sm">';
            html += '<thead><tr><th>Material</th><th>Code</th><th>Quantity</th></tr></thead><tbody>';
            
            scannedItems.forEach(item => {
                html += `<tr>
                    <td>${item.material.name}</td>
                    <td>${item.material.code}</td>
                    <td>${item.quantity} ${item.material.unit_abbreviation || item.material.unit_name || 'units'}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
            
            // Load warehouses
            loadWarehousesIntoSelect('transfer-from-warehouse');
            loadWarehousesIntoSelect('transfer-to-warehouse');
        }

        // Adjust Stock Modal
        document.getElementById('adjust-stock-btn')?.addEventListener('click', function() {
            populateAdjustModal();
        });

        function populateAdjustModal() {
            const container = document.getElementById('adjust-items-container');
            let html = '<div class="table-responsive"><table class="table table-sm">';
            html += '<thead><tr><th>Material</th><th>Code</th><th>Current Qty</th><th>Scanned Qty</th></tr></thead><tbody>';
            
            scannedItems.forEach(item => {
                const currentQty = item.inventory && item.inventory.length > 0 ? item.inventory[0].quantity || 0 : 0;
                html += `<tr>
                    <td>${item.material.name}</td>
                    <td>${item.material.code}</td>
                    <td>${currentQty} ${item.material.unit_abbreviation || item.material.unit_name || 'units'}</td>
                    <td><input type="number" class="form-control form-control-sm adjust-qty" 
                               data-material-id="${item.material.id}" 
                               value="${item.quantity}" min="0" step="0.01"></td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
            
            // Load warehouses
            loadWarehousesIntoSelect('adjust-warehouse');
        }

        function loadWarehousesIntoSelect(selectId) {
            const select = document.getElementById(selectId);
            if (!select) return;
            
            // Clear existing options except the first one
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            // First try to get warehouses from scanned items' inventory
            const warehousesSet = new Set();
            scannedItems.forEach(item => {
                if (item.inventory && item.inventory.length > 0) {
                    item.inventory.forEach(inv => {
                        if (inv.warehouse_id && inv.warehouse_name) {
                            warehousesSet.add(JSON.stringify({
                                id: inv.warehouse_id,
                                name: inv.warehouse_name
                            }));
                        }
                    });
                }
            });
            
            if (warehousesSet.size > 0) {
                warehousesSet.forEach(warehouseStr => {
                    const warehouse = JSON.parse(warehouseStr);
                    const option = document.createElement('option');
                    option.value = warehouse.id;
                    option.textContent = warehouse.name;
                    select.appendChild(option);
                });
            } else {
                // Fallback: load all warehouses via AJAX
                fetch('<?= base_url('warehouse-staff/ajax/warehouses') ?>')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.warehouses) {
                            data.warehouses.forEach(warehouse => {
                                const option = document.createElement('option');
                                option.value = warehouse.id;
                                option.textContent = warehouse.name;
                                select.appendChild(option);
                            });
                        }
                    })
                    .catch(err => console.error('Error loading warehouses:', err));
            }
        }

        // Track loading state to prevent duplicates
        const departmentLoadingState = new Map();
        
        function loadDepartmentsIntoSelect(selectId) {
            const select = document.getElementById(selectId);
            if (!select) return;
            
            // Check if already loading for this select
            if (departmentLoadingState.get(selectId) === 'loading') {
                console.log('Departments already loading for', selectId);
                return;
            }
            
            // Clear existing options except the first one
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            // Mark as loading
            departmentLoadingState.set(selectId, 'loading');
            
            // Load departments via AJAX
            fetch('<?= base_url('warehouse-staff/ajax/departments') ?>', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Departments response:', data);
                    
                    // Clear again to be safe (in case of race conditions)
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                    
                    // Use Set to track added departments to prevent duplicates
                    const addedDepartments = new Set();
                    
                    if (data.success && data.departments && data.departments.length > 0) {
                        data.departments.forEach(department => {
                            // Skip if already added
                            if (addedDepartments.has(department.name)) {
                                console.warn('Duplicate department skipped:', department.name);
                                return;
                            }
                            
                            addedDepartments.add(department.name);
                            const option = document.createElement('option');
                            option.value = department.name;
                            option.textContent = department.name;
                            select.appendChild(option);
                        });
                    } else {
                        console.warn('No departments found or invalid response:', data);
                        // Add a fallback option
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No departments available';
                        option.disabled = true;
                        select.appendChild(option);
                    }
                    
                    // Mark as loaded
                    departmentLoadingState.set(selectId, 'loaded');
                })
                .catch(err => {
                    console.error('Error loading departments:', err);
                    // Clear loading state on error
                    departmentLoadingState.set(selectId, 'error');
                    
                    // Add error option
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Error loading departments';
                    option.disabled = true;
                    select.appendChild(option);
                });
        }

        // Confirm Issue Stock
        document.getElementById('confirm-issue-stock')?.addEventListener('click', function() {
            processIssueStockFromScanned();
        });

        // Confirm Transfer Stock
        document.getElementById('confirm-transfer-stock')?.addEventListener('click', function() {
            processTransferStockFromScanned();
        });

        // Confirm Adjust Stock
        document.getElementById('confirm-adjust-stock')?.addEventListener('click', function() {
            processAdjustStockFromScanned();
        });

        function processIssueStockFromScanned() {
            const warehouseId = document.getElementById('issue-warehouse').value;
            const issuedTo = document.getElementById('issue-to').value;
            const reference = document.getElementById('issue-reference').value;
            const notes = document.getElementById('issue-notes').value;

            if (!warehouseId || !issuedTo) {
                alert('Please fill in all required fields');
                return;
            }

            const items = scannedItems.map(item => ({
                material_id: item.material.id,
                quantity: item.quantity
            }));

            fetch('<?= base_url('warehouse-staff/scan/process-issue') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    items: items,
                    warehouse_id: warehouseId,
                    issued_to: issuedTo,
                    reference: reference,
                    notes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Stock issued successfully!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('issueStockModal')).hide();
                    scannedItems = [];
                    updateScannedItemsList();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        function processTransferStockFromScanned() {
            const fromWarehouseId = document.getElementById('transfer-from-warehouse').value;
            const toWarehouseId = document.getElementById('transfer-to-warehouse').value;
            const reference = document.getElementById('transfer-reference').value;
            const notes = document.getElementById('transfer-notes').value;

            if (!fromWarehouseId || !toWarehouseId) {
                alert('Please select both warehouses');
                return;
            }

            if (fromWarehouseId === toWarehouseId) {
                alert('From and To warehouses must be different');
                return;
            }
            
            const items = scannedItems.map(item => ({
                material_id: item.material.id,
                quantity: item.quantity,
                from_warehouse_id: fromWarehouseId,
                to_warehouse_id: toWarehouseId
            }));

            fetch('<?= base_url('warehouse-staff/scan/process-transfer') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    items: items,
                    reference: reference,
                    notes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Stock transferred successfully!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('transferStockModal')).hide();
                    scannedItems = [];
                    updateScannedItemsList();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        function processAdjustStockFromScanned() {
            const warehouseId = document.getElementById('adjust-warehouse').value;
            const reason = document.getElementById('adjust-reason').value;
            const notes = document.getElementById('adjust-notes').value;

            if (!warehouseId || !reason) {
                alert('Please fill in all required fields');
                return;
            }

            const adjustments = [];
            document.querySelectorAll('.adjust-qty').forEach(input => {
                const materialId = input.dataset.materialId;
                const newQuantity = parseFloat(input.value) || 0;
                const item = scannedItems.find(i => i.material.id == materialId);
                if (item) {
                    const currentQty = item.inventory && item.inventory.length > 0 
                        ? parseFloat(item.inventory[0].quantity || 0) 
                        : 0;
                    adjustments.push({
                        material_id: materialId,
                        current_quantity: currentQty,
                        new_quantity: newQuantity,
                        adjustment_type: newQuantity > currentQty ? 'increase' : 'decrease',
                        adjustment_quantity: Math.abs(newQuantity - currentQty)
                    });
                }
            });

            if (adjustments.length === 0) {
                alert('No adjustments to process');
                return;
            }

            fetch('<?= base_url('warehouse-staff/scan/process-adjust') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    adjustments: adjustments,
                    warehouse_id: warehouseId,
                    reason: reason,
                    notes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Stock adjusted successfully!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('adjustStockModal')).hide();
                    scannedItems = [];
                    updateScannedItemsList();
            setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    </script>
</body>
</html>
