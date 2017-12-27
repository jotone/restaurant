$(document).ready(function() {
	if ($('.main-wrap>.items-list').length > 0) {
		var getParams = getRequest();
		if (typeof getParams.sort_by == 'undefined') {
			var getParams = {
				sort_by: 'id',
				dir: 'asc'
			}
		}
		$('.main-wrap>.items-list #' + getParams.sort_by + ' .' + getParams.dir).addClass('active');
	}

	buildFixedNavMenu();

	$('button[name=save]').click(function(e){
		e.preventDefault();
		var validation = validate('form[name=visitors]');
		if(validation){
			var name = $('input[name=name]').val().trim();
			var id = $('input[name=id]').val().trim();
			var id = (id.length > 0)? '/'+id: '';
			var type = (id.length > 0)? 'PUT': 'POST';

			$.ajax({
				url:	'/admin/visitors'+id,
				type:	type,
				headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
				data:	{
					ajax:		1,
					name:		name,
					surname: 	$('input[name=surname]').val(),
					email:		$('input[name=email]').val().trim(),
					phone:		$('input[name=phone]').val(),
					password:	$('input[name=password]').val().trim(),
					password_confirmation: $('input[name=password_confirmation]').val().trim()
				},
				error:	function(jqXHR){
					showError(jqXHR.responseText, type+'::/admin/visitors'+id);
				},
				success:function(data){
					try{
						data = JSON.parse(data);
						if(data.message == 'success'){
							statusBarAddMessage(true, 'Пользователь "' + name + '" успешно сохранен.');
							showStatus(true);

							var confirmMessage = (id.length > 0)
								? 'Продолжить редактирование данного пользователя?'
								: 'Приступить к созданию следующего пользователя?';
							showConfirm(confirmMessage);

							$(document).on('customEvent', function (e) {
								if (e.message === true) {
									if (id.length == 0) {
										location.reload(true);
									}
								} else if (e.message === false) {
									location = '/admin/visitors';
								}
							});
						}else if(data.message == 'error'){
							for(var i in data.errors){
								statusBarAddMessage(false, data.errors[i]);
								showStatus(true);
							}
						}
					}catch(e){
						showError(e+data, type+'::/admin/visitors'+id);
					}
				}
			});
		}else{
			goTo('.validate-wrap');
		}
	});

	$('.items-list a.drop').click(function(e){
		e.preventDefault();
		var _this = $(this);
		showConfirm('Вы действительно хотите удалить пользователя "'+$(this).attr('data-title')+'"?');
		$(document).on('customEvent', function(e){
			if(e.message === true){
				var id = _this.attr('data-id');
				$.ajax({
					url:	'/admin/visitors/'+id,
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/visitors/'+id);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								location.reload(true);
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/visitors/'+id);
						}
					}
				})
			}
		});
	});
});