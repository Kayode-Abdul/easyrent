@include('header')

<div class="wrapper">
    <div class="main-panel">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Invite Someone to Pay Your Rent</h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Send a payment request to someone who will pay your rent (employer, sponsor, family member, etc.)</p>
                                
                                <form action="{{ route('tenant.invite.benefactor') }}" method="POST">
                                    @csrf
                                    
                                    <div class="form-group">
                                        <label for="benefactor_email">Benefactor's Email *</label>
                                        <input type="email" name="benefactor_email" id="benefactor_email" class="form-control" required>
                                        <small class="form-text text-muted">The person who will pay your rent</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="amount">Rent Amount (₦) *</label>
                                        <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="property_id">Property (Optional)</label>
                                        <select name="property_id" id="property_id" class="form-control">
                                            <option value="">Select Property</option>
                                            @foreach(auth()->user()->properties ?? [] as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="message">Message (Optional)</label>
                                        <textarea name="message" id="message" class="form-control" rows="3" maxlength="500"></textarea>
                                        <small class="form-text text-muted">Add a personal message to your benefactor</small>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <strong>Note:</strong> The benefactor will receive an email with a secure payment link. They can choose to pay once or set up recurring payments.
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="nc-icon nc-send"></i> Send Payment Request
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('footer')
