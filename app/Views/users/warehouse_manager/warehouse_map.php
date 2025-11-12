<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
        #map {
            height: 600px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .map-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .warehouse-list {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-height: 600px;
            overflow-y: auto;
        }
        .warehouse-item {
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .warehouse-item:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
            transform: translateX(5px);
        }
        .warehouse-item.active {
            border-color: #667eea;
            background-color: #e7eaff;
        }
        .warehouse-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge-active {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-coords {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .badge-no-coords {
            background-color: #fff3cd;
            color: #856404;
        }
        .info-window-content {
            padding: 10px;
        }
        .info-window-content h6 {
            margin: 0 0 10px 0;
            color: #667eea;
            font-weight: bold;
        }
        .info-window-content p {
            margin: 5px 0;
            font-size: 0.9rem;
        }    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user]) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user]) ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-map"></i> Warehouse Map</h2>
                <p class="text-muted mb-0">Visual location of all warehouses</p>
            </div>
            <a href="<?=base_url('/warehouse-manager/warehouse-management')?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="row">
            <!-- Map -->
            <div class="col-lg-8">
                <div class="map-container">
                    <div id="map"></div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Click on markers to view warehouse details. 
                            <?php if (empty($apiKey)): ?>
                                <span class="text-danger">Note: Google Maps API key not configured.</span>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Warehouse List -->
            <div class="col-lg-4">
                <div class="warehouse-list">
                    <h5 class="mb-3"><i class="bi bi-building"></i> Warehouses (<?= count($mapData) ?>)</h5>
                    
                    <?php if (!empty($mapData)): ?>
                        <?php foreach ($mapData as $index => $warehouse): ?>
                            <div class="warehouse-item" 
                                 id="warehouse-<?= $index ?>"
                                 onclick="focusWarehouse(<?= $index ?>)">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong><?= esc($warehouse['warehouse_name'] ?? $warehouse['title']) ?></strong>
                                    <span class="warehouse-badge <?= $warehouse['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                        <?= $warehouse['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                                <div class="text-muted small mb-2">
                                    <i class="bi bi-geo-alt"></i> <?= esc($warehouse['shortAddress']) ?>
                                </div>
                                <div class="d-flex gap-2">
                                    <span class="warehouse-badge <?= $warehouse['hasCoords'] ? 'badge-coords' : 'badge-no-coords' ?>">
                                        <i class="bi bi-<?= $warehouse['hasCoords'] ? 'pin-map-fill' : 'pin-map' ?>"></i>
                                        <?= $warehouse['hasCoords'] ? 'GPS Available' : 'No GPS' ?>
                                    </span>
                                    <?php if (isset($warehouse['warehouse_code'])): ?>
                                        <span class="warehouse-badge" style="background-color: #e7eaff; color: #667eea;">
                                            <?= esc($warehouse['warehouse_code']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3">No warehouses to display</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Google Maps JavaScript API -->
    <?php if (!empty($apiKey)): ?>
        <script src="https://maps.googleapis.com/maps/api/js?key=<?= $apiKey ?>&callback=initMap" async defer></script>
    <?php endif; ?>    <script>
        // Warehouse data from PHP
        const warehouseData = <?= json_encode($mapData) ?>;
        const mapConfig = <?= json_encode($mapConfig) ?>;
        
        let map;
        let markers = [];
        let infoWindows = [];
        let warehouseToMarkerIndex = {}; // Maps warehouse array index to marker array index

        // Initialize Google Map
        function initMap() {
            <?php if (empty($apiKey)): ?>
                document.getElementById('map').innerHTML = '<div class="alert alert-warning m-3"><i class="bi bi-exclamation-triangle"></i> <strong>Google Maps API Key Not Configured</strong><br>Please add your Google Maps API key in the WarehouseLocationModel to enable map visualization.</div>';
                return;
            <?php endif; ?>// Default center (Philippines)
            const defaultCenter = { 
                lat: mapConfig.default_lat || 12.8797, 
                lng: mapConfig.default_lng || 121.7740 
            };

            // Initialize map
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: mapConfig.default_zoom || 6,
                center: defaultCenter,
                mapTypeId: 'roadmap',
                styles: [
                    {
                        featureType: 'poi',
                        elementType: 'labels',
                        stylers: [{ visibility: 'off' }]
                    }
                ]
            });            // Add markers for each warehouse
            const bounds = new google.maps.LatLngBounds();
            let hasValidCoords = false;

            warehouseData.forEach((warehouse, warehouseIndex) => {
                if (warehouse.hasCoords && warehouse.lat && warehouse.lng && warehouse.lat !== 0 && warehouse.lng !== 0) {
                    const position = { 
                        lat: parseFloat(warehouse.lat), 
                        lng: parseFloat(warehouse.lng) 
                    };
                    
                    // Map warehouse index to marker index
                    const markerIndex = markers.length;
                    warehouseToMarkerIndex[warehouseIndex] = markerIndex;
                    
                    // Create marker
                    const marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: warehouse.warehouse_name || warehouse.title,
                        icon: {
                            url: warehouse.is_active ? 
                                'http://maps.google.com/mapfiles/ms/icons/blue-dot.png' : 
                                'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
                            scaledSize: new google.maps.Size(40, 40)
                        },
                        animation: google.maps.Animation.DROP
                    });

                    // Create info window
                    const infoWindow = new google.maps.InfoWindow({
                        content: createInfoWindowContent(warehouse)
                    });

                    // Add click listener to marker
                    marker.addListener('click', () => {
                        closeAllInfoWindows();
                        infoWindow.open(map, marker);
                        highlightWarehouse(warehouseIndex);
                    });                    markers.push(marker);
                    infoWindows.push(infoWindow);
                    bounds.extend(position);
                    hasValidCoords = true;
                }
            });

            // Fit map to show all markers
            if (hasValidCoords) {
                map.fitBounds(bounds);
                
                // Prevent too much zoom for single marker
                google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
                    if (map.getZoom() > 15) {
                        map.setZoom(15);
                    }
                });
            }
        }

        // Create info window content
        function createInfoWindowContent(warehouse) {
            const statusBadge = warehouse.is_active ? 
                '<span style="background-color: #d4edda; color: #155724; padding: 3px 10px; border-radius: 10px; font-size: 0.8rem; font-weight: 600;">Active</span>' :
                '<span style="background-color: #f8d7da; color: #721c24; padding: 3px 10px; border-radius: 10px; font-size: 0.8rem; font-weight: 600;">Inactive</span>';
            
            return `
                <div class="info-window-content">
                    <h6>${warehouse.warehouse_name || warehouse.title}</h6>
                    ${warehouse.warehouse_code ? `<p><strong>Code:</strong> ${warehouse.warehouse_code}</p>` : ''}
                    <p><strong>Location:</strong> ${warehouse.address}</p>
                    ${warehouse.capacity ? `<p><strong>Capacity:</strong> ${warehouse.capacity} m²</p>` : ''}
                    <p><strong>Status:</strong> ${statusBadge}</p>
                    ${warehouse.warehouseId ? `<a href="/warehouse-manager/warehouse/view/${warehouse.warehouseId}" class="btn btn-sm btn-primary mt-2"><i class="bi bi-eye"></i> View Details</a>` : ''}
                </div>
            `;
        }        // Focus on warehouse marker
        function focusWarehouse(warehouseIndex) {
            // Get the marker index for this warehouse
            const markerIndex = warehouseToMarkerIndex[warehouseIndex];
            
            if (markerIndex !== undefined && markers[markerIndex]) {
                closeAllInfoWindows();
                map.setCenter(markers[markerIndex].getPosition());
                map.setZoom(15);
                infoWindows[markerIndex].open(map, markers[markerIndex]);
                markers[markerIndex].setAnimation(google.maps.Animation.BOUNCE);
                setTimeout(() => {
                    markers[markerIndex].setAnimation(null);
                }, 750);
                highlightWarehouse(warehouseIndex);
            } else {
                // Warehouse has no coordinates, just highlight it in the list
                highlightWarehouse(warehouseIndex);
                alert('This warehouse does not have GPS coordinates for map display.');
            }
        }

        // Highlight warehouse in list
        function highlightWarehouse(index) {
            document.querySelectorAll('.warehouse-item').forEach(item => {
                item.classList.remove('active');
            });
            const item = document.getElementById(`warehouse-${index}`);
            if (item) {
                item.classList.add('active');
                item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        // Close all info windows
        function closeAllInfoWindows() {
            infoWindows.forEach(infoWindow => {
                infoWindow.close();
            });
        }

        <?php if (empty($apiKey)): ?>
        // If no API key, show message
        window.addEventListener('load', function() {
            document.getElementById('map').innerHTML = '<div class="alert alert-warning m-3"><i class="bi bi-exclamation-triangle"></i> <strong>Google Maps API Key Not Configured</strong><br>Please add your Google Maps API key in the WarehouseLocationModel to enable map visualization.<br><br>To configure:<br>1. Open <code>app/Models/WarehouseLocationModel.php</code><br>2. Update the <code>getMapConfig()</code> method with your API key</div>';
        });
        <?php endif; ?>
    </script>
</body>
</html>
