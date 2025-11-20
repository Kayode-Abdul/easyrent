window.onload = function(){ //alert();
	$('#apartment-panel').hide();
    var base_url = $(location).attr('host');
	$("#propertyForm").unbind('submit').bind('submit', function(e) {
		e.preventDefault();
   // alert("ajax is working in login");
		$(".register-button").html("<div id='loader'><i class='ti-reload'></i></div>");
		var form = $(this);
		var url = form.attr('action');
		var type = form.attr('method'); 
		$.ajax({
			url  : url,
			type : type,
            headers: {'X-Requested-With': 'XMLHttpRequest'},
			data : form.serialize(),
			dataType: 'json',
			success:function(response) { //alert('Monument here');
				if(response.success === true) {	
				    $("#message").html('<div class="alert alert-success alert-dismissible" role="alert">'+
						  '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+response.messages.message +' </div>');
						if(response.messages.more){
							//make add(+) button available
							//display apartment panel
							//add property id to form
							$("#property-id").val(response.messages.propId);
							$('#apartment-panel').show(); 
							$('#propertyForm').hide(); 
							//alert("more flats added");#propertyForm
						}
				        //setTimeout(location.replace(response.messages), 5000);
				}else{
        		        $("#message").html('<div class="alert alert-warning alert-dismissible" role="alert">'+
						  '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
						  response.messages + '</div>');
                }
			},error:function(response){
		        $("#message").html('<div class="alert alert-warning alert-dismissible" role="alert">'+
						  '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
						  response.messages + '</div>');
            }, //if
            complete:function(){
		        $(".register-button").html("Create Property"); 
            }
		});

		return false;
	});
	
	$("#ApartmentForm").unbind('submit').bind('submit', function(e) {
		e.preventDefault();

		// $("[name=tenantId]").each(function(){
		// 	$(this).rules("add", {
		// 	  required: true,
		// 	  email: true,
		// 	  messages: {
		// 		required: "Specify a valid email"
		// 	  }
		// 	});   
		//   })
		var form = $(this);
		var url = form.attr('action');
		var type = form.attr('method'); 
		$.ajax({
			url  : url,
			type : type,
			data : form.serialize(),
			dataType: 'json',
			success:function(response) { 
				
				$('#apartment-panel').hide(); 
				$('#propertyForm').show(); 
				window.location = response.location;
			}
			
		});
	});
	$("#resetForm").unbind('submit').bind('submit', function(e) {
		e.preventDefault();
		var form = $(this);
		var url = form.attr('action');
		var type = form.attr('method'); 
		$.ajax({
			url  : url,
			type : type,
			data : form.serialize(),
			dataType: 'json',
			success:function(response) { 
				if(response.success === true) {
					window.location = response.messages;
				}
				else {
					if(response.messages instanceof Object) {
						$("#message").html('');		

						$.each(response.messages, function(index, value) {
							var key = $("#" + index);

							key.closest('.form-group')
							.removeClass('has-error')
							.removeClass('has-success')
							.addClass(value.length > 0 ? 'has-error' : 'has-success')
							.find('.text-danger').remove();							

							key.after(value);

						});
					} 
					else {						
						$(".text-danger").remove();
						$(".form-group").removeClass('has-error').removeClass('has-success');

						$("#message").html('<div class="alert alert-warning alert-dismissible" role="alert">'+
						  '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
						  response.messages + 
						'</div>');
					} // /else
				} // /else
			} // /if
		});

		return false;
	});

$('body').on('focus',".date_picker", function(e){
	e.preventDefault();
	$(this).datepicker({
		minDate: new Date(),
	});
});

}; 

function addRow(){
	
	var tableRow = document.querySelectorAll('#apartmentTable tr');//alert(table.length);
	var rowNo = tableRow.length;
	//alert(rowNo+ "revamp");
	var newRow =  "<tr><td> <label> "+ rowNo++ +" </label> </td>	<td> "+
		'<input size=25 type="text" class="text-secondary" placeholder="Tenant ID" name="tenantId[]">	</td>'+		
		'<td><input size=25 type="text" class="date_picker text-secondary" placeholder="From" name="fromRange[]"  value="" ></td>'+ 
		'<td><input size=25 type="text" class="date_picker text-secondary" placeholder="To" name="toRange[]"  value="" ></td>'+ 
		'<td><input size=25  type="number" class="text-secondary" min="1" step="any" placeholder="Price" name="amount[]"></td></tr>';
	$(newRow).insertAfter("#apartmentTable tr:last");
} 