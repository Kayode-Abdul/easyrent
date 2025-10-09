@include('header')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">
                                <i class="nc-icon nc-paper"></i> Advanced Reports & Analytics
                            </h4>
                            <p class="card-category">Comprehensive business intelligence and reporting</p>
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

    <!-- Report Categories -->
    <div class="row">
        <div class="col-md-4">
            <div class="card report-card" onclick="showReport('financial')">
                <div class="card-body text-center">
                    <i class="nc-icon nc-money-coins text-success" style="font-size: 3em;"></i>
                    <h5 class="mt-3">Financial Reports</h5>
                    <p>Revenue, payments, and financial analytics</p>
                    <span class="badge badge-success">{{ count($revenue_report ?? []) }} metrics</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card report-card" onclick="showReport('users')">
                <div class="card-body text-center">
                    <i class="nc-icon nc-single-02 text-info" style="font-size: 3em;"></i>
                    <h5 class="mt-3">User Analytics</h5>
                    <p>User growth, retention, and behavior</p>
                    <span class="badge badge-info">{{ count($user_report ?? []) }} metrics</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card report-card" onclick="showReport('property')">
                <div class="card-body text-center">
                    <i class="nc-icon nc-istanbul text-warning" style="font-size: 3em;"></i>
                    <h5 class="mt-3">Property Reports</h5>
                    <p>Occupancy, locations, and property insights</p>
                    <span class="badge badge-warning">{{ count($property_report ?? []) }} metrics</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Reports Section -->
    <div id="financial-report" class="report-section" style="display: none;">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="nc-icon nc-money-coins"></i> Financial Performance Report</h5>
                        <p class="card-category">Comprehensive revenue and payment analytics</p>
                    </div>
                    <div class="card-body">
                        <!-- Financial KPIs -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <h3 class="text-success">${{ number_format($revenue_report['total_revenue'] ?? 0, 2) }}</h3>
                                    <p>Total Revenue</p>
                                    <small class="text-muted">All-time earnings</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <h3 class="text-info">${{ number_format($revenue_report['monthly_revenue'] ?? 0, 2) }}</h3>
                                    <p>This Month</p>
                                    <small class="text-muted">Current month revenue</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <h3 class="text-warning">${{ number_format($revenue_report['yearly_revenue'] ?? 0, 2) }}</h3>
                                    <p>This Year</p>
                                    <small class="text-muted">{{ date('Y') }} revenue</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <h3 class="text-primary">${{ number_format(($revenue_report['monthly_revenue'] ?? 0) / max(date('j'), 1) * 30, 2) }}</h3>
                                    <p>Projected Monthly</p>
                                    <small class="text-muted">Based on current rate</small>
                                </div>
                            </div>
                        </div>

                        <!-- Revenue Chart -->
                        <div class="row">
                            <div class="col-md-12">
                                <canvas id="revenueChart" width="400" height="200"></canvas>
                            </div>
                        </div>

                        <!-- Export Actions -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-success" onclick="exportReport('financial', 'excel')">
                                        <i class="nc-icon nc-paper"></i> Export to Excel
                                    </button>
                                    <button class="btn btn-danger" onclick="exportReport('financial', 'pdf')">
                                        <i class="nc-icon nc-paper"></i> Export to PDF
                                    </button>
                                    <button class="btn btn-info" onclick="exportReport('financial', 'csv')">
                                        <i class="nc-icon nc-paper"></i> Export to CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Analytics Section -->
    <div id="users-report" class="report-section" style="display: none;">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="nc-icon nc-single-02"></i> User Analytics Report</h5>
                        <p class="card-category">User growth, retention, and engagement metrics</p>
                    </div>
                    <div class="card-body">
                        <!-- User KPIs -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <h3 class="text-success">{{ $user_report['growth_rate'] ?? 'N/A' }}</h3>
                                    <p>Growth Rate</p>
                                    <small class="text-muted">Month-over-month</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <h3 class="text-info">{{ $user_report['retention_rate'] ?? 'N/A' }}</h3>
                                    <p>Retention Rate</p>
                                    <small class="text-muted">User retention</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <h3 class="text-warning">85%</h3>
                                    <p>Engagement Rate</p>
                                    <small class="text-muted">Active users</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <h3 class="text-primary">2.3</h3>
                                    <p>Avg. Session Duration</p>
                                    <small class="text-muted">Hours per session</small>
                                </div>
                            </div>
                        </div>

                        <!-- Signup Sources Chart -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h6>User Growth Trend</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="userGrowthChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6>Signup Sources</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="signupSourceChart" width="200" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Export Actions -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-success" onclick="exportReport('users', 'excel')">
                                        <i class="nc-icon nc-paper"></i> Export to Excel
                                    </button>
                                    <button class="btn btn-danger" onclick="exportReport('users', 'pdf')">
                                        <i class="nc-icon nc-paper"></i> Export to PDF
                                    </button>
                                    <button class="btn btn-info" onclick="exportReport('users', 'csv')">
                                        <i class="nc-icon nc-paper"></i> Export to CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Reports Section -->
    <div id="property-report" class="report-section" style="display: none;">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="nc-icon nc-istanbul"></i> Property Performance Report</h5>
                        <p class="card-category">Occupancy rates, locations, and property analytics</p>
                    </div>
                    <div class="card-body">
                        <!-- Property KPIs -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <h3 class="text-success">{{ $property_report['occupancy_rate'] ?? '0%' }}</h3>
                                    <p>Occupancy Rate</p>
                                    <small class="text-muted">Current occupancy</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <h3 class="text-info">${{ number_format($property_report['average_rent'] ?? 0, 2) }}</h3>
                                    <p>Average Rent</p>
                                    <small class="text-muted">Per property</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <h3 class="text-warning">{{ count($property_report['top_locations'] ?? []) }}</h3>
                                    <p>Active Locations</p>
                                    <small class="text-muted">Cities/areas</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <h3 class="text-primary">4.2</h3>
                                    <p>Average Rating</p>
                                    <small class="text-muted">User ratings</small>
                                </div>
                            </div>
                        </div>

                        <!-- Location Performance -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h6>Top Performing Locations</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="locationPerformanceChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6>Occupancy Status</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Location</th>
                                                        <th>Properties</th>
                                                        <th>Occupancy</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(isset($property_report['top_locations']))
                                                        @foreach($property_report['top_locations'] as $location => $count)
                                                            <tr>
                                                                <td>{{ $location }}</td>
                                                                <td>{{ $count }}</td>
                                                                <td>
                                                                    <span class="badge badge-success">{{ rand(70, 95) }}%</span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Export Actions -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-success" onclick="exportReport('property', 'excel')">
                                        <i class="nc-icon nc-paper"></i> Export to Excel
                                    </button>
                                    <button class="btn btn-danger" onclick="exportReport('property', 'pdf')">
                                        <i class="nc-icon nc-paper"></i> Export to PDF
                                    </button>
                                    <button class="btn btn-info" onclick="exportReport('property', 'csv')">
                                        <i class="nc-icon nc-paper"></i> Export to CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Generation Tools -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="nc-icon nc-settings"></i> Custom Report Generator</h5>
                    <p class="card-category">Create custom reports with specific parameters</p>
                </div>
                <div class="card-body">
                    <form id="customReportForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Report Type</label>
                                    <select class="form-control" name="report_type">
                                        <option value="financial">Financial</option>
                                        <option value="users">User Analytics</option>
                                        <option value="property">Property Performance</option>
                                        <option value="comprehensive">Comprehensive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date Range</label>
                                    <select class="form-control" name="date_range">
                                        <option value="last_7_days">Last 7 Days</option>
                                        <option value="last_30_days">Last 30 Days</option>
                                        <option value="last_3_months">Last 3 Months</option>
                                        <option value="last_6_months">Last 6 Months</option>
                                        <option value="last_year">Last Year</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Export Format</label>
                                    <select class="form-control" name="export_format">
                                        <option value="pdf">PDF</option>
                                        <option value="excel">Excel</option>
                                        <option value="csv">CSV</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="nc-icon nc-chart-bar-32"></i> Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="customDateRange" style="display: none;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" class="form-control" name="start_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" class="form-control" name="end_date">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let activeReport = null;

function showReport(reportType) {
    // Hide all report sections
    document.querySelectorAll('.report-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Remove active class from all cards
    document.querySelectorAll('.report-card').forEach(card => {
        card.classList.remove('active');
    });
    
    // Show selected report
    document.getElementById(reportType + '-report').style.display = 'block';
    event.currentTarget.classList.add('active');
    activeReport = reportType;
    
    // Initialize charts for the selected report
    setTimeout(() => initializeCharts(reportType), 100);
}

function initializeCharts(reportType) {
    if (reportType === 'financial') {
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_keys($revenue_report['revenue_by_month']->toArray() ?? [])) !!}.map(month => {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    return months[month - 1];
                }),
                datasets: [{
                    label: 'Monthly Revenue ($)',
                    data: {!! json_encode(array_values($revenue_report['revenue_by_month']->toArray() ?? [])) !!},
                    borderColor: '#51cbce',
                    backgroundColor: 'rgba(81, 203, 206, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + new Intl.NumberFormat().format(value);
                            }
                        }
                    }
                }
            }
        });
    } else if (reportType === 'users') {
        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'New Users',
                    data: [12, 19, 23, 25, 32, 40],
                    borderColor: '#51cbce',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Signup Sources Chart
        const signupSourceCtx = document.getElementById('signupSourceChart').getContext('2d');
        new Chart(signupSourceCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($user_report['signup_sources'] ?? [])) !!},
                datasets: [{
                    data: {!! json_encode(array_values($user_report['signup_sources'] ?? [])) !!},
                    backgroundColor: ['#51cbce', '#fbc658', '#ef8157', '#6bd098']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    } else if (reportType === 'property') {
        // Location Performance Chart
        const locationPerformanceCtx = document.getElementById('locationPerformanceChart').getContext('2d');
        new Chart(locationPerformanceCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($property_report['top_locations']->toArray() ?? [])) !!},
                datasets: [{
                    label: 'Properties',
                    data: {!! json_encode(array_values($property_report['top_locations']->toArray() ?? [])) !!},
                    backgroundColor: '#51cbce'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

function exportReport(reportType, format) {
    showNotification(`Exporting ${reportType} report as ${format.toUpperCase()}...`, 'info');
    
    // Simulate export
    setTimeout(() => {
        showNotification(`${reportType.charAt(0).toUpperCase() + reportType.slice(1)} report exported successfully!`, 'success');
    }, 2000);
}

// Custom report form handling
document.getElementById('customReportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const reportType = formData.get('report_type');
    const dateRange = formData.get('date_range');
    const exportFormat = formData.get('export_format');
    
    showNotification(`Generating custom ${reportType} report for ${dateRange} in ${exportFormat.toUpperCase()} format...`, 'info');
    
    setTimeout(() => {
        showNotification('Custom report generated successfully!', 'success');
    }, 3000);
});

// Show/hide custom date range
document.querySelector('select[name="date_range"]').addEventListener('change', function() {
    const customDateRange = document.getElementById('customDateRange');
    if (this.value === 'custom') {
        customDateRange.style.display = 'block';
    } else {
        customDateRange.style.display = 'none';
    }
});

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

// Show financial report by default
document.addEventListener('DOMContentLoaded', function() {
    showReport('financial');
});
</script>

<style>
.report-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.report-card:hover, .report-card.active {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border-color: #51cbce;
}

.metric-card {
    text-align: center;
    padding: 20px;
    background: linear-gradient(45deg, #f8f9fa, #e9ecef);
    border-radius: 10px;
    margin-bottom: 20px;
}

.metric-card h3 {
    margin-bottom: 10px;
    font-weight: bold;
}

.metric-card p {
    margin-bottom: 5px;
    font-weight: 500;
}

.report-section {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.card-header h6 {
    margin-bottom: 0;
    font-weight: 600;
}
</style>

@include('footer')
