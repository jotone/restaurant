function sendAjax() {

	var title = $('input[name=title]').val();
	var id = $('input[name=id]').val().trim();
	var id = (id.length > 0)? '/'+id: '';
	var type = (id.length > 0)? 'PUT': 'POST';

	var data = {
		title:			$('input[name=title]').val(),
		is_recommended:	($('input[name=is_recommended]').prop('checked') == true)? 1: 0,
		enabled:		($('input[name=enabled]').prop('checked') == true)? 1: 0,
		price:			$('input[name=price]').val(),
		dish_weight:	$('input[name=dish_weight]').val(),
		calories:		$('input[name=calories]').val(),
		cooking_time:	$('input[name=cooking_time]').val(),
		model_3d:		$('input[name=model_3d]').closest('.row-wrap').find('span').text(),
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
	//Text
	if(typeof CKEDITOR.instances.text != 'undefined'){
		data.text = CKEDITOR.instances.text.getData();
	}
	$.ajax({
		url:		'/admin/restaurant/menu/dish'+id,
		method:		type,
		headers:	{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
		data:		data,
		error:		function(jqXHR){
			showError(jqXHR.responseText, type+'::/admin/restaurant/menu/dish'+id);
		},
		success:	function(data){
			try{
				data = JSON.parse(data);
				if(data.message == 'success'){
					statusBarAddMessage(true, 'Блюдо "'+title+'" было успешно сохранено.');
					showStatus(true);

					var confirmMessage = (id.length > 0)
						? 'Продолжить редактирование блюда?'
						: 'Перейти к добавлению следующего блюда?';
					showConfirm(confirmMessage);

					$(document).on('customEvent', function(e){
						if(e.message === true){
							if(id.length == 0){
								location.reload(true);
							}
						}else if(e.message === false){
							location = '/admin/restaurant/menu/dish';
						}
					});
				}
			}catch(e){
				showError(e+data, type+'::/admin/restaurant/menu/dish'+id);
			}
		}
	});
}

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

	$('.slider-wrap .slider-previews-wrap, .slider-wrap .slider-controls-wrap').show();

	if($('.preview-image .preview-image-wrap').length > 0){
		$('.preview-image .preview-image-wrap').closest('.preview-image').append('' +
		'<div class="preview-image-controls">'+
			'<input name="logo" style="display: none" type="file">'+
			'<button name="fakeSingleImageLoad" class="button" type="button">Обзор&hellip;</button>' +
			'<button name="galleryOverview" class="button" type="button">Галерея&hellip;</button> ' +
			'<button name="clear" class="button" type="button">Очистить</button>'+
		'</div>');
	}

	$('button[name=file_3d]').click(function(){
		$(this).closest('.row-wrap').find('input[name=model_3d]').trigger('click');
	});

	$('input[name=model_3d]').change(function(){
		var _this = $(this);
		var reader = new FileReader();
		reader.onloadend = (function(file) {
			return function(e){
				_this.closest('.row-wrap').find('span').text(file.name);
			};
		})($(this).prop('files')[0]);
		reader.readAsDataURL($(this).prop('files')[0]);
	});

//Save dish
	$('button[name=save]').click(function(e){
		e.preventDefault();
		var validation = validate('form[name=mealDish]');
		if(validation){
			saveImages(function() {
				if($('input[name=model_3d]').prop('files').length > 0){
					var formData = new FormData();
					formData.append('file', $('input[name=model_3d]').prop('files')[0]);
					$.ajax({
						url:		'/admin/restaurant/menu/dish/create_model_file',
						method:		'POST',
						processData:false,
						contentType:false,
						headers:	{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
						data:		formData,
						error:		function(jqXHR){
							showError(jqXHR.responseText, 'POST::/admin/restaurant/menu/dish/create_model_file');
						},
						success:	function(data){
							try{
								data = JSON.parse(data);
								switch(data.message){
									case 'success':
										statusBarAddMessage(true, data.text);
										showStatus(false);
										$('input[name=model_3d]').closest('.row-wrap').find('span').text(data.file);
										sendAjax();
										break;
									case 'error':
										statusBarAddMessage(false, data.text);
										showStatus(false);
										break;
								}
							}catch(e){
								showError(e+data, 'POST::/admin/restaurant/menu/dish/create_model_file');
							}
						}
					});
				}else{
					sendAjax()
				}
			});
		}else{
			goTo('.validate-wrap');
		}
	});

//Drop dish
	$('.items-list a.drop').click(function(e){
		e.preventDefault();
		var _this = $(this);
		showConfirm('Вы действительно хотите удалить блюдо "'+$(this).attr('data-title')+'"?');
		$(document).on('customEvent', function(e){
			if(e.message === true){
				var id = _this.attr('data-id');
				$.ajax({
					url:	'/admin/restaurant/menu/dish/'+id,
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/restaurant/menu/dish/'+id);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								location.reload(true);
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/restaurant/menu/dish/'+id);
						}
					}
				})
			}
		});
	});
});