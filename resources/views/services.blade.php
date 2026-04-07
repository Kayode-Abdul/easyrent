@extends('layout')
@section('content')

<div class="hero-wrap" style="background-image: url('/assets/images/bg_1.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
            <div class="col-lg-8 col-md-6 ftco-animate d-flex align-items-end">
                <div class="text text-center">
                    <h1 class="mb-4">Our <span>Services</span></h1>
                    <p class="breadcrumbs">
                        <span class="mr-2"><a href="/">Home</a></span> 
                        <span>Services</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section">
    <div class="container">
        <!-- Services Overview -->
        <div class="row justify-content-center mb-5 pb-3">
            <div class="col-md-7 heading-section text-center ftco-animate">
                <h2 class="mb-4">Comprehensive Property Solutions</h2>
                <p>EasyRent offers end-to-end property rental services designed to make property management and rental processes seamless for all stakeholders.</p>
            </div>
        </div>

        <!-- Main Services Grid -->
        <div class="row">
            <!-- Property Listing Service -->
            <div class="col-md-6 col-lg-4 ftco-animate">
                <div class="services-box">
                    <div class="services-icon">
                        <span class="icon-home"></span>
                    </div>
                    <div class="services-content">
                        <h3>Property Listing</h3>
                        <p>List your properties for free with professional photography, detailed descriptions, and maximum visibility across our platform.</p>
                        <ul class="service-features">
                            <li><i class="icon-check"></i> Free property listings</li>
                            <li><i class="icon-check"></i> Professional photography support</li>
                            <li><i class="icon-check"></i> SEO-optimized descriptions</li>
                            <li><i class="icon-check"></i> Multi-platform visibility</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                    </div>
                </div>
            </div>

            <!-- Tenant Screening -->
            <div class="col-md-6 col-lg-4 ftco-animate">
                <div class="services-box">
                    <div class="services-icon">
                        <span class="icon-user"></span>
                    </div>
                    <div class="services-content">
                        <h3>Tenant Screening</h3>
                        <p>Comprehensive background checks and verification services to ensure you get reliable, trustworthy tenants.</p>
                        <ul class="service-features">
                            <li><i class="icon-check"></i> Identity verification</li>
                            <li><i class="icon-check"></i> Employment verification</li>
                            <li><i class="icon-check"></i> Credit history checks</li>
                            <li><i class="icon-check"></i> Reference verification</li>
                        </ul>
                        <a href="{{ route('benefits') }}" class="btn btn-primary">Learn More</a>
                    </div>
                </div>
            </div>

            <!-- Property Management -->
            <div class="col-md-6 col-lg-4 ftco-animate">
                <div class="services-box">
                    <div class="services-icon">
                        <span class="icon-cog"></span>
                    </div>
                    <div class="services-content">
                        <h3>Property Management</h3>
                        <p>Professional property management services including maintenance, tenant relations, and financial reporting.</p>
                        <ul class="service-features">
                            <li><i class="icon-check"></i> 24/7 maintenance support</li>
                            <li><i class="icon-check"></i> Rent collection</li>
                            <li><i class="icon-check"></i> Financial reporting</li>
                            <li><i class="icon-check"></i> Tenant communication</li>
                        </ul>
                        <a href="{{ route('contact') }}" class="btn btn-primary">Contact Us</a>
                    </div>
                </div>
            </div>

            <!-- Digital Payments -->
            <div class="col-md-6 col-lg-4 ftco-animate">
                <div class="services-box">
                    <div class="services-icon">
                        <span class="icon-credit-card"></span>
                    </div>
                    <div class="services-content">
                        <h3>Digital Payments</h3>
                        <p>Secure online payment processing with multiple payment options and automated rent collection systems.</p>
                        <ul class="service-features">
                            <li><i class="icon-check"></i> Multiple payment methods</li>
                            <li><i class="icon-check"></i> Automated rent collection</li>
                            <li><i class="icon-check"></i> Payment tracking</li>
                            <li><i class="icon-check"></i> Digital receipts</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-primary">Start Now</a>
                    </div>
                </div>
            </div>

            <!-- Legal Support -->
            <div class="col-md-6 col-lg-4 ftco-animate">
                <div class="services-box">
                    <div class="services-icon">
                        <span class="icon-file-text"></span>
                    </div>
                    <div class="services-content">
                        <h3>Legal Support</h3>
                        <p>Comprehensive legal documentation and support for lease agreements, tenant disputes, and property law compliance.</p>
                        <ul class="service-features">
                            <li><i class="icon-check"></i> Lease agreement templates</li>
                            <li><i class="icon-check"></i> Legal consultation</li>
                            <li><i class="icon-check"></i> Dispute resolution</li>
                            <li><i class="icon-check"></i> Compliance support</li>
                        </ul>
                        <a href="{{ route('contact') }}" class="btn btn-primary">Get Help</a>
                    </div>
                </div>
            </div>

            <!-- Analytics & Reporting -->
            <div class="col-md-6 col-lg-4 ftco-animate">
                <div class="services-box">
                    <div class="services-icon">
                        <span class="icon-bar-chart"></span>
                    </div>
                    <div class="services-content">
                        <h3>Analytics & Reporting</h3>
                        <p>Detailed analytics and reporting tools to help you make informed decisions about your property investments.</p>
                        <ul class="service-features">
                            <li><i class="icon-check"></i> Market analysis</li>
                            <li><i class="icon-check"></i> Performance metrics</li>
                            <li><i class="icon-check"></i> Financial reports</li>
                            <li><i class="icon-check"></i> Trend analysis</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-primary">View Demo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Service Process Section -->
<section class="ftco-section bg-light">
    <div class="container">
        <div class="row justify-content-center mb-5 pb-3">
            <div class="col-md-7 heading-section text-center ftco-animate">
                <h2 class="mb-4">How It Works</h2>
                <p>Our streamlined process makes property rental simple and efficient</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3 ftco-animate">
                <div class="process-step text-center">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <span class="icon-user-plus"></span>
                    </div>
                    <h4>Sign Up</h4>
                    <p>Create your free account and choose your user type (Landlord, Tenant, or Property Manager)</p>
                </div>
            </div>
            
            <div class="col-md-3 ftco-animate">
                <div class="process-step text-center">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <span class="icon-home"></span>
                    </div>
                    <h4>List or Search</h4>
                    <p>List your properties or search for your perfect rental using our advanced filters</p>
                </div>
            </div>
            
            <div class="col-md-3 ftco-animate">
                <div class="process-step text-center">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <span class="icon-handshake-o"></span>
                    </div>
                    <h4>Connect</h4>
                    <p>Connect with verified landlords or tenants through our secure messaging system</p>
                </div>
            </div>
            
            <div class="col-md-3 ftco-animate">
                <div class="process-step text-center">
                    <div class="step-number">4</div>
                    <div class="step-icon">
                        <span class="icon-key"></span>
                    </div>
                    <h4>Move In</h4>
                    <p>Complete the rental process with digital contracts and secure payment processing</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center mb-5 pb-3">
            <div class="col-md-7 heading-section text-center ftco-animate">
                <h2 class="mb-4">Transparent Pricing</h2>
                <p>No hidden fees. Pay only when you succeed.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 ftco-animate">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Basic</h3>
                        <div class="price">Free</div>
                        <p>For individual landlords</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><i class="icon-check"></i> Free property listings</li>
                            <li><i class="icon-check"></i> Basic tenant screening</li>
                            <li><i class="icon-check"></i> Messaging system</li>
                            <li><i class="icon-check"></i> Payment processing</li>
                            <li><i class="icon-times"></i> Advanced analytics</li>
                            <li><i class="icon-times"></i> Priority support</li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <a href="{{ route('register') }}" class="btn btn-outline-primary btn-block">Get Started</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 ftco-animate">
                <div class="pricing-card featured">
                    <div class="pricing-header">
                        <h3>Professional</h3>
                        <div class="price">5%</div>
                        <p>Commission per successful rental</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><i class="icon-check"></i> Everything in Basic</li>
                            <li><i class="icon-check"></i> Advanced tenant screening</li>
                            <li><i class="icon-check"></i> Property management tools</li>
                            <li><i class="icon-check"></i> Advanced analytics</li>
                            <li><i class="icon-check"></i> Priority support</li>
                            <li><i class="icon-check"></i> Legal document templates</li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-block">Most Popular</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 ftco-animate">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Enterprise</h3>
                        <div class="price">Custom</div>
                        <p>For property management companies</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><i class="icon-check"></i> Everything in Professional</li>
                            <li><i class="icon-check"></i> White-label solution</li>
                            <li><i class="icon-check"></i> API access</li>
                            <li><i class="icon-check"></i> Custom integrations</li>
                            <li><i class="icon-check"></i> Dedicated account manager</li>
                            <li><i class="icon-check"></i> 24/7 phone support</li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <a href="{{ route('contact') }}" class="btn btn-outline-primary btn-block">Contact Sales</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="ftco-section bg-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h2 class="text-white mb-4">Ready to Get Started?</h2>
                <p class="text-white mb-4">Join thousands of satisfied users who have streamlined their property rental process with EasyRent</p>
                <div>
                    <a href="{{ route('register') }}" class="btn btn-white btn-lg mr-3">Sign Up Now</a>
                    <a href="{{ route('contact') }}" class="btn btn-outline-white btn-lg">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.services-box {
    background: #fff;
    padding: 40px 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    margin-bottom: 30px;
}

.services-box:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.services-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(45deg, #2c5aa0, #1e3d72);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
}

.services-icon span {
    font-size: 35px;
    color: white;
}

.services-content h3 {
    color: #2c5aa0;
    margin-bottom: 15px;
    font-weight: 600;
}

.service-features {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}

.service-features li {
    padding: 8px 0;
    color: #666;
}

.service-features i {
    color: #28a745;
    margin-right: 10px;
}

.process-step {
    position: relative;
    padding: 30px 20px;
}

.step-number {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 40px;
    background: #2c5aa0;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}

.step-icon {
    width: 80px;
    height: 80px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 20px auto;
    border: 3px solid #2c5aa0;
}

.step-icon span {
    font-size: 30px;
    color: #2c5aa0;
}

.pricing-card {
    background: white;
    border-radius: 15px;
    padding: 40px 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    margin-bottom: 30px;
    position: relative;
}

.pricing-card.featured {
    transform: scale(1.05);
    border: 3px solid #2c5aa0;
}

.pricing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.pricing-card.featured:hover {
    transform: scale(1.05) translateY(-5px);
}

.pricing-header h3 {
    color: #2c5aa0;
    margin-bottom: 10px;
}

.price {
    font-size: 48px;
    font-weight: bold;
    color: #2c5aa0;
    margin: 20px 0;
}

.pricing-features ul {
    list-style: none;
    padding: 0;
}

.pricing-features li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.pricing-features i.icon-check {
    color: #28a745;
    margin-right: 10px;
}

.pricing-features i.icon-times {
    color: #dc3545;
    margin-right: 10px;
}

.btn-white {
    background: white;
    color: #2c5aa0;
    border: 2px solid white;
}

.btn-white:hover {
    background: transparent;
    color: white;
    border-color: white;
}

.btn-outline-white {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.btn-outline-white:hover {
    background: white;
    color: #2c5aa0;
}
</style>

@endsection