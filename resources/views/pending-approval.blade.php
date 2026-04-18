@extends('layout')

@section('content')
<div class="content py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0 rounded-lg mt-5">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="nc-icon nc-time-alarm text-warning" style="font-size: 80px;"></i>
                        </div>
                        <h2 class="font-weight-bold mb-3">Approval Pending</h2>
                        <p class="text-muted mb-4" style="font-size: 18px;">
                            Thank you for joining EasyRent! Your account is currently under review by our administrative
                            team.
                        </p>

                        <div class="alert alert-info border-0 mb-4"
                            style="background-color: rgba(0, 123, 255, 0.1); color: #0056b3;">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fa fa-info-circle mr-3" style="font-size: 24px;"></i>
                                <div class="text-left">
                                    <strong>What happens next?</strong><br>
                                    Our team will verify your details and approve your account within 24-48 hours. You
                                    will receive an email once your account is active.
                                </div>
                            </div>
                        </div>

                        <p class="mb-5">
                            In the meantime, you can explore our public listings or contact our support team if you have
                            any questions.
                        </p>

                        <div class="d-flex justify-content-center">
                            @if(session('dashboard_mode') !== 'personal' && (auth()->user()->isLandlord() || auth()->user()->isTenant()))
                            <button onclick="switchBackToPersonal()" class="btn btn-info btn-round px-4 mr-3">
                                <i class="fa fa-user mr-2"></i> Personal Dashboard
                            </button>
                            @endif
                            <a href="{{ url('/') }}" class="btn btn-primary btn-round px-4 mr-3">
                                <i class="fa fa-home mr-2"></i> Go to Homepage
                            </a>
                            <a href="{{ route('contact') }}" class="btn btn-outline-primary btn-round px-4">
                                <i class="fa fa-envelope mr-2"></i> Contact Support
                            </a>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted">Logged in as: <strong>{{ auth()->user()->email }}</strong> | <a href="#"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="text-danger">Logout</a></p>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchBackToPersonal() {
    var mode = 'personal';
    var url = '';
    
    @if(session('dashboard_mode') === 'artisan')
        url = '/dashboard/switch-artisan-mode';
    @elseif(session('admin_dashboard_mode') === 'admin')
        url = '/dashboard/switch-admin-mode';
    @else
        url = '/dashboard/switch-mode';
    @endif

    $.ajax({
        url: url,
        method: 'POST',
        data: { mode: mode },
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(res) {
            window.location.href = '/dashboard';
        },
        error: function() {
            alert('Error switching dashboard mode.');
        }
    });
}
</script>
@endsection

@push('head')
<style>
    .card {
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .btn-round {
        border-radius: 30px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 12px 25px;
    }
</style>
@endpush