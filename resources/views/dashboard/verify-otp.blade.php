@extends('layout')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card mt-5">
                    <div class="card-header text-center pt-4">
                        <div class="icon-circle bg-warning text-white mb-3 mx-auto" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            <i class="nc-icon nc-lock-circle-open"></i>
                        </div>
                        <h4 class="card-title font-weight-bold">Security Verification</h4>
                        <p class="text-muted">For your security, we need to verify your identity before updating your {{ strtolower(session('pending_update_type') ?? 'account') }} details.</p>
                    </div>
                    <div class="card-body px-5 pb-4">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 pl-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="alert alert-info text-center" style="background-color: #f8f9fa; border: 1px solid #e9ecef; color: #495057;">
                            <i class="fa fa-envelope-open-o text-warning mr-2"></i> We've sent a 6-digit security code to your email.
                        </div>

                        <form action="{{ route('settings.otp.confirm') }}" method="POST">
                            @csrf
                            <div class="form-group mt-4 text-center">
                                <label for="otp" class="font-weight-bold mb-3">Enter Security Code</label>
                                <input type="text" name="otp" id="otp" class="form-control text-center text-uppercase font-weight-bold" 
                                    style="letter-spacing: 10px; font-size: 24px; height: 60px; color: #ef8157; background-color: #fffaf8; border: 2px solid #ef8157;" 
                                    placeholder="XXXXXX" maxlength="6" required autocomplete="off" autofocus>
                            </div>
                            
                            <button type="submit" class="btn btn-warning btn-block btn-round mt-4 py-3 font-weight-bold" style="font-size: 16px;">
                                Verify & Apply Changes
                            </button>
                        </form>
                    </div>
                    <div class="card-footer text-center pb-4">
                        <a href="{{ route('settings.index') }}" class="text-muted"><i class="fa fa-arrow-left mr-1"></i> Cancel Update</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
