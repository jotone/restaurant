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

	$(document).find('noscript').remove();

	if($('.preview-image .preview-image-wrap').length > 0){
		$('.preview-image .preview-image-wrap').closest('.preview-image').append('' +
		'<div class="preview-image-controls">'+
			'<input name="logo" style="display: none" type="file">'+
			'<button name="fakeSingleImageLoad" class="button" type="button">Обзор&hellip;</button>' +
			'<button name="galleryOverview" class="button" type="button">Галерея&hellip;</button> ' +
			'<button name="clear" class="button" type="button">Очистить</button>'+
		'</div>');
	}

	$('.slider-wrap .slider-previews-wrap, .slider-wrap .slider-controls-wrap').show();

//Save restaurant
	$('button[name=save]').click(function(e){
		e.preventDefault();
		var validation = validate('form[name=restaurant]');

		if(validation){
			$('.overlay-popup').show();
			saveImages(function(){
				var title = $('input[name=title]').val();
				var id = $('input[name=id]').val().trim();
				var id = (id.length > 0)? '/'+id: '';
				var type = (id.length > 0)? 'PUT': 'POST';

				var data = {
					title:			title,
					phone:			$('input[name=phone]').val(),
					time_begin:		$('input[name=time_begin]').val(),
					time_finish:	$('input[name=time_finish]').val(),
					address:		$('textarea[name=address]').val(),
					coordinateX:	$('input[name=coordinateX]').val(),
					coordinateY:	$('input[name=coordinateY]').val(),
					has_delivery:	($('input[name=has_delivery]').prop('checked') == true)? 1: 0,
					has_wifi:		($('input[name=has_wifi]').prop('checked') == true)? 1: 0,
					has_parking:	($('input[name=has_parking]').prop('checked') == true)? 1: 0,
					likes:			$('input[name=likes]').val(),
					dislikes:		$('input[name=dislikes]').val(),
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

				//Logo
				data.logo_img = {
					src: '',
					type: 'file'
				};
				if($('#logo_img .preview-image-wrap img').length > 0){
					data.logo_img.src = $('#logo_img .preview-image-wrap img').attr('src');
					if(typeof $('#logo_img .preview-image-wrap img').attr('data-type') != 'undefined'){
						data.logo_img.type = $('#logo_img .preview-image-wrap img').attr('data-type')
					}
				}
				data.logo_img = JSON.stringify(data.logo_img);

				//SquareImage
				data.square_img = {
					src: '',
					type: 'file'
				};
				if($('#square_img .preview-image-wrap img').length > 0){
					data.square_img.src = $('#square_img .preview-image-wrap img').attr('src');
					if(typeof $('#square_img .preview-image-wrap img').attr('data-type') != 'undefined'){
						data.square_img.type = $('#square_img .preview-image-wrap img').attr('data-type')
					}
				}
				data.square_img = JSON.stringify(data.square_img);

				//LargeImage
				data.large_img = {
					src: '',
					type: 'file'
				};
				if($('#large_img .preview-image-wrap img').length > 0){
					data.large_img.src = $('#large_img .preview-image-wrap img').attr('src');
					if(typeof $('#large_img .preview-image-wrap img').attr('data-type') != 'undefined'){
						data.large_img.type = $('#large_img .preview-image-wrap img').attr('data-type')
					}
				}
				data.large_img = JSON.stringify(data.large_img);

				//Slider
				if($('.slider-wrap').length > 0){
					data.images = [];
					$('.slider-wrap .slider-content-wrap .slide-image-wrap').each(function(){
						var temp = {
							src:	$(this).find('img').attr('src'),
							alt:	$(this).find('input[name=altText]').val().trim(),
							type:	$(this).find('img').attr('data-type')
						};
						data.images.push(temp)
					});
				}

				//Menus
				data.menus = [];
				$('.group-lists li input[name="menus[]"]').each(function(){
					if($(this).prop('checked') == true){
						data.menus.push($(this).val());
					}
				});

				//Text
				if($('textarea[name=text]').length > 0){
					data.text = $('textarea[name=text]').val();
				}
				$.ajax({
					url:	'/admin/restaurant'+id,
					type:	type,
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					data:	data,
					error:	function(jqXHR){
						showError(jqXHR.responseText, type+'::/admin/restaurant'+id);
					},
					success: function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								statusBarAddMessage(true, 'Данные ресторана "'+title+'" успешно сохранены.');
								showStatus(true);

								var confirmMessage = (id.length > 0)
									? 'Вы хотите продолжить редактирование данного ресторана?'
									: 'Приступить к добавлению следующего ресторана?';
								showConfirm(confirmMessage);

								$(document).on('customEvent', function(e){
									if(e.message === true){
										if(id.length == 0){
											location.reload(true);
										}
									}else if(e.message === false){
										location = '/admin/restaurant';
									}
								});
							}else if(data.message == 'error'){
								for(var i in data.errors){
									statusBarAddMessage(false, data.errors[i]);
									showStatus(true);
								}
							}
						}catch(e){
							showError(e+data, type+'::/admin/restaurant'+id);
						}
					}
				});
			});
		}else{
			goTo('.validate-wrap');
		}
	});

//Drop restaurant
	$('.items-list a.drop').click(function(e){
		e.preventDefault();
		var _this = $(this);
		showConfirm('Вы действительно хотите удалить ресторан "'+$(this).attr('data-title')+'"?');
		$(document).on('customEvent', function(e){
			if(e.message === true){
				var id = _this.attr('data-id');
				$.ajax({
					url:	'/admin/restaurant/'+id,
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/restaurant/'+id);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								location.reload(true);
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/restaurant/'+id);
						}
					}
				})
			}
		});
	});
});