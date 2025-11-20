@include('header')

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg text-center">
                    <div class="card-body p-5">
                        <div class="success-icon mb-4">
                            <i class="nc-icon nc-check-2" style="font-size: 80px; color: #28a745;"></i>
                        </div>
                        
                        <h2 class="text-success mb-3">Payment Successful!</h2>
                        <p class="lead">Thank you for your payment</p>
                        
                        <div class="payment-details mt-4 p-4 bg-light rounded">
                            <div class="row">
                                <div class="col-md-6 text-left">
                                    <p><strong>Payment Reference:</strong></p>
                                    <p class="text-primary">{{ $payment->payment_reference }}</p>
                                </div>
                                <div class="col-md-6 text-right">
                                    <p><strong>Amount Paid:</strong></p>
                                    <h3 class="text-success">₦{{ number_format($payment->amount, 2) }}</h3>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-md-6 text-left">
                                    <p><strong>Tenant:</strong> {{ $payment->tenant->first_name }} {{ $payment->tenant->last_name }}</p>
                                    <p><strong>Payment Type:</strong> {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</p>
                                </div>
                                <div class="col-md-6 text-right">
                                    <p><strong>Date:</strong> {{ $payment->paid_at->format('M d, Y') }}</p>
                                    <p><strong>Time:</strong> {{ $payment->paid_at->format('h:i A') }}</p>
                                </div>
                            </div>
                            
                            @if($payment->isRecurring())
                            <div class="alert alert-info mt-3">
                                <i class="nc-icon nc-refresh-69"></i> 
                                <strong>Recurring Payment Active</strong><br>
                                Next payment: {{ $payment->next_payment_date->format('M d, Y') }} ({{ ucfirst($payment->frequency) }})
                            </div>
                            @endif
                        </div>
                        
                        <div class="mt-4">
                            <a href="#" onclick="window.print()" class="btn btn-outline-primary mr-2">
                                <i class="nc-icon nc-paper"></i> Print Receipt
                            </a>
                            
                            @auth
                            <a href="{{ route('benefactor.dashboard') }}" class="btn btn-primary">
                                <i class="nc-icon nc-layout-11"></i> View Dashboard
                            </a>
                            @else
                            <a href="{{ route('register') }}" class="btn btn-success">
                                <i class="nc-icon nc-simple-add"></i> Create Account
                            </a>
                            @endauth
                        </div>
                        
                        @guest
                        <div class="mt-4 p-3 bg-warning text-dark rounded">
                            <p class="mb-2"><strong>Create an account to:</strong></p>
                            <ul class="list-unstyled mb-0">
                                <li>✓ Track all your payments</li>
                                <li>✓ Manage recurring payments</li>
                                <li>✓ View payment history</li>
                                <li>✓ Download receipts anytime</li>
                            </ul>
                        </div>
                        @endguest
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-muted">
                        A confirmation email has been sent to {{ $payment->benefactor->email }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

@include('footer')
