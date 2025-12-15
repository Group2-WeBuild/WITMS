<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner - WITMS</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- QR Scanner CSS -->
    <link rel="stylesheet" href="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.css">
    
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
        
        #reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        #qr-reader {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: white;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .scan-result {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .result-item {
            padding: 10px;
            border-left: 4px solid #007bff;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .manual-input {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .scan-history {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .history-item {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
        }
        
        .history-item:hover {
            background: #f8f9fa;
        }
        
        .history-item:last-child {
            border-bottom: none;
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
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'QR Scanner']) ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <!-- Scanner Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-qr-code-scan"></i> QR Code Scanner
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="qr-reader">
                                <div class="text-center">
                                    <i class="bi bi-camera display-1 text-muted"></i>
                                    <p class="text-muted mt-3">Click "Start Camera" to begin scanning</p>
                                    <button id="start-camera" class="btn btn-primary">
                                        <i class="bi bi-camera-video"></i> Start Camera
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-center gap-2 mt-3">
                                <button id="stop-camera" class="btn btn-danger" style="display:none;">
                                    <i class="bi bi-stop-circle"></i> Stop Camera
                                </button>
                                <button id="switch-camera" class="btn btn-secondary" style="display:none;">
                                    <i class="bi bi-arrow-repeat"></i> Switch Camera
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Input -->
                    <div class="manual-input">
                        <h6 class="mb-3">
                            <i class="bi bi-keyboard"></i> Manual Input
                        </h6>
                        <div class="input-group">
                            <input type="text" id="manual-input" class="form-control" placeholder="Enter material code or scan result...">
                            <button class="btn btn-outline-primary" id="manual-submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <!-- Upload QR Code -->
                    <div class="manual-input">
                        <h6 class="mb-3">
                            <i class="bi bi-upload"></i> Upload QR Code Image
                        </h6>
                        <div class="input-group">
                            <input type="file" id="qr-upload" class="form-control" accept="image/*">
                            <button class="btn btn-outline-success" id="upload-submit">
                                <i class="bi bi-cloud-upload"></i> Read QR Code
                            </button>
                        </div>
                        <div id="upload-preview" class="mt-3" style="display:none;">
                            <img id="preview-image" class="img-fluid" style="max-height: 200px;">
                        </div>
                    </div>

                    <!-- Scan Result -->
                    <div id="scan-result" class="scan-result" style="display:none;">
                        <h6 class="mb-3">
                            <i class="bi bi-check-circle text-success"></i> Scan Result
                        </h6>
                        <div id="result-content"></div>
                    </div>

                    <!-- Scan History -->
                    <div class="scan-history">
                        <h6 class="mb-3">
                            <i class="bi bi-clock-history"></i> Recent Scans
                        </h6>
                        <div id="history-list">
                            <p class="text-muted text-center">No scans yet</p>
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
        let scanHistory = JSON.parse(localStorage.getItem('scanHistory') || '[]');

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            displayHistory();
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
            }
        });

        document.getElementById('manual-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const input = this.value.trim();
                if (input) {
                    processScanResult(input);
                }
            }
        });

        // QR Code Upload
        document.getElementById('qr-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                    document.getElementById('upload-preview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('upload-submit').addEventListener('click', function() {
            const fileInput = document.getElementById('qr-upload');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Please select an image file');
                return;
            }

            const html5QrCode = new Html5Qrcode("qr-reader");
            html5QrCode.scanFile(file, true)
                .then(decodedText => {
                    processScanResult(decodedText);
                    document.getElementById('qr-upload').value = '';
                    document.getElementById('upload-preview').style.display = 'none';
                })
                .catch(err => {
                    console.error('Error reading QR from image:', err);
                    alert('Could not detect QR code in image. Please try again.');
                });
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

        function onScanSuccess(decodedText, decodedResult) {
            console.log(`QR Code detected: ${decodedText}`);
            processScanResult(decodedText);
        }

        function onScanError(error) {
            // Ignore continuous scan errors
        }

        function processScanResult(decodedText) {
            // Try to parse as JSON first
            let scanData;
            try {
                scanData = JSON.parse(decodedText);
            } catch (e) {
                // If not JSON, treat as material code
                scanData = {
                    type: 'material_code',
                    code: decodedText
                };
            }
            
            // Add to history
            addToHistory(scanData);
            
            // Fetch data via AJAX
            fetchScanData(scanData);
        }

        function fetchScanData(scanData) {
            const resultDiv = document.getElementById('scan-result');
            const contentDiv = document.getElementById('result-content');
            
            resultDiv.style.display = 'block';
            contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
            
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
                    displayScanResult(data);
                } else {
                    contentDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Error: ${error.message}
                    </div>
                `;
            });
        }

        function displayScanResult(data) {
            const contentDiv = document.getElementById('result-content');
            let html = '';
            
            if (data.type === 'material') {
                html = `
                    <div class="result-item">
                        <strong>Material:</strong> ${data.material.name}<br>
                        <small class="text-muted">Code: ${data.material.code}</small>
                    </div>
                    <div class="result-item">
                        <strong>Category:</strong> ${data.material.category}<br>
                        <strong>Unit:</strong> ${data.material.unit}
                    </div>
                `;
                
                if (data.inventory && data.inventory.length > 0) {
                    html += '<h6 class="mt-3">Inventory Locations:</h6>';
                    data.inventory.forEach(item => {
                        html += `
                            <div class="result-item">
                                <strong>Warehouse:</strong> ${item.warehouse}<br>
                                <strong>Quantity:</strong> ${item.quantity}<br>
                                <strong>Location:</strong> ${item.location || 'N/A'}
                            </div>
                        `;
                    });
                }
                
                html += `
                    <div class="quick-actions">
                        <a href="<?= base_url('warehouse-staff/inventory/view') ?>/${data.material.id}" class="btn btn-primary btn-sm">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                        <button onclick="adjustStock(${data.material.id})" class="btn btn-warning btn-sm">
                            <i class="bi bi-arrow-left-right"></i> Adjust Stock
                        </button>
                    </div>
                `;
            } else if (data.type === 'inventory') {
                html = `
                    <div class="result-item">
                        <strong>Material:</strong> ${data.material.name}<br>
                        <small class="text-muted">Code: ${data.material.code}</small>
                    </div>
                    <div class="result-item">
                        <strong>Warehouse:</strong> ${data.inventory.warehouse}<br>
                        <strong>Quantity:</strong> ${data.inventory.quantity}<br>
                        <strong>Available:</strong> ${data.inventory.available}<br>
                        <strong>Location:</strong> ${data.inventory.location || 'N/A'}
                    </div>
                    <div class="quick-actions">
                        <a href="<?= base_url('warehouse-staff/inventory/view') ?>/${data.inventory.id}" class="btn btn-primary btn-sm">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                        <button onclick="adjustStock(${data.inventory.id})" class="btn btn-warning btn-sm">
                            <i class="bi bi-arrow-left-right"></i> Adjust Stock
                        </button>
                    </div>
                `;
            }
            
            contentDiv.innerHTML = html;
        }

        function adjustStock(id) {
            window.location.href = `<?= base_url('warehouse-staff/inventory/adjust') ?>/${id}`;
        }

        function addToHistory(scanData) {
            const historyItem = {
                data: scanData,
                timestamp: new Date().toISOString()
            };
            
            scanHistory.unshift(historyItem);
            if (scanHistory.length > 10) {
                scanHistory = scanHistory.slice(0, 10);
            }
            
            localStorage.setItem('scanHistory', JSON.stringify(scanHistory));
            displayHistory();
        }

        function displayHistory() {
            const historyDiv = document.getElementById('history-list');
            
            if (scanHistory.length === 0) {
                historyDiv.innerHTML = '<p class="text-muted text-center">No scans yet</p>';
                return;
            }
            
            let html = '';
            scanHistory.forEach(item => {
                const date = new Date(item.timestamp);
                const timeStr = date.toLocaleTimeString();
                const data = item.data;
                
                html += `
                    <div class="history-item" onclick="processScanResult('${JSON.stringify(data).replace(/'/g, "\\'")}')">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>${data.type === 'material' ? 'Material' : 'Inventory'}</strong><br>
                                <small class="text-muted">${data.code || data.material_code}</small>
                            </div>
                            <small class="text-muted">${timeStr}</small>
                        </div>
                    </div>
                `;
            });
            
            historyDiv.innerHTML = html;
        }
    </script>
</body>
</html>
