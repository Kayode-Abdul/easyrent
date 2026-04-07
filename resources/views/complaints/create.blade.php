@extends('layout')

@section('content')

<div class="content">
        <!-- Page Header -->
        <div class="row">
            <div class="col-md-12">
                <div class="card page-header-custom">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="nc-icon nc-simple-add"></i> Submit New Complaint
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Complaint Form -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('complaints.store') }}" method="POST" enctype="multipart/form-data" id="complaintForm">
                            @csrf
                            
                            <!-- Apartment Selection -->
                            <div class="form-group">
                                <label for="apartment_id">Select Apartment <span class="text-danger">*</span></label>
                                <select name="apartment_id" id="apartment_id" class="form-control @error('apartment_id') is-invalid @enderror" required>
                                    <option value="">Choose your apartment...</option>
                                    @foreach($apartments as $apartment)
                                        <option value="{{ $apartment->apartment_id }}" 
                                                {{ old('apartment_id', $selectedApartment) == $apartment->apartment_id ? 'selected' : '' }}
                                                data-property="{{ $apartment->property->address }}"
                                                data-landlord="{{ $apartment->owner->first_name }} {{ $apartment->owner->last_name }}">
                                            {{ $apartment->property->address }} - {{ $apartment->apartment_type ?? 'Apartment' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('apartment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Select the apartment where the issue is occurring.</small>
                            </div>

                            <!-- Category Selection -->
                            <div class="form-group">
                                <label for="category_id">Issue Category <span class="text-danger">*</span></label>
                                <div class="row">
                                    @foreach($categories as $category)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="category-option" data-category="{{ $category->id }}">
                                                <input type="radio" name="category_id" value="{{ $category->id }}" 
                                                       id="category_{{ $category->id }}" 
                                                       {{ old('category_id') == $category->id ? 'checked' : '' }}
                                                       class="d-none category-radio">
                                                <label for="category_{{ $category->id }}" class="category-card">
                                                    <div class="text-center p-3">
                                                        <i class="{{ $category->icon }} category-icon text-{{ $category->priority_color }}" style="font-size: 2rem;"></i>
                                                        <h6 class="mt-2 mb-1">{{ $category->name }}</h6>
                                                        <small class="text-muted">{{ $category->description }}</small>
                                                        <div class="mt-2">
                                                            <span class="badge badge-{{ $category->priority_color }} badge-sm">
                                                                {{ $category->priority_level_formatted }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('category_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Priority Selection -->
                            <div class="form-group">
                                <label for="priority">Priority Level <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-control @error('priority') is-invalid @enderror" required>
                                    <option value="">Select priority...</option>
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low - Can wait a few days</option>
                                    <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium - Should be addressed soon</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High - Needs attention within 24 hours</option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent - Immediate attention required</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Title -->
                            <div class="form-group">
                                <label for="title">Complaint Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title') }}" 
                                       placeholder="Brief description of the issue" 
                                       maxlength="255" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label for="description">Detailed Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="description" 
                                          class="form-control @error('description') is-invalid @enderror" 
                                          rows="5" 
                                          placeholder="Please provide detailed information about the issue, including when it started, how it affects you, and any steps you've already taken..."
                                          required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Minimum 10 characters. The more details you provide, the faster we can resolve your issue.</small>
                            </div>

                            <!-- File Attachments -->
                            <div class="form-group">
                                <label for="attachments">Attachments (Optional)</label>
                                <input type="file" name="attachments[]" id="attachments" 
                                       class="form-control-file @error('attachments.*') is-invalid @enderror" 
                                       multiple 
                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                @error('attachments.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    You can upload photos or documents to help explain the issue. 
                                    Supported formats: JPG, PNG, PDF, DOC, DOCX. Max size: 10MB per file.
                                </small>
                                <div id="file-preview" class="mt-2"></div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="nc-icon nc-send"></i> Submit Complaint
                                </button>
                                <a href="{{ route('complaints.index') }}" class="btn btn-secondary ml-2">
                                    <i class="nc-icon nc-simple-remove"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Help Sidebar -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="nc-icon nc-bulb-63"></i> Tips for Better Support
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6><i class="nc-icon nc-camera-compact text-info"></i> Include Photos</h6>
                            <p class="small text-muted">Photos help us understand the issue better and resolve it faster.</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6><i class="nc-icon nc-paper text-success"></i> Be Specific</h6>
                            <p class="small text-muted">Include details like when the issue started, how often it occurs, and what you've tried.</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6><i class="nc-icon nc-time-alarm text-warning"></i> Set Correct Priority</h6>
                            <p class="small text-muted">Choose the right priority level to ensure appropriate response time.</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6><i class="nc-icon nc-chat-33 text-primary"></i> Stay Updated</h6>
                            <p class="small text-muted">You'll receive email notifications about updates to your complaint.</p>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="nc-icon nc-alert-circle-i"></i> Emergency?
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="small">For urgent issues that pose immediate safety risks (gas leaks, electrical hazards, security breaches), contact emergency services or your landlord directly:</p>
                        <div class="text-center">
                            <a href="tel:911" class="btn btn-danger btn-sm">
                                <i class="nc-icon nc-mobile"></i> Emergency: 911
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

<style>
.category-card {
    display: block;
    border: 2px solid #e3e3e3;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    height: 100%;
}

.category-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.2);
    text-decoration: none;
    color: inherit;
}

.category-radio:checked + .category-card {
    border-color: #007bff;
    background-color: #f8f9ff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.3);
}

.category-icon {
    transition: transform 0.2s ease;
}

.category-card:hover .category-icon {
    transform: scale(1.1);
}

#file-preview .file-item {
    display: inline-block;
    margin: 5px;
    padding: 8px 12px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 0.875rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File preview functionality
    const fileInput = document.getElementById('attachments');
    const filePreview = document.getElementById('file-preview');
    
    fileInput.addEventListener('change', function() {
        filePreview.innerHTML = '';
        
        if (this.files.length > 0) {
            Array.from(this.files).forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <i class="nc-icon nc-attach-87"></i>
                    ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
                `;
                filePreview.appendChild(fileItem);
            });
        }
    });

    // Auto-set priority based on category selection
    const categoryRadios = document.querySelectorAll('.category-radio');
    const prioritySelect = document.getElementById('priority');
    
    categoryRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const categoryCard = this.nextElementSibling;
                const badge = categoryCard.querySelector('.badge');
                
                if (badge) {
                    const priorityLevel = badge.textContent.trim().toLowerCase();
                    if (prioritySelect.value === '') {
                        prioritySelect.value = priorityLevel;
                    }
                }
            }
        });
    });

    // Form validation
    document.getElementById('complaintForm').addEventListener('submit', function(e) {
        const categorySelected = document.querySelector('.category-radio:checked');
        
        if (!categorySelected) {
            e.preventDefault();
            alert('Please select a category for your complaint.');
            return false;
        }
    });
});
</script>

@endsection