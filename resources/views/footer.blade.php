@php $isDashboard = request()->segment(1) === 'dashboard'; @endphp
@if($isDashboard)
<footer class="footer footer-black  footer-white ">
    <div class="container-fluid">
      <div class="row">
        <nav class="footer-nav">
          <ul>
            <li><a href="#" target="_blank">Affiliate Tim</a></li>
            <li><a href="#" target="_blank">Blog</a></li>
            <li><a href="#" target="_blank">Licenses</a></li>
          </ul>
        </nav>
        <div class="credits ml-auto">
          <span class="copyright">
            © <script>document.write(new Date().getFullYear())</script>, made with <i class="fa fa-heart heart"></i> by Walls and Gates
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
<script src="/assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script><!-- Paper Dashboard DEMO methods, don't include it in your project! -->
<script src="/assets/demo/demo.js"></script>
<script>
  $(document).ready(function() {
    // Javascript method's body can be found in assets/assets-for-demo/js/demo.js
    if(typeof demo !== 'undefined') demo.initChartsPages();
  });
</script>
@else
<!-- ======= Footer ======= -->
<footer id="footer" style="background: linear-gradient(to right, #1a1a1a, #2d2d2d); color: #f8f9fa; padding-top: 60px; font-family: 'Poppins', sans-serif;">
  <div class="footer-top">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
          <h4 style="color: #ff5e15; font-weight: 600; margin-bottom: 25px; font-size: 1.5rem; position: relative; padding-bottom: 10px;">
            EasyRent
            <span style="display: block; width: 50px; height: 3px; background: #ff5e15; margin-top: 10px;"></span>
          </h4>
          <p style="color: #adb5bd; line-height: 1.8; margin-bottom: 20px;">
            Find your perfect home with EasyRent. We make property rental simple, secure, and stress-free.
          </p>
          <div class="social-links" style="margin-top: 20px;">
            <a href="#" style="display: inline-block; width: 40px; height: 40px; background: rgba(255,94,21,0.1); border-radius: 50%; text-align: center; line-height: 40px; margin-right: 10px; transition: all 0.3s ease;">
              <i class="bx bxl-facebook" style="color: #ff5e15; font-size: 18px;"></i>
            </a>
            <a href="#" style="display: inline-block; width: 40px; height: 40px; background: rgba(255,94,21,0.1); border-radius: 50%; text-align: center; line-height: 40px; margin-right: 10px; transition: all 0.3s ease;">
              <i class="bx bxl-twitter" style="color: #ff5e15; font-size: 18px;"></i>
            </a>
            <a href="#" style="display: inline-block; width: 40px; height: 40px; background: rgba(255,94,21,0.1); border-radius: 50%; text-align: center; line-height: 40px; margin-right: 10px; transition: all 0.3s ease;">
              <i class="bx bxl-instagram" style="color: #ff5e15; font-size: 18px;"></i>
            </a>
            <a href="#" style="display: inline-block; width: 40px; height: 40px; background: rgba(255,94,21,0.1); border-radius: 50%; text-align: center; line-height: 40px; transition: all 0.3s ease;">
              <i class="bx bxl-linkedin" style="color: #ff5e15; font-size: 18px;"></i>
            </a>
          </div>
        </div>
        
        <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
          <h4 style="color: #ff5e15; font-weight: 600; margin-bottom: 25px; font-size: 1.2rem; position: relative; padding-bottom: 10px;">
            Quick Links
            <span style="display: block; width: 50px; height: 3px; background: #ff5e15; margin-top: 10px;"></span>
          </h4>
          <ul style="list-style: none; padding-left: 0;">
            <li style="margin-bottom: 12px;">
              <i class="bx bx-chevron-right" style="color: #ff5e15; font-size: 18px;"></i> 
              <a href="#hero" style="color: #adb5bd; text-decoration: none; transition: all 0.3s ease;">Home</a>
            </li>
            <li style="margin-bottom: 12px;">
              <i class="bx bx-chevron-right" style="color: #ff5e15; font-size: 18px;"></i> 
              <a href="#menu" style="color: #adb5bd; text-decoration: none; transition: all 0.3s ease;">About</a>
            </li>
            <li style="margin-bottom: 12px;">
              <i class="bx bx-chevron-right" style="color: #ff5e15; font-size: 18px;"></i> 
              <a href="#events" style="color: #adb5bd; text-decoration: none; transition: all 0.3s ease;">Services</a>
            </li>
            <li style="margin-bottom: 12px;">
              <i class="bx bx-chevron-right" style="color: #ff5e15; font-size: 18px;"></i> 
              <a href="#specials" style="color: #adb5bd; text-decoration: none; transition: all 0.3s ease;">Contact</a>
            </li>
          </ul>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
          <h4 style="color: #ff5e15; font-weight: 600; margin-bottom: 25px; font-size: 1.2rem; position: relative; padding-bottom: 10px;">
            Our Services
            <span style="display: block; width: 50px; height: 3px; background: #ff5e15; margin-top: 10px;"></span>
          </h4>
          <ul style="list-style: none; padding-left: 0;">
            <li style="margin-bottom: 12px;">
              <i class="bx bx-check-circle" style="color: #ff5e15; font-size: 18px;"></i> 
              <span style="color: #adb5bd;">Property Listings</span>
            </li>
            <li style="margin-bottom: 12px;">
              <i class="bx bx-check-circle" style="color: #ff5e15; font-size: 18px;"></i> 
              <span style="color: #adb5bd;">Tenant Screening</span>
            </li>
            <li style="margin-bottom: 12px;">
              <i class="bx bx-check-circle" style="color: #ff5e15; font-size: 18px;"></i> 
              <span style="color: #adb5bd;">Online Payments</span>
            </li>
            <li style="margin-bottom: 12px;">
              <i class="bx bx-check-circle" style="color: #ff5e15; font-size: 18px;"></i> 
              <span style="color: #adb5bd;">Property Management</span>
            </li>
          </ul>
        </div>
        
        <div class="col-lg-3 col-md-6">
          <h4 style="color: #ff5e15; font-weight: 600; margin-bottom: 25px; font-size: 1.2rem; position: relative; padding-bottom: 10px;">
            Contact Us
            <span style="display: block; width: 50px; height: 3px; background: #ff5e15; margin-top: 10px;"></span>
          </h4>
          <div style="margin-bottom: 15px;">
            <i class="bx bx-map" style="color: #ff5e15; font-size: 20px; margin-right: 10px; float: left;"></i>
            <p style="color: #adb5bd; margin-bottom: 0; margin-left: 30px;">123 Rental Street, Lagos, Nigeria</p>
          </div>
          <div style="margin-bottom: 15px;">
            <i class="bx bx-phone" style="color: #ff5e15; font-size: 20px; margin-right: 10px; float: left;"></i>
            <p style="color: #adb5bd; margin-bottom: 0; margin-left: 30px;">+234 123 456 7890</p>
          </div>
          <div style="margin-bottom: 15px;">
            <i class="bx bx-envelope" style="color: #ff5e15; font-size: 20px; margin-right: 10px; float: left;"></i>
            <p style="color: #adb5bd; margin-bottom: 0; margin-left: 30px;">info@easyrent.com</p>
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
            &copy; <script>document.write(new Date().getFullYear())</script> <strong><span style="color: #ff5e15;">EasyRent</span></strong>. All Rights Reserved
          </div>
        </div>
        <div class="col-md-6 text-md-right text-center">
          <div class="credits" style="color: #adb5bd;"> 
            Designed with <i class="bx bx-heart" style="color: #ff5e15;"></i> by <a href="https://wandggroup.com/" style="color: #ff5e15; text-decoration: none;">Walls and Gates</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer><!-- End Footer -->
<!-- loader -->
<div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>
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

  <!-- SweetAlert2 (global) -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
