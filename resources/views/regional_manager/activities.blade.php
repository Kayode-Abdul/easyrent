@extends('layout')
@section('content')
<div class="content">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-1">
                        <i class="fa fa-history text-primary me-2"></i>
                        Regional Activities Log
                    </h4>
                    <p class="text-muted">View all recent activities in your managed region.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(count($sorted) > 0)
                        <div class="timeline">
                            @foreach($sorted as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-marker {{ $activity['color'] ?? 'bg-primary' }}">
                                        <i class="{{ $activity['icon'] ?? 'fa fa-circle' }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1">{{ $activity['title'] }}</h6>
                                            <small class="text-muted">{{ $activity['time'] }}</small>
                                        </div>
                                        <p class="mb-1">{{ $activity['description'] }}</p>
                                        @if(isset($activity['link']))
                                            <a href="{{ $activity['link'] }}" class="btn btn-sm btn-link p-0">View Details</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle me-2"></i>
                            No recent activities recorded.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    height: 100%;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -1.5rem;
    width: 1rem;
    height: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #007bff;
    color: white;
    font-size: 0.5rem;
}

.timeline-content {
    border-left: 2px solid #e9ecef;
    padding-left: 1rem;
}

.text-info .timeline-marker {
    background-color: #17a2b8;
}

.text-success .timeline-marker {
    background-color: #28a745;
}

.text-warning .timeline-marker {
    background-color: #ffc107;
}
</style>
@endsection
