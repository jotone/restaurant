function hideShowCharacteristicsInput(_this){
	if(_this.prop('checked') == true){
		_this.closest('fieldset').find('input[data-type=default_characteristics]').closest('.row-wrap').show();
	}else{
		_this.closest('fieldset').find('input[data-type=default_characteristics]').closest('.row-wrap').hide();
	}
}

$(document).ready(function(){
	buildFixedNavMenu();

	hideShowCharacteristicsInput($('input[data-type=characteristics_table]'));
	$('input[data-type=characteristics_table]').change(function(){
		hideShowCharacteristicsInput($(this))
	});

	$('button[name=save]').click(function(e){
		e.preventDefault();

		var data = {
			ajax: 1
		};
		$('form fieldset').each(function(){
			$(this).find('select').each(function(){
				data[$(this).attr('name')] = $(this).val()
			});
			$(this).find('input[type=text]').each(function(){
				data[$(this).attr('name')] = ($(this).val().length > 0)? $(this).val(): null;
			});
			$(this).find('input[type=checkbox]').each(function(){
				data[$(this).attr('name')] = ($(this).prop('checked') == true)? 1: 0;
			});
		});
		$.ajax({
			url:	'/admin/settings',
			type:	'PUT',
			headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
			data:	data,
			error:	function(jqXHR){
				showError(jqXHR.responseText, 'PUT::/admin/settings');
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data.message == 'success'){
						statusBarAddMessage(true, 'Settings saved successfully');
						showStatus(true);
					}
				}catch(e){
					showError(e+data, 'PUT::/admin/settings');
				}
			}
		});
	});
});