<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'System Logs') ?></title>
    
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
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid;
            background: white;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .stat-card.emergency { border-left-color: #000; }
        .stat-card.alert { border-left-color: #721c24; }
        .stat-card.critical { border-left-color: #dc3545; }
        .stat-card.error { border-left-color: #fd7e14; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.notice { border-left-color: #0dcaf0; }
        .stat-card.info { border-left-color: #0d6efd; }
        .stat-card.debug { border-left-color: #6c757d; }
        .log-entry {
            border-left: 4px solid;
            padding: 12px;
            margin-bottom: 10px;
            background: white;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .log-entry.emergency { border-left-color: #000; background-color: #f8f9fa; }
        .log-entry.alert { border-left-color: #721c24; background-color: #f8d7da; }
        .log-entry.critical { border-left-color: #dc3545; background-color: #f8d7da; }
        .log-entry.error { border-left-color: #fd7e14; background-color: #fff3cd; }
        .log-entry.warning { border-left-color: #ffc107; background-color: #fff3cd; }
        .log-entry.notice { border-left-color: #0dcaf0; background-color: #d1ecf1; }
        .log-entry.info { border-left-color: #0d6efd; background-color: #cfe2ff; }
        .log-entry.debug { border-left-color: #6c757d; background-color: #e9ecef; }
        .log-level-badge {
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.75rem;
        }
        .log-timestamp {
            color: #6c757d;
            font-size: 0.85rem;
        }
        .log-message {
            margin-top: 5px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .log-file-item {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .log-file-item:hover {
            background-color: #f8f9fa;
        }
        .log-file-item.active {
            background-color: #e7f3ff;
            border-left: 3px solid #0d6efd;
        }
    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'System Logs']) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1"><i class="bi bi-file-text"></i> System Logs</h2>
                        <p class="text-muted mb-0">View and manage system log files for debugging and monitoring</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#clearOldLogsModal">
                            <i class="bi bi-trash"></i> Clear Old Logs
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Log Files List -->
                <div class="col-lg-3 mb-4">
                    <div class="card table-card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-files"></i> Log Files</h5>
                        </div>
                        <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                            <?php if (!empty($logFiles)): ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($logFiles as $logFile): ?>
                                        <li class="list-group-item log-file-item <?= $logFile['filename'] == $selectedFile ? 'active' : '' ?>" 
                                            data-filename="<?= esc($logFile['filename']) ?>"
                                            onclick="loadLogFile('<?= esc($logFile['filename']) ?>', this)">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <strong><?= esc($logFile['filename']) ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?= esc($logFile['date']) ? date('M j, Y', strtotime($logFile['date'])) : 'Unknown' ?>
                                                    </small>
                                                    <br>
                                                    <small class="text-muted"><?= esc($logFile['size_formatted']) ?></small>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-link" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation()">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="<?= base_url('it-administrator/logs/download/' . urlencode($logFile['filename'])) ?>">
                                                                <i class="bi bi-download"></i> Download
                                                            </a>
                                                        </li>
                                                        <?php if ($logFile['filename'] != 'log-' . date('Y-m-d') . '.log'): ?>
                                                            <li>
                                                                <a class="dropdown-item text-danger" href="#" onclick="deleteLogFile('<?= esc($logFile['filename']) ?>'); event.stopPropagation(); return false;">
                                                                    <i class="bi bi-trash"></i> Delete
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-file-x fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No log files found</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Log Entries -->
                <div class="col-lg-9">
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card critical">
                                <p>Critical</p>
                                <h4 id="stat-critical"><?= $stats['critical'] ?? 0 ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card error">
                                <p>Errors</p>
                                <h4 id="stat-error"><?= $stats['error'] ?? 0 ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card warning">
                                <p>Warnings</p>
                                <h4 id="stat-warning"><?= $stats['warning'] ?? 0 ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card info">
                                <p>Total Entries</p>
                                <h4 id="stat-total"><?= $stats['total'] ?? 0 ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- Filters and Search -->
                    <div class="card table-card mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="logLevelFilter" class="form-label">Filter by Level</label>
                                    <select class="form-select" id="logLevelFilter" onchange="applyFilters()">
                                        <option value="">All Levels</option>
                                        <option value="emergency">Emergency</option>
                                        <option value="alert">Alert</option>
                                        <option value="critical">Critical</option>
                                        <option value="error">Error</option>
                                        <option value="warning">Warning</option>
                                        <option value="notice">Notice</option>
                                        <option value="info">Info</option>
                                        <option value="debug">Debug</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="logSearch" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="logSearch" placeholder="Search log entries..." onkeyup="applyFilters()">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-secondary w-100" onclick="clearFilters()">
                                        <i class="bi bi-x-circle"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Log Entries Display -->
                    <div class="card table-card">
                        <div class="card-header bg-white">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0">
                                        <i class="bi bi-file-text"></i> Log Entries: 
                                        <span id="selectedFileName"><?= esc($selectedFile) ?></span>
                                    </h5>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshLogs()">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 600px; overflow-y: auto;" id="logEntriesContainer">
                            <?php if (!empty($logEntries)): ?>
                                <?php foreach ($logEntries as $entry): ?>
                                    <div class="log-entry <?= esc($entry['level']) ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="log-level-badge bg-<?= $entry['level'] == 'emergency' ? 'dark' : ($entry['level'] == 'critical' ? 'danger' : ($entry['level'] == 'error' ? 'warning' : ($entry['level'] == 'warning' ? 'warning' : ($entry['level'] == 'info' ? 'info' : 'secondary')))) ?> text-white">
                                                <?= strtoupper($entry['level']) ?>
                                            </span>
                                            <span class="log-timestamp"><?= esc($entry['timestamp']) ?></span>
                                        </div>
                                        <div class="log-message"><?= esc($entry['message']) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-file-x fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No log entries found</p>
                                    <p class="text-muted">Select a log file from the list to view entries</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clear Old Logs Modal -->
    <div class="modal fade" id="clearOldLogsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Clear Old Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> This will permanently delete log files older than the specified number of days.
                    </div>
                    <div class="mb-3">
                        <label for="clearDays" class="form-label">Delete logs older than (days)</label>
                        <input type="number" class="form-control" id="clearDays" value="30" min="1" max="365">
                        <small class="form-text text-muted">Log files older than this many days will be deleted. Today's log will never be deleted.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="clearOldLogs()">
                        <i class="bi bi-trash"></i> Clear Old Logs
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        let currentFile = '<?= esc($selectedFile) ?>';

        function loadLogFile(filename, element) {
            currentFile = filename;
            document.getElementById('selectedFileName').textContent = filename;
            
            // Update active file in list
            document.querySelectorAll('.log-file-item').forEach(item => {
                item.classList.remove('active');
            });
            if (element) {
                element.classList.add('active');
            } else {
                // Fallback: find by data attribute
                document.querySelectorAll('.log-file-item').forEach(item => {
                    if (item.getAttribute('data-filename') === filename) {
                        item.classList.add('active');
                    }
                });
            }

            applyFilters();
        }

        function applyFilters() {
            const level = document.getElementById('logLevelFilter').value;
            const search = document.getElementById('logSearch').value;

            $.ajax({
                url: '<?= base_url('it-administrator/logs/get-entries') ?>',
                method: 'POST',
                data: {
                    filename: currentFile,
                    level: level,
                    search: search
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        displayLogEntries(response.entries);
                        updateStats(response.stats);
                    } else {
                        alert('Error: ' + (response.message || 'Failed to load log entries'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        function displayLogEntries(entries) {
            const container = document.getElementById('logEntriesContainer');
            
            if (entries.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-search fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No log entries match your filters</p>
                    </div>
                `;
                return;
            }

            let html = '';
            entries.forEach(entry => {
                const levelClass = entry.level;
                const badgeClass = getBadgeClass(entry.level);
                html += `
                    <div class="log-entry ${levelClass}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="log-level-badge ${badgeClass} text-white">
                                ${entry.level.toUpperCase()}
                            </span>
                            <span class="log-timestamp">${entry.timestamp}</span>
                        </div>
                        <div class="log-message">${escapeHtml(entry.message)}</div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function getBadgeClass(level) {
            const classes = {
                'emergency': 'bg-dark',
                'alert': 'bg-danger',
                'critical': 'bg-danger',
                'error': 'bg-warning',
                'warning': 'bg-warning',
                'notice': 'bg-info',
                'info': 'bg-info',
                'debug': 'bg-secondary'
            };
            return classes[level] || 'bg-secondary';
        }

        function updateStats(stats) {
            document.getElementById('stat-critical').textContent = stats.critical || 0;
            document.getElementById('stat-error').textContent = stats.error || 0;
            document.getElementById('stat-warning').textContent = stats.warning || 0;
            document.getElementById('stat-total').textContent = stats.total || 0;
        }

        function clearFilters() {
            document.getElementById('logLevelFilter').value = '';
            document.getElementById('logSearch').value = '';
            applyFilters();
        }

        function refreshLogs() {
            location.reload();
        }

        function deleteLogFile(filename) {
            if (!confirm(`Are you sure you want to delete "${filename}"?\n\nThis action cannot be undone!`)) {
                return;
            }

            $.ajax({
                url: '<?= base_url('it-administrator/logs/delete') ?>',
                method: 'POST',
                data: { filename: filename },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Log file deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to delete log file'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        function clearOldLogs() {
            const days = document.getElementById('clearDays').value;
            if (!days || days < 1) {
                alert('Please enter a valid number of days');
                return;
            }

            if (!confirm(`Are you sure you want to delete all log files older than ${days} days?\n\nThis action cannot be undone!`)) {
                return;
            }

            $.ajax({
                url: '<?= base_url('it-administrator/logs/clear-old') ?>',
                method: 'POST',
                data: { days: days },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message || `Deleted ${response.deleted_count} log file(s)`);
                        bootstrap.Modal.getInstance(document.getElementById('clearOldLogsModal')).hide();
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to clear old logs'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>

