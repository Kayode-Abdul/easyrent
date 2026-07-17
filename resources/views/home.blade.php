@include('header')
<link rel="stylesheet" href="/assets/css/search-hero.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
  /* ============ PREMIUM HERO SECTION ============ */
  .er-hero {
    position: relative;
    width: 100%;
    min-height: 100vh;
    overflow: hidden;
    display: flex;
    align-items: center;
  }

  .er-hero__bg {
    position: absolute;
    inset: 0;
    z-index: 0;
  }

  .er-hero__bg img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    animation: er-heroZoom 25s ease-in-out infinite alternate;
    transition: opacity 0.8s ease;
  }

  /* Light mode: show day image, hide night */
  .er-hero__bg-day { opacity: 1; }
  .er-hero__bg-night { opacity: 0; }

  /* Dark mode: show night image, hide day */
  html[data-chrome-dark="true"] .er-hero__bg-day { opacity: 0; }
  html[data-chrome-dark="true"] .er-hero__bg-night { opacity: 1; }

  @keyframes er-heroZoom {
    0% { transform: scale(1); }
    100% { transform: scale(1.08); }
  }

  .er-hero__overlay {
    position: absolute;
    inset: 0;
    z-index: 1;
    background: linear-gradient(
      135deg,
      rgba(0, 30, 32, 0.88) 0%,
      rgba(12, 84, 85, 0.72) 40%,
      rgba(12, 84, 85, 0.45) 70%,
      rgba(0, 0, 0, 0.55) 100%
    );
    transition: opacity 0.8s ease;
  }

  /* Hide the overlay in dark mode since the night image is dark enough */
  html[data-chrome-dark="true"] .er-hero__overlay {
    opacity: 0;
  }

  /* CRITICAL: Completely opt out the entire hero section from global dark mode inversion.
     This stops the dark search card from turning bright, the white text from turning black,
     and prevents the "filmy" double-filter look on the background image. */
  html[data-chrome-dark="true"] .er-hero {
    filter: invert(1) hue-rotate(180deg) !important;
  }
  
  html[data-chrome-dark="true"] .er-hero img,
  html[data-chrome-dark="true"] .er-hero .er-hero__bg {
    filter: none !important;
  }

  /* Force visible form controls and buttons in dark mode */
  html[data-chrome-dark="true"] .er-search-card .form-control {
    background: rgba(0, 0, 0, 0.8) !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    color: #ffffff !important;
  }
  html[data-chrome-dark="true"] .er-search-card .btn-search {
    color: #ffffff !important;
  }

  /* Floating particles */
  .er-hero__particles {
    position: absolute;
    inset: 0;
    z-index: 2;
    pointer-events: none;
    overflow: hidden;
  }

  .er-hero__particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    animation: er-float linear infinite;
  }

  .er-hero__particle:nth-child(1) { left: 10%; animation-duration: 18s; animation-delay: 0s; width: 3px; height: 3px; }
  .er-hero__particle:nth-child(2) { left: 25%; animation-duration: 22s; animation-delay: 2s; width: 5px; height: 5px; }
  .er-hero__particle:nth-child(3) { left: 45%; animation-duration: 16s; animation-delay: 4s; }
  .er-hero__particle:nth-child(4) { left: 60%; animation-duration: 20s; animation-delay: 1s; width: 6px; height: 6px; }
  .er-hero__particle:nth-child(5) { left: 80%; animation-duration: 24s; animation-delay: 3s; width: 3px; height: 3px; }
  .er-hero__particle:nth-child(6) { left: 92%; animation-duration: 19s; animation-delay: 5s; }

  @keyframes er-float {
    0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-10vh) rotate(720deg); opacity: 0; }
  }

  .er-hero__content {
    position: relative;
    z-index: 3;
    width: 100%;
    padding: 0 0 40px;
  }

  /* Badge / pill */
  .er-hero__badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 50px;
    color: rgba(255, 255, 255, 0.9);
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 0.5px;
    margin-bottom: 28px;
    animation: er-fadeInUp 0.8s ease-out;
  }

  .er-hero__badge-dot {
    width: 8px;
    height: 8px;
    background: #4ecdc4;
    border-radius: 50%;
    animation: er-pulse 2s ease-in-out infinite;
  }

  @keyframes er-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.3); }
  }

  /* Heading */
  .er-hero__title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(44px, 7.5vw, 68px);
    font-weight: 800;
    color: #ffffff;
    line-height: 1.15;
    margin-bottom: 30px;
    animation: er-fadeInUp 0.8s ease-out 0.15s both;
  }

  .er-hero__title-accent {
    background: linear-gradient(135deg, #4ecdc4, #44b8a8, #f7b71d);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-block !important;
  }

  .er-hero__subtitle {
    font-family: 'Inter', sans-serif;
    font-size: clamp(16px, 2vw, 20px);
    color: rgba(255, 255, 255, 0.75);
    font-weight: 500;
    /* max-width: 560px; */
    line-height: 1.7;
    margin-bottom: 40px;
    animation: er-fadeInUp 0.8s ease-out 0.3s both;
  }

  @keyframes er-fadeInUp {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
  }

  /* Stats bar */
  .er-hero__stats {
    display: flex;
    gap: 32px;
    margin-bottom: 48px;
    animation: er-fadeInUp 0.8s ease-out 0.45s both;
  }

  .er-hero__stat {
    text-align: center;
    padding: 16px 24px;
    background: rgba(255, 255, 255, 0.06);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    transition: all 0.3s ease;
    min-width: 120px;
  }

  .er-hero__stat:hover {
    background: rgba(255, 255, 255, 0.12);
    transform: translateY(-3px);
    border-color: rgba(78, 205, 196, 0.3);
  }

  .er-hero__stat-number {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 800;
    color: #4ecdc4;
    line-height: 1;
    margin-bottom: 4px;
  }

  .er-hero__stat-label {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.55);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 500;
  }

  /* Search form card */
  .er-hero__search {
    animation: er-fadeInUp 0.8s ease-out 0.6s both;
  }

  .er-search-card {
    background: #0a282ab3;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(78, 205, 196, 0.25);
    border-top: 3px solid #4ecdc4;
    border-radius: 20px;
    padding: 28px 32px 20px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4), 0 0 40px rgba(78, 205, 196, 0.08);
  }

  .er-search-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
  }

  .er-search-card__title {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 1.5px;
  }

  .er-search-card__toggle {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #4ecdc4;
    text-decoration: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: color 0.3s;
  }

  .er-search-card__toggle:hover {
    color: #f7b71d;
    text-decoration: none;
  }

  .er-search-card .form-control {
    height: 52px !important;
    background: rgba(255, 255, 255, 0.12) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    border-radius: 12px !important;
    padding: 12px 16px !important;
    font-family: 'Inter', sans-serif !important;
    font-size: 14px !important;
    color: #ffffff !important;
    transition: all 0.3s ease;
  }

  .er-search-card .form-control::placeholder {
    color: rgba(255, 255, 255, 0.5) !important;
  }

  .er-search-card .form-control option {
    background: #0a282a;
    color: #ffffff;
  }

  .er-search-card .form-control:focus {
    background: rgba(255, 255, 255, 0.18) !important;
    border-color: #4ecdc4 !important;
    box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.15) !important;
  }

  .er-search-card .btn-search {
    height: 52px;
    background: linear-gradient(135deg, #4ecdc4 0%, #44b8a8 50%, #3e8189 100%) !important;
    border: none !important;
    border-radius: 12px !important;
    font-family: 'Inter', sans-serif !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    letter-spacing: 0.5px !important;
    color: #fff !important;
    transition: all 0.35s ease !important;
    text-transform: uppercase;
    box-shadow: 0 4px 15px rgba(78, 205, 196, 0.3);
  }

  .er-search-card .btn-search:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(78, 205, 196, 0.4) !important;
  }

  /* CTA buttons */
  .er-hero__cta {
    display: flex;
    gap: 16px;
    margin-top: 24px;
    flex-wrap: wrap;
    justify-content: center;
  }

  .er-hero__cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 32px;
    border-radius: 12px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.35s ease;
    letter-spacing: 0.3px;
  }

  .er-hero__cta-btn--primary {
    background: linear-gradient(135deg, #4ecdc4, #3e8189);
    color: #fff;
    box-shadow: 0 4px 20px rgba(78, 205, 196, 0.3);
  }

  .er-hero__cta-btn--primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(78, 205, 196, 0.45);
    color: #fff;
    text-decoration: none;
  }

  .er-hero__cta-btn--outline {
    background: transparent;
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.25);
  }

  .er-hero__cta-btn--outline:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-3px);
    color: #fff;
    text-decoration: none;
  }

  .er-hero__cta-btn--info {
    background: transparent;
    color: #fff;
    border: 1px solid rgba(78, 205, 196, 0.6);
  }

  .er-hero__cta-btn--info:hover {
    background: rgba(78, 205, 196, 0.15);
    border-color: #4ecdc4;
    transform: translateY(-3px);
    color: #fff;
    text-decoration: none;
  }

  /* --- Role Tabs --- */
  .er-role-tabs-wrapper {
    margin-top: 30px;
    animation: er-fadeInUp 0.8s ease-out 0.45s both;
    width: 100%;
  }
  
  .er-role-tabs-nav {
    display: inline-flex;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 50px;
    padding: 6px;
    gap: 8px;
    margin-bottom: 24px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .er-role-tab-btn {
    background: transparent;
    border: none;
    color: rgba(0, 0, 0, 0.7);
    padding: 12px 24px;
    border-radius: 50px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .er-role-tab-btn:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.1);
  }

  .er-role-tab-btn.active {
    background: #4ecdc4;
    color: #0a282a;
    box-shadow: 0 4px 15px rgba(78, 205, 196, 0.4);
  }

  .er-role-tab-content {
    display: none;
    background: rgba(10, 40, 42, 0.6);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(78, 205, 196, 0.2);
    border-radius: 20px;
    padding: 30px 40px;
    text-align: left;
    max-width: 650px;
    margin: 0 auto;
    animation: er-fadeIn 0.4s ease-in-out;
  }

  .er-role-tab-content.active {
    display: block;
  }

  .er-role-tab-content h4 {
    color: #fff;
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    margin-bottom: 12px;
    font-weight: 700;
  }

  .er-role-tab-content p {
    color: rgba(255, 255, 255, 0.85);
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    line-height: 1.6;
    margin-bottom: 24px;
  }

  .er-role-tab-content .er-hero__cta-btn {
    width: auto;
    display: inline-flex;
  }

  @keyframes er-fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Scroll indicator */
  .er-hero__scroll {
    position: absolute;
    bottom: 32px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 3;
    text-align: center;
    animation: er-fadeInUp 0.8s ease-out 1s both;
  }

  .er-hero__scroll-line {
    width: 1px;
    height: 50px;
    background: linear-gradient(to bottom, rgba(255,255,255,0.5), transparent);
    margin: 0 auto 8px;
    animation: er-scrollLine 2s ease-in-out infinite;
  }

  @keyframes er-scrollLine {
    0%, 100% { opacity: 0.3; transform: scaleY(0.5); }
    50% { opacity: 1; transform: scaleY(1); }
  }

  .er-hero__scroll-text {
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgba(255, 255, 255, 0.4);
  }

  /* Responsive */
  @media (max-width: 991.98px) {
    .er-hero__stats {
      gap: 16px;
      flex-wrap: wrap;
    }
    .er-hero__stat {
      min-width: 90px;
      padding: 12px 16px;
    }
    .er-hero__stat-number { font-size: 22px; }
  }

  @media (max-width: 767.98px) {
    .er-hero { min-height: 100vh; }
    .er-hero__content { padding: 100px 0 100px; }
    .er-hero__stats { gap: 10px; }
    .er-hero__stat { min-width: 80px; padding: 10px 12px; }
    .er-hero__stat-number { font-size: 20px; }
    .er-hero__stat-label { font-size: 10px; }
    .er-search-card { padding: 20px 18px 14px; }
    .er-hero__cta-btn { width: 100%; justify-content: center; }
    
    .er-role-tabs-nav {
      flex-direction: column;
      border-radius: 16px;
      width: 100%;
    }
    .er-role-tab-btn {
      border-radius: 12px;
      width: 100%;
      justify-content: center;
    }
    .er-role-tab-content {
      padding: 24px 20px;
      text-align: center;
    }
    .er-role-tab-content .er-hero__cta-btn {
      width: 100%;
    }

    .er-hero__scroll { display: none; }
  }
</style>

<!-- ============ PREMIUM HERO SECTION ============ -->
<div class="er-hero ftco-degree-bg">
  <!-- Background images: day (light mode) + night (dark mode) -->
  <div class="er-hero__bg">
    <img src="{{ asset('assets/images/bg_1.jpg') }}" alt="EasyRent - Property Management" class="er-hero__bg-day">
    <img src="{{ asset('assets/images/bg_1.png') }}" alt="EasyRent - Property Management" class="er-hero__bg-night">
  </div>

  <!-- Gradient overlay -->
  <!-- <div class="er-hero__overlay"></div> -->

  <!-- Floating particles -->
  <div class="er-hero__particles">
    <div class="er-hero__particle"></div>
    <div class="er-hero__particle"></div>
    <div class="er-hero__particle"></div>
    <div class="er-hero__particle"></div>
    <div class="er-hero__particle"></div>
    <div class="er-hero__particle"></div>
  </div>

  <!-- Main content -->
  <div class="er-hero__content">
    <div class="container">
      <div class="row no-gutters slider-text justify-content-center">
        <!-- Left: Text -->
        <div class="col-lg-10 col-md-12 ftco-animate d-flex fadeInUp ftco-animated">
          <div class="text text-center w-100">
<!-- 
          <div class="er-hero__badge">
            <span class="er-hero__badge-dot"></span>
            Nigeria's #1 Rent Collection Platform
          </div> -->

          <h1 class="er-hero__title">
            The Smart Way to <span class="er-hero__title-accent">Collect Rent</span><br/> &  <span class="er-hero__title-accent">Manage Property</span>
          </h1>

          <!-- <p class="er-hero__subtitle">
            Automate rent collection, track payments in real-time, and manage all your properties from a single powerful dashboard.
          </p> -->

          <div class="er-hero__cta" style="margin-top: 20px;">
            @auth
              <a href="{{ url('/dashboard') }}" class="er-hero__cta-btn er-hero__cta-btn--primary">
                <i class="bi bi-grid-1x2-fill"></i> Go to Dashboard
              </a>
            @else
              <div class="er-role-tabs-wrapper">
                <div class="er-role-tabs-nav">
                  <button class="er-role-tab-btn" data-target="role-tenant">
                    <i class="bi bi-person-check-fill"></i> I'm a Tenant
                  </button>
                  <button class="er-role-tab-btn active" data-target="role-landlord">
                    <i class="bi bi-house-add-fill"></i> I'm a Landlord
                  </button>
                  <button class="er-role-tab-btn" data-target="role-manager">
                    <i class="bi bi-building-fill-gear"></i> I'm a Property Manager
                  </button>
                </div>
                
                <div class="er-role-tab-content" id="role-tenant">
                  <h4>Find Your Perfect Home</h4>
                  <p>Browse thousands of available properties, apply online, and pay rent securely from anywhere. Enjoy a seamless renting experience with easy maintenance requests.</p>
                  <a href="{{ route('register') }}" class="er-hero__cta-btn er-hero__cta-btn--outline">
                    <i class="bi bi-rocket-takeoff-fill"></i> Get Started Free
                  </a>
                </div>
                
                <div class="er-role-tab-content active" id="role-landlord">
                  <h4>Maximize Your Rental Income</h4>
                  <p>List your properties to millions of users, find verified tenants quickly, and collect rent automatically. Say goodbye to late payments and stressful property management.</p>
                  <a href="{{ route('onboarding.index') }}" class="er-hero__cta-btn er-hero__cta-btn--primary">
                    <i class="bi bi-house-add-fill"></i> List Your Property
                  </a>
                </div>


                <div class="er-role-tab-content" id="role-manager">
                  <h4>Scale Your Operations</h4>
                  <p>Oversee multiple properties efficiently. Manage tenant communications, streamline maintenance workflows, and generate detailed financial reports in one centralized dashboard.</p>
                  <a href="{{ route('register') }}" class="er-hero__cta-btn er-hero__cta-btn--info">
                    <i class="bi bi-building"></i> Start Managing
                  </a>
                </div>
              </div>
            @endauth
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>

  <!-- Scroll indicator -->
  <div class="mouse">
    <a href="#" class="mouse-icon">
      <div class="mouse-wheel"><span class="ion-ios-arrow-round-down"></span></div>
    </a>
  </div>
</div>

<script>
// Animated counter for hero stats
document.addEventListener('DOMContentLoaded', function() {
  const counters = document.querySelectorAll('.er-hero__stat-number[data-count]');
  const observerOptions = { threshold: 0.5 };

  const observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseInt(el.getAttribute('data-count'));
        let current = 0;
        const duration = 2000;
        const increment = target / (duration / 16);
        const timer = setInterval(function() {
          current += increment;
          if (current >= target) {
            el.textContent = target.toLocaleString() + '+';
            clearInterval(timer);
          } else {
            el.textContent = Math.floor(current).toLocaleString();
          }
        }, 16);
        observer.unobserve(el);
      }
    });
  }, observerOptions);

  counters.forEach(function(counter) { observer.observe(counter); });
});
</script>

<section class="ftco-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12 heading-section text-center ftco-animate mb-5">
        <span class="subheading">Our Services</span>
        <h2 class="mb-2">The smartest way to manage your property(s)</h2>
      </div>
    </div>
    <div class="row d-flex">
      <div class="col-md-3 d-flex align-self-stretch ftco-animate">
        <div class="media block-6 services d-block text-center">
          <div class="icon d-flex justify-content-center align-items-center"><span class="flaticon-piggy-bank"></span>
          </div>
          <div class="media-body py-md-4">
            <h3>Rent Collection Made Easy</h3>
            <p>Automated rent payment reminder via email, whatsapp and robocalls</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 d-flex align-self-stretch ftco-animate">
        <div class="media block-6 services d-block text-center">
          <div class="icon d-flex justify-content-center align-items-center"><span class="flaticon-wallet"></span></div>
          <div class="media-body py-md-4">
            <h3> e-Rent Invoice, e-Payment and e-Receipt</h3>
            <p>Rent invoice generated automatically with integrated payment gateway and instant e-receipt.</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 d-flex align-self-stretch ftco-animate">
        <div class="media block-6 services d-block text-center">
          <div class="icon d-flex justify-content-center align-items-center"><span class="flaticon-file"></span></div>
          <div class="media-body py-md-4">
            <h3>All Tenants in one Dashboard</h3>
            <p>View all your tenants and rent status in one dashboard.</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 d-flex align-self-stretch ftco-animate">
        <div class="media block-6 services d-block text-center">
          <div class="icon d-flex justify-content-center align-items-center"><span class="flaticon-locked"></span></div>
          <div class="media-body py-md-4">
            <h3>Property Managers and Tenants also Benefits</h3>
            <p>Property management now made easy and tenants can also refer and earn.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="ftco-section ftco-degree-bg services-section img mx-md-5"
  style="background-image: url(assets/images/bg_2.jpg);">
  <div class="overlay"></div>
  <div class="container">
    <div class="row justify-content-start mb-5">
      <div class="col-md-6 text-center heading-section heading-section-white ftco-animate">
        <span class="subheading">Work flow</span>
        <h2 class="mb-3">How it works</h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="row">
          <div class="col-md-12 col-lg-6 d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services services-2">
              <div class="media-body py-md-4 text-center">
                <div class="icon mb-3 d-flex align-items-center justify-content-center"><span>01</span></div>
                <h3>Create Account</h3>
                <p>Create a free landlord’s or property manager’s account.</p>
              </div>
            </div>
          </div>
          <div class="col-md-12 col-lg-6 d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services services-2">
              <div class="media-body py-md-4 text-center">
                <div class="icon mb-3 d-flex align-items-center justify-content-center"><span>02</span></div>
                <h3>Profile Your Property and Fix Rents</h3>
                <p>Create your property profile, choose apartment types and fix the rent for each.</p>
              </div>
            </div>
          </div>
          <div class="col-md-12 col-lg-6 d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services services-2">
              <div class="media-body py-md-4 text-center">
                <div class="icon mb-3 d-flex align-items-center justify-content-center"><span>03</span></div>
                <h3>Request for Property Validation</h3>
                <p>Invite us for property validation after which you are good to go easy with rent.</p>
              </div>
            </div>
          </div>
          <div class="col-md-12 col-lg-6 d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services services-2">
              <div class="media-body py-md-4 text-center">
                <div class="icon mb-3 d-flex align-items-center justify-content-center"><span>04</span></div>
                <h3>Invite your Tenants</h3>
                <p>Share your link with your tenants to click and register, assume their apartments and start enjoying
                  easyrent service.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="ftco-section ftco-no-pb" style="background-color: antiquewhite">
  <div class="container">
    <div class="row no-gutters">
      <div class="col-md-6 p-md-5 img img-2 d-flex justify-content-center align-items-center"
        style="background-image: url(assets/images/side-hustle-1.jpg);">
      </div>
      <div class="col-md-6 py-md-5 ftco-animate">
        <div class="heading-section p-md-5">
          <h2 class="mb-4">Relax, While Our Automated System Calls to Follow Up Your Tenants for Due Rents.</h2>
          <p>
            Let EasyRent send SMS, Whatsapp, emails and notifications to remind your tenants of their due rent before it
            is time. Signup and enable automated reminder phone calls to owing tenants with feedback reports in audio
            and text formats.
          </p>
                    <div class="er-hero__cta">
            @auth
              <a href="{{ url('/dashboard') }}" class="er-hero__cta-btn er-hero__cta-btn--primary">
                <i class="bi bi-grid-1x2-fill"></i> Go to Dashboard
              </a>
            @else
              <a href="{{ route('register') }}" class="er-hero__cta-btn er-hero__cta-btn--primary">
                <i class="bi bi-rocket-takeoff-fill"></i> Get Started Free
              </a>
              <a href="{{ route('login') }}" class="er-hero__cta-btn er-hero__cta-btn--info">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
              </a>
            @endauth
          </div> 
        </div>
      </div>
    </div>
  </div>
</section>

<section class="ftco-counter img" id="section-counter">
  <div class="container">
    <div class="row">
      <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
        <div class="block-18 py-4 mb-4">
          <div class="text text-border d-flex align-items-center">
            <strong class="number" data-number="305">0</strong>
            <span>Total <br>Properties</span>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
        <div class="block-18 py-4 mb-4">
          <div class="text text-border d-flex align-items-center">
            <strong class="number" data-number="1179">0</strong>
            <span>Total <br>Apartment</span>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
        <div class="block-18 py-4 mb-4">
          <div class="text text-border d-flex align-items-center">
            <strong class="number" data-number="3460">0</strong>
            <span>Total <br>Rent Payments</span>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
        <div class="block-18 py-4 mb-4">
          <div class="text d-flex align-items-center">
            <strong class="number" data-number="6598">0</strong>
            <span>Total <br>Commissions</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<section class="ftco-section bg-gradient-info ">
  <div class="container">
    <div class="row no-gutters">
      <div class="col-md-6 py-md-5 ftco-animate">
        <div class="heading-section p-md-5">
          <h2 class="mb-4">Introduce Transparency and Eliminate Conflicts with Rent Collection.</h2>
          <p>

            As a landlord, property heir or manager, you can do away with
            conflicts associated with rent collection by
            introducing transparency to all stake holders with EasyRent so that
            joint heirs of a property can view rent payments from a
            common dashboard.
          </p>
                    <div class="er-hero__cta">
            @auth
              <a href="{{ url('/dashboard') }}" class="er-hero__cta-btn er-hero__cta-btn--primary">
                <i class="bi bi-grid-1x2-fill"></i> Go to Dashboard
              </a>
            @else
              <a href="{{ route('register') }}" class="er-hero__cta-btn er-hero__cta-btn--primary">
                <i class="bi bi-rocket-takeoff-fill"></i> Get Started Free
              </a>
              <a href="{{ route('login') }}" class="er-hero__cta-btn er-hero__cta-btn--outline">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
              </a>
            @endauth
          </div> 
        </div>
      </div>
      <div class="col-md-6 p-md-5 img img-2 d-flex justify-content-center align-items-center"
        style="background-image: url(assets/images/transparency.jpeg);">
      </div>
    </div>
  </div>
</section>
<section class="ftco-section testimony-section">
  <div class="container">
    <div class="row justify-content-center mb-5">
      <div class="col-md-7 text-center heading-section ftco-animate">
        <span class="subheading">Testimonial</span>
        <h2 class="mb-3">Happy Clients</h2>
      </div>
    </div>
    <div class="row ftco-animate">
      <div class="col-md-12">
        <div class="carousel-testimony owl-carousel ftco-owl">
          <div class="item">
            <div class="testimony-wrap py-4">
              <div class="text">
                <p class="mb-4">No more fuss, I now have all my properties in my hand. easyrent made it easy</p>
                <div class="d-flex align-items-center">
                  <div class="user-img" style="background-image: url(assets/images/person_1.jpg)"></div>
                  <div class="pl-3">
                    <p class="name">Roger Samuel</p>
                    <span class="position">Landlord</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="testimony-wrap py-4">
              <div class="text">
                <p class="mb-4">Real estate sound tough and strict but easyrent africa makes it simple, I can now easily
                  manage my tenants, rents paid without issues just by choosing a verified Property Manager. I put all
                  my hopes on easyrent</p>
                <div class="d-flex align-items-center">
                  <div class="user-img" style="background-image: url(assets/images/person_2.jpg)"></div>
                  <div class="pl-3">
                    <p class="name">Chima Andrew</p>
                    <span class="position">Landlord</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="testimony-wrap py-4">
              <div class="text">
                <p class="mb-4">I dont worry about my rent because easy rent pays it for me. I just introduce landlords
                  and i get my commission for that.</p>
                <div class="d-flex align-items-center">
                  <div class="user-img" style="background-image: url(assets/images/person_3.jpeg)"></div>
                  <div class="pl-3">
                    <p class="name">Zaki Abdul</p>
                    <span class="position">Tenant</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="testimony-wrap py-4">
              <div class="text">
                <p class="mb-4">I go anywhere and feel relax because easyrent Africa got my back.</p>
                <div class="d-flex align-items-center">
                  <div class="user-img" style="background-image: url(assets/images/person_4.jpg)"></div>
                  <div class="pl-3">
                    <p class="name">Charles Uzo</p>
                    <span class="position">Landlord</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="testimony-wrap py-4">
              <div class="text">
                <p class="mb-4">Managing Properties seems hard when You have alot to do. easyrent africa gives me update
                  on each property/apartment when I need it.</p>
                <div class="d-flex align-items-center">
                  <div class="user-img" style="background-image: url(assets/images/person_5.jpg)"></div>
                  <div class="pl-3">
                    <p class="name">Olabisi Jackson</p>
                    <span class="position">Property Manager</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="ftco-section ftco-no-pt">
  <div class="container" id="blog">
    <div class="row justify-content-center mb-5">
      <div class="col-md-7 heading-section text-center ftco-animate">
        <span class="subheading">Blog</span>
        <h2>Recent Blog</h2>
      </div>
    </div>
    <div class="row d-flex">
      @php
        $recentPosts = \App\Models\Blog::published()->recent(4)->get();
      @endphp

      @if($recentPosts->count() > 0)
        @foreach($recentPosts as $post)
          <div class="col-md-3 d-flex ftco-animate">
            <div class="blog-entry justify-content-end">
              <div class="text">
                <h3 class="heading">
                  <a href="/readmore/{{ $post->topic_url }}">{{ $post->topic }}</a>
                </h3>{{ (strlen($post->topic) >= 24 ? '<br>' : '')}}
                <div class="meta mb-3">
                  <div><a href="#">{{ $post->date->format('M. d, Y') }}</a></div>
                  <div><a href="#">{{ $post->author }}</a></div>
                  <div><a href="#" class="meta-chat"><span class="icon-chat"></span> 0</a></div>
                </div>
                <a href="/readmore/{{ $post->topic_url }}" class="block-20 img"
                  style="background-image: url('{{ $post->cover_photo ?? 'assets/images/image_1.jpg' }}');">
                </a>
                <p>{{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->content), 100) }}</p>
              </div>
            </div>
          </div>
        @endforeach
      @else
        <!-- Fallback to static content if no blog posts exist -->
        <div class="col-md-3 d-flex ftco-animate">
          <div class="blog-entry justify-content-end">
            <div class="text">
              <h3 class="heading"><a href="#">Property Management Made Easy</a></h3>
              <div class="meta mb-3">
                <div><a href="#">Nov. 11, 2025</a></div>
                <div><a href="#">Admin</a></div>
                <div><a href="#" class="meta-chat"><span class="icon-chat"></span> 0</a></div>
              </div>
              <a href="#" class="block-20 img" style="background-image: url('assets/images/image_1.jpg');">
              </a>
              <p>Discover how EasyRent makes property management simple and efficient for landlords and property managers.
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-3 d-flex ftco-animate">
          <div class="blog-entry justify-content-end">
            <div class="text">
              <h3 class="heading"><a href="#">Automated Rent Collection</a></h3><br>
              <div class="meta mb-3">
                <div><a href="#">Nov. 10, 2025</a></div>
                <div><a href="#">Admin</a></div>
                <div><a href="#" class="meta-chat"><span class="icon-chat"></span> 0</a></div>
              </div>
              <a href="#" class="block-20 img" style="background-image: url('assets/images/image_2.jpg');">
              </a>
              <p>Learn how our automated rent collection system saves time and reduces payment delays.</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 d-flex ftco-animate">
          <div class="blog-entry justify-content-end">
            <div class="text">
              <h3 class="heading"><a href="#">Tenant Management Tips</a></h3><br>
              <div class="meta mb-3">
                <div><a href="#">Nov. 09, 2025</a></div>
                <div><a href="#">Admin</a></div>
                <div><a href="#" class="meta-chat"><span class="icon-chat"></span> 0</a></div>
              </div>
              <a href="#" class="block-20 img" style="background-image: url('assets/images/image_3.jpg');">
              </a>
              <p>Best practices for maintaining good relationships with your tenants while protecting your investment.</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 d-flex ftco-animate">
          <div class="blog-entry justify-content-end">
            <div class="text">
              <h3 class="heading"><a href="#">Earning Through Referrals</a></h3><br>
              <div class="meta mb-3">
                <div><a href="#">Nov. 08, 2025</a></div>
                <div><a href="#">Admin</a></div>
                <div><a href="#" class="meta-chat"><span class="icon-chat"></span> 0</a></div>
              </div>
              <a href="#" class="block-20 img" style="background-image: url('assets/images/image_4.jpg');">
              </a>
              <p>Start your side hustle by introducing landlords to EasyRent and earn commissions for 5 years.</p>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>
</section>
<!-- Footer area start -->
@include('footer')

<script>
  function loadStates(country) {
    const stateSelect = document.getElementById('state');
    const citySelect = document.getElementById('lga');

    // Clear current options
    stateSelect.innerHTML = '<option value="">Loading...</option>';
    citySelect.innerHTML = '<option value="">Any City</option>';

    if (!country) {
      stateSelect.innerHTML = '<option value="">Any State</option>';
      return Promise.resolve();
    }

    return fetch(`/api/location-data?country=${encodeURIComponent(country)}`)
      .then(response => response.json())
      .then(data => {
        stateSelect.innerHTML = '<option value="">Any State</option>';
        if (data.states && data.states.length > 0) {
          data.states.forEach(state => {
            const option = document.createElement('option');
            option.value = state.name;
            option.textContent = state.name;
            // Store LGAs in a data attribute for easy access
            option.setAttribute('data-lgas', JSON.stringify(state.lgas));
            stateSelect.appendChild(option);
          });
        }

        // Update currency symbol if applicable
        if (data.currency_symbol) {
          document.querySelectorAll('label[for="max_price"]').forEach(label => {
            label.textContent = `Max Price (${data.currency_symbol})`;
          });
        }
      })
      .catch(error => {
        console.error('Error loading states:', error);
        stateSelect.innerHTML = '<option value="">Any State</option>';
      });
  }

  function loadCities(stateName) {
    const stateSelect = document.getElementById('state');
    const citySelect = document.getElementById('lga');

    citySelect.innerHTML = '<option value="">Any City</option>';

    if (!stateName) return Promise.resolve();

    const selectedOption = stateSelect.options[stateSelect.selectedIndex];
    const lgas = JSON.parse(selectedOption.getAttribute('data-lgas') || '[]');

    if (lgas.length > 0) {
      lgas.forEach(lga => {
        const option = document.createElement('option');
        option.value = lga.name;
        option.textContent = lga.name;
        citySelect.appendChild(option);
      });
    } else {
      // If no LGAs/Cities found in data attribute, maybe fetch them or show "Any City"
      citySelect.innerHTML = '<option value="">Any City</option>';
    }

    return Promise.resolve();
  }

  document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById('toggle-advanced-search');
    const advancedFields = document.getElementById('advanced-location-fields');

    // Toggle advanced search visibility
    if (toggleBtn && advancedFields) {
      toggleBtn.addEventListener('click', function () {
        if (advancedFields.style.display === 'none') {
          advancedFields.style.display = 'flex';
          toggleBtn.innerHTML = '<i class="bi bi-chevron-up"></i> Simple Search';
        } else {
          advancedFields.style.display = 'none';
          toggleBtn.innerHTML = '<i class="bi bi-sliders"></i> Advanced Search';
        }
      });
    }

    // Role Tabs Logic
    const tabBtns = document.querySelectorAll('.er-role-tab-btn');
    const tabContents = document.querySelectorAll('.er-role-tab-content');

    if (tabBtns.length > 0) {
      tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          tabBtns.forEach(b => b.classList.remove('active'));
          tabContents.forEach(c => c.classList.remove('active'));

          btn.classList.add('active');

          const targetId = btn.getAttribute('data-target');
          const targetContent = document.getElementById(targetId);
          if (targetContent) {
            targetContent.classList.add('active');
          }
        });
      });
    }

    // Auto-fill logic
    const countrySelect = document.getElementById('country');
    const stateSelect = document.getElementById('state');
    const lgaSelect = document.getElementById('lga');

    // Helper to safely select an option if it exists (case-insensitive partial match)
    function selectOption(selectElement, targetValue) {
      if (!targetValue || !selectElement) return false;
      const target = targetValue.toLowerCase();
      for (let i = 0; i < selectElement.options.length; i++) {
        const optVal = selectElement.options[i].value.toLowerCase();
        if (optVal.includes(target) || target.includes(optVal)) {
          selectElement.selectedIndex = i;
          return true;
        }
      }
      return false;
    }

    @auth
              // User is logged in - pull from profile
              const userCountry = @json(auth()->user()->country_name ?? '');
      const userState = @json(auth()->user()->state ?? '');
      const userCity = @json(auth()->user()->lga ?? auth()->user()->city ?? '');

      if (userCountry && selectOption(countrySelect, userCountry)) {
        loadStates(countrySelect.value).then(() => {
          if (userState && selectOption(stateSelect, userState)) {
            loadCities(stateSelect.value).then(() => {
              if (userCity) selectOption(lgaSelect, userCity);
            });
          }
        });
      }
    @else
      // Guest user - attempt IP geolocation
      fetch('https://ipapi.co/json/')
        .then(response => response.json())
        .then(data => {
          if (data.country_name && selectOption(countrySelect, data.country_name)) {
            loadStates(countrySelect.value).then(() => {
              if (data.region && selectOption(stateSelect, data.region)) {
                loadCities(stateSelect.value).then(() => {
                  if (data.city) selectOption(lgaSelect, data.city);
                });
              }
            });
          }
        })
        .catch(err => console.error('GeoIP check failed:', err));
    @endauth
  });

</script>