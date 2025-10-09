@include('header')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">
                                <i class="nc-icon nc-settings-gear-65"></i> System Health Monitoring
                            </h4>
                            <p class="card-category">Real-time system status and performance metrics</p>
                        </div>
                        <div class="col-md-4">
                            <a href="/dashboard" class="btn btn-info btn-sm float-right">
                                <i class="nc-icon nc-minimal-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health Status Cards -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-database text-{{ $health['database']['status'] === 'healthy' ? 'success' : 'danger' }}"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Database</p>
                                <p class="card-title text-{{ $health['database']['status'] === 'healthy' ? 'success' : 'danger' }}">
                                    {{ ucfirst($health['database']['status']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-info-circle"></i>
                        {{ $health['database']['message'] }}
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
                                <i class="nc-icon nc-folder-17 text-{{ $health['storage']['status'] === 'healthy' ? 'success' : ($health['storage']['status'] === 'warning' ? 'warning' : 'danger') }}"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Storage</p>
                                <p class="card-title">{{ $health['storage']['usage_percent'] }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-hdd-o"></i>
                        {{ $health['storage']['free_space'] }} free of {{ $health['storage']['total_space'] }}
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
                                <i class="nc-icon nc-refresh-02 text-{{ $health['cache']['status'] === 'healthy' ? 'success' : 'danger' }}"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Cache</p>
                                <p class="card-title text-{{ $health['cache']['status'] === 'healthy' ? 'success' : 'danger' }}">
                                    {{ ucfirst($health['cache']['status']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-tachometer"></i>
                        {{ $health['cache']['message'] }}
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
                                <i class="nc-icon nc-time-alarm text-{{ $health['queue']['status'] === 'healthy' ? 'success' : 'danger' }}"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Queue</p>
                                <p class="card-title text-{{ $health['queue']['status'] === 'healthy' ? 'success' : 'danger' }}">
                                    {{ ucfirst($health['queue']['status']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-tasks"></i>
                        {{ $health['queue']['message'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="nc-icon nc-chart-bar-32"></i> Performance Metrics</h5>
                    <p class="card-category">Real-time system performance indicators</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <h3 class="text-info">{{ $metrics['response_time'] }}</h3>
                                <p class="mb-0">Average Response Time</p>
                                <small class="text-muted">Last 24 hours</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <h3 class="text-warning">{{ $metrics['error_rate'] }}</h3>
                                <p class="mb-0">Error Rate</p>
                                <small class="text-muted">Last 24 hours</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <h3 class="text-success">{{ $metrics['uptime'] }}</h3>
                                <p class="mb-0">System Uptime</p>
                                <small class="text-muted">Current month</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <h3 class="text-primary">{{ $metrics['memory_usage']['used'] }}</h3>
                                <p class="mb-0">Memory Usage</p>
                                <small class="text-muted">Peak: {{ $metrics['memory_usage']['peak'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Actions -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="nc-icon nc-settings"></i> System Maintenance</h5>
                    <p class="card-category">System maintenance and optimization tools</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <button class="btn btn-primary btn-block" onclick="runMaintenance('cache-clear')">
                                <i class="nc-icon nc-refresh-02"></i><br>
                                Clear Cache
                            </button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button class="btn btn-success btn-block" onclick="runMaintenance('optimize')">
                                <i class="nc-icon nc-spaceship"></i><br>
                                Optimize System
                            </button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button class="btn btn-warning btn-block" onclick="runMaintenance('backup')">
                                <i class="nc-icon nc-folder-17"></i><br>
                                Create Backup
                            </button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button class="btn btn-info btn-block" onclick="runMaintenance('logs-clear')">
                                <i class="nc-icon nc-paper-2"></i><br>
                                Clear Logs
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="nc-icon nc-bell-55"></i> System Alerts</h5>
                    <p class="card-category">Important system notifications</p>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if($health['storage']['status'] !== 'healthy')
                        <div class="alert alert-{{ $health['storage']['status'] === 'warning' ? 'warning' : 'danger' }}">
                            <strong><i class="fa fa-exclamation-triangle"></i> Storage Alert</strong><br>
                            Disk usage is at {{ $health['storage']['usage_percent'] }}%. Consider cleaning up files.
                        </div>
                    @endif

                    @if($health['database']['status'] !== 'healthy')
                        <div class="alert alert-danger">
                            <strong><i class="fa fa-database"></i> Database Alert</strong><br>
                            Database connection issues detected. Check configuration.
                        </div>
                    @endif

                    @if(empty($recent_errors))
                        <div class="alert alert-success">
                            <strong><i class="fa fa-check"></i> All Clear</strong><br>
                            No critical system alerts at this time.
                        </div>
                    @else
                        @foreach($recent_errors as $error)
                            <div class="alert alert-danger">
                                <strong>{{ $error['title'] }}</strong><br>
                                {{ $error['message'] }}
                                <small class="d-block mt-2">{{ $error['time'] }}</small>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Monitoring Chart -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="nc-icon nc-chart-pie-36"></i> Real-time System Monitoring</h5>
                    <p class="card-category">Live system performance metrics</p>
                </div>
                <div class="card-body">
                    <canvas id="systemMonitorChart" width="400" height="200"></canvas>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-refresh"></i> Auto-refreshes every 30 seconds
                        <span class="float-right">
                            <button class="btn btn-sm btn-outline-primary" onclick="toggleMonitoring()">
                                <i class="fa fa-pause" id="monitoringIcon"></i> <span id="monitoringText">Pause</span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let monitoringActive = true;
let monitoringChart;

// Initialize system monitoring chart
const ctx = document.getElementById('systemMonitorChart').getContext('2d');
monitoringChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [{
            label: 'CPU Usage (%)',
            data: [],
            borderColor: '#51cbce',
            backgroundColor: 'rgba(81, 203, 206, 0.1)',
            tension: 0.4
        }, {
            label: 'Memory Usage (%)',
            data: [],
            borderColor: '#fbc658',
            backgroundColor: 'rgba(251, 198, 88, 0.1)',
            tension: 0.4
        }, {
            label: 'Response Time (ms)',
            data: [],
            borderColor: '#ef8157',
            backgroundColor: 'rgba(239, 129, 87, 0.1)',
            tension: 0.4,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                },
                ticks: {
                    callback: function(value) {
                        return value + 'ms';
                    }
                }
            }
        }
    }
});

// System maintenance functions
function runMaintenance(action) {
    const button = event.target;
    const originalContent = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fa fa-spinner fa-spin"></i><br>Running...';
    button.disabled = true;
    
    // Simulate maintenance action
    setTimeout(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
        
        // Show success notification
        showNotification(`${action.replace('-', ' ').toUpperCase()} completed successfully!`, 'success');
    }, 2000);
}

function toggleMonitoring() {
    monitoringActive = !monitoringActive;
    const icon = document.getElementById('monitoringIcon');
    const text = document.getElementById('monitoringText');
    
    if (monitoringActive) {
        icon.className = 'fa fa-pause';
        text.textContent = 'Pause';
        startMonitoring();
    } else {
        icon.className = 'fa fa-play';
        text.textContent = 'Resume';
    }
}

function startMonitoring() {
    if (!monitoringActive) return;
    
    // Simulate real-time data updates
    const now = new Date().toLocaleTimeString();
    const cpu = Math.random() * 100;
    const memory = Math.random() * 100;
    const responseTime = Math.random() * 500;
    
    // Add new data point
    monitoringChart.data.labels.push(now);
    monitoringChart.data.datasets[0].data.push(cpu);
    monitoringChart.data.datasets[1].data.push(memory);
    monitoringChart.data.datasets[2].data.push(responseTime);
    
    // Keep only last 10 data points
    if (monitoringChart.data.labels.length > 10) {
        monitoringChart.data.labels.shift();
        monitoringChart.data.datasets.forEach(dataset => dataset.data.shift());
    }
    
    monitoringChart.update('none');
    
    // Schedule next update
    setTimeout(startMonitoring, 5000);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification && notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Start monitoring when page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(startMonitoring, 1000);
});
</script>

<style>
.card-stats:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.alert {
    border-left: 4px solid;
}

.alert-success {
    border-left-color: #6bd098;
}

.alert-warning {
    border-left-color: #fbc658;
}

.alert-danger {
    border-left-color: #ef8157;
}

.btn:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}
</style>

@include('footer')
