@extends('layout')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-user">
                    <div class="image">
                        <img src="{{ asset('assets/images/bg/blog_bg.jpg') }}" alt="...">
                    </div>
                    <div class="card-body">
                        <div class="author">
                            <a href="#">
                                <img class="avatar border-gray" src="{{ $user->photo ? asset($user->photo) : asset('assets/images/default-avatar.png') }}" alt="...">
                                <h5 class="title">{{ $user->first_name }} {{ $user->last_name }}</h5>
                            </a>
                            <p class="description">
                                @ {{ $user->username }}
                            </p>
                        </div>
                        <p class="description text-center">
                            {{ $user->occupation ?? 'Member' }} <br>
                            <span class="badge badge-info">{{ $user->state }}</span>
                        </p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Quick Stats</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled team-members">
                            <li>
                                <div class="row">
                                    <div class="col-md-2 col-2">
                                        <div class="avatar">
                                            <i class="nc-icon nc-email-85 text-success"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-7 col-7">
                                        Messages
                                        <br />
                                        <span class="text-muted"><small>Received</small></span>
                                    </div>
                                    <div class="col-md-3 col-3 text-right">
                                        {{ $user->receivedMessages()->count() }}
                                    </div>
                                </div>
                            </li>
                            @if($user->isLandlord())
                            <li>
                                <div class="row">
                                    <div class="col-md-2 col-2">
                                        <div class="avatar">
                                            <i class="nc-icon nc-bank text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-7 col-7">
                                        Properties
                                        <br />
                                        <span class="text-muted"><small>Managed</small></span>
                                    </div>
                                    <div class="col-md-3 col-3 text-right">
                                        {{ $user->managedProperties()->count() }}
                                    </div>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="card-title">Account Settings Hub</h4>
                    </div>
                    <div class="card-body">
                        <!-- Navigation Tabs -->
                        <ul class="nav nav-tabs custom-settings-tabs mb-4" id="settingsTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true">
                                    <i class="nc-icon nc-single-02"></i> Profile
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="payout-tab" data-toggle="tab" href="#payout" role="tab" aria-controls="payout" aria-selected="false">
                                    <i class="nc-icon nc-money-coins"></i> Payouts
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="security-tab" data-toggle="tab" href="#security" role="tab" aria-controls="security" aria-selected="false">
                                    <i class="nc-icon nc-key-25"></i> Security
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="notifications-tab" data-toggle="tab" href="#notifications" role="tab" aria-controls="notifications" aria-selected="false">
                                    <i class="nc-icon nc-bell-55"></i> Alerts
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="settingsTabContent">
                            
                            <!-- Profile Tab -->
                            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                <form action="{{ route('user.update', auth()->id()) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6 pr-1">
                                            <div class="form-group">
                                                <label>First Name</label>
                                                <input type="text" name="first_name" class="form-control" placeholder="First Name" value="{{ $user->first_name }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6 pl-1">
                                            <div class="form-group">
                                                <label>Last Name</label>
                                                <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="{{ $user->last_name }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Email Address</label>
                                                <input type="email" class="form-control" placeholder="Email" value="{{ $user->email }}" disabled>
                                                <small class="text-muted">Contact support to change your email.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Profile Photo</label>
                                                <input type="file" name="photo" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary btn-round">Update Profile</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Payout Tab -->
                            <div class="tab-pane fade" id="payout" role="tabpanel" aria-labelledby="payout-tab">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> Provide your bank details to receive payments for rent or artisan tasks.
                                </div>
                                <form action="{{ route('settings.payouts.update') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Bank Name</label>
                                                <input type="text" name="bank_name" class="form-control" placeholder="e.g. GTBank, Zenith" value="{{ $user->bank_name }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 pr-1">
                                            <div class="form-group">
                                                <label>Account Number</label>
                                                <input type="text" name="bank_account_number" class="form-control" placeholder="10 Digits" value="{{ $user->bank_account_number }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6 pl-1">
                                            <div class="form-group">
                                                <label>Account Name</label>
                                                <input type="text" name="bank_account_name" class="form-control" placeholder="Legal Name on Account" value="{{ $user->bank_account_name }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>BVN (Optional for Verification)</label>
                                                <input type="password" name="bvn" class="form-control" placeholder="Bank Verification Number" value="{{ $user->bvn }}">
                                                <small class="text-muted">Stored securely for identity verification.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-warning btn-round">Update Payout Info</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Security Tab -->
                            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                                <form action="{{ route('settings.security.update') }}" method="POST">
                                    @csrf
                                    <h6 class="mb-3">Change Password</h6>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Current Password</label>
                                                <input type="password" name="current_password" class="form-control" placeholder="Current Password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 pr-1">
                                            <div class="form-group">
                                                <label>New Password</label>
                                                <input type="password" name="password" class="form-control" placeholder="At least 8 characters">
                                            </div>
                                        </div>
                                        <div class="col-md-6 pl-1">
                                            <div class="form-group">
                                                <label>Confirm New Password</label>
                                                <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password">
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <h6 class="mb-3">Advanced Security</h6>
                                    <div class="form-check p-0">
                                        <label class="form-check-label d-flex align-items-center">
                                            <input class="form-check-input mb-0" type="checkbox" name="two_factor_enabled" {{ $user->two_factor_enabled ? 'checked' : '' }}>
                                            <span class="form-check-sign ml-4"><strong>Enable Two-Factor Authentication (2FA)</strong></span>
                                        </label>
                                        <p class="description pl-4 ml-1 mt-1">
                                            Adds an extra layer of security by requiring a verification code sent to your email when logging in from a new device.
                                        </p>
                                    </div>

                                    <div class="text-right mt-3">
                                        <button type="submit" class="btn btn-danger btn-round">Update Security</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Notifications Tab -->
                            <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                                <form action="{{ route('settings.notifications.update') }}" method="POST">
                                    @csrf
                                    <h6 class="mb-3">Notification Preferences</h6>
                                    <div class="alert alert-light border">
                                        Choose which events you want to be notified about via on-platform badges.
                                    </div>

                                    @php
                                        $prefs = $user->notification_preferences ?? ['bids' => true, 'messages' => true, 'payments' => true, 'overdue' => true];
                                    @endphp

                                    <div class="form-group">
                                        <div class="custom-control custom-switch mb-3">
                                            <input type="checkbox" class="custom-control-input" id="notif_bids" name="notif_bids" {{ ($prefs['bids'] ?? true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="notif_bids">New Artisan Bids (Receive alerts when artisans bid on tasks)</label>
                                        </div>

                                        <div class="custom-control custom-switch mb-3">
                                            <input type="checkbox" class="custom-control-input" id="notif_messages" name="notif_messages" {{ ($prefs['messages'] ?? true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="notif_messages">Direct Messages (New chat messages from users)</label>
                                        </div>

                                        <div class="custom-control custom-switch mb-3">
                                            <input type="checkbox" class="custom-control-input" id="notif_payments" name="notif_payments" {{ ($prefs['payments'] ?? true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="notif_payments">Payment Updates (Completed rent or fee payments)</label>
                                        </div>

                                        <div class="custom-control custom-switch mb-3">
                                            <input type="checkbox" class="custom-control-input" id="notif_overdue" name="notif_overdue" {{ ($prefs['overdue'] ?? true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="notif_overdue">Overdue Notices (Rent expirations and lease reminders)</label>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <button type="submit" class="btn btn-success btn-round">Save Preferences</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
<style>
    .custom-settings-tabs .nav-link {
        color: #66615b;
        font-weight: 600;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 10px 20px;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .custom-settings-tabs .nav-link i {
        font-size: 18px;
    }
    .custom-settings-tabs .nav-link:hover {
        color: #ef8157;
        background: rgba(239, 129, 87, 0.05);
    }
    .custom-settings-tabs .nav-link.active {
        color: #ef8157 !important;
        background: transparent !important;
        border-bottom: 2px solid #ef8157;
    }
    .card-user .image {
        height: 120px;
    }
    /* Simple Switch styling if native custom-switch isn't fully styled */
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #ef8157;
        border-color: #ef8157;
    }
</style>
@endpush
