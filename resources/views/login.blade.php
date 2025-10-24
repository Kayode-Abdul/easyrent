<!-- Header area start -->
@include('header')
<!-- Header area end -->
 
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<section class="ftco-section contact-section">
      <div class="container">
        <div class="row block-9 justify-content-center mb-5">
          <div class="col-md-8 mb-md-5">
          <div id="message">
              @if (session('status'))
                  <div class="alert alert-success" role="alert">
                      {{ session('status') }}
                  </div>
              @endif
              @if (session('message'))
                  <div class="alert alert-success" role="alert">
                      {{ session('message') }}
                  </div>
              @endif
              @if (session('error'))
                  <div class="alert alert-danger" role="alert">
                      {{ session('error') }}
                  </div>
              @endif
          </div>
          	<h2 class="text-center">If you got any questions <br>please do not hesitate to send us a message</h2>
            <form  method="post" id="loginForm" action="/login" class="bg-light p-5 contact-form">
                <input type = "hidden" name = "_token" value = "<?php echo csrf_token() ?>">  
              <div class="form-group">
                    <input name="email" class="form-control"  id="name" type="email" placeholder="Your email..." required>
                    <i class="fa-solid fa-user"></i>
              </div>
              <div class="form-group">
                    <input name="password" class="form-control" id="password" type="password" placeholder="Your password..." required>
                    <i class="fa-solid fa-phone"></i>
              </div>              <div class="form-group">
                <input type="submit" value="Send Message" class="btn btn-primary py-3 px-5">
              </div>
              <div class="form-group">
                <center><a href="{{ route('password.request') }}">Forgot Your Password?</a></center>
              </div>
            </form>
          
            <center><label >Don't have an account? <a href="/register">Sign Up</a></label></center>
          </div>
        </div>
        <div class="row justify-content-center">
        	<div class="col-md-10">
        		<div id="map" class="bg-white"></div>
        	</div>
        </div>
      </div>
    </section>
    
<script  src="assets/js/custom/login.js"></script>
@if (auth()->check())
    <script>window.location = '/dashboard';</script>
@endif
<!-- Footer area start -->
@include('footer')
<!-- Footer area end -->


<!-- Use Laravel Auth for login, and display errors from Auth if present. -->