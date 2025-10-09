@include('header')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">
                                <i class="nc-icon nc-atom"></i> API Management
                            </h4>
                            <p class="card-category">External integrations and API access control</p>
                        </div>
                        <div class="col-md-4">
                            <div class="btn-group float-right">
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createApiKeyModal">
                                    <i class="nc-icon nc-simple-add"></i> Create API Key
                                </button>
                                <a href="/admin-dashboard" class="btn btn-info btn-sm">
                                    <i class="nc-icon nc-minimal-left"></i> Back to Admin
                                </a>
                            </div>
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

    <!-- API Statistics -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-chart-bar-32 text-primary"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Total Requests</p>
                                <p class="card-title">{{ number_format($apiStats['total_requests']) }}</p>
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
                                <i class="nc-icon nc-time-alarm text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Requests Today</p>
                                <p class="card-title">{{ number_format($apiStats['requests_today']) }}</p>
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
                                <i class="nc-icon nc-key-25 text-info"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Active API Keys</p>
                                <p class="card-title">{{ number_format($apiStats['active_api_keys']) }}</p>
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
                                <i class="nc-icon nc-alert-circle-i text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Rate Limit Hits</p>
                                <p class="card-title">{{ number_format($apiStats['rate_limit_hits']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Endpoints Documentation -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-paper"></i> Available API Endpoints
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Method</th>
                                    <th>Endpoint</th>
                                    <th>Description</th>
                                    <th>Auth Required</th>
                                    <th>Rate Limit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-success">GET</span></td>
                                    <td><code>/api/properties</code></td>
                                    <td>List all properties</td>
                                    <td>❌ Public</td>
                                    <td>100/hour</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success">GET</span></td>
                                    <td><code>/api/properties/{id}</code></td>
                                    <td>Get property details</td>
                                    <td>❌ Public</td>
                                    <td>100/hour</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-primary">POST</span></td>
                                    <td><code>/api/properties</code></td>
                                    <td>Create new property</td>
                                    <td>✅ API Key</td>
                                    <td>50/hour</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-warning">PUT</span></td>
                                    <td><code>/api/properties/{id}</code></td>
                                    <td>Update property</td>
                                    <td>✅ API Key</td>
                                    <td>50/hour</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success">GET</span></td>
                                    <td><code>/api/apartments</code></td>
                                    <td>List apartments</td>
                                    <td>❌ Public</td>
                                    <td>100/hour</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success">GET</span></td>
                                    <td><code>/api/users/{id}/properties</code></td>
                                    <td>User's properties</td>
                                    <td>✅ API Key</td>
                                    <td>200/hour</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-primary">POST</span></td>
                                    <td><code>/api/bookings</code></td>
                                    <td>Create booking</td>
                                    <td>✅ API Key</td>
                                    <td>30/hour</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success">GET</span></td>
                                    <td><code>/api/payments</code></td>
                                    <td>Payment records</td>
                                    <td>✅ API Key</td>
                                    <td>100/hour</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="nc-icon nc-bell-55"></i>
                        <strong>Authentication:</strong> Include your API key in the <code>Authorization: Bearer {api_key}</code> header for protected endpoints.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- API Usage Guidelines -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-bulb-63"></i> API Usage Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="nc-icon nc-check-2 text-success"></i>
                            <strong>Rate Limits:</strong> Respect the rate limits to avoid blocking
                        </li>
                        <li class="mb-2">
                            <i class="nc-icon nc-check-2 text-success"></i>
                            <strong>API Keys:</strong> Keep your API keys secure and private
                        </li>
                        <li class="mb-2">
                            <i class="nc-icon nc-check-2 text-success"></i>
                            <strong>HTTPS Only:</strong> All API calls must use HTTPS
                        </li>
                        <li class="mb-2">
                            <i class="nc-icon nc-check-2 text-success"></i>
                            <strong>Error Handling:</strong> Handle HTTP status codes properly
                        </li>
                        <li class="mb-2">
                            <i class="nc-icon nc-check-2 text-success"></i>
                            <strong>Pagination:</strong> Use pagination for large data sets
                        </li>
                    </ul>

                    <div class="alert alert-warning">
                        <strong>Rate Limit Headers:</strong>
                        <br><small>
                        <code>X-RateLimit-Limit</code><br>
                        <code>X-RateLimit-Remaining</code><br>
                        <code>X-RateLimit-Reset</code>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Quick Test -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-send"></i> Quick API Test
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Test Endpoint</label>
                        <select class="form-control" id="testEndpoint">
                            <option value="/api/properties">GET /api/properties</option>
                            <option value="/api/apartments">GET /api/apartments</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>API Key (Optional)</label>
                        <input type="text" class="form-control" id="testApiKey" placeholder="Enter API key...">
                    </div>
                    <button class="btn btn-primary btn-block" onclick="testApiEndpoint()">
                        <i class="nc-icon nc-send"></i> Test API Call
                    </button>
                    <div id="apiTestResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Keys Management -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-key-25"></i> API Keys Management
                    </h5>
                </div>
                <div class="card-body">
                    @if($apiKeys->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>API Key</th>
                                        <th>Created</th>
                                        <th>Last Used</th>
                                        <th>Requests Count</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($apiKeys as $key)
                                        <tr>
                                            <td><strong>{{ $key['name'] }}</strong></td>
                                            <td>
                                                <code class="api-key-display">{{ substr($key['key'], 0, 20) }}...</code>
                                                <button class="btn btn-sm btn-outline-secondary ml-2" onclick="copyToClipboard('{{ $key['key'] }}')">
                                                    <i class="nc-icon nc-single-copy-04"></i>
                                                </button>
                                            </td>
                                            <td>{{ $key['created_at'] }}</td>
                                            <td>{{ $key['last_used'] ?: 'Never' }}</td>
                                            <td>{{ number_format($key['request_count']) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $key['status'] === 'active' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($key['status']) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-info btn-sm" title="View Details">
                                                        <i class="nc-icon nc-zoom-split"></i>
                                                    </button>
                                                    <button class="btn btn-warning btn-sm" title="Regenerate Key">
                                                        <i class="nc-icon nc-refresh-69"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" title="Revoke Key" onclick="return confirm('Are you sure you want to revoke this API key?')">
                                                        <i class="nc-icon nc-simple-remove"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="nc-icon nc-bell-55"></i>
                            <h5>No API Keys Created</h5>
                            <p>Create your first API key to start integrating with the EasyRent API.</p>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#createApiKeyModal">
                                <i class="nc-icon nc-simple-add"></i> Create First API Key
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent API Requests -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-time-alarm"></i> Recent API Requests
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Method</th>
                                        <th>Endpoint</th>
                                        <th>IP Address</th>
                                        <th>API Key</th>
                                        <th>Status</th>
                                        <th>Response Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRequests as $request)
                                        <tr>
                                            <td>{{ $request['timestamp'] }}</td>
                                            <td>
                                                <span class="badge badge-{{ $request['method'] === 'GET' ? 'success' : 'primary' }}">
                                                    {{ $request['method'] }}
                                                </span>
                                            </td>
                                            <td><code>{{ $request['endpoint'] }}</code></td>
                                            <td>{{ $request['ip_address'] }}</td>
                                            <td>
                                                @if($request['api_key'])
                                                    <code>{{ substr($request['api_key'], 0, 8) }}...</code>
                                                @else
                                                    <span class="text-muted">Public</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ substr($request['status'], 0, 1) === '2' ? 'success' : 'danger' }}">
                                                    {{ $request['status'] }}
                                                </span>
                                            </td>
                                            <td>{{ $request['response_time'] }}ms</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="nc-icon nc-bell-55"></i>
                            No recent API requests to display. API activity will appear here once requests are made.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create API Key Modal -->
<div class="modal fade" id="createApiKeyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.api-management.create-key') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="nc-icon nc-key-25"></i> Create New API Key
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Key Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Mobile App Integration" required>
                        <small class="text-muted">Choose a descriptive name for this API key</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe what this API key will be used for..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Permissions</label>
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="read_properties" class="form-check-input" id="readProperties" checked>
                            <label class="form-check-label" for="readProperties">Read Properties</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="write_properties" class="form-check-input" id="writeProperties">
                            <label class="form-check-label" for="writeProperties">Write Properties</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="read_users" class="form-check-input" id="readUsers">
                            <label class="form-check-label" for="readUsers">Read Users</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="manage_bookings" class="form-check-input" id="manageBookings">
                            <label class="form-check-label" for="manageBookings">Manage Bookings</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Rate Limit (requests per hour)</label>
                        <select name="rate_limit" class="form-control">
                            <option value="100">100 requests/hour (Default)</option>
                            <option value="500">500 requests/hour</option>
                            <option value="1000">1,000 requests/hour</option>
                            <option value="5000">5,000 requests/hour</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="nc-icon nc-key-25"></i> Create API Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('API key copied to clipboard!');
    });
}

function testApiEndpoint() {
    const endpoint = document.getElementById('testEndpoint').value;
    const apiKey = document.getElementById('testApiKey').value;
    const resultDiv = document.getElementById('apiTestResult');
    
    resultDiv.innerHTML = '<div class="alert alert-info"><i class="nc-icon nc-time-alarm"></i> Testing API endpoint...</div>';
    
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    };
    
    if (apiKey) {
        headers['Authorization'] = 'Bearer ' + apiKey;
    }
    
    fetch(endpoint, {
        method: 'GET',
        headers: headers
    })
    .then(response => {
        const status = response.status;
        return response.json().then(data => ({status, data}));
    })
    .then(({status, data}) => {
        const statusClass = status >= 200 && status < 300 ? 'success' : 'danger';
        resultDiv.innerHTML = `
            <div class="alert alert-${statusClass}">
                <strong>Status:</strong> ${status}<br>
                <strong>Response:</strong><br>
                <pre class="mt-2">${JSON.stringify(data, null, 2)}</pre>
            </div>
        `;
    })
    .catch(error => {
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    });
}
</script>

@include('footer')
