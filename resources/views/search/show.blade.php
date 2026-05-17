@include('header')
<style>
    .apartment-details-section {
        padding: 60px 0;
        background: #fff;
    }
    .gallery-wrap {
        margin-bottom: 40px;
    }
    .main-img {
        height: 500px;
        background-size: cover;
        background-position: center;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .thumb-img {
        height: 100px;
        background-size: cover;
        background-position: center;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .thumb-img:hover, .thumb-img.active {
        border-color: #3e8189;
        transform: scale(1.05);
    }
    .details-sidebar {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 15px;
        position: sticky;
        top: 100px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .price-box {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #ddd;
    }
    .price-box h2 {
        color: #3e8189;
        font-weight: 800;
        margin-bottom: 5px;
    }
    .feature-list {
        list-style: none;
        padding: 0;
        margin: 25px 0;
    }
    .feature-list li {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        font-size: 16px;
        color: #444;
    }
    .feature-list li i {
        background: #e9f2f3;
        color: #3e8189;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 15px;
        font-size: 14px;
    }
    .landlord-box {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        margin-top: 30px;
        border: 1px solid #eee;
    }
    .landlord-box h5 {
        font-weight: 700;
        margin-bottom: 15px;
    }
    .btn-contact {
        background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        color: #fff;
        border: none;
        padding: 12px;
        font-weight: 700;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .btn-contact:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(62, 129, 137, 0.3);
        color: #fff;
    }
</style>

<section class="apartment-details-section">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="gallery-wrap">
                    @php
                        // Get apartment images first
                        $aptImages = $apartment->images;
                        // Get property images as fallback
                        $propImages = $apartment->property ? $apartment->property->images : collect();
                        
                        $allImgs = $aptImages->isNotEmpty() ? $aptImages : $propImages;
                        
                        // Determine main image
                        $mainImgUrl = $apartment->getFirstAvailableImage();
                    @endphp
                    <div class="main-img" id="mainImage" style="background-image: url('{{ $mainImgUrl }}');"></div>
                    <div class="row no-gutters">
                        @forelse($allImgs as $image)
                        <div class="col-3 px-1">
                            <div class="thumb-img {{ $loop->first ? 'active' : '' }}" 
                                 onclick="changeImage('{{ asset('storage/'.$image->file_path) }}', this)"
                                 style="background-image: url('{{ asset('storage/'.$image->file_path) }}');"></div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> No additional images available for this apartment.
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="description-wrap mb-5">
                    <h1 class="mb-3">{{ $apartment->apartment_type }} at {{ $apartment->property->address }}</h1>
                    <p class="text-muted mb-4"><i class="bi bi-geo-alt"></i> {{ $apartment->property->address }}, {{ $apartment->property->lga }}, {{ $apartment->property->state }}, {{ $apartment->property->country_name ?? $apartment->property->country }}</p>
                    
                    <h4 class="font-weight-bold mb-3">About this Apartment</h4>
                    <p>This beautiful {{ strtolower($apartment->apartment_type) }} is located in a prime area of {{ $apartment->property->lga }}. It features modern finishes and is perfect for individuals or families looking for a comfortable living space.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold mb-3">Property Details</h5>
                            <ul class="feature-list">
                                <li><i class="bi bi-building"></i> Property Type: {{ $apartment->property->getPropertyTypeName() }}</li>
                                <li><i class="bi bi-house"></i> Apartment Type: {{ $apartment->apartment_type }}</li>
                                <li><i class="bi bi-calendar-check"></i> Status: {{ $apartment->occupied ? 'Occupied' : 'Available' }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold mb-3">Amenities</h5>
                            <ul class="feature-list">
                                <li><i class="bi bi-lightning-charge"></i> Electricity</li>
                                <li><i class="bi bi-droplet"></i> Water Supply</li>
                                <li><i class="bi bi-shield-check"></i> 24/7 Security</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="details-sidebar">
                    <div class="price-box">
                        <p class="text-muted mb-0">Rent Price</p>
                        <h2>{{ $apartment->getFormattedRentAttribute() }}</h2>
                        <p class="mb-0 text-success font-weight-bold">Per {{ $apartment->default_rental_type }}</p>
                    </div>

                    <div class="action-box">
                        <h5 class="font-weight-bold mb-3">Interested?</h5>
                        <p class="text-muted small mb-4">Click below to start your application or contact the landlord for more information.</p>
                        <a href="{{ route('login') }}" class="btn btn-contact btn-block mb-3">Apply for this Apartment</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-block">Sign Up to View Contact</a>
                    </div>

                    <div class="landlord-box">
                        <h5>Property Manager</h5>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 50px; height: 50px; font-weight: 700;">
                                <i class="bi bi-person-fill" style="font-size: 22px;"></i>
                            </div>
                            <div>
                                <p class="mb-0 font-weight-bold">EasyRent Verified</p>
                                <p class="mb-0 text-muted small">Sign in to view contact details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function changeImage(url, el) {
        document.getElementById('mainImage').style.backgroundImage = 'url(' + url + ')';
        document.querySelectorAll('.thumb-img').forEach(function(item) {
            item.classList.remove('active');
        });
        el.classList.add('active');
    }
</script>

@include('footer')
