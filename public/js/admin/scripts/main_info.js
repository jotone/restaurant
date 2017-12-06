$(document).ready(function(){
	buildFixedNavMenu();

	$('button[name=add]').click(function(){
		var cloned = $(this).closest('.row-wrap').prev('.row-wrap').clone();
		$(this).closest('.row-wrap').before(cloned);
	});

	$('button[name=save]').click(function(e){
		e.preventDefault();

		var data = {
			ajax: 1
		};
		$('form fieldset').each(function(){
			$(this).find('input').each(function(){
				data[$(this).attr('name')] = [];
			});
			$(this).find('textarea').each(function(){
				data[$(this).attr('name')] = [];
			});

			$(this).find('input').each(function(){
				if($(this).val().length > 0){
					data[$(this).attr('name')].push($(this).val())
				}
			});
			$(this).find('textarea').each(function(){
				if($(this).val().length > 0){
					data[$(this).attr('name')].push($(this).val());
				}
			});
		});
		$.ajax({
			url:	'/admin/settings/main_info',
			type:	'PUT',
			headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
			data:	data,
			error:	function(jqXHR){
				showError(jqXHR.responseText, 'PUT::/admin/settings/main_info');
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data.message == 'success'){
						statusBarAddMessage(true, 'Settings saved successfully');
						showStatus(true);
					}
				}catch(e){
					showError(e+data, 'PUT::/admin/settings/main_info');
				}
			}
		});
	});
});