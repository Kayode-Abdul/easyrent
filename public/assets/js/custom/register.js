window.onload = function(){ //alert();
	$('.reg2').hide();
    var base_url = $(location).attr('host');
	$("#registration-Form").unbind('submit').bind('submit', function(e) {
   // alert("ajax is working in login");
		e.preventDefault();
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
						  '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
						response.messages +' </div>');
				        //setTimeout(location.replace(response.messages), 5000);
						$('html, body').animate({  
							scrollTop: $('#anchor').offset().top  
						}, 1000); 
						$("#registration-Form")[0].reset()
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
		        $(".register-button").html("Sign In"); 
            }
		});

		return false;
	});
	// Only allow ordinary roles (1=Landlord, 2=Tenant, 3=Artisan, 4=Property Manager)
	$("#user-role").change(function(e){
		e.preventDefault();
		var opt = $('#user-role option:selected').prop('value');
		if(['1','2','3','4'].indexOf(opt) === -1) {
			// If a privileged role is somehow present, reset selection and hide extra fields
			$('#user-role').val('');
			$('.reg2').hide();
			return;
		}
		if(opt !='1'){
			//alert("The text has been changed to "+opt);
			$('.reg2').show();
		}else{
			$('.reg2').hide();
		}
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
};