@extends('layout')

@section('title', 'Invitation Already Used')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                    <h3 class="text-success mb-3">Invitation Already Used</h3>
                    <p class="text-muted mb-4">
                        This apartment invitation has already been used and the apartment has been assigned to a tenant.
                    </p>
                    
                    @if(isset($invitation) && $invitation->landlord)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Looking for other apartments?</strong><br>
                        Contact the landlord to see if they have other available properties:<br><br>
                        <strong>{{ $invitation->landlord->first_name }} {{ $invitation->landlord->last_name }}</strong><br>
                        <a href="mailto:{{ $invitation->landlord->email }}">{{ $invitation->landlord->email }}</a><br>
                        <a href="tel:{{ $invitation->landlord->phone }}">{{ $invitation->landlord->phone }}</a>
                    </div>
                    @endif
                    
                    <div class="d-grid gap-2">
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Browse Other Properties
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Your Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection