<form method="GET" action="{{ route('payments.index') }}" class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Date Range</label>
                    <div class="input-daterange input-group">
                        <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                        <div class="input-group-append input-group-prepend">
                            <span class="input-group-text">to</span>
                        </div>
                        <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control">
                        <option value="">All Methods</option>
                        <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="ussd" {{ request('payment_method') == 'ussd' ? 'selected' : '' }}>USSD</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Export Format</label>
                    <div class="input-group">
                        <select name="export_format" class="form-control">
                            <option value="">Select Format</option>
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                        <div class="input-group-append">
                            <button type="submit" name="export" value="1" class="btn btn-primary">Export</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-info">Apply Filters</button>
                <a href="{{ route('payments.index') }}" class="btn btn-default">Reset</a>
            </div>
        </div>
    </div>
</form>
