<!-- Header area start -->
@include('header')
<!-- Header area end -->
 
        <!-- start wpo-page-title -->
        <section class="wpo-page-title">
            <div class="container">
                <div class="row">
                    <div class="col col-xs-12">
                        <div class="wpo-breadcumb-wrap">
                            <h2>Contact</h2>
                            <ol class="wpo-breadcumb-wrap">
                                <li><a href="index.html">Home</a></li>
                                <li>Contact</li>
                            </ol>
                        </div>
                    </div>
                </div> <!-- end row -->
            </div> <!-- end container -->
        </section>
        <!-- end page-title -->

        <!--  start wpo-contact-map -->
        <section class="wpo-contact-map-section pt-120">
            <div class="container-fluid">
                <h2 class="hidden">Contact map</h2>
                <div class="wpo-contact-map">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d193595.9147703055!2d-74.11976314309273!3d40.69740344223377!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew+York%2C+NY%2C+USA!5e0!3m2!1sen!2sbd!4v1547528325671"
                        allowfullscreen></iframe>
                </div>
            </div>
        </section>
        <!-- end wpo-contact-map -->
        <!-- start wpo-contact-pg-section -->
        <section class="wpo-contact-pg-section section-padding pt-0">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="wpo-contact-form-area">
                            <div class="wpo-contact-title">
                                <h2>Have Any Question?</h2>
                                <p>It is a long established fact that a reader will be distracted
                                    content of a page when looking.</p>
                            </div>
                            <form method="post" class="contact-validation-active" id="contact-form-main">
                                <div>
                                    <input type="text" class="form-control" name="name" id="name"
                                        placeholder="Your Name*">
                                </div>
                                <div>
                                    <input type="email" class="form-control" name="email" id="email"
                                        placeholder="Your Email*">
                                </div>
                                <div>
                                    <input type="text" class="form-control" name="phone" id="phone"
                                        placeholder="Your Phone*">
                                </div>
                                <div>
                                    <select name="subject" class="form-control">
                                        <option disabled="disabled" selected>Subject</option>
                                        <option>Concrete Tile</option>
                                        <option>Family Room</option>
                                        <option>Guest Quaters</option>
                                        <option>Heated Floors</option>
                                    </select>
                                </div>
                                <div class="fullwidth">
                                    <textarea class="form-control" name="note" id="note"
                                        placeholder="Message..."></textarea>
                                </div>
                                <div class="submit-area">
                                    <button type="submit" class="theme-btn">Get in Touch</button>
                                    <div id="loader">
                                        <i class="ti-reload"></i>
                                    </div>
                                </div>
                                <div class="clearfix error-handling-messages">
                                    <div id="success">Thank you</div>
                                    <div id="error"> Error occurred while sending email. Please try again later. </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col col-lg-4">
                        <div class="office-info">
                            <div class="office-info-item">
                                <div class="office-info-icon">
                                    <div class="icon">
                                        <i class="fi flaticon-phone-call"></i>
                                    </div>
                                </div>
                                <div class="office-info-text">
                                    <h2>Call Us:</h2>
                                    <p>+00 697-836-937</p>
                                </div>
                            </div>
                            <div class="office-info-item">
                                <div class="office-info-icon">
                                    <div class="icon">
                                        <i class="fi flaticon-email"></i>
                                    </div>
                                </div>
                                <div class="office-info-text">
                                    <h2>E-mail Us:</h2>
                                    <p>wosabinfo@gmail.com</p>
                                </div>
                            </div>
                            <div class="office-info-item">
                                <div class="office-info-icon">
                                    <div class="icon">
                                        <i class="fi flaticon-house"></i>
                                    </div>
                                </div>
                                <div class="office-info-text">
                                    <h2>Opening Hours:</h2>
                                    <p>09:00 am to 10:00 pm</p>
                                </div>
                            </div>
                            <div class="office-info-item">
                                <div class="office-info-icon">
                                    <div class="icon">
                                        <i class="fi flaticon-placeholder"></i>
                                    </div>
                                </div>
                                <div class="office-info-text">
                                    <h2>Our Location:</h2>
                                    <p>8932, Natiska, USA.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end container -->
        </section>
        <!-- end wpo-contact-pg-section -->

<!-- Footer area start -->
@include('footer')
<!-- Footer area end -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>