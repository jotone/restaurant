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
				console.log(data);
			}
		});
	});
});