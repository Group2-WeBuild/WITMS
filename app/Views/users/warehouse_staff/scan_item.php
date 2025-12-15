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
                                <button id="issue-stock" class="btn btn-warning">
                                    <i class="bi bi-box-arrow-up"></i> Issue Stock
                                </button>
                                <button id="transfer-stock" class="btn btn-info">
                                    <i class="bi bi-arrow-left-right"></i> Transfer Stock
                                </button>
                                <button id="adjust-stock" class="btn btn-secondary">
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

        // Action buttons
        document.getElementById('issue-stock').addEventListener('click', function() {
            processScannedItems('issue');
        });

        document.getElementById('transfer-stock').addEventListener('click', function() {
            processScannedItems('transfer');
        });

        document.getElementById('adjust-stock').addEventListener('click', function() {
            processScannedItems('adjust');
        });

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
            console.log(`QR Code detected: ${decodedText}`);
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
            try {
                scanData = JSON.parse(decodedText);
            } catch (e) {
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
                                    <small class="text-muted">Category: ${item.material.category || 'N/A'}</small>
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
                                <span class="text-muted">${item.material.unit || 'units'}</span>
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

        function processScannedItems(action) {
            if (scannedItems.length === 0) {
                alert('No items to process');
                return;
            }
            
            fetch('<?= base_url('warehouse-staff/scan/store-items') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    items: scannedItems,
                    action: action
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

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
    </script>
</body>
</html>
