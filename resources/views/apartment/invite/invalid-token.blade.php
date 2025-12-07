@extends('layout')

@section('title', 'Invalid Invitation')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-circle"></i>
                        Invalid Invitation Link
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h5 class="card-title">Invalid or Corrupted Link</h5>
                    <p class="card-text">
                        This invitation link appears to be invalid or corrupted. 
                        Please check the link and try again, or contact the property owner for a new invitation.
                    </p>
                    
                    <div class="alert alert-info">
                        <strong>Possible Causes:</strong>
                        <ul class="text-left mt-2 mb-0">
                            <li>The link was copied incorrectly</li>
                            <li>The link has been modified or corrupted</li>
                            <li>The invitation has been revoked</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="/" class="btn btn-primary">
                            <i class="fas fa-home"></i> Return to Home
                        </a>
                        <a href="/dashboard/properties" class="btn btn-outline-primary ml-2">
                            <i class="fas fa-search"></i> Browse Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection