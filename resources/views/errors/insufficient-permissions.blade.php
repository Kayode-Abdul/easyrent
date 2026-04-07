@extends('layouts.app')

@section('title', 'Insufficient Permissions')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-lock"></i>
                        Access Denied
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning" role="alert">
                        <h5 class="alert-heading">Insufficient Permissions</h5>
                        <p>{{ $message ?? 'You do not have sufficient permissions to access this resource.' }}</p>
                    </div>

                    @if(isset($required_permission))
                        <div class="mt-4">
                            <h6>Required Permission</h6>
                            <p>
                                To access this resource, you need the following permission: 
                                <code>{{ $required_permission }}</code>
                            </p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <h6>What can you do?</h6>
                        <ul>
                            @auth
                                <li>Contact your administrator to request the necessary permissions</li>
                                <li>Verify that you're logged in with the correct account</li>
                                <li>Check if you have the appropriate role assigned to your account</li>
                            @else
                                <li>Log in with an account that has the necessary permissions</li>
                                <li>Contact support if you believe you should have access</li>
                            @endauth
                        </ul>
                    </div>

                    @auth
                        <div class="alert alert-info mt-4" role="alert">
                            <h6 class="alert-heading">Current Account Information</h6>
                            <p class="mb-1"><strong>Email:</strong> {{ auth()->user()->email }}</p>
                            @if(method_exists(auth()->user(), 'getRoleNames'))
                                <p class="mb-0"><strong>Roles:</strong> {{ auth()->user()->getRoleNames()->implode(', ') ?: 'No roles assigned' }}</p>
                            @endif
                        </div>
                    @endauth

                    <div class="mt-4">
                        @auth
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Go Back
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-primary">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                            <a href="mailto:admin@easyrent.com" class="btn btn-info">
                                <i class="fas fa-envelope"></i> Request Access
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-secondary">
                                <i class="fas fa-home"></i> Home
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection