@include('header')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">
                                <i class="nc-icon nc-cloud-download-93"></i> Backup & Restore System
                            </h4>
                            <p class="card-category">Comprehensive data protection and recovery management</p>
                        </div>
                        <div class="col-md-4">
                            <a href="/admin-dashboard" class="btn btn-info btn-sm float-right">
                                <i class="nc-icon nc-minimal-left"></i> Back to Admin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="nc-icon nc-check-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="nc-icon nc-simple-remove"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Statistics Row -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-folder-17 text-primary"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Total Backups</p>
                                <p class="card-title">{{ $totalBackups }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-spaceship text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Total Size</p>
                                <p class="card-title">{{ number_format($totalSize / 1024 / 1024, 1) }} MB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-chart-bar-32 text-info"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">DB Tables</p>
                                <p class="card-title">{{ $dbStats['total_tables'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-paper text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Total Records</p>
                                <p class="card-title">{{ number_format($dbStats['total_records']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Create Backup Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-cloud-download-93"></i> Create New Backup
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.backup.create') }}">
                        @csrf
                        <div class="form-group">
                            <label>Backup Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="">Select backup type...</option>
                                <option value="database">Database Only</option>
                                <option value="files">Files Only</option>
                                <option value="full">Full System (Database + Files)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Description (Optional)</label>
                            <input type="text" name="description" class="form-control" placeholder="Brief description of this backup...">
                        </div>

                        <div class="alert alert-info">
                            <i class="nc-icon nc-bell-55"></i>
                            <strong>Note:</strong> Full system backups may take several minutes to complete. Database backups are usually faster.
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="nc-icon nc-cloud-download-93"></i> Create Backup
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Latest Backup Info -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-time-alarm"></i> Latest Backup Information
                    </h5>
                </div>
                <div class="card-body">
                    @if($latestBackup)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><strong>Filename:</strong></label>
                                    <p>{{ $latestBackup['filename'] }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Type:</strong></label>
                                    <p>
                                        <span class="badge badge-{{ $latestBackup['type'] === 'database' ? 'primary' : ($latestBackup['type'] === 'files' ? 'info' : 'success') }}">
                                            {{ ucfirst($latestBackup['type']) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Size:</strong></label>
                                    <p>{{ $latestBackup['size_human'] }}</p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><strong>Created:</strong></label>
                                    <p>{{ $latestBackup['created_at'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="btn-group">
                            <a href="{{ route('admin.backup.download', $latestBackup['filename']) }}" class="btn btn-success btn-sm">
                                <i class="nc-icon nc-cloud-download-93"></i> Download
                            </a>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="nc-icon nc-bell-55"></i>
                            No backups found. Create your first backup to ensure data protection.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Existing Backups -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-folder-17"></i> Existing Backups ({{ count($backups) }})
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($backups) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Filename</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $backup)
                                        <tr>
                                            <td>
                                                <strong>{{ $backup['filename'] }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $backup['type'] === 'database' ? 'primary' : ($backup['type'] === 'files' ? 'info' : 'success') }}">
                                                    {{ ucfirst($backup['type']) }}
                                                </span>
                                            </td>
                                            <td>{{ $backup['size_human'] }}</td>
                                            <td>
                                                {{ date('M d, Y H:i', strtotime($backup['created_at'])) }}
                                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($backup['created_at'])->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.backup.download', $backup['filename']) }}" 
                                                       class="btn btn-success btn-sm" title="Download">
                                                        <i class="nc-icon nc-cloud-download-93"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-warning btn-sm" 
                                                            data-toggle="modal" data-target="#restoreModal"
                                                            data-filename="{{ $backup['filename'] }}"
                                                            data-type="{{ $backup['type'] }}" title="Restore">
                                                        <i class="nc-icon nc-refresh-69"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.backup.delete', $backup['filename']) }}" 
                                                          style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this backup? This action cannot be undone.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                            <i class="nc-icon nc-simple-remove"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="nc-icon nc-bell-55"></i>
                            No backup files found. Create your first backup to get started.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Database Tables Information -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-chart-bar-32"></i> Database Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Table Name</th>
                                    <th>Records Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dbStats['table_stats'] as $table)
                                    <tr>
                                        <td>{{ $table['name'] }}</td>
                                        <td>{{ number_format($table['records']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Restore Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.backup.restore') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="nc-icon nc-refresh-69"></i> Restore from Backup
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="nc-icon nc-bell-55"></i>
                        <strong>Warning:</strong> Restoring will overwrite current data. This action cannot be undone. Please ensure you have a recent backup before proceeding.
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Backup File:</strong></label>
                        <p id="restore-filename" class="form-control-static"></p>
                        <input type="hidden" name="backup_file" id="restore-backup-file">
                    </div>
                    
                    <div class="form-group">
                        <label>Restore Type <span class="text-danger">*</span></label>
                        <select name="restore_type" class="form-control" required>
                            <option value="">Select what to restore...</option>
                            <option value="database">Database Only</option>
                            <option value="files">Files Only</option>
                            <option value="full">Full System (Database + Files)</option>
                        </select>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="confirmRestore" required>
                        <label class="form-check-label" for="confirmRestore">
                            I understand that this action will overwrite existing data and cannot be undone.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="nc-icon nc-refresh-69"></i> Restore Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#restoreModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var filename = button.data('filename');
    var type = button.data('type');
    
    $('#restore-filename').text(filename);
    $('#restore-backup-file').val(filename);
    $('select[name="restore_type"]').val(type);
});
</script>

@include('footer')
