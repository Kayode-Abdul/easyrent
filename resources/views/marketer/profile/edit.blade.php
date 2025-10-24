@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Marketer Profile</h5>
                    <a href="{{ route('marketer.profile.show') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Profile
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('marketer.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Personal Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">Personal Information</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required>
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Business Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Business Information</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="business_name">Business Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('business_name') is-invalid @enderror" 
                                           id="business_name" name="business_name" 
                                           value="{{ old('business_name', $profile->business_name ?? '') }}" required>
                                    @error('business_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="business_registration">Business Registration Number</label>
                                    <input type="text" class="form-control @error('business_registration') is-invalid @enderror" 
                                           id="business_registration" name="business_registration" 
                                           value="{{ old('business_registration', $profile->business_registration ?? '') }}">
                                    @error('business_registration')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="business_type">Business Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('business_type') is-invalid @enderror" 
                                            id="business_type" name="business_type" required>
                                        <option value="">Select Business Type</option>
                                        <option value="individual" {{ old('business_type', $profile->business_type ?? '') === 'individual' ? 'selected' : '' }}>Individual</option>
                                        <option value="partnership" {{ old('business_type', $profile->business_type ?? '') === 'partnership' ? 'selected' : '' }}>Partnership</option>
                                        <option value="company" {{ old('business_type', $profile->business_type ?? '') === 'company' ? 'selected' : '' }}>Company</option>
                                        <option value="marketing_agency" {{ old('business_type', $profile->business_type ?? '') === 'marketing_agency' ? 'selected' : '' }}>Marketing Agency</option>
                                    </select>
                                    @error('business_type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="experience_years">Years of Experience <span class="text-danger">*</span></label>
                                    <select class="form-control @error('experience_years') is-invalid @enderror" 
                                            id="experience_years" name="experience_years" required>
                                        <option value="">Select Experience</option>
                                        @for($i = 1; $i <= 20; $i++)
                                            <option value="{{ $i }}" {{ old('experience_years', $profile->experience_years ?? '') == $i ? 'selected' : '' }}>
                                                {{ $i }} {{ $i === 1 ? 'year' : 'years' }}
                                            </option>
                                        @endfor
                                        <option value="20+" {{ old('experience_years', $profile->experience_years ?? '') === '20+' ? 'selected' : '' }}>20+ years</option>
                                    </select>
                                    @error('experience_years')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Contact Information</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address">Business Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3" required>{{ old('address', $profile->address ?? '') }}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="website">Website (Optional)</label>
                                    <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                           id="website" name="website" 
                                           value="{{ old('website', $profile->website ?? '') }}" 
                                           placeholder="https://example.com">
                                    @error('website')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Marketing Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Marketing Information</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="target_market">Target Market <span class="text-danger">*</span></label>
                                    <select class="form-control @error('target_market') is-invalid @enderror" 
                                            id="target_market" name="target_market" required>
                                        <option value="">Select Target Market</option>
                                        <option value="landlords" {{ old('target_market', $profile->target_market ?? '') === 'landlords' ? 'selected' : '' }}>Landlords</option>
                                        <option value="property_owners" {{ old('target_market', $profile->target_market ?? '') === 'property_owners' ? 'selected' : '' }}>Property Owners</option>
                                        <option value="real_estate_investors" {{ old('target_market', $profile->target_market ?? '') === 'real_estate_investors' ? 'selected' : '' }}>Real Estate Investors</option>
                                        <option value="property_managers" {{ old('target_market', $profile->target_market ?? '') === 'property_managers' ? 'selected' : '' }}>Property Managers</option>
                                    </select>
                                    @error('target_market')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Marketing Channels <span class="text-danger">*</span></label>
                                    <div class="row">
                                        @php
                                            $channels = ['social_media', 'referrals', 'online_advertising', 'email_marketing', 'content_marketing', 'events'];
                                            $selectedChannels = old('marketing_channels', $profile && $profile->marketing_channels ? json_decode($profile->marketing_channels) : []);
                                        @endphp
                                        @foreach($channels as $channel)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="marketing_channels[]" value="{{ $channel }}" 
                                                           id="channel_{{ $channel }}"
                                                           {{ in_array($channel, $selectedChannels) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="channel_{{ $channel }}">
                                                        {{ ucfirst(str_replace('_', ' ', $channel)) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('marketing_channels')
                                        <span class="text-danger">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Banking Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Banking Information</h6>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bank_name">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                                           id="bank_name" name="bank_name" 
                                           value="{{ old('bank_name', auth()->user()->bank_name) }}" required>
                                    @error('bank_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="account_number">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                                           id="account_number" name="account_number" 
                                           value="{{ old('account_number', auth()->user()->account_number) }}" required>
                                    @error('account_number')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="account_name">Account Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('account_name') is-invalid @enderror" 
                                           id="account_name" name="account_name" 
                                           value="{{ old('account_name', auth()->user()->account_name) }}" required>
                                    @error('account_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- KYC Documents -->
                        @if($profile && $profile->kyc_status !== 'verified')
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="border-bottom pb-2 mb-3 mt-4">KYC Documents</h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_document">ID Document (PDF/Image)</label>
                                        <input type="file" class="form-control-file @error('id_document') is-invalid @enderror" 
                                               id="id_document" name="id_document" accept=".pdf,.jpg,.jpeg,.png">
                                        <small class="form-text text-muted">Upload your national ID, passport, or driver's license</small>
                                        @error('id_document')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="business_permit">Business Permit (PDF/Image)</label>
                                        <input type="file" class="form-control-file @error('business_permit') is-invalid @enderror" 
                                               id="business_permit" name="business_permit" accept=".pdf,.jpg,.jpeg,.png">
                                        <small class="form-text text-muted">Upload your business permit or certificate</small>
                                        @error('business_permit')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
