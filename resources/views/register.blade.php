<!-- Header area start -->
@include('header')
<!-- Header area end -->
 
 
<section class="hero-wrap hero-wrap-2 ftco-degree-bg js-fullheight" style="background-image: url('assets/images/bg_1.jpg');" data-stellar-background-ratio="0.5">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
          <div class="col-md-9 ftco-animate pb-5 text-center">
          	<p class="breadcrumbs"><span class="mr-2"><a href="index.html">Home <i class="ion-ios-arrow-forward"></i></a></span> <span>Contact <i class="ion-ios-arrow-forward"></i></span></p>
            <h1 class="mb-3 bread">Signup</h1>
          </div>
        </div>
      </div>
    </section>         
<?php //print_r($locations); 
  //echo($locations); 
  $location = json_decode($locations, true);
  //foreach ($location['Abia'] as $list) {
    //foreach (array_values($list)[0] as $card) {
    //    echo $card['name'];
    //}
//}
?>
    <section class="ftco-section contact-section">
      <div class="container"> 
        <div class="row block-9 justify-content-center mb-5">
          <div class="col-md-8 mb-md-5">
          <div id="anchor"></div>
          <div id="message"></div>
          <form  method="post" action="/register" id="registration-Form" class="registeration bg-light p-5 contact-form" enctype="multipart/form-data">
                <input type = "hidden" name = "_token" value = "<?php echo csrf_token() ?>">
                <div class="form-group">
                    <label for="photo">Profile Photo</label>
                    <input type="file" class="form-control" name="photo" id="photo" accept="image/*">
                </div>
              <div class="form-group">
                <input type="text" class="form-control" name="username" id="username"  placeholder="Your User Name*">
              </div>
              <div class="form-group">
                <input type="text" class="form-control" placeholder="First Name" name="f_name" id="f-name">
              </div>
              <div class="form-group">
                <input type="text" class="form-control" placeholder="Last Name" name="l_name" id="l-name">
              </div>
              <div class="form-group">
                <input type="email" class="form-control" name="email" id="email" placeholder="Your Email*">
              </div>
              <div class="form-group">
                <select name="role" id="user-role" class="form-control">
                    <option value="" disabled="disabled" selected>Role</option>
          @php
            $publicRoles = \DB::table('roles')->whereIn('name', ['tenant', 'landlord', 'marketer', 'Artisan', 'property_manager'])->get();
          @endphp
                    @foreach($publicRoles as $role)
                        <option value="{{ $role->id }}">{{ ucfirst($role->display_name ?? $role->name) }}</option>
                    @endforeach
                </select>
              </div>
              <div class="form-group">
                 <input type="password" class="form-control" name="password" id="password"  placeholder="Your Password*">              
             </div>
              <div class="form-group">
                <input type="password" class="form-control" name="repassword" id="repassword" placeholder="Re Password*">
              </div>
              <div class="reg2 form-group">
                    <input type="text" class="form-control" name="occupation" id="occupation"
                        placeholder="Occupation ...">
                </div>
                <div class="reg2 form-group">
                    <input type="text" class="form-control" name="phone" id="phone"
                        placeholder="Phone Number">
                </div>
                <!--div class="reg2 form-group">
                    <input type="text" class="form-control" name="officeNum" id="officeNum"
                        placeholder="Office Phone No.">
                </div-->
                <div class="fullwidth reg2 form-group">
                    <textarea class="form-control" name="address" id="address"
                        placeholder="Address..."></textarea>
                </div>
              <div class="form-group  reg2">
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
              <div class="form-group  reg2">
                <select name="city" id="cities" class="form-control">
                    <option value="" disabled="disabled" selected>Cities</option> 
                </select>
              </div>  
              <div class="form-group">
                <input type="submit" value="Submit Profile" class="btn btn-primary py-3 px-5">
              </div>
            </form>
          <center><label >Already Signed Up? <a href="/login">Sign In</a></label></center>
          </div>
        </div> 
      </div>
    </section>
        <script  src="assets/js/custom/register.js"></script>
        
    <script>
      function getCities(){
        var stet = document.getElementById("states").value;
        var dayArr = <?php echo json_encode($location); ?>;
        //console.log(dayArr);
        var findDay =stet; //find price for day 1

        var price = $.map(dayArr, function(value, key) {
         if (value.name === findDay)
         {
             //console.log( value.cities);
             //const select_elem = document.getElementById('');  
              var dynamicSelect = document.getElementById("cities");
              dynamicSelect.innerHTML = "";
              value.cities.forEach(function(item){ 
                  var newOption = document.createElement("option");
                  newOption.text = item.toString();//item.whateverProperty
                  newOption.value = item.toString();
                  //append acquired data
                  dynamicSelect.add(newOption);
            });
         }
     });
  }
    </script>
<!-- Footer area start -->
@include('footer')
<!-- Footer area end -->
