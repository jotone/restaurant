function fillHTMLContent(container, ignoreClosest){
	if(typeof container == 'string'){
		container = $(container);
	}
	var content = [];
	//Fill data from inputs
	$(container).find('label>input').each(function(){
		if(($(this).closest('.custom-slider-wrap').length <1) || ignoreClosest){
			switch($(this).attr('type')){
				case 'checkbox':
					content.push({
						type:	$(this).attr('type'),
						key:	$(this).attr('name'),
						val:	($(this).prop('checked') == true)? 1: 0
					});
					break;
				case 'radio':
					if($(this).prop('checked') == true){
						content.push({
							type:	$(this).attr('type'),
							key:	$(this).attr('name'),
							val:	$(this).val()
						});
					};
					break;
				default:
					content.push({
						type:	$(this).attr('type'),
						key:	$(this).attr('name'),
						val:	$(this).val()
					});
			}
		}
	});
	//Fill data from CKEDITOR
	$(container).find('textarea.needCKE').each(function(){
		if(($(this).closest('.custom-slider-wrap').length <1) || ignoreClosest){
			var taName = $(this).attr('name');
			if(typeof CKEDITOR.instances[taName] != 'undefined'){
				if(taName != 'seo_text'){
					content.push({
						type:	'wysiwyg',
						key:	taName,
						val:	CKEDITOR.instances[taName].getData()
					});
				}
			}
		}
	});
	//Fill data from select
	$(container).find('select').each(function(){
		if(($(this).closest('.custom-slider-wrap').length <1) || ignoreClosest){
			content.push({
				type:	'select',
				key:	$(this).attr('name'),
				val:	$(this).val()
			});
		}
	});
	//Fill data from textarea
	$(container).find('textarea:not(.needCKE)').each(function(){
		if(($(this).closest('.custom-slider-wrap').length <1) || ignoreClosest){
			content.push({
				type:	'textarea',
				key:	$(this).attr('name'),
				val:	$(this).val()
			});
		}
	});
	//Fill data from single-image
	$(container).find('.preview-image>.preview-image-wrap>img').each(function(){
		if(($(this).closest('.custom-slider-wrap').length <1) || ignoreClosest){
			content.push({
				type:	'single-image',
				key:	str2url($(this).closest('fieldset').children('legend').text()),
				val:	$(this).attr('src'),
				move:	$(this).attr('data-type')
			});
		}
	});
	//Fill data from slider
	$(container).find('.slider-wrap').each(function(){
		if(($(this).closest('.custom-slider-wrap').length <1) || ignoreClosest){
			var temp = [];
			$(this).find('.slider-content-wrap .slide-image-wrap').each(function () {
				temp.push({
					src: $(this).find('img').attr('src'),
					alt: $(this).find('input[name=altText]').val(),
					type: (typeof $(this).find('img').attr('data-type') == 'undefined') ? 'file' : $(this).find('img').attr('data-type')
				})
			});
			content.push({
				type:	'slider',
				key:	str2url($(this).closest('fieldset').children('legend').text()),
				val:	temp
			});
		}
	});
	//Fill data from custom slider
	$(container).find('.custom-slider-wrap').each(function(){
		var temp = [];
		$(this).find('.custom-slider-content-wrap .custom-slider-content').each(function(){
			temp.push(fillHTMLContent($(this), true));
		});
		content.push({
			type:	'custom-slider',
			key:	str2url($(this).closest('fieldset').children('legend').text()),
			val:	temp
		});
	});
	return content;
}


function setHTMLContent(content, container, ignoreClosest){
	if(typeof container == 'string'){
		container = $(container);
	}
	for(var i in content){
		switch(content[i].type){
			case 'checkbox':
				if(content[i].val == 1){
					container.find('input[name='+content[i].key+']').prop('checked',true);
				}
			break;

			case 'custom-slider':
				var slider = container.find('fieldset[data-link="'+content[i].key+'"] .custom-slider-wrap');
				var slideHandler = slider.find('.custom-slider-body .custom-slider-content-wrap .custom-slider-content:first');
				for(var slideIter=0; slideIter < content[i].val.length; slideIter++){
					if(slideIter > 0){
						var cloned= slideHandler.clone();
						if(cloned.hasClass('active')){
							cloned.removeClass('active');
						}
						slider.find('.custom-slider-body .custom-slider-content-wrap').append(cloned);
						refillSlide(cloned, slider.find('.custom-slider-content-wrap'));
					}
					var slide = slider.find('.custom-slider-body .custom-slider-content-wrap .custom-slider-content:eq('+slideIter+')');
					setHTMLContent(content[i].val[slideIter], slide, true)
				}
			break;

			case 'radio':
				container.find('input[name='+content[i].key+'][value="'+content[i].val+'"]').prop('checked',true);
			break;

			case 'select':
				container.find('select[name='+content[i].key+']>option[value='+content[i].val+']').attr('selected','selected');
			break;

			case 'single-image':
				try{
					var image = JSON.parse(content[i].val);
				}catch(e){
					var image = content[i].val;
				}
				var temp = (ignoreClosest)
					? container.find('.preview-image-wrap')
					: container.find('fieldset[data-link="'+content[i].key+'"] .preview-image-wrap');
				temp.append('<img src="'+image.src+'" alt="" data-type="file">');
			break;

			case 'slider':
				var sliderImages = container.find('fieldset[data-link="'+content[i].key+'"] .slider-images .slider-content-wrap');
				var sliderPreview = container.find('fieldset[data-link="'+content[i].key+'"] .slider-previews-wrap ul');

				for(var slideIter in content[i].val){
					var slideData = buildSliderImage(content[i].val[slideIter], 'file');
					var slidePreview = buildSliderPreviewData(content[i].val[slideIter]);
					sliderImages.append(slideData);
					sliderPreview.append(slidePreview);
				}
				if(content[i].val.length > 0){
					sliderImages.find('.slide-image-wrap').removeClass('active');
					sliderImages.find('.slide-image-wrap:first').addClass('active');
					sliderImages.closest('.slider-images').css({display: 'flex'});
				}
			break;

			case 'textarea':
				container.find('textarea[name='+content[i].key+']').val(content[i].val);
			break;

			case 'wysiwyg':
				if(typeof CKEDITOR.instances[content[i].key] == 'undefined'){
					CKEDITOR.replace(content[i].key)
				}
				CKEDITOR.instances[content[i].key].setData(content[i].val);
			break;

			default:
				container.find('input[name='+content[i].key+']').val(content[i].val);
		}
	}
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


	if($('input[name=id]').length && ($('input[name=id]').val().length > 0)){
		var id = $('input[name=id]').val();
		$.ajax({
			url:	'/admin/page_content/'+id,
			type:	'GET',
			error:	function(jqXHR){
				showError(jqXHR.responseText, 'GET::/admin/page_content/'+id);
			},
			success: function(data){
				try{
					data = JSON.parse(data);
					if(data.message == 'success'){
						setHTMLContent(data.content, '#htmlContent', false);
					}
				}catch(e){
					showError(e+data, 'GET::/admin/page_content/'+id);
				}
			}
		})
	}

	//Change template
	$('select[name=template]').change(function(){
		$(this).closest('form[name=changeTemplate]').submit();
	});

//Save Page
	$('button[name=save]').click(function(e){
		e.preventDefault();
		var validation = validate('form[name=pages]');
		if(validation){
			var content = fillHTMLContent('#htmlContent', false);

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

			var title = $('input[name=title]').val();
			var id = $('input[name=id]').val().trim();
			var id = (id.length > 0)? '/'+id: '';
			var type = (id.length > 0)? 'PUT': 'POST';

			$.ajax({
				url:	'/admin/pages'+id,
				type:	type,
				headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
				data:	{
					template_id:	$('input[name=template_id]').val(),
					title:			title,
					slug:			$('input[name=slug]').val(),
					enabled:		($('input[name=enabled]').prop('checked') == true)? 1: 0,
					meta_title:			meta.title,
					meta_description:	meta.descr,
					meta_keywords:		meta.keywd,
					need_seo:		seo.need,
					seo_title:		seo.title,
					seo_text:		seo.text,
					content:		JSON.stringify(content),
					ajax:	1
				},
				error:	function(jqXHR){
					showError(jqXHR.responseText, type+'::/admin/pages'+id);
				},
				success: function(data){
					try{
						data = JSON.parse(data);
						if(data.message == 'success'){
							statusBarAddMessage(true, 'Page "'+title+'" was successfully saved');
							showStatus(true);

							var confirmMessage = (id.length > 0)
								? 'Продолжить редактирование данной страницы?'
								: 'Приступить к наполнению следующей страницы?';
							showConfirm(confirmMessage);

							$(document).on('customEvent', function(e){
								if(e.message === true){
									if(id.length == 0){
										location.reload(true);
									}
								}else if(e.message === false){
									location = '/admin/pages';
								}
							});
						}else if(data.message == 'error'){
							for(var i in data.errors){
								statusBarAddMessage(false, data.errors[i]);
								showStatus(true);
							}
						}
					}catch(e){
						showError(e+data, type+'::/admin/pages'+id);
					}
				}
			});
		}else{
			goTo('.validate-wrap');
		}
	});

//Page drop
	$('.items-list a.drop').click(function(e){
		e.preventDefault();
		var _this = $(this);
		showConfirm('Вы действительно хотите удалить страницу "'+$(this).attr('data-title')+'"?');
		$(document).on('customEvent', function(e){
			if(e.message === true){
				var id = _this.attr('data-id');
				$.ajax({
					url:	'/admin/pages/'+id,
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/pages/'+id);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								location.reload(true);
							}else if(data.message == 'error'){
								for(var i in data.errors){
									statusBarAddMessage(false, data.errors[i]);
									showStatus(true);
								}
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/pages/'+id);
						}
					}
				})
			}
		});
	});
});