@include('header')

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <i class="nc-icon nc-time-alarm" style="font-size: 80px; color: #dc3545;"></i>
                        <h2 class="mt-4 mb-3">Payment Link Expired</h2>
                        <p class="text-muted">This payment invitation has expired and is no longer valid.</p>
                        
                        <div class="alert alert-info mt-4">
                            <p class="mb-0">Please contact <strong>{{ $invitation->tenant->first_name }} {{ $invitation->tenant->last_name }}</strong> to request a new payment link.</p>
                        </div>
                        
                        <a href="{{ route('contact') }}" class="btn btn-primary mt-3">
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('footer')
