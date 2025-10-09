@extends('layout')

@section('title', 'Create Marketer Profile')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-tie me-2"></i>
                        Create Your Marketer Profile
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Welcome to the EasyRent Marketer Program!</strong> 
                        Complete your profile to start earning commissions from landlord referrals. 
                        All information will be reviewed by our team.
                    </div>

                    <form action="{{ route('marketer.profile.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Business Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-building me-2"></i>Business Information
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="business_name" class="form-label">Business Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('business_name') is-invalid @enderror" 
                                           id="business_name" name="business_name" value="{{ old('business_name') }}" required>
                                    @error('business_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="business_type" class="form-label">Business Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('business_type') is-invalid @enderror" 
                                            id="business_type" name="business_type" required>
                                        <option value="">Select Business Type</option>
                                        <option value="real_estate_agency" {{ old('business_type') == 'real_estate_agency' ? 'selected' : '' }}>Real Estate Agency</option>
                                        <option value="individual_marketer" {{ old('business_type') == 'individual_marketer' ? 'selected' : '' }}>Individual Marketer</option>
                                        <option value="marketing_company" {{ old('business_type') == 'marketing_company' ? 'selected' : '' }}>Marketing Company</option>
                                        <option value="freelancer" {{ old('business_type') == 'freelancer' ? 'selected' : '' }}>Freelancer</option>
                                        <option value="other" {{ old('business_type') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('business_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="years_of_experience" class="form-label">Years of Experience <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('years_of_experience') is-invalid @enderror" 
                                           id="years_of_experience" name="years_of_experience" 
                                           value="{{ old('years_of_experience') }}" min="0" max="50" required>
                                    @error('years_of_experience')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="preferred_commission_rate" class="form-label">Preferred Commission Rate (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('preferred_commission_rate') is-invalid @enderror" 
                                           id="preferred_commission_rate" name="preferred_commission_rate" 
                                           value="{{ old('preferred_commission_rate', '5') }}" min="1" max="15" step="0.5" required>
                                    <div class="form-text">Standard rate is 5%. Higher rates require approval.</div>
                                    @error('preferred_commission_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Marketing Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-bullhorn me-2"></i>Marketing Information
                                </h6>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="marketing_channels" class="form-label">Marketing Channels <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('marketing_channels') is-invalid @enderror" 
                                              id="marketing_channels" name="marketing_channels" rows="3" required>{{ old('marketing_channels') }}</textarea>
                                    <div class="form-text">Describe your marketing channels (e.g., Social Media, WhatsApp Groups, Email Marketing, etc.)</div>
                                    @error('marketing_channels')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="target_regions" class="form-label">Target Regions <span class="text-danger">*</span></label>
                                    <select class="form-select @error('target_regions') is-invalid @enderror" 
                                            id="target_regions" name="target_regions[]" multiple required>
                                        <option value="lagos" {{ in_array('lagos', old('target_regions', [])) ? 'selected' : '' }}>Lagos</option>
                                        <option value="abuja" {{ in_array('abuja', old('target_regions', [])) ? 'selected' : '' }}>Abuja</option>
                                        <option value="port-harcourt" {{ in_array('port-harcourt', old('target_regions', [])) ? 'selected' : '' }}>Port Harcourt</option>
                                        <option value="kano" {{ in_array('kano', old('target_regions', [])) ? 'selected' : '' }}>Kano</option>
                                        <option value="ibadan" {{ in_array('ibadan', old('target_regions', [])) ? 'selected' : '' }}>Ibadan</option>
                                        <option value="kaduna" {{ in_array('kaduna', old('target_regions', [])) ? 'selected' : '' }}>Kaduna</option>
                                        <option value="benin" {{ in_array('benin', old('target_regions', [])) ? 'selected' : '' }}>Benin City</option>
                                        <option value="warri" {{ in_array('warri', old('target_regions', [])) ? 'selected' : '' }}>Warri</option>
                                        <option value="jos" {{ in_array('jos', old('target_regions', [])) ? 'selected' : '' }}>Jos</option>
                                        <option value="calabar" {{ in_array('calabar', old('target_regions', [])) ? 'selected' : '' }}>Calabar</option>
                                        <option value="other" {{ in_array('other', old('target_regions', [])) ? 'selected' : '' }}>Other Regions</option>
                                    </select>
                                    <div class="form-text">Hold Ctrl/Cmd to select multiple regions</div>
                                    @error('target_regions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Additional Information
                                </h6>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Professional Bio</label>
                                    <textarea class="form-control @error('bio') is-invalid @enderror" 
                                              id="bio" name="bio" rows="4" maxlength="1000">{{ old('bio') }}</textarea>
                                    <div class="form-text">Tell us about your marketing experience and expertise (optional)</div>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="website" class="form-label">Website URL</label>
                                    <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                           id="website" name="website" value="{{ old('website') }}">
                                    @error('website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="social_media_handles" class="form-label">Social Media Handles</label>
                                    <input type="text" class="form-control @error('social_media_handles') is-invalid @enderror" 
                                           id="social_media_handles" name="social_media_handles" 
                                           value="{{ old('social_media_handles') }}"
                                           placeholder="@username or profile links">
                                    @error('social_media_handles')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- KYC Documents -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-file-upload me-2"></i>KYC Documents <span class="text-danger">*</span>
                                </h6>
                                <p class="text-muted small">Please upload the following documents for verification:</p>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kyc_document_id" class="form-label">Valid ID Document <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control @error('kyc_documents.id') is-invalid @enderror" 
                                           id="kyc_document_id" name="kyc_documents[id]" 
                                           accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div class="form-text">Upload your National ID, Driver's License, or Passport</div>
                                    @error('kyc_documents.id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kyc_document_address" class="form-label">Proof of Address <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control @error('kyc_documents.address') is-invalid @enderror" 
                                           id="kyc_document_address" name="kyc_documents[address]" 
                                           accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div class="form-text">Upload utility bill or bank statement (not older than 3 months)</div>
                                    @error('kyc_documents.address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kyc_document_business" class="form-label">Business Registration</label>
                                    <input type="file" class="form-control @error('kyc_documents.business') is-invalid @enderror" 
                                           id="kyc_document_business" name="kyc_documents[business]" 
                                           accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text">Upload CAC certificate or business registration (if applicable)</div>
                                    @error('kyc_documents.business')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kyc_document_tax" class="form-label">Tax Identification</label>
                                    <input type="file" class="form-control @error('kyc_documents.tax') is-invalid @enderror" 
                                           id="kyc_document_tax" name="kyc_documents[tax]" 
                                           accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text">Upload TIN certificate (if available)</div>
                                    @error('kyc_documents.tax')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms_accepted" required>
                                    <label class="form-check-label" for="terms_accepted">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> 
                                        of the EasyRent Marketer Program <span class="text-danger">*</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('marketer.dashboard') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Application
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

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">EasyRent Marketer Program Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="terms-content" style="max-height: 400px; overflow-y: auto;">
                    <h6>1. Program Overview</h6>
                    <p>The EasyRent Marketer Program allows qualified individuals and businesses to earn commissions by referring landlords to our platform.</p>
                    
                    <h6>2. Eligibility</h6>
                    <ul>
                        <li>Must be 18 years or older</li>
                        <li>Must have valid identification documents</li>
                        <li>Must comply with all applicable laws and regulations</li>
                        <li>Must not engage in spam or fraudulent activities</li>
                    </ul>
                    
                    <h6>3. Commission Structure</h6>
                    <ul>
                        <li>Standard commission rate: 5% of first year rent</li>
                        <li>Commission paid only for successful landlord registrations</li>
                        <li>Minimum payout threshold: ₦10,000</li>
                        <li>Payments processed monthly</li>
                    </ul>
                    
                    <h6>4. Responsibilities</h6>
                    <ul>
                        <li>Maintain accurate and up-to-date profile information</li>
                        <li>Use provided marketing materials appropriately</li>
                        <li>Comply with EasyRent brand guidelines</li>
                        <li>Report any issues or concerns promptly</li>
                    </ul>
                    
                    <h6>5. Prohibited Activities</h6>
                    <ul>
                        <li>Spamming or unsolicited communications</li>
                        <li>False or misleading advertising</li>
                        <li>Trademark or copyright infringement</li>
                        <li>Self-referrals or fraudulent referrals</li>
                    </ul>
                    
                    <h6>6. Termination</h6>
                    <p>EasyRent reserves the right to terminate marketer accounts for violation of these terms or at its sole discretion.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // File upload validation
    $('input[type="file"]').on('change', function() {
        const file = this.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (file && file.size > maxSize) {
            alert('File size must be less than 5MB');
            $(this).val('');
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        const requiredFiles = ['kyc_documents[id]', 'kyc_documents[address]'];
        let isValid = true;
        
        requiredFiles.forEach(function(fieldName) {
            const input = $(`input[name="${fieldName}"]`)[0];
            if (!input.files.length) {
                alert('Please upload all required documents');
                isValid = false;
                return false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
