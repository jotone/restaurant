$(document).ready(function(){
	if($('.main-wrap>.items-list').length > 0){
		var getParams = getRequest();
		if(typeof getParams.sort_by == 'undefined'){
			var getParams = {
				sort_by: 'title',
				dir: 'asc'
			}
		}
		$('.main-wrap>.items-list #'+getParams.sort_by+' .'+getParams.dir).addClass('active');
	}

	buildFixedNavMenu();

	$('.group-lists .fa').click(function(e){
		e.preventDefault();
		if($(this).hasClass('fa-plus')){
			$(this).removeClass('fa-plus').addClass('fa-times');
		}else{
			$(this).removeClass('fa-times').addClass('fa-plus');
		}
	});

//Save meal menu
	$('button[name=save]').click(function(e){
		e.preventDefault();
		var validation = validate('form[name=mealDish]');
		if(validation){
			var title = $('input[name=title]').val();
			var id = $('input[name=id]').val().trim();
			var id = (id.length > 0)? '/'+id: '';
			var type = (id.length > 0)? 'PUT': 'POST';

			var data = {
				title:			title,
				restaurant_id:	$('select[name=restaurant_id]').val(),
				enabled:		($('input[name=enabled]').prop('checked') == true)? 1: 0,
				ajax:			1
			};

			//Category ID
			if($('select[name=category]').length > 0){
				data.category = [$('select[name=category]').val()];
			}else if($('.checkbox-group-wrap').length > 0){
				data.category = [];
				$('.checkbox-group-wrap input[name="category[]"]:checked').each(function(){
					data.category.push($(this).val());
				})
			}

			data.dish_ids = [];
			$('.group-lists input[name="dish_ids[]"]').each(function(){
				if($(this).prop('checked') == true){
					data.dish_ids.push($(this).val());
				}
			});
			data.dish_ids = JSON.stringify(data.dish_ids);

			$.ajax({
				url:		'/admin/restaurant/menu'+id,
				method:		type,
				headers:	{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
				data:		data,
				error:		function(jqXHR){
					showError(jqXHR.responseText, type+'::/admin/restaurant/menu'+id);
				},
				success:	function(data){
					try{
						data = JSON.parse(data);
						if(data.message == 'success'){
							statusBarAddMessage(true, 'Меню "'+title+'" было успешно сохранено.');
							showStatus(true);

							var confirmMessage = (id.length > 0)
								? 'Продолжить редактирование меню?'
								: 'Перейти к добавлению следующего меню?';
							showConfirm(confirmMessage);

							$(document).on('customEvent', function(e){
								if(e.message === true){
									if(id.length == 0){
										location.reload(true);
									}
								}else if(e.message === false){
									location = '/admin/restaurant/menu';
								}
							});
						}
					}catch(e){
						showError(e+data, type+'::/admin/restaurant/menu'+id);
					}
				}
			});
		}else{
			goTo('.validate-wrap');
		}
	});

//Drop meal menu
	$('.items-list a.drop').click(function(e){
		e.preventDefault();
		var _this = $(this);
		showConfirm('Вы действительно хотите удалить меню "'+$(this).attr('data-title')+'"?');
		$(document).on('customEvent', function(e){
			if(e.message === true){
				var id = _this.attr('data-id');
				$.ajax({
					url:	'/admin/restaurant/menu/'+id,
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/restaurant/menu/'+id);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								location.reload(true);
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/restaurant/menu/'+id);
						}
					}
				})
			}
		});
	});
});