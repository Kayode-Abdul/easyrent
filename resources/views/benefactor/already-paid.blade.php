@include('header')

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <i class="nc-icon nc-check-2" style="font-size: 80px; color: #28a745;"></i>
                        <h2 class="mt-4 mb-3">Already Paid</h2>
                        <p class="text-muted">This payment has already been completed.</p>
                        
                        <div class="alert alert-success mt-4">
                            <p class="mb-0">Payment was successfully processed on {{ $invitation->accepted_at->format('M d, Y') }}</p>
                        </div>
                        
                        @auth
                        <a href="{{ route('benefactor.dashboard') }}" class="btn btn-primary mt-3">
                            View Dashboard
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-primary mt-3">
                            Sign In
                        </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('footer')
