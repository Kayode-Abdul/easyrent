@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm text-center">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 80px;"></i>
                    </div>
                    <h3 class="mb-3">Payment Request Declined</h3>
                    <p class="text-muted mb-4">
                        This payment request has been declined.
                        @if($invitation->decline_reason)
                            <br><br>
                            <strong>Reason:</strong> {{ $invitation->decline_reason }}
                        @endif
                    </p>
                    <p class="text-muted">
                        The tenant has been notified of your decision.
                    </p>
                    <div class="mt-4">
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> Go to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
