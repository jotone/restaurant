$(document).ready(function(){

	$('.main-wrap input[name=quantity]').change(function () {
		if($(this).val() > 0){
			$(this).prev('input[name=dish]').prop('checked',true)
		}else{
			$(this).prev('input[name=dish]').prop('checked',false)
		}
	});
	$('button[name=apply]').click(function(){
		var result = {};
		$('.rest').each(function(){
			var restId = $(this).attr('data-rest_id');
			result[restId] = {};
			$(this).find('input[name=dish]:checked').each(function () {
				result[restId][$(this).val()] = $(this).next('input[name=quantity]').val();
				if(result[restId][$(this).val()] == null){
					result[restId].splice($(this).val(), 1);
				}
			});
			if((result[restId].length == 0) || (result[restId] == null)){
				result.splice(restId, 1);
			}
		});

		$.ajax({
			url:	'/api/create_order',
			method:	'POST',
			data:	{
				user_id:	$('select[name=user]').val(),
				order:		result
			},
			error:	function(jqXHR){
				showError(jqXHR.responseText, '/api/create_order');
			},
			success:function(data){
				showError(data, '/api/create_order');
			}
		});
	});
});