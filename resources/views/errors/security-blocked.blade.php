@extends('layouts.app')

@section('title', 'Security Block')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-shield-alt"></i>
                        Security Block
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger" role="alert">
                        <h5 class="alert-heading">Request Blocked</h5>
                        <p>{{ $message ?? 'Your request has been blocked due to security concerns.' }}</p>
                    </div>

                    <div class="mt-4">
                        <h6>Why was my request blocked?</h6>
                        <p>Our security system detected potentially harmful content or suspicious activity in your request. This could be due to:</p>
                        <ul>
                            <li>Invalid or malicious input data</li>
                            <li>Suspicious request patterns</li>
                            <li>Potential security threats</li>
                            <li>Automated attacks or bot activity</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <h6>What should you do?</h6>
                        <ul>
                            <li>Review your input data for any unusual characters or content</li>
                            <li>Ensure you're using the application as intended</li>
                            <li>Try your request again with different input</li>
                            @if(isset($contact_support) && $contact_support)
                                <li>Contact support if you believe this is an error</li>
                            @endif
                        </ul>
                    </div>

                    @if(isset($contact_support) && $contact_support)
                        <div class="alert alert-info mt-4" role="alert">
                            <h6 class="alert-heading">Need Help?</h6>
                            <p class="mb-2">
                                If you believe this security block is in error, please contact our support team 
                                with the following information:
                            </p>
                            <ul class="mb-2">
                                <li>Time of the blocked request: {{ now()->format('Y-m-d H:i:s T') }}</li>
                                <li>What you were trying to do when the block occurred</li>
                                <li>Any error messages you received</li>
                            </ul>
                            <p class="mb-0">
                                <strong>Reference ID:</strong> {{ Str::random(8) }}
                            </p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> Home
                        </a>
                        @if(isset($contact_support) && $contact_support)
                            <a href="mailto:support@easyrent.com" class="btn btn-info">
                                <i class="fas fa-envelope"></i> Contact Support
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection