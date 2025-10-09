<!-- Header area start -->
@include('header')
<!-- Header area end -->

<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<section class="hero-wrap hero-wrap-2 ftco-degree-bg js-fullheight" style="background-image: url('assets/images/bg_1.jpg');" data-stellar-background-ratio="0.5">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
          <div class="col-md-9 ftco-animate pb-5 text-center">
          	<p class="breadcrumbs"><span class="mr-2"><a href="index.html">Home <i class="ion-ios-arrow-forward"></i></a></span> <span>Contact <i class="ion-ios-arrow-forward"></i></span></p>
            <h1 class="mb-3 bread">Property Listing</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section contact-section">
      <div class="container"> 
        <div class="row block-9 justify-content-center mb-5">
          <div class="col-md-8 mb-md-5">
            
<?php //print_r($locations); 
  //echo($locations); 
  $location = json_decode($locations, true);
  //foreach ($location['Abia'] as $list) {
    //foreach (array_values($list)[0] as $card) {
    //    echo $card['name'];
    //}
//}
?>
            <h1>Add Property</h1>
          <div id="message"></div>
          	<!--h2 class="text-center">If you got any questions <br>please do not hesitate to send us a message</h2-->
            <form   method="post" class="bg-light p-5" id="propertyForm">
                <input type = "hidden" name = "_token" value = "<?php echo csrf_token() ?>">
              <!--div class="form-group">
                <input type="text" class="form-control" placeholder="Your Name" name="name" id="name">
              </div>
              <div class="form-group">
                <input type="text" class="form-control" name="username" id="username"  placeholder="Your User Name*">
              </div>
              <div class="form-group">
                <input type="email" class="form-control" name="email" id="email" placeholder="Your Email*">
              </div -->
              <div class="form-group">
                <select name="propertyType" id="property-type" class="form-control">
                    <option value="" disabled="disabled" selected>Property Type</option>
                    <option value="1">Mansion</option>
                    <option value="2">Duplex</option>
                    <option value="3">Flat</option>
                    <option value="4">Terrace</option>
                </select>
              </div>
              <div class="form-group">
                <select name="state" id="states" class="form-control" onchange="getCities()">
                    <option value="" disabled="disabled" selected>State</option>
                    <?php 

                      foreach ($location as $key =>$item) {   
                    ?> 
                        <option value="<?=$item['name'] ?>"><?=$item['name'] ?></option>  
                    <?php
                      } 
                    ?>
                </select>
              </div>
              <div class="form-group">
                <select name="city" id="cities" class="form-control">
                    <option value="" disabled="disabled" selected>L.G.A</option> 
                </select>
              </div>  
                <div class="fullwidth reg2 form-group">
                    <textarea class="form-control" name="address" id="propertyAdd"
                        placeholder="Property Address..."></textarea>
                </div>
              <div class="form-group">
                <input type="submit" value="Create Property" class="btn btn-primary py-3 px-5">
              </div>
            </form>
          
          </div>
        </div>
        <div class="row justify-content-center" id="apartment-panel">
        	<div class="col-md-10">
        		
            <div id="message"></div>
                            <form method="post" action="/apartment" class="p-5 bg-primary" id="ApartmentForm">
                                <input type = "hidden" name = "_token" value = "<?php echo csrf_token(); ?>">
                                <input type = "hidden" id="property-id" name="propertyId" >
                               
                                <table id="apartmentTable" class="text-center">  
                                    <tbody>
                                        <tr>
                                            <td></td>
                                            <td>Tenant ID</td>
                                            <td>To</td> 
                                            <td>From</td> 
                                            <td>Price</td>
                                        </tr>
                                    </tbody>    
                                </table>
                                <div class="submit-area m-3">
                                    <button type="submit" class="register-button">Create Property</button>
                                     <button type="button" class="btn btn-success float-end"  onclick="addRow()">+ Add Apartment</button>
                               </div>
                            </form>
        	</div>
        </div>
      </div>
        </section>   
<!-- Footer area start -->
@include('footer')
<!-- Footer area end -->