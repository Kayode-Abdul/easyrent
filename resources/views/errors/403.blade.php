@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning">
                    <h4><i class="fas fa-exclamation-triangle"></i> Access Denied</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-shield-alt fa-5x text-warning"></i>
                    </div>
                    <h3>Access Denied</h3>
                    <p class="lead">{{ $message ?? 'You do not have permission to access this resource.' }}</p>
                    
                    <div class="mt-4">
                        @auth
                            <p>You are logged in as: <strong>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</strong></p>
                            @if(auth()->user()->admin != 1 && auth()->user()->role != 1)
                                <div class="alert alert-info">
                                    <strong>Note:</strong> This area requires administrator privileges. 
                                    Contact your system administrator if you believe you should have access.
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning">
                                <strong>Not logged in?</strong> 
                                <a href="{{ route('login') }}" class="alert-link">Click here to login</a>
                            </div>
                        @endauth
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-success">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
