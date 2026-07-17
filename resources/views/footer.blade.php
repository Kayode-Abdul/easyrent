@php $currentSegment = request()->segment(1);
 $isDashboard = in_array($currentSegment, ['', 'contact', 'benefits', 'faq', 'login', 'password', 'register', 'apartment', 'payment','benefactor', 'onboarding']);
@endphp
@if(!$isDashboard)
  <footer class="footer footer-black  footer-white ">
    <div class="container-fluid">
      <div class="row">
        <nav class="footer-nav">
          <ul>
            <li><a href="#" target="_blank">Affiliate Marketer</a></li>
            <li><a href="#" target="_blank">Blog</a></li>
            <li><a href="#" target="_blank">Property Manager</a></li>
          </ul>
        </nav>
        <div class="credits ml-auto">
          <span class="copyright">
            ©
            <script>document.write(new Date().getFullYear())</script>, made with <i
              class="fa fa-heart heart text-danger"></i> by Walls and Gates
          </span>
        </div>
      </div>
    </div>
  </footer>
  </div>
  </div>
  <!--   Core JS Files   -->
  <!-- <script src="/assets/js/core/jquery.min.js"></script> -->
  <script src="/assets/js/core/popper.min.js"></script>
  <script src="/assets/js/core/bootstrap.min.js"></script>
  <script src="/assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
  <!--  Google Maps Plugin    -->
  <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>
  <!-- Chart JS -->
  <script src="/assets/js/plugins/chartjs.min.js"></script>
  <!--  Notifications Plugin    -->
  <script src="/assets/js/plugins/bootstrap-notify.js"></script>
  <!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="/assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script>
  <!-- Paper Dashboard DEMO methods, don't include it in your project! -->
  <script src="/assets/demo/demo.js"></script>
  <script>
    $(document).ready(function () {
      // Javascript method's body can be found in assets/assets-for-demo/js/demo.js
      if (typeof demo !== 'undefined') demo.initChartsPages();
    });
  </script>
@else
  <!-- ======= Footer ======= -->
  <footer id="footer"
    style="background: linear-gradient(to right, #1a1a1a, #2d2d2d); color: #f8f9fa; padding-top: 60px; font-family: 'Poppins', sans-serif;">
    <div class="footer-top">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
            <h4
              style="color: #1f849e; font-weight: 600; margin-bottom: 25px; font-size: 1.5rem; position: relative; padding-bottom: 10px;">
              EasyRent
              <span style="display: block; width: 50px; height: 3px; background: #1f849e; margin-top: 10px;"></span>
            </h4>
            <p style="color: #adb5bd; line-height: 1.8; margin-bottom: 20px;">
              Manage your properties and tenants easily. We make property administration, rent collection and tenant
              management simple, secure, and stress-free.
            </p>
            <div class="social-links" style="margin-top: 20px;">
              <a href="#"
                style="display: inline-block; width: 40px; height: 40px; background: rgba(255,94,21,0.1); border-radius: 50%; text-align: center; line-height: 40px; margin-right: 10px; transition: all 0.3s ease;">
                <i class="bi bi-facebook" style="color: #1f849e; font-size: 18px;"></i>
              </a>
              <a href="#"
                style="display: inline-block; width: 40px; height: 40px; background: rgba(255,94,21,0.1); border-radius: 50%; text-align: center; line-height: 40px; margin-right: 10px; transition: all 0.3s ease;">
                <i class="bi bi-twitter-x" style="color: #1f849e; font-size: 18px;"></i>
              </a>
              <a href="#"
                style="display: inline-block; width: 40px; height: 40px; background: rgba(255,94,21,0.1); border-radius: 50%; text-align: center; line-height: 40px; margin-right: 10px; transition: all 0.3s ease;">
                <i class="bi bi-instagram" style="color: #1f849e; font-size: 18px;"></i>
              </a>
              <a href="#"
                style="display: inline-block; width: 40px; height: 40px; background: rgba(255,94,21,0.1); border-radius: 50%; text-align: center; line-height: 40px; transition: all 0.3s ease;">
                <i class="bi bi-linkedin" style="color: #1f849e; font-size: 18px;"></i>
              </a>
            </div>
          </div>

          <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
            <h4
              style="color: #1f849e; font-weight: 600; margin-bottom: 25px; font-size: 1.2rem; position: relative; padding-bottom: 10px;">
              Quick Links
              <span style="display: block; width: 50px; height: 3px; background: #1f849e; margin-top: 10px;"></span>
            </h4>
            <ul style="list-style: none; padding-left: 0;">
              <li style="margin-bottom: 12px;">
                <i class="bx bx-chevron-right" style="color: #1f849e; font-size: 18px;"></i>
                <a href="/" style="color: #adb5bd; text-decoration: none; transition: all 0.3s ease;">Home</a>
              </li>
              <li style="margin-bottom: 12px;">
                <i class="bx bx-chevron-right" style="color: #1f849e; font-size: 18px;"></i>
                <a href="/benefits" style="color: #adb5bd; text-decoration: none; transition: all 0.3s ease;">Benefit</a>
              </li>
              <li style="margin-bottom: 12px;">
                <i class="bx bx-chevron-right" style="color: #1f849e; font-size: 18px;"></i>
                <a href="/faq" style="color: #adb5bd; text-decoration: none; transition: all 0.3s ease;">FAQ</a>
              </li>
              <li style="margin-bottom: 12px;">
                <i class="bx bx-chevron-right" style="color: #1f849e; font-size: 18px;"></i>
                <a href="#specials" style="color: #adb5bd; text-decoration: none; transition: all 0.3s ease;">Contact</a>
              </li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
            <h4
              style="color: #1f849e; font-weight: 600; margin-bottom: 25px; font-size: 1.2rem; position: relative; padding-bottom: 10px;">
              Our Services
              <span style="display: block; width: 50px; height: 3px; background: #1f849e; margin-top: 10px;"></span>
            </h4>
            <ul style="list-style: none; padding-left: 0;">
              <li style="margin-bottom: 12px;">
                <i class="bx bx-check-circle" style="color: #1f849e; font-size: 18px;"></i>
                <span style="color: #adb5bd;">Property Listings</span>
              </li>
              <li style="margin-bottom: 12px;">
                <i class="bx bx-check-circle" style="color: #1f849e; font-size: 18px;"></i>
                <span style="color: #adb5bd;">Tenant Screening</span>
              </li>
              <li style="margin-bottom: 12px;">
                <i class="bx bx-check-circle" style="color: #1f849e; font-size: 18px;"></i>
                <span style="color: #adb5bd;">Online Payments</span>
              </li>
              <li style="margin-bottom: 12px;">
                <i class="bx bx-check-circle" style="color: #1f849e; font-size: 18px;"></i>
                <span style="color: #adb5bd;">Property Management</span>
              </li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6">
            <h4
              style="color: #1f849e; font-weight: 600; margin-bottom: 25px; font-size: 1.2rem; position: relative; padding-bottom: 10px;">
              Contact Us
              <span style="display: block; width: 50px; height: 3px; background: #1f849e; margin-top: 10px;"></span>
            </h4>
            <div style="margin-bottom: 15px;">
              <i class="bx bx-map" style="color: #1f849e; font-size: 20px; margin-right: 10px; float: left;"></i>
              <p style="color: #adb5bd; margin-bottom: 0; margin-left: 30px;">33 Adegoke Street, Surulere Lagos, Nigeria
              </p>
            </div>
            <div style="margin-bottom: 15px;">
              <i class="bx bx-phone" style="color: #1f849e; font-size: 20px; margin-right: 10px; float: left;"></i>
              <p style="color: #adb5bd; margin-bottom: 0; margin-left: 30px;">+234 9092469137</p>
            </div>
            <div style="margin-bottom: 15px;">
              <i class="bx bx-envelope" style="color: #1f849e; font-size: 20px; margin-right: 10px; float: left;"></i>
              <p style="color: #adb5bd; margin-bottom: 0; margin-left: 30px;">info@easyrent.africa</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div style="background: #1a1a1a; padding: 20px 0; margin-top: 40px; text-align: center;">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 text-md-left text-center mb-3 mb-md-0">
            <div class="copyright" style="color: #adb5bd;">
              &copy;
              <script>document.write(new Date().getFullYear())</script> <strong><span
                  style="color: #1f849e;">EasyRent</span></strong>. All Rights Reserved
            </div>
          </div>
          <div class="col-md-6 text-md-right text-center">
            <div class="credits" style="color: #adb5bd;">
              Designed by <a href="https://wandggroup.com/" style="color: #1f849e; text-decoration: none;">Walls and
                Gates</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer><!-- End Footer -->
  <!-- loader -->
  <style>
    /* Premium Loader Styles */
    #ftco-loader {
      position: fixed;
      inset: 0;
      width: 100vw;
      height: 100vh;
      background: linear-gradient(135deg, #0a282a 0%, #0d3d3f 50%, #0a282a 100%);
      z-index: 99999;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      gap: 24px;
      transition: opacity 0.6s ease-out, visibility 0.6s;
    }

    #ftco-loader:not(.show) {
      opacity: 0;
      visibility: hidden;
    }

    .er-loader {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 20px;
    }

    /* Building bars animation */
    .er-loader__bars {
      display: flex;
      align-items: flex-end;
      gap: 5px;
      height: 48px;
    }

    .er-loader__bar {
      width: 8px;
      background: linear-gradient(to top, #4ecdc4, #3e8189);
      border-radius: 4px 4px 0 0;
      animation: er-barGrow 1.2s ease-in-out infinite;
    }

    .er-loader__bar:nth-child(1) { height: 20px; animation-delay: 0s; }
    .er-loader__bar:nth-child(2) { height: 32px; animation-delay: 0.15s; }
    .er-loader__bar:nth-child(3) { height: 48px; animation-delay: 0.3s; }
    .er-loader__bar:nth-child(4) { height: 36px; animation-delay: 0.45s; }
    .er-loader__bar:nth-child(5) { height: 24px; animation-delay: 0.6s; }

    @keyframes er-barGrow {
      0%, 100% { opacity: 0.4; transform: scaleY(0.6); }
      50% { opacity: 1; transform: scaleY(1); }
    }

    .er-loader__text {
      font-family: 'Inter', 'Nunito Sans', sans-serif;
      font-size: 14px;
      font-weight: 600;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.6);
    }

    .er-loader__progress {
      width: 120px;
      height: 3px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 3px;
      overflow: hidden;
    }

    .er-loader__progress-bar {
      height: 100%;
      width: 30%;
      background: linear-gradient(90deg, #4ecdc4, #f7b71d);
      border-radius: 3px;
      animation: er-progress 1.5s ease-in-out infinite;
    }

    @keyframes er-progress {
      0% { transform: translateX(-100%); width: 30%; }
      50% { width: 60%; }
      100% { transform: translateX(400%); width: 30%; }
    }
  </style>
  <div id="ftco-loader" class="show fullscreen">
    <div class="er-loader">
      <div class="er-loader__bars">
        <div class="er-loader__bar"></div>
        <div class="er-loader__bar"></div>
        <div class="er-loader__bar"></div>
        <div class="er-loader__bar"></div>
        <div class="er-loader__bar"></div>
      </div>
      <span class="er-loader__text">EasyRent</span>
      <div class="er-loader__progress">
        <div class="er-loader__progress-bar"></div>
      </div>
    </div>
  </div>
  <script src="/assets/js/jquery-migrate-3.0.1.min.js"></script>
  <script src="/assets/js/popper.min.js"></script>
  <script src="/assets/js/bootstrap.min.js"></script>
  <script src="/assets/js/jquery.easing.1.3.js"></script>
  <script src="/assets/js/jquery.waypoints.min.js"></script>
  <script src="/assets/js/jquery.stellar.min.js"></script>
  <script src="/assets/js/owl.carousel.min.js"></script>
  <script src="/assets/js/jquery.magnific-popup.min.js"></script>
  <script src="/assets/js/aos.js"></script>
  <script src="/assets/js/jquery.animateNumber.min.js"></script>
  <script src="/assets/js/bootstrap-datepicker.js"></script>
  <script src="/assets/js/jquery.timepicker.min.js"></script>
  <script src="/assets/js/scrollax.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
  <script src="/assets/js/google-map.js"></script>
  <script src="/assets/js/main.js"></script>
@endif

@include('components.mobile-floating-footer')

<!-- SweetAlert2 (global) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  });

  @if(session('success'))
    Toast.fire({
      icon: 'success',
      title: '{{ session('success') }}'
    });
  @endif

  @if(session('error'))
    Toast.fire({
      icon: 'error',
      title: '{{ session('error') }}'
    });
  @endif
</script>

<!-- Make Date Fields Clickable Globally -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (e) {
      // Check if clicking on an input-group addon (like a calendar icon container)
      const addon = e.target.closest('.input-group-text, .input-group-prepend, .input-group-append');
      if (addon) {
        const inputGroup = addon.closest('.input-group');
        if (inputGroup) {
          const dateInput = inputGroup.querySelector('input[type="date"]');
          if (dateInput && typeof dateInput.showPicker === 'function') {
            try {
              dateInput.showPicker();
            } catch (err) {
              dateInput.focus();
            }
          }
        }
      } else {
        // Check if clicking directly on a date input (in some browsers, the text area doesn't always trigger the calendar popup)
        const dateInput = e.target.closest('input[type="date"]');
        if (dateInput && typeof dateInput.showPicker === 'function') {
          try {
            dateInput.showPicker();
          } catch (err) {
            dateInput.focus();
          }
        }
      }
    });
  });
</script>
</body>

</html>