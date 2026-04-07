@extends('layout')

@section('title', 'Invitation Already Used')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-lock"></i>
                        Invitation Already Used
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>

                    <h5 class="card-title">This Link is No Longer Active</h5>
                    <p class="card-text">
                        The apartment invitation associated with this link has already been used to secure the
                        apartment.
                        For security reasons, each invitation link can only be used once.
                    </p>

                    <div class="alert alert-info">
                        <strong>What does this mean?</strong>
                        <ul class="text-left mt-2 mb-0">
                            <li>The payment for this apartment has been successfully processed.</li>
                            <li>The apartment has been assigned to a tenant.</li>
                            <li>This specific link is now invalid to prevent duplicate payments or unauthorized access.
                            </li>
                        </ul>
                    </div>

                    <p class="mt-3">
                        If you have already paid for this apartment, please log in to your dashboard to view your rental
                        details and receipt.
                    </p>

                    <div class="mt-4">
                        @if(Auth::check())
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login to Your Account
                        </a>
                        @endif
                        <a href="/public/properties" class="btn btn-outline-primary ml-2">
                            <i class="fas fa-search"></i> Browse Other Apartments
                        </a>
                    </div>
                </div>
                <div class="card-footer text-muted text-center">
                    <small>Reference ID: {{ $invitation->invitation_token }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection