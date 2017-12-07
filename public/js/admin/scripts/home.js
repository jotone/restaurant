function processStep(step){
	for(var i = 0; i<=step; i++){
		$('#step_'+i).show();
	}
}

$(document).ready(function(){
	$('button[name=send_phone]').click(function () {
		var phone = $('input[name=phone]').val();
		$.ajax({
			url:	'/api/create_account',
			type:	'POST',
			data:	{phone: phone},
			error:	function(jqXHR){
				showError(jqXHR.responseText, 'POST::/api/create_account');
			},
			success:function(data){
				data = JSON.parse(data);
				processStep(data.step);
				$('input[name=id]').val(data.id);
			}
		});
	});

	$('button[name=send_sms]').click(function () {
		var id = $('input[name=id]').val();
		var sms = $('input[name=sms_code]').val();
		$.ajax({
			url:	'/api/submit_sms_code/'+id,
			type:	'PUT',
			data:	{sms: sms},
			error:	function(jqXHR){
				showError(jqXHR.responseText, 'PUT::/api/submit_sms_code/'+id);
			},
			success:function(data){
				data = JSON.parse(data);
				processStep(data.step);
			}
		});
	});

	$('button[name=save]').click(function () {
		var id = $('input[name=id]').val();
		$.ajax({
			url:	'/api/submit_profile/'+id,
			type:	'PUT',
			data:	{
				email:		$('input[name=email]').val(),
				name:		$('input[name=name]').val(),
				surname:	$('input[name=surname]').val(),
				pass:		$('input[name=password]').val(),
				confirm:	$('input[name=confirm_password]').val()
			},
			error:	function(jqXHR){
				showError(jqXHR.responseText, 'PUT::/api/submit_profile/'+id);
			},
			success:function(data){
				data = JSON.parse(data);
				if(data.step == 3){
					alert("WELL DONE!!");
				}
			}
		});
	});

	$('button[name=login]').click(function () {
		$.ajax({
			url:	'/api/log_in',
			type:	'POST',
			data:	{
				email:		$('input[name=user_login]').val(),
				pass:		$('input[name=user_pass]').val(),
			},
			error:	function(jqXHR){
				showError(jqXHR.responseText, 'POST::/api/log_in');
			},
			success:function(data){
				console.log(data);
			}
		});
	})
});