$(document).ready(function(){
	if($('.main-wrap>.items-list').length > 0){
		var getParams = getRequest();
		if(typeof getParams.sort_by == 'undefined'){
			var getParams = {
				sort_by: 'id',
				dir: 'asc'
			}
		}
		$('.main-wrap>.items-list #'+getParams.sort_by+' .'+getParams.dir).addClass('active');
	}

	buildFixedNavMenu();

	$('.chbox-selector-wrap input.crud-control').click(function(){
		var checked = $(this).prop('checked');
		$(this).closest('.chbox-selector-item').find('.crud-wrap input[type=checkbox]').prop('checked', checked);
	});

	$('.chbox-selector-wrap .crud-input').click(function(){
		var status = 0;
		$(this).closest('.crud-wrap').find('.crud-input').each(function(){
			if($(this).prop('checked') == true){
				status++
			}
		});
		var max = $(this).closest('.crud-wrap').find('.crud-input').length;
		if(status == max){
			$(this).closest('.chbox-selector-item').find('input.crud-control').prop('checked',true);
		}else{
			$(this).closest('.chbox-selector-item').find('input.crud-control').prop('checked',false);
		}
	});

	$('button[name=save]').click(function(e){
		e.preventDefault();
		var validation = validate('form[name=role]');
		if(validation){
			var pages = {};
			$('.chbox-selector-wrap .chbox-selector-item').each(function(){
				var pageID = $(this).find('.crud-control').val();
				var status = '';
				if($(this).find('.crud-input[name="read[]"]').length > 0){
					if($(this).find('.crud-input[name="read[]"]').prop('checked') === true){
						status += 'r';
					}
				}else{
					status += 'r';
				}
				if($(this).find('.crud-input[name="create[]"]').length > 0){
					if($(this).find('.crud-input[name="create[]"]').prop('checked') === true){
						status += 'c';
					}
				}else{
					status += 'c';
				}
				if($(this).find('.crud-input[name="update[]"]').length > 0){
					if($(this).find('.crud-input[name="update[]"]').prop('checked') === true){
						status += 'u';
					}
				}else{
					status += 'u';
				}
				if($(this).find('.crud-input[name="delete[]"]').length > 0){
					if($(this).find('.crud-input[name="delete[]"]').prop('checked') === true){
						status += 'd';
					}
				}else{
					status += 'd';
				}
				pages[pageID] = status;
			});
			var title = $('form[name=role] input[name=title]').val();
			var id = $('input[name=id]').val().trim();
			var id = (id.length > 0)? '/'+id: '';
			var type = (id.length > 0)? 'PUT': 'POST';

			$.ajax({
				url:	'/admin/users/roles'+id,
				type:	type,
				headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
				data:	{
					title:	title,
					pages:	JSON.stringify(pages),
					ajax:	1
				},
				error:	function(jqXHR){
					showError(jqXHR.responseText, type+'::/admin/users/roles'+id);
				},
				success:function(data){
					try{
						data = JSON.parse(data);
						if(data.message == 'success'){
							statusBarAddMessage(true, 'Роль "'+title+'" успешно сохранена');
							showStatus(true);

							var confirmMessage = (id.length > 0)
								? 'Продолжить редактирование данной роли?'
								: 'Приступить к наполнению следующей роли?';
								showConfirm(confirmMessage);

							$(document).on('customEvent', function(e){
								if(e.message === true){
									if(id.length == 0){
										$('input[name=id]').val('');
										$('input[type=text]').val('');
										$('input[type=checkbox]').prop('checked',false);
									}
								}else if(e.message === false){
									location = '/admin/users/roles';
								}
							});
						}else if(data.message == 'error'){
							for(var i in data.errors){
								statusBarAddMessage(false, data.errors[i]);
								showStatus(true);
							}
						}
					}catch(e){
						showError(e+data, type+'::/admin/users/roles'+id);
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
		showConfirm('Вы действительно хотите удалить роль "'+$(this).attr('data-title')+'" и отменить права всех прикрепленных к ней пользователей?');
		$(document).on('customEvent', function(e){
			if(e.message === true){
				var id = _this.attr('data-id');
				$.ajax({
					url:	'/admin/users/roles/'+id,
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/users/roles/'+id);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								location.reload(true);
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/users/roles/'+id);
						}
					}
				})
			}
		});
	});
});