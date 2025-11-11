<!-- Header area start -->
@include('header')
<!-- Header area end -->

<!-- CSRF Token for AJAX requests -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Enhanced Contact Section -->
<div class="content">
 <div class="container pt-pad">
<section class="contact-hero">
    <div class="contact-hero-content">
        <div class="container">
            <div class="hero-text">
                <h1>Get in Touch</h1>
                <p>We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
            </div>
        </div>
    </div>
    <div class="hero-shape">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" class="shape-fill"></path>
            <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" class="shape-fill"></path>
            <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" class="shape-fill"></path>
        </svg>
    </div>
</section>

<!-- Contact Methods -->
<section class="contact-methods">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="contact-method">
                    <div class="method-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="method-content">
                        <h3>Call Us</h3>
                        <p>Speak directly with our team</p>
                        <a href="tel:+2348123456789" class="contact-link">+234 812 345 6789</a>
                        <p><small>Mon - Fri: 9:00 AM - 6:00 PM</small></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="contact-method">
                    <div class="method-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="method-content">
                        <h3>Email Us</h3>
                        <p>Send us your questions anytime</p>
                        <a href="mailto:support@easyrent.com" class="contact-link">support@easyrent.com</a>
                        <p><small>We respond within 24 hours</small></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="contact-method">
                    <div class="method-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="method-content">
                        <h3>Visit Us</h3>
                        <p>Come see us in person</p>
                        <address class="contact-link">
                            33 Adegoke Street, Marsha Surulere,<br>
                            Lagos, Nigeria
                        </address>
                        <small>Open Monday - Friday</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Contact Form -->
<section class="contact-form-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="contact-form-wrapper">
                    <div class="form-header">
                        <h2>Send us a Message</h2>
                        <p>Fill out the form below and we'll get back to you as soon as possible.</p>
                    </div>
                    
                    <form method="post" action="{{ route('contact.submit') }}" class="enhanced-contact-form" id="contact-form-main">
                        @csrf
                        
                        <div class="form-row">
                            <div class="input-group half">
                                <div class="input-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="name" id="name" class="form-input" placeholder="Your Name" required>
                                    <label class="floating-label">Full Name</label>
                                </div>
                            </div>
                            <div class="input-group half">
                                <div class="input-wrapper">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" name="email" id="email" class="form-input" placeholder="Your Email" required>
                                    <label class="floating-label">Email Address</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group half">
                                <div class="input-wrapper">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="tel" name="phone" id="phone" class="form-input" placeholder="Your Phone">
                                    <label class="floating-label">Phone Number</label>
                                </div>
                            </div>
                            <div class="input-group half">
                                <div class="input-wrapper">
                                    <i class="fas fa-tag input-icon"></i>
                                    <select name="subject" class="form-select" required>
                                        <option value="">Select Subject</option>
                                        <option value="general">General Inquiry</option>
                                        <option value="property">Property Listing</option>
                                        <option value="rental">Rental Inquiry</option>
                                        <option value="support">Technical Support</option>
                                        <option value="partnership">Partnership</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <label class="floating-label">Subject</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-comment input-icon"></i>
                                    <textarea name="message" id="message" class="form-textarea" placeholder="Your Message" rows="5" required></textarea>
                                    <label class="floating-label">Message</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="privacy-notice">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" name="privacy" required>
                                    <span class="checkmark"></span>
                                    I agree to the <a href="#" target="_blank">Privacy Policy</a> and consent to my data being processed.
                                </label>
                            </div>
                        </div>

                        <div class="submit-area">
                            <button type="submit" class="contact-submit-btn">
                                <span class="btn-text">Send Message</span>
                                <i class="fas fa-paper-plane btn-icon"></i>
                            </button>
                        </div>

                        <div class="form-messages">
                            <div id="success-message" class="alert alert-success" style="display: none;">
                                <i class="fas fa-check-circle"></i>
                                Thank you! Your message has been sent successfully.
                            </div>
                            <div id="error-message" class="alert alert-danger" style="display: none;">
                                <i class="fas fa-exclamation-circle"></i>
                                Sorry, there was an error sending your message. Please try again.
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="contact-info-sidebar">
                    <div class="info-card">
                        <h3>Why Choose EasyRent?</h3>
                        <div class="feature-list">
                            <div class="feature-item">
                                <i class="fas fa-shield-alt"></i>
                                <div>
                                    <h4>Secure Platform</h4>
                                    <p>Your data and transactions are protected with bank-level security.</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <h4>24/7 Support</h4>
                                    <p>Our team is available around the clock to assist you.</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-users"></i>
                                <div>
                                    <h4>Trusted Community</h4>
                                    <p>Join thousands of satisfied landlords and tenants.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3>Frequently Asked</h3>
                        <div class="faq-list">
                            <div class="faq-item">
                                <h4>How do I list my property?</h4>
                                <p>Simply create an account and follow our easy property listing process.</p>
                            </div>
                            <div class="faq-item">
                                <h4>Is there a fee to use EasyRent?</h4>
                                <p>Basic listing is free. We only charge a small commission on successful rentals.</p>
                            </div>
                            <div class="faq-item">
                                <h4>How do I contact support?</h4>
                                <p>Use this contact form, email us, or call our support line during business hours.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="container-fluid">
        <div class="map-wrapper">
           <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.1355861745774!2d3.3478239743788425!3d6.50451529348775!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x103b8c3d416418f5%3A0x9569129df40285f6!2s!5e0!3m2!1sen!2sng!4v1762893428102!5m2!1sen!2sng" 
                width="100%" 
                height="400" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy"> 
            </iframe>
            <div class="map-overlay">
                <div class="map-info">
                    <h3>Visit Our Office</h3>
                    <p>33 Adegoke Street, Surulere, Lagos, Nigeria</p>
                    <a href="https://maps.google.com" target="_blank" class="directions-btn">
                        <i class="fas fa-directions"></i> Get Directions
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
</div>
<style>
/* Enhanced Contact Form Styles */
.contact-hero {
    background: linear-gradient(45deg, #51cbce, #6bd098) !important;
    color: white;
    padding: 100px 0 50px;
    position: relative;
    overflow: hidden;
}

.contact-hero-content h1 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.contact-hero-content p {
    font-size: 1.2rem;
    opacity: 0.9;
}

.hero-shape {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    overflow: hidden;
    line-height: 0;
}

.hero-shape svg {
    position: relative;
    display: block;
    width: calc(100% + 1.3px);
    height: 60px;
}

.hero-shape .shape-fill {
    fill: #FFFFFF;
}

.contact-methods {
    padding: 80px 0;
    background: #f8f9fa;
}

.contact-method {
    text-align: center;
    padding: 40px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    margin-bottom: 30px;
}

.contact-method:hover {
    transform: translateY(-10px);
}

.method-icon {
    width: 80px;
    height: 80px;
    background:linear-gradient(45deg, #51cbce, #6bd098) !important;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.method-icon i {
    font-size: 2rem;
    color: white;
}

.contact-method h3 {
    color: #333;
    margin-bottom: 10px;
}

.contact-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.contact-form-section {
    padding: 80px 0;
}

.contact-form-wrapper {
    background: white;
    padding: 50px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.form-header h2 {
    color: #333;
    margin-bottom: 10px;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
}

.input-group {
    flex: 1;
}

.input-group.half {
    flex: 0 0 calc(50% - 10px);
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    z-index: 2;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 15px 15px 15px 45px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: white;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.floating-label {
    position: absolute;
    left: 45px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    pointer-events: none;
    transition: all 0.3s ease;
    background: white;
    padding: 0 5px;
}

.form-input:focus + .floating-label,
.form-input:not(:placeholder-shown) + .floating-label,
.form-select:focus + .floating-label,
.form-select:not([value=""]) + .floating-label,
.form-textarea:focus + .floating-label,
.form-textarea:not(:placeholder-shown) + .floating-label {
    top: 0;
    left: 40px;
    font-size: 12px;
    color: #667eea;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.checkbox-wrapper input[type="checkbox"] {
    margin-right: 10px;
}

.contact-submit-btn {
    background: linear-gradient(45deg, #51cbce, #6bd098) !important;
    color: white;
    border: none;
    padding: 15px 40px;
    border-radius: 50px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.contact-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.contact-info-sidebar {
    padding-left: 30px;
}

.info-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.feature-item, .faq-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
}

.feature-item i {
    color: #667eea;
    margin-right: 15px;
    margin-top: 5px;
}

.map-section {
    position: relative;
}

.map-wrapper {
    position: relative;
}

.map-overlay {
    position: absolute;
    top: 20px;
    left: 20px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.directions-btn {
    background: #667eea;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.alert {
    padding: 15px;
    border-radius: 10px;
    margin-top: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .input-group.half {
        flex: 1;
    }
    
    .contact-info-sidebar {
        padding-left: 0;
        margin-top: 50px;
    }
    
    .contact-form-wrapper {
        padding: 30px 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contact-form-main');
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    const submitBtn = form.querySelector('.contact-submit-btn');
    const originalBtnText = submitBtn.innerHTML;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        submitBtn.disabled = true;
        
        // Hide previous messages
        successMessage.style.display = 'none';
        errorMessage.style.display = 'none';
        
        // Prepare form data
        const formData = new FormData(form);
        
        // Submit form via AJAX
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                successMessage.querySelector('i').nextSibling.textContent = ' ' + data.message;
                successMessage.style.display = 'block';
                form.reset();
                
                // Show toast notification if available
                if (typeof showToast === 'function') {
                    showToast(data.message, 'success');
                }
            } else {
                let errorText = 'Sorry, there was an error sending your message. Please try again.';
                if (data.errors) {
                    errorText = Object.values(data.errors).flat().join(' ');
                } else if (data.message) {
                    errorText = data.message;
                }
                
                errorMessage.querySelector('i').nextSibling.textContent = ' ' + errorText;
                errorMessage.style.display = 'block';
                
                // Show toast notification if available
                if (typeof showToast === 'function') {
                    showToast(errorText, 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorMessage.style.display = 'block';
            
            // Show toast notification if available
            if (typeof showToast === 'function') {
                showToast('Network error. Please check your connection and try again.', 'error');
            }
        })
        .finally(() => {
            // Restore button state
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        });
    });
    
    // Enhanced form interactions
    const inputs = form.querySelectorAll('.form-input, .form-textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
});
</script>

@include('footer') 