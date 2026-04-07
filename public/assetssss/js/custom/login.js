window.onload = function(){ //alert();
    var base_url = $(location).attr('host');
	$("#loginForm").unbind('submit').bind('submit', function(e) {
		e.preventDefault();
   // alert("ajax is working in login");
			$(".login-button").html("<i class='fa fa-refresh fa-spin'></i>");
		var form = $(this);
		var url = form.attr('action');
		var type = form.attr('method'); 
		$.ajax({
			url  : url,
			type : type,
            headers: {'X-Requested-With': 'XMLHttpRequest'},
			data : form.serialize(),
			dataType: 'json',
			success:function(response) { 
				if(response.success === true) {	
				    $("#message").html('<div class="alert alert-success alert-dismissible" role="alert">'+
						  '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
						'Welcome </div>');
				        setTimeout(location.replace(response.messages), 5000);
				//	location.replace(response.messages); 		
				}else{
        		        $("#message").html('<div class="alert alert-warning alert-dismissible" role="alert">'+
						  '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
						  response.messages + 
						'</div>');
                }
			},error:function(response){
		        $("#message").html('<div class="alert alert-warning alert-dismissible" role="alert">'+
						  '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
						  response.messages + 
						'</div>');
            }, //if
            complete:function(){
		        $(".login-button").html("Login"); 
            }
		});

		return false;
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