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

	$('button[name=save]').click(function(e){
		e.preventDefault();
		var validation = validate('form[name=category_type]');
		if(validation){
			var positions = [];
			$('.categories-list-wrap ul li').each(function(){
				var temp = {
					id:			$(this).attr('data-id'),
					position:	$(this).index(),
					refer_to:	($(this).closest('ul').parent('li').length > 0)
						? $(this).closest('ul').parent('li').attr('data-id')
						: 0
				};
				positions.push(temp);
			});

			var options = {
				image:	($('input[name=option_image]').prop('checked') == true)? 1: 0,
				text:	($('input[name=option_text]').prop('checked') == true)? 1: 0,
				meta:	($('input[name=option_meta]').prop('checked') == true)? 1: 0,
				seo:	($('input[name=option_seo]').prop('checked') == true)? 1: 0
			}


			var id = $('input[name=id]').val().trim();
			var id = (id.length > 0)? '/'+id: '';
			var type = (id.length > 0)? 'PUT': 'POST';
			var title = $('input[name=title]').val().trim();
			$.ajax({
				url:	'/admin/category_types'+id,
				type:	type,
				headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
				data:	{
					title:		title,
					slug:		$('input[name=slug]').val().trim(),
					enabled:	($('input[name=enabled]').prop('checked') == true)? 1: 0,
					positions:	JSON.stringify(positions),
					options:	options,
					ajax:		1
				},
				error:	function(jqXHR){
					showError(jqXHR.responseText, type+'::/admin/category_types'+id);
				},
				success:function(data){
					try{
						data = JSON.parse(data);
						if(data.message == 'success'){
							statusBarAddMessage(true, 'Category Type "' + title + '" was saved successfully');
							showStatus(true);

							var confirmMessage = (id.length > 0)
								? 'Продолжить редактирование данного типа категорий?'
								: 'Приступить к наполнению следующего типа категорий?';
							showConfirm(confirmMessage);

							$(document).on('customEvent', function(e){
								if(e.message === true){
									if(id.length == 0){
										$('input[name=id]').val('');
										$('input[type=text]').val('');
										$('input[type=checkbox]').prop('checked',false);
									}
								}else if(e.message === false){
									location = '/admin/category_types';
								}
							});
						}else if(data.message == 'error'){
							for(var i in data.errors){
								statusBarAddMessage(false, data.errors[i]);
								showStatus(true);
							}
						}
					}catch(e){
						showError(e+data, type+'::/admin/category_types'+id);
					}
				}
			});
		}else{
			goTo('.validate-wrap');
		}
	});

	$('.items-list tr td input[name=enabled]').click(function(){
		var id = $(this).closest('tr').find('a.drop').attr('data-id');
		var title = $(this).closest('tr').find('a.drop').attr('data-title');
		var enabled = ($(this).prop('checked') == true)? 1: 0;
		$.ajax({
			url:'/admin/category_types/'+id,
			type:'PATCH',
			headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
			data:	{
				enabled:enabled,
				ajax:	1
			},
			error:	function(jqXHR){
				showError(jqXHR.responseText, 'PATCH::/admin/category_types/'+id);
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data.message == 'success'){
						statusBarAddMessage(true, 'Category type "'+title+'" status changed successfully');
						showStatus(true);
					}else{
						showError(data, 'PATCH::/admin/category_types/'+id);
					}
				}catch(e){
					showError(e+data, 'PATCH::/admin/category_types/'+id);
				}
			}
		});
	});

	$('.items-list a.drop').click(function(e) {
		e.preventDefault();
		var _this = $(this);
		showConfirm('Вы действительно хотите удалить тип категорий "'+$(this).attr('data-title')+'" вместе с вложеными категориями?');
		$(document).on('customEvent', function(e){
			if(e.message === true){
				var id = _this.attr('data-id');
				$.ajax({
					url:	'/admin/category_types/'+id,
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/category_types/'+id);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								location.reload(true);
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/category_types/'+id);
						}
					}
				})
			}
		});
	});

	$('.categories-list-wrap').on('click','.fa-ban, .fa-check', function(e){
		e.preventDefault();
		var enabled = ($(this).hasClass('fa-ban'))? 1: 0;
		var _this = $(this);
		var id = $(this).closest('li').attr('data-id');
		var title = $(this).closest('li').find('.title-wrap').text();
		$.ajax({
			url:	'/admin/category/'+id,
			type:	'PATCH',
			headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
			data:	{enabled:enabled},
			error:	function(jqXHR){
				showError(jqXHR.responseText, 'PATCH::/admin/category/'+id);
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data.message == 'success'){
						if(_this.hasClass('fa-ban')){
							_this.removeClass('fa-ban').addClass('fa-check');
						}else{
							_this.removeClass('fa-check').addClass('fa-ban');
						}
						_this.closest('.category-wrap').toggleClass('disabled');
						statusBarAddMessage(true, 'Статус категории "'+title+'" успешно изменен');
						showStatus(true);
					}
				}catch(e){
					showError(e + data, 'PATCH::/admin/category/'+id);
				}
			}
		});
	});

	$('.categories-list-wrap').on('click','a.drop',function(e){
		e.preventDefault();
		var _this = $(this);
		var title = $(this).closest('li').find('.title-wrap').text();
		showConfirm('Вы действительно хотите удалить категорию "'+title+'"?');
		$(document).on('customEvent', function(e){
			if(e.message === true){
				var id = _this.closest('li').attr('data-id');
				$.ajax({
					url:'/admin/category/'+id,
					type:'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/category/'+id);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								location.reload(true);
							}
						}catch(e){
							showError(e+data, 'DELETE::/admin/category/'+id);
						}
					}
				});
			}
		});
	});
});