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

//Tags
	//Start search on keyup stop
	$('input[name=tags]').keyup( $.debounce(250, sendTagSearchRequest));

	//Choose tag
	$('.helper-wrap .list').on('click','li',function(){
		var tags = $(this).closest('.helper-wrap').find('input[name=tags]').val().trim().split(',').slice(0,-1).join(',');
		tags += (tags.length > 0)? ', '+$(this).text(): $(this).text();

		$(this).closest('.helper-wrap').find('input[name=tags]').val(tags);
		$('.helper-wrap .list').empty();
	});
// /Tags

//Save product
	$('button[name=save]').click(function(e){
		e.preventDefault();
		var validation = validate('form[name=products]');
		if(validation){
			saveImages(function(){
				var title = $('input[name=title]').val();
				var id = $('input[name=id]').val().trim();
				var id = (id.length > 0)? '/'+id: '';
				var type = (id.length > 0)? 'PUT': 'POST';

				var data = {
					title:		title,
					slug:		$('input[name=slug]').val(),
					price:		$('input[name=price]').val().trim(),
					enabled:	($('input[name=enabled]').prop('checked') == true)? 1: 0,
					ajax:		1
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
				//Vendor code
				if($('input[name=vendor_code]').length > 0){
					data.vendor_code = $('input[name=vendor_code]').val().trim()
				}
				//Quantity
				if($('input[name=quantity]').length > 0){
					data.quantity = $('input[name=quantity]').val().trim();
				}
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
				//Tags
				if($('input[name=tags]').length > 0){
					data.tags = $('input[name=tags]').val().trim();
				}
				//Characteristics
				if($(document).find('.characteristic-table').length > 0){
					data.characteristic = [];
					$(document).find('.characteristic-table tbody tr').each(function(){
						if($(this).find('input[name="rowCaption[]"]').val().length > 0){
							var temp = {
								key: $(this).find('input[name="rowCaption[]"]').val().trim(),
								val: $(this).find('input[name="rowValue[]"]').val().trim()
							};
							data.characteristic.push(temp);
						}
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
					url:	'/admin/products'+id,
					type:	type,
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					data:	data,
					error:	function(jqXHR){
						showError(jqXHR.responseText, type+'::/admin/products'+id);
					},
					success: function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								statusBarAddMessage(true, 'Product "'+title+'" was successfully saved');
								showStatus(true);

								var confirmMessage = (id.length > 0)
									? 'Do you want to continue edit product?'
									: 'Do you want to add next product?';
								showConfirm(confirmMessage);

								$(document).on('customEvent', function(e){
									if(e.message === true){
										if(id.length == 0){
											location.reload(true);
										}
									}else if(e.message === false){
										location = '/admin/products';
									}
								});
							}else if(data.message == 'error'){
								for(var i in data.errors){
									statusBarAddMessage(false, data.errors[i]);
									showStatus(true);
								}
							}
						}catch(e){
							showError(e+data, type+'::/admin/products'+id);
						}
					}
				});
			});
		}else{
			goTo('.validate-wrap');
		};
	});
//Drop product
	$('.items-list a.drop').click(function(e){
		e.preventDefault();
		var _this = $(this);
		showConfirm('Do you really want to delete product "'+$(this).attr('data-title')+'"?');
		$(document).on('customEvent', function(e){
			if(e.message === true){
				var id = _this.attr('data-id');
				$.ajax({
					url:	'/admin/products/'+id,
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/products/'+id);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								location.reload(true);
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/products/'+id);
						}
					}
				})
			}
		});
	})
});