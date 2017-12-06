$(document).ready(function(){
	$('.items-controls-wrap .button-wrap form[name=galleryUpload]').hide();
	$('.items-controls-wrap .button-wrap').append('' +
		'<input name="upload" type="file" multiple="multiple" style="display: none;">' +
		'<a class="button inline" href="#" data-type="add">Upload Files</a>'+
		'<a class="button inline" href="#" data-type="drop-all">Drop all unused</a>');

	//click "Upload Files"
	$('a[data-type=add]').click(function(e){
		e.preventDefault();
		$(this).closest('div').find('input[name=upload]').trigger('click');
	});

	//Save files
	$('input[name=upload]').change(function(){
		var count = $(this).prop('files').length;
		for(var i=0; i<count; i++) {
			var reader = new FileReader();
			reader.onload = function(e){
				$.ajax({
					url:	'/admin/settings/gallery/create',
					type:	'POST',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					data:	{
						upload:	e.target.result,
						ajax:	1
					},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'POST::/admin/settings/gallery/create');
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							switch(data.message){
								case 'success':
									statusBarAddMessage(true, data.text);
									showStatus(false);
									//add image
									$('.images-wrap').append('<div class="image-container">' +
										'<div class="image-wrap active">'+
											'<div class="drop-image">'+
												'<span class="fa fa-times"></span>'+
											'</div>'+
											'<img src="'+data.image.src+'" alt="">'+
										'</div>'+
										'<div class="image-info">'+
											'<p data-type="name">Name:'+
												'<span>'+data.image.name+'</span>'+
											'</p>'+
											'<p>Size: '+data.image.size+'</p>'+
											'<p>Is not used anywhere</p>'+
										'</div>'+
									'</div>');
								break;
								case 'error':
									statusBarAddMessage(false, data.text);
									showStatus(false);
								break;
							}
						}catch(e){
							showError(e + data, 'POST::/admin/settings/gallery/create');
						}
					}
				});
			}
			reader.readAsDataURL($(this).prop('files')[i]);
		}
	});

	//Drop all unused files
	$('a[data-type=drop-all]').click(function(e){
		e.preventDefault();
		var _this = $(this);
		showConfirm('Do you really want to delete all unused images?');
		$(document).off('customEvent').on('customEvent', function(e){
			if(e.message === true){
				var files = [];
				$('.images-wrap .image-container .active').each(function(){
					var temp = $(this).find('img').attr('src').match(/\/img\/(.*)/gi);
					files.push(temp[0]);
				});
				$.ajax({
					url:'/admin/settings/gallery/drop_unused',
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					data:{
						files: files
					},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/settings/gallery/drop_unused');
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								$('.images-wrap .image-container .active').closest('.image-container').remove();
								statusBarAddMessage(true, 'Unused images was successfully deleted');
								showStatus(false);
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/settings/gallery/drop_unused');
						}
					}
				});
			}
		});
	});

	$('.images-wrap').on('click', '.drop-image', function(){
		var _this = $(this);
		var image = _this.closest('.image-container').find('p[data-type=name] span').text();
		showConfirm('Do you really want to delete image '+image+'?');
		$(document).off('customEvent').on('customEvent', function(e){
			if(e.message === true){
				$.ajax({
					url:	'/admin/settings/gallery/'+image,
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/settings/gallery/'+image);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								_this.closest('.image-container').remove();
								statusBarAddMessage(true, 'Image '+image+' was successfully deleted');
								showStatus(false);
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/settings/gallery/'+image);
						}
					}
				});
			}
		});
	});
});