@include('header')

<div class="content">
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fas fa-file-alt text-info me-2"></i>
                                System Logs Viewer
                            </h4>
                            <p class="text-muted mb-0">Monitor system logs and application activity</p>
                        </div>
                        <div>
                            <button class="btn btn-outline-primary" onclick="refreshLogs()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button class="btn btn-outline-danger" onclick="clearLogs()">
                                <i class="fas fa-trash"></i> Clear Old Logs
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Log Files</h6>
                            <h3 class="mb-0">{{ count($logFiles) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Recent Entries</h6>
                            <h3 class="mb-0">{{ count($recentLogs) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Log Size</h6>
                            <h3 class="mb-0">
                                @php
                                    $totalSize = array_sum(array_column($logFiles, 'size'));
                                    echo $totalSize > 1024*1024 ? number_format($totalSize/(1024*1024), 2) . ' MB' : number_format($totalSize/1024, 2) . ' KB';
                                @endphp
                            </h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-hdd fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Status</h6>
                            <h3 class="mb-0">
                                @if(count($logFiles) > 0)
                                    <i class="fas fa-check-circle"></i> Active
                                @else
                                    <i class="fas fa-exclamation-circle"></i> No Logs
                                @endif
                            </h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-heartbeat fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Log Files List -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-folder text-warning me-2"></i>
                        Log Files
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(count($logFiles) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($logFiles as $index => $file)
                                <a href="#" class="list-group-item list-group-item-action log-file-item {{ $index === 0 ? 'active' : '' }}" 
                                   data-file="{{ $file['name'] }}" data-path="{{ $file['path'] }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $file['name'] }}</h6>
                                            <small class="text-muted">
                                                {{ number_format($file['size'] / 1024, 2) }} KB
                                                <span class="mx-1">•</span>
                                                {{ \Carbon\Carbon::parse($file['modified'])->diffForHumans() }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <button class="btn btn-sm btn-outline-primary" onclick="downloadLog('{{ $file['name'] }}')">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-file-alt fa-3x mb-3 d-block"></i>
                            <h6>No Log Files Found</h6>
                            <p class="small">System logs will appear here when available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Log Content Viewer -->
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-eye text-info me-2"></i>
                            Log Content
                        </h5>
                        <small class="text-muted" id="current-log-file">
                            @if(count($logFiles) > 0)
                                {{ $logFiles[0]['name'] }}
                            @else
                                No file selected
                            @endif
                        </small>
                    </div>
                    <div>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-secondary" onclick="toggleAutoRefresh()">
                                <i class="fas fa-sync" id="auto-refresh-icon"></i>
                                <span id="auto-refresh-text">Auto Refresh</span>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="searchLogs()">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0" style="height: 600px; overflow-y: auto;">
                    <!-- Search Bar -->
                    <div id="search-bar" class="p-3 border-bottom" style="display: none;">
                        <div class="input-group">
                            <input type="text" class="form-control" id="search-input" placeholder="Search in logs...">
                            <button class="btn btn-outline-secondary" type="button" onclick="performSearch()">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-danger" type="button" onclick="clearSearch()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div id="log-content">
                        @if(count($recentLogs) > 0)
                            <pre class="p-3 mb-0" style="font-size: 12px; line-height: 1.4; color: #333; background: #f8f9fa;">@foreach($recentLogs as $log)<span class="log-line">{{ $log }}</span>
@endforeach</pre>
                        @else
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-file-alt fa-3x mb-3 d-block"></i>
                                <h6>No Log Content</h6>
                                <p class="small">Select a log file to view its contents</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Level Filtering Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Log Level</label>
                        <select class="form-select" id="log-level-filter">
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
                        <label class="form-label">Date Range</label>
                        <input type="date" class="form-control" id="date-filter">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
            </div>
        </div>
    </div>
</div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let autoRefreshInterval = null;
let isAutoRefreshing = false;

$(document).ready(function() {
    // Handle log file selection
    $('.log-file-item').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all items
        $('.log-file-item').removeClass('active');
        // Add active class to clicked item
        $(this).addClass('active');
        
        const fileName = $(this).data('file');
        const filePath = $(this).data('path');
        
        // Update current file display
        $('#current-log-file').text(fileName);
        
        // Load log content
        loadLogContent(filePath);
    });
});

function loadLogContent(filePath) {
    $('#log-content').html('<div class="p-4 text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading log content...</p></div>');
    
    $.get(`{{ route('admin.logs.content') }}?file=${encodeURIComponent(filePath)}`)
        .done(function(data) {
            if (data.content) {
                $('#log-content').html(`<pre class="p-3 mb-0" style="font-size: 12px; line-height: 1.4; color: #333; background: #f8f9fa;">${escapeHtml(data.content)}</pre>`);
            } else {
                $('#log-content').html('<div class="p-4 text-center text-muted"><i class="fas fa-file-alt fa-3x mb-3 d-block"></i><h6>Empty Log File</h6></div>');
            }
        })
        .fail(function() {
            $('#log-content').html('<div class="p-4 text-center text-danger"><i class="fas fa-exclamation-triangle fa-3x mb-3 d-block"></i><h6>Error Loading Log</h6><p class="small">Could not load log file content</p></div>');
        });
}

function refreshLogs() {
    location.reload();
}

function clearLogs() {
    if (confirm('Are you sure you want to clear old log files? This action cannot be undone.')) {
        // Show loading state
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';
        btn.disabled = true;
        
        $.ajax({
            url: '{{ route("admin.logs.clear-old") }}',
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            }
        })
        .done(function(data) {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(data.message, 'danger');
            }
        })
        .fail(function() {
            showNotification('Error clearing log files', 'danger');
        })
        .always(function() {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
}

function toggleAutoRefresh() {
    if (isAutoRefreshing) {
        clearInterval(autoRefreshInterval);
        isAutoRefreshing = false;
        $('#auto-refresh-icon').removeClass('fa-pause').addClass('fa-sync');
        $('#auto-refresh-text').text('Auto Refresh');
    } else {
        autoRefreshInterval = setInterval(() => {
            const activeFile = $('.log-file-item.active').data('path');
            if (activeFile) {
                loadLogContent(activeFile);
            }
        }, 5000);
        isAutoRefreshing = true;
        $('#auto-refresh-icon').removeClass('fa-sync').addClass('fa-pause');
        $('#auto-refresh-text').text('Stop Auto');
    }
}

function searchLogs() {
    $('#search-bar').toggle();
    if ($('#search-bar').is(':visible')) {
        $('#search-input').focus();
    }
}

function performSearch() {
    const searchTerm = $('#search-input').val().trim();
    if (!searchTerm) return;
    
    const logLines = $('#log-content pre').html().split('\n');
    let highlightedContent = '';
    let matchCount = 0;
    
    logLines.forEach(line => {
        if (line.toLowerCase().includes(searchTerm.toLowerCase())) {
            const highlightedLine = line.replace(new RegExp(searchTerm, 'gi'), `<mark class="bg-warning">$&</mark>`);
            highlightedContent += highlightedLine + '\n';
            matchCount++;
        } else {
            highlightedContent += line + '\n';
        }
    });
    
    $('#log-content pre').html(highlightedContent);
    
    if (matchCount > 0) {
        showNotification(`Found ${matchCount} matches for "${searchTerm}"`, 'success');
    } else {
        showNotification(`No matches found for "${searchTerm}"`, 'warning');
    }
}

function clearSearch() {
    $('#search-input').val('');
    $('#search-bar').hide();
    
    // Reload current log content without highlighting
    const activeFile = $('.log-file-item.active').data('path');
    if (activeFile) {
        loadLogContent(activeFile);
    }
}

function downloadLog(fileName) {
    window.open(`{{ route('admin.logs.download') }}?file=${encodeURIComponent(fileName)}`, '_blank');
}

function applyFilters() {
    const level = $('#log-level-filter').val();
    const date = $('#date-filter').val();
    
    // Apply filters to current log view
    // This would typically involve an AJAX request to filter server-side
    showNotification('Filters applied successfully', 'success');
    $('#filterModal').modal('hide');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : type === 'warning' ? 'alert-warning' : type === 'danger' ? 'alert-danger' : 'alert-info';
    
    const notification = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('body').append(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
}

// Cleanup on page unload
$(window).on('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
</script>

@include('footer')
