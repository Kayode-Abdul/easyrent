@extends('layouts.admin')

@section('title', 'Pricing Configuration Audit Trail')

@push('styles')
<style>
    .page-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 32px;
        border-radius: 16px;
        margin-bottom: 32px;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
    }
    
    .audit-entry {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid #3e8189;
        transition: transform 0.2s ease;
    }
    
    .audit-entry:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    
    .audit-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 12px;
    }
    
    .audit-timestamp {
        font-size: 14px;
        color: #6c757d;
        font-weight: 500;
    }
    
    .audit-user {
        background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .change-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px;
        margin: 8px 0;
        border-left: 3px solid #dee2e6;
    }
    
    .change-item.added {
        border-left-color: #28a745;
        background: #d4edda;
    }
    
    .change-item.modified {
        border-left-color: #ffc107;
        background: #fff3cd;
    }
    
    .change-item.removed {
        border-left-color: #dc3545;
        background: #f8d7da;
    }
    
    .old-value {
        color: #dc3545;
        text-decoration: line-through;
    }
    
    .new-value {
        color: #28a745;
        font-weight: 500;
    }
    
    .json-display {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 8px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        max-height: 200px;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div class="content">
    <!-- Page Header -->
    <div class="page-header-custom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2"><i class="fa fa-history me-3"></i>Pricing Configuration Audit Trail</h1>
                <p class="mb-0 opacity-90">Track all changes made to apartment pricing configurations</p>
            </div>
            <a href="{{ route('admin.pricing-configuration.index') }}" class="btn btn-outline-light btn-lg">
                <i class="fa fa-arrow-left me-2"></i>Back to Configuration
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="apartment_id" class="form-label">Apartment</label>
                            <select class="form-select" id="apartment_id" name="apartment_id">
                                <option value="">All Apartments</option>
                                @foreach($apartments as $apartment)
                                    <option value="{{ $apartment->apartment_id }}" {{ $apartmentId == $apartment->apartment_id ? 'selected' : '' }}>
                                        Apartment {{ $apartment->apartment_id }} 
                                        @if($apartment->property)
                                            ({{ $apartment->property->property_name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fa fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.pricing-configuration.audit-trail') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Audit Entries -->
            @if($auditLogs->count() > 0)
                <div class="mb-4">
                    <h5>Audit Entries ({{ $auditLogs->total() }})</h5>
                </div>

                @foreach($auditLogs as $log)
                    <div class="audit-entry">
                        <div class="audit-header">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-edit me-2 text-primary"></i>
                                <strong>Apartment {{ $log->auditable_id }} - Pricing Configuration Updated</strong>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="audit-timestamp me-3">
                                    {{ $log->created_at->format('M j, Y g:i A') }}
                                </span>
                                @if($log->user)
                                    <span class="audit-user">
                                        {{ $log->user->first_name }} {{ $log->user->last_name }}
                                    </span>
                                @else
                                    <span class="audit-user">System</span>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Changes Made</h6>
                                
                                @if($log->old_values && $log->new_values)
                                    @foreach($log->new_values as $field => $newValue)
                                        @php
                                            $oldValue = $log->old_values[$field] ?? null;
                                            $hasChanged = $oldValue !== $newValue;
                                        @endphp
                                        
                                        @if($hasChanged)
                                            <div class="change-item modified">
                                                <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}:</strong><br>
                                                
                                                @if($field === 'price_configuration')
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <small class="text-muted">Old:</small>
                                                            <div class="json-display">
                                                                @if($oldValue)
                                                                    {{ json_encode($oldValue, JSON_PRETTY_PRINT) }}
                                                                @else
                                                                    <em>No configuration</em>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted">New:</small>
                                                            <div class="json-display">
                                                                @if($newValue)
                                                                    {{ json_encode($newValue, JSON_PRETTY_PRINT) }}
                                                                @else
                                                                    <em>No configuration</em>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($field === 'amount')
                                                    <span class="old-value">₦{{ number_format($oldValue, 2) }}</span>
                                                    →
                                                    <span class="new-value">₦{{ number_format($newValue, 2) }}</span>
                                                @else
                                                    <span class="old-value">{{ $oldValue ?? 'Not set' }}</span>
                                                    →
                                                    <span class="new-value">{{ $newValue ?? 'Not set' }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="change-item">
                                        <em class="text-muted">No detailed change information available</em>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="mb-3">Audit Information</h6>
                                
                                <div class="change-item">
                                    <strong>IP Address:</strong> {{ $log->ip_address ?? 'Unknown' }}<br>
                                    <strong>User Agent:</strong> 
                                    <small class="text-muted">{{ Str::limit($log->user_agent ?? 'Unknown', 50) }}</small><br>
                                    <strong>URL:</strong> 
                                    <small class="text-muted">{{ $log->url ?? 'Unknown' }}</small>
                                </div>
                                
                                @if($log->old_values)
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-outline-info" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#rawData{{ $log->id }}" 
                                                aria-expanded="false">
                                            <i class="fa fa-code"></i> View Raw Data
                                        </button>
                                        
                                        <div class="collapse mt-2" id="rawData{{ $log->id }}">
                                            <div class="json-display">
                                                <strong>Old Values:</strong><br>
                                                {{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}
                                                <br><br>
                                                <strong>New Values:</strong><br>
                                                {{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        Showing {{ $auditLogs->firstItem() }} to {{ $auditLogs->lastItem() }} 
                        of {{ $auditLogs->total() }} results
                    </div>
                    {{ $auditLogs->appends(request()->query())->links() }}
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fa fa-history fa-3x text-muted mb-3"></i>
                        <h5>No Audit Entries Found</h5>
                        <p class="text-muted">No pricing configuration changes match your current filters.</p>
                        @if($apartmentId || $dateFrom || $dateTo)
                            <a href="{{ route('admin.pricing-configuration.audit-trail') }}" class="btn btn-outline-primary">
                                <i class="fa fa-times me-2"></i>Clear Filters
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-set date_to to today if date_from is selected but date_to is not
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    dateFromInput.addEventListener('change', function() {
        if (this.value && !dateToInput.value) {
            dateToInput.value = new Date().toISOString().split('T')[0];
        }
    });
    
    // Validate date range
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const dateFrom = dateFromInput.value;
        const dateTo = dateToInput.value;
        
        if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
            e.preventDefault();
            alert('Date From cannot be later than Date To');
            return false;
        }
    });
    
    // Highlight search terms in results
    const urlParams = new URLSearchParams(window.location.search);
    const apartmentId = urlParams.get('apartment_id');
    
    if (apartmentId) {
        // Highlight apartment ID in results
        const auditEntries = document.querySelectorAll('.audit-entry');
        auditEntries.forEach(entry => {
            const content = entry.innerHTML;
            const highlightedContent = content.replace(
                new RegExp(`Apartment ${apartmentId}`, 'gi'),
                `<mark>Apartment ${apartmentId}</mark>`
            );
            entry.innerHTML = highlightedContent;
        });
    }
});

// Function to export audit data (could be enhanced)
function exportAuditData() {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', 'csv');
    
    // Create a temporary link and click it
    const link = document.createElement('a');
    link.href = currentUrl.toString();
    link.download = 'pricing-configuration-audit.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Add export button if needed
const pageHeader = document.querySelector('.page-header-custom .d-flex');
if (pageHeader) {
    const exportBtn = document.createElement('button');
    exportBtn.className = 'btn btn-outline-light btn-sm me-2';
    exportBtn.innerHTML = '<i class="fa fa-download me-1"></i>Export';
    exportBtn.onclick = exportAuditData;
    
    const backBtn = pageHeader.querySelector('a');
    pageHeader.insertBefore(exportBtn, backBtn);
}
</script>
@endpush