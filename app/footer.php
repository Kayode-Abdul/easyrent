
<?php 
    if(request()->segment(1) === 'dashboard'){
    ?>
<footer class="footer footer-black  footer-white ">
        <div class="container-fluid">
          <div class="row">
            <nav class="footer-nav">
              <ul>
                <li><a href="https://www.creative-tim.com" target="_blank">Walls & Gates</a></li>
                <li><a href="https://www.creative-tim.com/blog" target="_blank">Blog</a></li>
                <li><a href="https://www.creative-tim.com/license" target="_blank">Licenses</a></li>
              </ul>
            </nav>
            <div class="credits ml-auto">
              <span class="copyright">
                © <script>
                  document.write(new Date().getFullYear())
                </script>, made with <i class="fa fa-heart heart"></i> by Walls & Gates
              </span>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
  <!--   Core JS Files   -->
  <script src="/assets/js/core/jquery.min.js"></script>
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
      demo.initChartsPages();
    });
  </script>

<?php }else{?>


  <!-- ======= Footer ======= -->
  <footer id="footer">
    <div class="footer-top">
      <div class="container">
        <div class="row">

          <div class="col-md-6 footer-links">
            <h4>Useful Links</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="#hero">Home</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#menu">About</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#events">Services</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#specials">Contact</a></li>
            </ul>
          </div>

          <div class="col-md-6 footer-newsletter">
            <h4>Our Social</h4>
            <div class="social-links mt-3">
              <p>Follow us on social media for updates on our latest specials!</p>
              <a href="#" class="instagram"><i class="bx bxl-instagram"></i></a>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="container">
      <div class="copyright">
        &copy; Copyright <strong><span>EasyRent</span></strong>. All Rights Reserved
      </div>
          <div class="credits"> Designed by <a href="https://bootstrapmade.com/">Walls and Gates</a>
      </div>
    </div>
  </footer><!-- End Footer -->

  <!-- loader -->
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>


  <!-- jQuery core (full build, not slim) -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- jQuery Migrate should load immediately after jQuery -->
  <script src="/assets/js/jquery-migrate-3.0.1.min.js"></script>
  <!-- Date libs (require jQuery) -->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <script src="/assets/js/popper.min.js"></script>
  <script src="/assets/js/bootstrap.min.js"></script>
  <!-- Ensure easing/fx namespaces exist before plugins that extend them -->
  <script>
    (function($){
      if (!($ && $.fn)) return;
      $.easing = $.easing || {};
      if (!$.easing.swing) {
        $.easing.swing = function (x, t, b, c, d) { return c*(t/d)+b; };
      }
      $.fx = $.fx || {};
      $.fx.step = $.fx.step || {};
    })(window.jQuery);
  </script>
  <!-- SweetAlert2 (global) -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <?php }?>
  <!-- SweetAlert2 (global) -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </body>
</html>