<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    
    <!-- Include Sidebar Navigation -->
    <?= view('templates/sidebar_navigation', ['user' => $user]) ?>
    
    <!-- Include Top Navigation Bar -->
    <?= view('templates/top_navbar', ['user' => $user, 'page_title' => 'Search Inventory']) ?>
    
    <!-- Main Content Area -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">Search Inventory</h1>
                    
                    <!-- Search Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form id="searchForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="searchInput" class="form-label">Search by Material Code/Name</label>
                                        <input type="text" class="form-control" id="searchInput" placeholder="Enter material code or name">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="warehouseFilter" class="form-label">Warehouse</label>
                                        <select class="form-select" id="warehouseFilter">
                                            <option value="">All Warehouses</option>
                                            <?php if(isset($warehouses)): ?>
                                                <?php foreach($warehouses as $warehouse): ?>
                                                    <option value="<?= $warehouse['id'] ?>"><?= esc($warehouse['name']) ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="bi bi-search"></i> Search
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Search Results -->
                    <div id="searchResults">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-search display-1"></i>
                                    <p class="mt-3">Enter search criteria to find inventory items</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('searchForm');
            const searchResults = document.getElementById('searchResults');
            
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                performSearch();
            });
            
            // Auto-search on input change (with debounce)
            let searchTimeout;
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 500);
            });
            
            document.getElementById('warehouseFilter').addEventListener('change', performSearch);
            
            function performSearch() {
                const searchTerm = document.getElementById('searchInput').value.trim();
                const warehouseId = document.getElementById('warehouseFilter').value;
                
                if (!searchTerm && !warehouseId) {
                    showEmptyState();
                    return;
                }
                
                // Show loading
                searchResults.innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3">Searching inventory...</p>
                            </div>
                        </div>
                    </div>
                `;
                
                // Make AJAX request
                fetch('<?= base_url('warehouse-staff/search-inventory') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        search: searchTerm,
                        warehouse_id: warehouseId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayResults(data.results);
                    } else {
                        showError(data.message || 'Search failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('An error occurred while searching');
                });
            }
            
            function displayResults(results) {
                if (results.length === 0) {
                    searchResults.innerHTML = `
                        <div class="card">
                            <div class="card-body">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-inbox display-1"></i>
                                    <p class="mt-3">No inventory items found</p>
                                </div>
                            </div>
                        </div>
                    `;
                    return;
                }
                
                let html = '<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-hover">';
                html += `
                    <thead>
                        <tr>
                            <th>Material Code</th>
                            <th>Material Name</th>
                            <th>Warehouse</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                `;
                
                results.forEach(item => {
                    html += `
                        <tr>
                            <td><code>${item.code}</code></td>
                            <td>${item.name}</td>
                            <td>${item.warehouse_name || '-'}</td>
                            <td>
                                <span class="badge ${item.quantity <= item.reorder_level ? 'bg-danger' : 'bg-success'}">
                                    ${item.quantity}
                                </span>
                            </td>
                            <td>${item.unit || '-'}</td>
                            <td>${item.location_in_warehouse || '-'}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewDetails(${item.id})">
                                    <i class="bi bi-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table></div></div></div>';
                searchResults.innerHTML = html;
            }
            
            function showEmptyState() {
                searchResults.innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-search display-1"></i>
                                <p class="mt-3">Enter search criteria to find inventory items</p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            function showError(message) {
                searchResults.innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> ${message}
                            </div>
                        </div>
                    </div>
                `;
            }
            
            function viewDetails(inventoryId) {
                // You can implement a modal or redirect to details page
                console.log('View details for inventory ID:', inventoryId);
            }
        });
    </script>
</body>
</html>