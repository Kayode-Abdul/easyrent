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
                <label for="property-type">Property Type *</label>
                <select name="propertyType" id="property-type" class="form-control" required>
                    <option value="" disabled="disabled" selected>-- Select Property Type --</option>
                    <optgroup label="Residential">
                        <option value="1">Mansion</option>
                        <option value="2">Duplex</option>
                        <option value="3">Flat</option>
                        <option value="4">Terrace</option>
                    </optgroup>
                    <optgroup label="Commercial">
                        <option value="5">Warehouse</option>
                        <option value="8">Store</option>
                        <option value="9">Shop</option>
                    </optgroup>
                    <optgroup label="Land/Agricultural">
                        <option value="6">Land</option>
                        <option value="7">Farm</option>
                    </optgroup>
                </select>
              </div>

              <!-- Size Fields (for commercial and land properties only) -->
              <div class="form-group" id="size-fields" style="display: none;">
                <label for="size_value">Property Size *</label>
                <div class="row">
                    <div class="col-md-6">
                        <input type="number" name="size_value" id="size_value" class="form-control" 
                               placeholder="Enter size" step="0.01" min="0">
                    </div>
                    <div class="col-md-6">
                        <select name="size_unit" id="size_unit" class="form-control">
                            <option value="sqm">Square Meters (sqm)</option>
                            <option value="sqft">Square Feet (sqft)</option>
                            <option value="acres">Acres</option>
                            <option value="hectares">Hectares</option>
                        </select>
                    </div>
                </div>
                <small class="form-text text-muted">Required for commercial and land properties</small>
              </div>

              <!-- Warehouse-specific fields -->
              <div id="warehouse-fields" style="display: none;">
                <h5 class="mt-3 mb-3">Warehouse Details</h5>
                <div class="form-group">
                    <label for="height_clearance">Height Clearance (meters)</label>
                    <input type="number" name="height_clearance" id="height_clearance" 
                           class="form-control" placeholder="e.g., 8" step="0.1">
                </div>
                <div class="form-group">
                    <label for="loading_docks">Number of Loading Docks</label>
                    <input type="number" name="loading_docks" id="loading_docks" 
                           class="form-control" placeholder="e.g., 3" min="0">
                </div>
                <div class="form-group">
                    <label for="storage_type">Storage Type</label>
                    <select name="storage_type" id="storage_type" class="form-control">
                        <option value="">-- Select Storage Type --</option>
                        <option value="dry_storage">Dry Storage</option>
                        <option value="cold_storage">Cold Storage</option>
                        <option value="hazmat">Hazardous Materials</option>
                        <option value="general">General Storage</option>
                    </select>
                </div>
              </div>

              <!-- Land/Farm-specific fields -->
              <div id="land-fields" style="display: none;">
                <h5 class="mt-3 mb-3">Land/Farm Details</h5>
                <div class="form-group">
                    <label for="land_type">Land Type</label>
                    <select name="land_type" id="land_type" class="form-control">
                        <option value="">-- Select Land Type --</option>
                        <option value="agricultural">Agricultural</option>
                        <option value="residential">Residential</option>
                        <option value="commercial">Commercial</option>
                        <option value="mixed">Mixed Use</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="soil_type">Soil Type</label>
                    <input type="text" name="soil_type" id="soil_type" 
                           class="form-control" placeholder="e.g., loamy, sandy, clay">
                </div>
                <div class="form-group">
                    <label for="water_access">Water Access</label>
                    <select name="water_access" id="water_access" class="form-control">
                        <option value="">-- Select --</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="water_source">Water Source (if applicable)</label>
                    <input type="text" name="water_source" id="water_source" 
                           class="form-control" placeholder="e.g., borehole, river, well">
                </div>
                <div class="form-group">
                    <label for="topography">Topography</label>
                    <select name="topography" id="topography" class="form-control">
                        <option value="">-- Select --</option>
                        <option value="flat">Flat</option>
                        <option value="hilly">Hilly</option>
                        <option value="sloped">Sloped</option>
                    </select>
                </div>
              </div>

              <!-- Store/Shop-specific fields -->
              <div id="store-fields" style="display: none;">
                <h5 class="mt-3 mb-3">Store/Shop Details</h5>
                <div class="form-group">
                    <label for="frontage_width">Frontage Width (meters)</label>
                    <input type="number" name="frontage_width" id="frontage_width" 
                           class="form-control" placeholder="e.g., 6" step="0.1">
                </div>
                <div class="form-group">
                    <label for="store_type">Store Type</label>
                    <select name="store_type" id="store_type" class="form-control">
                        <option value="">-- Select Store Type --</option>
                        <option value="retail">Retail</option>
                        <option value="restaurant">Restaurant</option>
                        <option value="office">Office</option>
                        <option value="salon">Salon/Spa</option>
                        <option value="pharmacy">Pharmacy</option>
                        <option value="supermarket">Supermarket</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="foot_traffic">Foot Traffic Level</label>
                    <select name="foot_traffic" id="foot_traffic" class="form-control">
                        <option value="">-- Select --</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="parking_spaces">Parking Spaces</label>
                    <input type="number" name="parking_spaces" id="parking_spaces" 
                           class="form-control" placeholder="Number of parking spaces" min="0">
                </div>
              </div>

              <div class="form-group">
                <label for="states">State *</label>
                <select name="state" id="states" class="form-control" onchange="getCities()" required>
                    <option value="" disabled="disabled" selected>-- Select State --</option>
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
                <label for="cities">L.G.A *</label>
                <select name="city" id="cities" class="form-control" required>
                    <option value="" disabled="disabled" selected>-- Select L.G.A --</option> 
                </select>
              </div>  
              <div class="fullwidth reg2 form-group">
                <label for="propertyAdd">Property Address *</label>
                <textarea class="form-control" name="address" id="propertyAdd"
                    placeholder="Enter full property address..." rows="3" required></textarea>
              </div>

              <!-- Number of Apartments (for residential properties only) -->
              <div class="form-group" id="apartments-field" style="display: none;">
                <label for="noOfApartment">Number of Units/Apartments *</label>
                <input type="number" class="form-control" name="noOfApartment" id="noOfApartment" 
                       min="1" placeholder="Enter number of units/apartments">
                <small class="form-text text-muted">Number of rentable units in this property</small>
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
                                            <td>From</td> 
                                            <td>To</td> 
                                            <td>Price</td>
                                            <td>Rental Type</td>
                                            <td>Action</td>
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

<script>
// Handle property type change to show/hide conditional fields
document.getElementById('property-type').addEventListener('change', function() {
    const propType = parseInt(this.value);
    
    // Hide all conditional fields
    document.getElementById('size-fields').style.display = 'none';
    document.getElementById('apartments-field').style.display = 'none';
    document.getElementById('warehouse-fields').style.display = 'none';
    document.getElementById('land-fields').style.display = 'none';
    document.getElementById('store-fields').style.display = 'none';
    
    // Remove required attributes
    document.getElementById('size_value').removeAttribute('required');
    document.getElementById('noOfApartment').removeAttribute('required');
    
    // Show relevant fields based on property type
    if (propType >= 1 && propType <= 4) { // Residential (Mansion, Duplex, Flat, Terrace)
        document.getElementById('apartments-field').style.display = 'block';
        document.getElementById('noOfApartment').setAttribute('required', 'required');
    } else if (propType === 5) { // Warehouse
        document.getElementById('size-fields').style.display = 'block';
        document.getElementById('warehouse-fields').style.display = 'block';
        document.getElementById('size_value').setAttribute('required', 'required');
        // Warehouse doesn't need apartments field
    } else if (propType === 6 || propType === 7) { // Land or Farm
        document.getElementById('size-fields').style.display = 'block';
        document.getElementById('land-fields').style.display = 'block';
        document.getElementById('size_value').setAttribute('required', 'required');
        // No apartments field for land/farm
    } else if (propType === 8 || propType === 9) { // Store or Shop
        document.getElementById('size-fields').style.display = 'block';
        document.getElementById('store-fields').style.display = 'block';
        document.getElementById('size_value').setAttribute('required', 'required');
        // Store/Shop doesn't need apartments field
    }
});

// Trigger change event on page load if property type is already selected
if (document.getElementById('property-type').value) {
    document.getElementById('property-type').dispatchEvent(new Event('change'));
}
</script>
   
<!-- Footer area start -->
@include('footer')
<!-- Footer area end -->