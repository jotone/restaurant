$(document).ready(function(){
	buildFixedNavMenu();

	if($('.preview-image').length > 0){
		$('.preview-image').append('' +
		'<div class="preview-image-controls">'+
			'<input name="image" style="display: none" type="file">'+
			'<button name="fakeSingleImageLoad" class="button" type="button">Обзор&hellip;</button>' +
			'<button name="galleryOverview" class="button" type="button">Галлерея&hellip;</button> ' +
			'<button name="clear" class="button" type="button">Очистить</button>'+
		'</div>');
	}

	$('button[name=save]').click(function(e){
		e.preventDefault();
		var validation = validate('form[name=category]');
		if(validation){
			var title = $('input[name=title]').val();

			var image = {
				src: '',
				type: 'file'
			};

			if($('.preview-image .preview-image-wrap img').length > 0){
				image.src = $('.preview-image .preview-image-wrap img').attr('src');
				if(typeof $('.preview-image .preview-image-wrap img').attr('data-type') != 'undefined'){
					image.type = $('.preview-image .preview-image-wrap img').attr('data-type')
				}
			}

			var meta = {
				title:	'',
				descr:	'',
				keywd:	''
			};
			meta.title = ($('input[name=meta_title]').length > 0)? $('input[name=meta_title]').val().trim(): '';
			meta.descr = ($('textarea[name=meta_description]').length > 0)? $('textarea[name=meta_description]').val().trim(): '';
			meta.keywd = ($('textarea[name=meta_keywords]').length > 0)? $('textarea[name=meta_keywords]').val().trim(): '';

			var seo = {
				need:	0,
				title:	'',
				text:	''
			};
			if($('input[name=need_seo]').length > 0){
				seo.need = ($('input[name=need_seo]').prop('checked') == true)? 1: 0;
				if(seo.need){
					seo.title = $('input[name=seo_title]').val().trim();
					seo.text = CKEDITOR.instances.seo_text.getData();
				}
			}
			var categoryType = $('input[name=category_type]').val();

			var id = $('input[name=id]').val().trim();
			var id = (id.length > 0)? '/'+id: '';
			var type = (id.length > 0)? 'PUT': 'POST';
			$.ajax({
				url:	'/admin/category'+id,
				type:	type,
				headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
				data:	{
					title:			title,
					slug:			$('input[name=slug]').val().trim(),
					refer_to:		$('select[name=refer_to]').val(),
					enabled:		($('input[name=enabled]').prop('checked') == true)? 1: 0,
					image:			JSON.stringify(image),
					text:			(typeof CKEDITOR.instances.text != 'undefined')? CKEDITOR.instances.text.getData(): '',
					need_seo:		seo.need,
					seo_title:		seo.title,
					seo_text:		seo.text,
					meta_title:		meta.title,
					meta_description:meta.descr,
					meta_keywords:	meta.keywd,
					category_type:	categoryType,
					ajax:			1
				},
				error:	function(jqXHR){
					showError(jqXHR.responseText, type+'::/admin/category'+id);
				},
				success:function(data){
					try{
						data = JSON.parse(data);
						if(data.message == 'success'){
							statusBarAddMessage(true, 'Категория "'+title+'" была успешно сохранена');
							showStatus(true);

							var confirmMessage = (id.length > 0)
								? 'Продолжить редактирование данной категории?'
								: 'Приступить к наполнению следующей категории?';
							showConfirm(confirmMessage);

							$(document).on('customEvent', function(e){
								if(e.message === true){
									if(id.length == 0){
										location.reload(true);
									}
								}else if(e.message === false){
									location = '/admin/category_types/'+categoryType+'/edit';
								}
							});
						}else if(data.message == 'error'){
							for(var i in data.errors){
								statusBarAddMessage(false, data.errors[i]);
								showStatus(true);
							}
						}
					}catch(e){
						showError(e+data, type+'::/admin/category'+id);
					}
				}
			});
		}else{
			goTo('.validate-wrap');
		}
	});
});