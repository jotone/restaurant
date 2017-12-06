$(document).ready(function(){
	if($('.main-wrap>.items-list').length > 0){
		var getParams = getRequest();
		if(typeof getParams.sort_by == 'undefined'){
			var getParams = {
				sort_by: 'date_finish',
				dir: 'desc'
			}
		}
		$('.main-wrap>.items-list #'+getParams.sort_by+' .'+getParams.dir).addClass('active');
	}

	buildFixedNavMenu();

	$('.slider-wrap .slider-previews-wrap, .slider-wrap .slider-controls-wrap').show();

	$('.group-lists .fa').click(function(e){
		e.preventDefault();
		if($(this).hasClass('fa-plus')){
			$(this).removeClass('fa-plus').addClass('fa-times');
		}else{
			$(this).removeClass('fa-times').addClass('fa-plus');
		}
	});

//Save promo
	$('button[name=save]').click(function(e){
		e.preventDefault();
		var validation = validate('form[name=promo]');
		if(validation) {
			saveImages(function(){
				var images = [];
				$('.slider-wrap .slider-content-wrap .slide-image-wrap').each(function(){
					var temp = {
						src:	$(this).find('img').attr('src'),
						alt:	$(this).find('input[name=altText]').val().trim(),
						type:	$(this).find('img').attr('data-type')
					};
					images.push(temp);
				});

				var product_ids = [];
				$('.group-lists input[name="product_id[]"]').each(function(){
					if($(this).prop('checked') == true){
						product_ids.push($(this).val());
					}
				});

				var meta = {
					title:	'',
					descr:	'',
					keywd:	''
				};
				meta.title = ($('input[name=meta_title]').length > 0)? $('input[name=meta_title]').val().trim(): '';
				meta.descr = ($('textarea[name=meta_description]').length > 0)? $('textarea[name=meta_description]').val().trim(): '';
				meta.keywd = ($('textarea[name=meta_keywords]').length > 0)? $('textarea[name=meta_keywords]').val().trim(): '';

				var title = $('input[name=title]').val();
				var id = $('input[name=id]').val().trim();
				var id = (id.length > 0)? '/'+id: '';
				var type = (id.length > 0)? 'PUT': 'POST';

				var data = {
					title:			title,
					slug:			$('input[name=slug]').val(),
					date_start:		$('input[name=date_start]').val(),
					date_finish:	$('input[name=date_finish]').val(),
					discount:		$('input[name=discount]').val(),
					discount_type:	$('input[name=discount_type]:checked').val(),
					product_id:		product_ids,
					enabled:		($('input[name=enabled]').prop('checked') == true)? 1: 0,
					ajax:			1
				};
				//Description
				if(typeof CKEDITOR.instances.description != 'undefined'){
					data.description = CKEDITOR.instances.description.getData();
				}
				//Text
				if(typeof CKEDITOR.instances.text != 'undefined'){
					data.text = CKEDITOR.instances.text.getData();
				}
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
				//Meta data
				if($('input[name=meta_title]').length > 0){
					data.meta_title = $('input[name=meta_title]').val().trim();
					data.meta_description = $('textarea[name=meta_description]').val();
					data.meta_keywords = $('textarea[name=meta_keywords]').val();
				}
				//Seo Data
				if($('input[name=seo_title]').length > 0){
					data.seo_title = $('input[name=seo_title]').val();
					data.seo_text = CKEDITOR.instances.seo_text.getData();
				}
				$.ajax({
					url:	'/admin/promo'+id,
					type:	type,
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					data:	data,
					error:	function(jqXHR){
						showError(jqXHR.responseText, type+'::/admin/promo'+id);
					},
					success: function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								statusBarAddMessage(true, 'Promotion "'+title+'" was successfully saved');
								showStatus(true);

								var confirmMessage = (id.length > 0)
									? 'Do you want to continue edit promotion?'
									: 'Do you want to add next promotion?';
								showConfirm(confirmMessage);

								$(document).on('customEvent', function(e){
									if(e.message === true){
										if(id.length == 0){
											location.reload(true);
										}
									}else if(e.message === false){
										location = '/admin/promo';
									}
								});
							}else if(data.message == 'error'){
								for(var i in data.errors){
									statusBarAddMessage(false, data.errors[i]);
									showStatus(true);
								}
							}
						}catch(e){
							showError(e+data, type+'::/admin/promo'+id);
						}
					}
				})
			});
		}else{
			goTo('.validate-wrap');
		}
	});

//Drop promo
	$('.items-list a.drop').click(function(e){
		e.preventDefault();
		var _this = $(this);
		showConfirm('Do you really want to delete promotion "'+$(this).attr('data-title')+'"?');
		$(document).on('customEvent', function(e){
			if(e.message === true){
				var id = _this.attr('data-id');
				$.ajax({
					url:	'/admin/promo/'+id,
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/promo/'+id);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								location.reload(true);
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/promo/'+id);
						}
					}
				})
			}
		});
	})
});