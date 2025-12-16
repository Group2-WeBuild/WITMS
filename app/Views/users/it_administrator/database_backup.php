<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Database Backup') ?></title>
    
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
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid;
            background: white;
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
        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            float: right;
        }
        .backup-item {
            border-left: 4px solid #0d6efd;
            transition: all 0.2s;
        }
        .backup-item:hover {
            border-left-color: #198754;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .alert-backup {
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Database Backup']) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1"><i class="bi bi-database"></i> Database Backup</h2>
                        <p class="text-muted mb-0">Create and manage database backups for emergency recovery</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <button type="button" class="btn btn-primary" id="createBackupBtn" onclick="createBackup()">
                            <i class="bi bi-download"></i> Create Backup Now
                        </button>
                    </div>
                </div>
            </div>

            <!-- Important Notice -->
            <div class="alert alert-warning alert-backup mb-4">
                <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Important Backup Information</h5>
                <ul class="mb-0">
                    <li><strong>Database:</strong> <?= esc($database_name ?? 'witms_db') ?></li>
                    <li>Backups are stored in: <code><?= esc(WRITEPATH . 'backups') ?></code></li>
                    <li>Regular backups are essential for disaster recovery</li>
                    <li>Download backups and store them in a secure location</li>
                    <li>Test restore procedures periodically to ensure backups are valid</li>
                </ul>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card primary">
                        <p>Total Backups</p>
                        <h3><?= count($backups ?? []) ?></h3>
                        <div class="icon bg-primary text-white">
                            <i class="bi bi-database"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card success">
                        <p>Latest Backup</p>
                        <h6 class="mb-0" style="font-size: 14px;">
                            <?php if (!empty($backups)): ?>
                                <?= date('M j, Y H:i', strtotime($backups[0]['created'])) ?>
                            <?php else: ?>
                                <span class="text-muted">No backups yet</span>
                            <?php endif; ?>
                        </h6>
                        <div class="icon bg-success text-white">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card info">
                        <p>Total Size</p>
                        <h3>
                            <?php
                                $totalSize = 0;
                                foreach ($backups ?? [] as $backup) {
                                    $totalSize += $backup['size'];
                                }
                                echo $totalSize > 0 ? number_format($totalSize / 1024 / 1024, 2) . ' MB' : '0 MB';
                            ?>
                        </h3>
                        <div class="icon bg-info text-white">
                            <i class="bi bi-hdd"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card warning">
                        <p>Backup Status</p>
                        <h6 class="mb-0" style="font-size: 14px;">
                            <?php if (!empty($backups)): ?>
                                <?php
                                    $latestBackup = strtotime($backups[0]['created']);
                                    $hoursAgo = (time() - $latestBackup) / 3600;
                                    if ($hoursAgo < 24) {
                                        echo '<span class="text-success">Recent</span>';
                                    } elseif ($hoursAgo < 168) {
                                        echo '<span class="text-warning">Old</span>';
                                    } else {
                                        echo '<span class="text-danger">Very Old</span>';
                                    }
                                ?>
                            <?php else: ?>
                                <span class="text-danger">No Backups</span>
                            <?php endif; ?>
                        </h6>
                        <div class="icon bg-warning text-white">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backups List -->
            <div class="card table-card">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Available Backups</h5>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($backups)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Filename</th>
                                        <th>Size</th>
                                        <th>Created</th>
                                        <th>Age</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($backups as $backup): ?>
                                        <?php
                                            $createdTime = strtotime($backup['created']);
                                            $age = time() - $createdTime;
                                            $ageDays = floor($age / 86400);
                                            $ageHours = floor(($age % 86400) / 3600);
                                            $ageMinutes = floor(($age % 3600) / 60);
                                            
                                            if ($ageDays > 0) {
                                                $ageText = $ageDays . ' day' . ($ageDays > 1 ? 's' : '') . ' ago';
                                            } elseif ($ageHours > 0) {
                                                $ageText = $ageHours . ' hour' . ($ageHours > 1 ? 's' : '') . ' ago';
                                            } else {
                                                $ageText = $ageMinutes . ' minute' . ($ageMinutes > 1 ? 's' : '') . ' ago';
                                            }
                                        ?>
                                        <tr class="backup-item">
                                            <td>
                                                <i class="bi bi-file-earmark-code text-primary"></i>
                                                <strong><?= esc($backup['filename']) ?></strong>
                                            </td>
                                            <td><?= esc($backup['size_formatted']) ?></td>
                                            <td><?= esc($backup['created']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $ageDays < 7 ? 'success' : ($ageDays < 30 ? 'warning' : 'danger') ?>">
                                                    <?= $ageText ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('it-administrator/backup/download/' . urlencode($backup['filename'])) ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Download Backup">
                                                        <i class="bi bi-download"></i> Download
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            onclick="deleteBackup('<?= esc($backup['filename']) ?>')"
                                                            title="Delete Backup">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-database-x fs-1 text-muted"></i>
                            <p class="text-muted mt-3">No backups found</p>
                            <p class="text-muted">Click "Create Backup Now" to create your first backup</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Backup Instructions -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Backup & Restore Instructions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="bi bi-download"></i> Creating a Backup</h6>
                            <ol>
                                <li>Click "Create Backup Now" button</li>
                                <li>Wait for the backup process to complete</li>
                                <li>Download the backup file immediately</li>
                                <li>Store it in a secure, off-site location</li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-arrow-counterclockwise"></i> Restoring from Backup</h6>
                            <ol>
                                <li>Ensure you have a recent backup file</li>
                                <li>Access your MySQL/MariaDB server</li>
                                <li>Run: <code>mysql -u username -p database_name < backup_file.sql</code></li>
                                <li>Or use phpMyAdmin/MySQL Workbench to import</li>
                                <li>Verify data integrity after restoration</li>
                            </ol>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3">
                        <strong>Warning:</strong> Restoring a backup will overwrite all current data. Always test restores on a development server first!
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        function createBackup() {
            const btn = document.getElementById('createBackupBtn');
            const originalText = btn.innerHTML;
            
            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Backup...';
            
            $.ajax({
                url: '<?= base_url('it-administrator/backup/create') ?>',
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Backup created successfully!\n\nFilename: ' + response.filename + '\nSize: ' + response.size);
                        // Reload page to show new backup
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        alert('Error: ' + (response.message || 'Failed to create backup'));
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred while creating the backup. Please check server logs.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        }

        function deleteBackup(filename) {
            if (!confirm('Are you sure you want to delete this backup?\n\n' + filename + '\n\nThis action cannot be undone!')) {
                return;
            }

            $.ajax({
                url: '<?= base_url('it-administrator/backup/delete') ?>',
                method: 'POST',
                data: { filename: filename },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Backup deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to delete backup'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        // Auto-refresh backup status every 5 minutes
        setInterval(function() {
            // Only refresh if no modals are open
            if (!document.querySelector('.modal.show')) {
                // Silently check for new backups (optional)
            }
        }, 300000); // 5 minutes
    </script>
</body>
</html>

