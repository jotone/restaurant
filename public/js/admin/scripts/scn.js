$.browser = {};
$.browser.mozilla = /mozilla/.test(navigator.userAgent.toLowerCase()) && !/webkit/.test(navigator.userAgent.toLowerCase());
$.browser.webkit = /webkit/.test(navigator.userAgent.toLowerCase());
$.browser.opera = /opera/.test(navigator.userAgent.toLowerCase());
$.browser.msie = /msie/.test(navigator.userAgent.toLowerCase());

var scroller = $.browser.webkit ? "body": "html";

//Scroll-to-element function
function goTo(href){
	var target = $(href).offset().top-65;
	$(scroller).animate({scrollTop:target},500);
}

function str2url(str){
	str = str.toLowerCase();
	str = str.replace(/[^-a-z0-9_\/\.\#]/g, '_');
	return str;
}

//Check for $_GET parameters
function getRequest(){
	var params = window.location.search.replace('?','').split('&').reduce(
		function(prev, curr){
			var temp = curr.split('=');
			prev[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
			return prev;
		},{}
	);
	return params;
}
// /Check for $_GET parameters

//Fixed nav menu building
function buildFixedNavMenu(){
	$('.goto-block ul').empty();

	//search for fieldsets elements
	$(document).find('.workplace-wrap').find('fieldset').each(function(){
		var slug = str2url($(this).children('legend').text());
		$(this).attr('data-link',slug);
		$('.goto-block ul').append('<li data-link="'+slug+'">'+$(this).children('legend').text()+'</li>');
	});

	//click on menu item
	$(document).find('.goto-block').on('click','li',function(){
		var link = $(this).attr('data-link');
		goTo('fieldset[data-link='+link+']');
	});
}
// /Fixed nav menu building

//Error popup view
function showError(error, url){
	$('.error-popup .popup-caption span').text(url);
	$('.error-popup .error-body').empty().append(error);
	$('.error-popup').show(300);

	//Error popup close
	$('.error-popup').on('click', '.close-popup', function(){
		$(this).closest('.error-popup').hide(200);
	})
}
// /Error popup view

//Show confirm popup
function showConfirm(message){
	$('.confirm-popup .confirm-message-wrap').text(message);
	$('.confirm-popup').show();

	$('.confirm-popup').off('click').on('click','button[name=yes], button[name=no]',function(){
		var confirmResult = ($(this).attr('name') == 'yes')? true: false;
		$.event.trigger({
			type: 'customEvent',
			message: confirmResult
		});
		$(this).closest('.confirm-popup').hide();
	});
}
// /Show confirm popup

//Status bar
function statusBarAddMessage(success ,message){
	var className = (success)? 'success': 'error';
	$('footer .status-bar .messages').append('<p class="'+className+'">'+message+'</p>');
}
function showStatus(autohide){
	if(autohide){
		$('footer .status-bar').fadeIn(300).delay(5000).fadeOut(200)
	}else{
		$('footer .status-bar').fadeIn(300);
	}
}
// /Status bar

//Drop empty UL tags from categories list
function dropEmptyUl(){
	$('.categories-list-wrap li').each(function (){
		if($(this).find('ul').children('li').length < 1){
			$(this).find('ul').remove();
		}
	});
}
// /Drop empty UL tags from categories list

//Function gets filesize and convert it to Byte-KB-MB-GB view
function niceFilesize(size){
	var ext = 'Bytes';
	if(size > 1024){
		size = size/1024;
		ext = 'KB';
	}
	if(size > 1024){
		size = size/1024;
		ext = 'MB';
	}
	if(size > 1024){
		size = size/1024;
		ext = 'GB';
	}
	return size.toFixed(2)+' '+ext;
}

//Create slider image from uploaded files
function createSlideFromUploaded(e, file, _this){
	var image = {
		src: e.target.result,
		name: file.name,
		size: niceFilesize(file.size)
	};
	if(_this.closest('.slider-controls-wrap').find('.slider-content-wrap .slide-image-wrap').length > 0){
		_this.closest('.slider-controls-wrap').find('.slider-content-wrap .slide-image-wrap').removeClass('active')
	}

	_this.closest('.slider-controls-wrap').find('.slider-content-wrap').append(buildSliderImage(image, 'upload'));

	_this.closest('.slider-wrap').find('.slider-previews-wrap ul').append(buildSliderPreviewData(image));

	if(_this.closest('.slider-controls-wrap').find('.slider-content-wrap .slide-image-wrap').length > 0){
		_this.closest('.slider-controls-wrap').find('.slider-images').addClass('display');
	}
}

//Create slider image
function buildSliderImage(image, type){
	return '<div class="slide-image-wrap active">'+
		'<div class="slide-container">'+
			'<img src="'+image.src+'" alt="" data-type="'+type+'">'+
		'</div>'+
		'<div class="slide-alt-wrap">'+
			'<input name="altText" type="text" class="input-text" placeholder="Alternative text&hellip;">'+
			'<span class="drop-image-icon fa fa-times"></span>'+
		'</div>'+
	'</div>';
}
//Create slider preview data
function buildSliderPreviewData(image){
	return '<li>'+
		'<div class="controls">'+
			'<div class="preview-controls fa fa-angle-up col_1 tac"></div>'+
			'<div class="preview-controls fa fa-angle-down col_1 tac"></div>'+
		'</div>'+
		'<div class="preview-image">'+
			'<img src="'+image.src+'" alt="">'+
		'</div>'+
		'<div class="preview-data">'+
			'<p data-type="name">Filename: <span>'+image.name+'</span></p>'+
			'<p data-type="size">Size: <span>'+image.size+'</span></p>'+
			'<p data-type="alt">Alt: <span></span></p>'+
		'</div>' +
		'<div class="drop-preview-icon fa fa-times"></div>'+
	'</li>';
}

//Function using for search the similar tags
function sendTagSearchRequest(){
	var text = $('input[name=tags]').val().trim().split(',');
	var searched = text[text.length-1].trim();
	if(searched.length > 0){
		$.ajax({
			url:	'/admin/news/get_tag/'+searched,
			type:	'GET',
			error:	function(jqXHR){
				showError(jqXHR.responseText, 'GET::/admin/news/get_tag/'+searched);
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data.message == 'success'){
						$('.helper-wrap .list').empty();
						for(var i in data.result){
							$('.helper-wrap .list').append('<li>'+data.result[i]+'</li>')
						}
					}else if(data.message == 'nope'){
						$('.helper-wrap .list').empty();
					}
				}catch(e){
					showError(e+data, 'GET::/admin/news/get_tag/'+searched);
				}
			}
		});
	}
}

//Function used for saving images one-by-one
function saveImages(callback){
	if($('.slider-wrap .slider-content-wrap .slide-image-wrap').length > 0){
		var uploadCount = $('.slider-wrap .slider-content-wrap .slide-image-wrap img[data-type=upload]').length;
		var i = 0;
		if(uploadCount > 0){
			$('.slider-wrap .slider-content-wrap .slide-image-wrap').each(function(){
				var _this = $(this);
				if($(this).find('img').attr('data-type') == 'upload'){
					$.ajax({
						url:	'/admin/settings/gallery/create',
						type:	'POST',
						headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
						data:	{
							upload:	$(this).find('img').attr('src'),
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
										_this.find('img').attr('src',data.image.src).attr('data-type','file');
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
					}).done(function(){
						i++;
						if(uploadCount == i){
							callback();
						}
					});
				}
			});
		}else{
			callback();
		}
	}else{
		callback();
	}
}

//Function clears data for cloned slide
function refillSlide(cloned, _this){
	//Clear text inputs
	cloned.find('input:not([type=checkbox]):not([type=radio])').val('');
	//Uncheck checkboxes
	cloned.find('input[type=checkbox]').prop('checked',false);
	//Clear textareas
	cloned.find('textarea:not(.needCKE)').val('');
	//Clear image-case containers
	cloned.find('.preview-image .preview-image-wrap').empty();
	//Get position for current slide
	var position = _this.closest('.custom-slider-body').find('.custom-slider-content-wrap .custom-slider-content').length;
	//Clear wysiwyg
	cloned.find('.needCKE').each(function(){
		//Get cloned wysiwyg name
		var ckeDrop = 'cke_'+$(this).attr('name');
		//Drop cloned wysiwyg
		cloned.find('#'+ckeDrop).remove();
		//Creating new name for wysiwyg textarea
		//Divide textarea by separator "_"
		var textareaName = $(this).attr('name').split('_');
		//if name has separator
		if(textareaName.length > 1){
			textareaName[textareaName.length -1] = position;
			textareaName = textareaName.join('_');
		}else{
			textareaName = textareaName[0]+'_'+position;
		}
		//Replace textarea name
		$(this).attr('name',textareaName);
		//Initialize new wysiwyg editor
		if($.isEmptyObject(CKEDITOR.instances[textareaName])){
			CKEDITOR.replace(textareaName);
		}
	});
}

$(document).ready(function(){
//Seo block
	$(document).find('input[name=need_seo]:not(:checked)').closest('fieldset').find('.row-wrap:gt(0)').hide();
	$(document).find('input[name=need_seo]').change(function(){
		if($(this).prop('checked') == true){
			$(this).closest('fieldset').find('.row-wrap:gt(0)').show(200);
		}else{
			$(this).closest('fieldset').find('.row-wrap:gt(0)').hide(200);
		}
	});

//Status bar close click
	$('footer .status-bar .close-status-bar').click(function(){
		$('footer .status-bar .messages').empty();
		$(this).closest('.status-bar').hide();
	});
	$('.nav-icon-menu').click(function(){
		$(this).closest('.top-menu').children('ul').toggleClass('active');
	});

//CKE replace
	CKEDITOR.replaceAll('needCKE');

	$('form .needDatepick').datepicker({
		language: {
			days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
			daysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
			daysMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
			months: ['January','February','March','April','May','June', 'July','August','September','October','November','December'],
			monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
			today: 'Today',
			clear: 'Clear',
			dateFormat: 'dd/mm/yyyy',
			timeFormat: 'hh:ii aa',
			firstDay: 0
		}
	});

//Single image loader
	//Browse click
	$(document).on('click', 'button[name=fakeSingleImageLoad]',function(){
		$(this).closest('.preview-image-controls').find('input[name=image]').trigger('click');
	});

	//User pick the image
	$(document).on('change', '.preview-image input[name=image]', function(){
		var reader = new FileReader();
		var _this = $(this);
		reader.onload = function(e){
			_this.closest('.preview-image').find('.preview-image-wrap').empty().append('<img src="'+e.target.result+'" alt="" data-type="upload">');
		};
		reader.readAsDataURL(_this.prop('files')[0]);
	});

	//Clear image
	$(document).on('click', '.preview-image button[name=clear]', function(){
		$(this).closest('.preview-image').find('.preview-image-wrap').empty()
	});
// /Single image loader


//Categories controls
	$('.categories-list-wrap').on('click','.fa-angle-up',function(){
		if($(this).closest('li').prev().length > 0){
			var temp = $(this).closest('li').clone();
			$(this).closest('li').prev().before(temp);
			$(this).closest('li').remove();
			dropEmptyUl();
		}
	});

	$('.categories-list-wrap').on('click','.fa-angle-down',function(){
		if($(this).closest('li').next().length > 0){
			var temp = $(this).closest('li').clone();
			$(this).closest('li').next().after(temp);
			$(this).closest('li').remove();
			dropEmptyUl();
		}
	});

	$('.categories-list-wrap').on('click', '.fa-angle-left', function(){
		if($(this).closest('li').parent('ul').parent('li').length > 0){
			var temp = $(this).closest('li').clone();
			$(this).closest('li').parent('ul').parent('li').after(temp);
			$(this).closest('li').remove();
			dropEmptyUl();
		}
	});

	$('.categories-list-wrap').on('click', '.fa-angle-right', function(){
		if($(this).closest('li').prev().length > 0){
			var temp = $(this).closest('li').clone();
			if($(this).closest('li').prev().children('ul').length == 0) {
				$(this).closest('li').prev().append('<ul></ul>');
			}
			$(this).closest('li').prev().children('ul').append(temp);
			$(this).closest('li').remove();
			dropEmptyUl();
		}
	});
// /Categories controls

//Gallery image load
	//Image case view
	$(document).on('click','button[name=galleryOverview]',function(){
		var _this = $(this);
		$.ajax({
			url:	'/admin/settings/gallery/all',
			type:	'GET',
			headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
			error:	function(jqXHR){
				showError(jqXHR.responseText, 'GET::/admin/settings/gallery/all');
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data.message == 'success'){
						$('.overview-popup .popup-images').empty();
						for(var i in data.images){
							$('.overview-popup .popup-images').append('<div class="image-wrap">'+
								'<img src="/'+data.images[i]['src']+'" alt="">' +
								'<div class="details">' +
									'<p data-type="name">Name: <span>'+data.images[i]['name']+'</span></p>' +
									'<p data-type="size">Size: <span>'+data.images[i]['size']+'</span></p>' +
								'</div>' +
							'</div>');
						}

						if($('.overview-popup .popup-images .image-wrap:first').length > 0){
							$('.overview-popup .popup-images .image-wrap:first').append('<div class="hover-active fa fa-check"></div>');
						}

						$('.overview-popup').show(200);
						//if gallery used for single-image case
						if(_this.closest('.preview-image-controls').find('button[name=fakeSingleImageLoad]').length > 0){

							//image case
							$(document).find('.overview-popup .popup-images').off('click').on('click', '.image-wrap', function (){
								$(this).closest('.popup-images').find('.image-wrap .hover-active').remove();
								if($(this).find('.hover-active').length > 0){
									$(this).find('.hover-active').remove();
								}else{
									$(this).append('<div class="hover-active fa fa-check"></div>');
								}
							});

							//Apply button click
							$('.overview-popup button[name=addImageFromSaved]').click(function(){
								var image = $('.overview-popup .popup-images .hover-active').closest('.image-wrap').find('img').attr('src');
								_this.closest('.preview-image').find('.preview-image-wrap').empty().append('<img src="'+image+'" alt="" data-type="file">');
								$(this).closest('.overview-popup').hide(200);
							});

						//If gallery used for slider
						}else if(_this.closest('.slider-wrap').find('button[name=uploadSliderImages]').length > 0){

							//image case
							$(document).find('.overview-popup .popup-images').off('click').on('click', '.image-wrap', function (){
								if($(this).find('.hover-active').length > 0){
									$(this).find('.hover-active').remove();
								}else{
									$(this).append('<div class="hover-active fa fa-check"></div>');
								}
							});

							//Apply button click
							$('.overview-popup').off('click','button[name=addImageFromSaved]').on('click', 'button[name=addImageFromSaved]', function(){
								$(this).closest('.overview-popup').hide(200);
								$('.overview-popup .popup-images .hover-active').each(function(){
									var image = {
										src: $(this).closest('.image-wrap').find('img').attr('src'),
										name: $(this).closest('.image-wrap').find('.details p[data-type=name]').text(),
										size: $(this).closest('.image-wrap').find('.details p[data-type=size]').text()
									};
									if(_this.closest('.slider-controls-wrap').find('.slider-content-wrap .slide-image-wrap').length > 0){
										_this.closest('.slider-controls-wrap').find('.slider-content-wrap .slide-image-wrap').removeClass('active')
									}
									_this.closest('.slider-controls-wrap').find('.slider-content-wrap').append(buildSliderImage(image, 'file'));
									_this.closest('.slider-wrap').find('.slider-previews-wrap ul').append(buildSliderPreviewData(image));
									if(_this.closest('.slider-controls-wrap').find('.slider-content-wrap .slide-image-wrap').length > 0){
										_this.closest('.slider-controls-wrap').find('.slider-images').addClass('display');
									}
								});
							});
						}
					}
				}catch(e){
					showError(e+data, 'GET::/admin/settings/gallery/all');
				}
			}
		});
	});
	//Close gallery popup
	$('.overview-popup').on('click', '.close-popup', function(){
		$(this).closest('.overview-popup').hide(200);
	});
// /Gallery image load

//Slider
	//Click 'Browse'
	$(document).on('click','button[name=uploadSliderImages]', function(){
		$(this).closest('.slider-buttons').find('input[name=upload]').trigger('click');
	});

	//Files upload process
	$(document).on('change', '.slider-wrap .slider-buttons input[name=upload]', function(){
		var count = $(this).prop('files').length;
		var _this = $(this);
		for(var i=0; i<count; i++) {
			var reader = new FileReader();
			reader.onloadend = (function(file) {
				return function(e){
					createSlideFromUploaded(e, file, _this);
				};
			})($(this).prop('files')[i]);
			reader.readAsDataURL($(this).prop('files')[i]);
		}
	});

	//Slider left/right click
	$(document).on('click','.slider-control-elem',function(){
		var current = $(this).closest('.slider-images').find('.slider-content-wrap .active');
		current.removeClass('active');

		if($(this).children('span').hasClass('fa-angle-left')){
			if(current.prev().length > 0){
				current.prev().addClass('active');
			}else{
				$(this).closest('.slider-images').find('.slider-content-wrap .slide-image-wrap:last').addClass('active');
			}
		}else{
			if(current.next().length > 0){
				current.next().addClass('active');
			}else{
				$(this).closest('.slider-images').find('.slider-content-wrap .slide-image-wrap:first').addClass('active');
			}
		}
	});

	//Slider preview up/down click
	$(document).on('click','.slider-wrap .preview-controls', function(){
		//Current element position
		var elemIndex = $(this).closest('li').index();
		//Current slider image
		var image = $(this).closest('.slider-wrap').find('.slider-content-wrap .slide-image-wrap:eq('+elemIndex+')').clone();
		if($(this).hasClass('fa-angle-up')){
			if($(this).closest('li').prev().length > 0){
				//Move preview element
				var prevElem = $(this).closest('li').prev().clone();
				$(this).closest('li').prev().remove();
				$(this).closest('li').after(prevElem);
				//Move slider image
				$(this).closest('.slider-wrap').find('.slider-content-wrap .slide-image-wrap:eq('+elemIndex+')').remove();
				$(this).closest('.slider-wrap').find('.slider-content-wrap .slide-image-wrap:eq('+(elemIndex-1)+')').before(image);
			}
		}else{
			if($(this).closest('li').next().length > 0){
				//Move preview element
				var nextElem = $(this).closest('li').next().clone();
				$(this).closest('li').next().remove();
				$(this).closest('li').before(nextElem);
				//Move slider image
				$(this).closest('.slider-wrap').find('.slider-content-wrap .slide-image-wrap:eq('+elemIndex+')').remove();
				$(this).closest('.slider-wrap').find('.slider-content-wrap .slide-image-wrap:eq('+(elemIndex)+')').after(image);
			}
		}
	});

	//Add alt text to image
	$(document).on('keyup','.slider-wrap input[name=altText]',function(){
		var elemIndex = $(this).closest('.slide-image-wrap').index();
		var value = $(this).val();
		$(this).closest('.slider-wrap').find('.slider-previews-wrap li:eq('+elemIndex+') p[data-type=alt] span').text(value);
	});

	//Drop slide
	$(document).on('click', '.slider-wrap .drop-image-icon', function(){
		var elemIndex = $(this).closest('.slide-image-wrap').index();
		$(this).closest('.slider-wrap').find('.slider-previews-wrap li:eq('+elemIndex+')').remove();
		$(this).closest('.slide-image-wrap').removeClass('active');
		//If there is next slide
		if($(this).closest('.slide-image-wrap').next().length > 0){
			$(this).closest('.slide-image-wrap').next().addClass('active');
		//If there is no next slide -> make active first one
		}else if($(this).closest('.slider-content-wrap').find('.slide-image-wrap').length > 0){
			$(this).closest('.slider-content-wrap').find('.slide-image-wrap:eq(0)').addClass('active');
		}
		$(this).closest('.slide-image-wrap').remove();
	});

	//Drop preview slide
	$(document).on('click', '.slider-wrap .drop-preview-icon', function(){
		var elemIndex = $(this).closest('li').index();
		var image = $(this).closest('.slider-wrap').find('.slider-content-wrap .slide-image-wrap:eq('+elemIndex+')');
		image.removeClass('active');
		//If there is next slide
		if(image.next().length > 0){
			$(this).closest('.slide-image-wrap').next().addClass('active');
		//If there are no slides -> hide slider view
		}else if($(this).closest('.slider-wrap').find('.slider-content-wrap .slide-image-wrap').length > 0){
			$(this).closest('.slider-wrap').find('.slider-content-wrap .slide-image-wrap:eq(0)').addClass('active');
		}
		image.remove();
		$(this).closest('li').remove();
	});
// /Slider

//Characteristics table
	//remove
	$('.characteristic-table').on('click', 'a.delete', function(e){
		e.preventDefault();
		$(this).closest('tr').remove();
	});
	//add
	$(document).on('click', 'button[name=addCharacteristicsRow]', function(){
		$(this).closest('fieldset').find('.items-list tbody').append('<tr>'+
			'<td><a class="delete fa fa-times" href="#"></a></td>'+
			'<td><input name="rowCaption[]" type="text" class="input-text col_1" placeholder="Caption&hellip;"></td>'+
			'<td><input name="rowValue[]" type="text" class="input-text col_1" placeholder="Value&hellip;"></td>'+
		'</tr>');
	});
	$('.characteristic-table').closest('.row-wrap').find('.row-wrap').append('<button name="addCharacteristicsRow" type="button" class="button">Add Row</button>');

// /Characteristics table

//Custom slider
	//Add Slide
	$(document).on('click','.custom-slider-wrap .add-button',function(){
		var cloned = $(this).closest('.custom-slider-body').find('.custom-slider-content-wrap .custom-slider-content:first').clone();
		$(this).closest('.custom-slider-body').find('.custom-slider-content-wrap .custom-slider-content').removeClass('active');
		if(!cloned.hasClass('active')){
			cloned.addClass('active');
		}
		$(this).closest('.custom-slider-body').find('.custom-slider-content-wrap').append(cloned);
		refillSlide(cloned, $(this));
	});

	//Right/left click
	$(document).on('click','.custom-slider-wrap .custom-slider-controls',function(){
		var current = $(this).closest('.custom-slider-wrap').find('.custom-slider-content-wrap .active');
		current.removeClass('active');

		if($(this).children('span').hasClass('fa-angle-left')){
			if(current.prev().length > 0){
				current.prev().addClass('active');
			}else{
				$(this).closest('.custom-slider-wrap').find('.custom-slider-body .custom-slider-content:last').addClass('active')
			}
		}else{
			if(current.next().length > 0){
				current.next().addClass('active');
			}else{
				$(this).closest('.custom-slider-wrap').find('.custom-slider-body .custom-slider-content:first').addClass('active')
			}
		}
	});

	//Overview slider
	$(document).on('click', '.custom-slider-wrap .view-button', function(){
		var content = [];
		$(this).closest('.custom-slider-body').find('.custom-slider-content-wrap .custom-slider-content').each(function(){
			var temp = [];
			var maxElemCount = 8;
			$(this).find('.row-wrap').each(function(){
				if(maxElemCount > 0){
					if($(this).find('input[type=text]').length > 0){
						temp.push({
							type: 'text',
							title: $(this).find('span').text(),
							value: $(this).find('input[type=text]').val()
						});
					}
					if($(this).find('.preview-image-wrap img').length > 0){
						temp.push({
							type: 'image',
							title: 'Image',
							value: $(this).find('.preview-image-wrap img').attr('src')
						});
					}
					if($(this).find('input[type=number]').length > 0){
						temp.push({
							type: 'text',
							title: $(this).find('span').text(),
							value: $(this).find('input[type=number]').val()
						});
					}
					if($(this).find('textarea:not(.needCKE)').length > 0){
						temp.push({
							type: 'fulltext',
							title: $(this).find('textarea:not(.needCKE)').attr('name'),
							value: $(this).find('textarea:not(.needCKE)').val().substr(0,50)
						});
					}
					if($(this).find('textarea.needCKE').length > 0){
						var textareaName = $(this).find('textarea.needCKE').attr('name');
						temp.push({
							type: 'fulltext',
							title: 'text',
							value: CKEDITOR.instances[textareaName].getData().substr(0,50)
						});
					}
				}
				maxElemCount--;
			});
			content.push(temp)
		});

		var tag = '';

		for(var row in content){
			tag +=
			'<li>'+
				'<div class="controls">'+
					'<div class="preview-controls fa fa-angle-up col_1 tac"></div>'+
					'<div class="preview-controls fa fa-angle-down col_1 tac"></div>'+
				'</div>';
			for(var elem in content[row]){
				tag +='<div class="block-wrap">';
				switch(content[row][elem].type){
					case 'fulltext':
						tag +=
						'<div class="row-wrap">'+
							'<p>'+content[row][elem].title+': </p>'+
							'<div><code>'+content[row][elem].value.replace('/</gi','&lt;').replace('/>/gi','&gt;')+'</code></div>'+
						'</div>';
					break;
					case 'image':
						tag += '<img src="'+content[row][elem].value+'" alt="">';
					break;
					default:
						tag += '<p>'+content[row][elem].title+' :'+content[row][elem].value+'</p>';
				}
				tag += '</div>';
			}
			tag +=
			'</li>';
		}
		$('.custom-slider-preview-popup .custom-slider-preview-wrap ul').empty().append(tag);
		$('.custom-slider-preview-popup').show(200);
		var _this = $(this);

		//Preview up/down click
		$(document).off('click', '.custom-slider-preview-wrap .preview-controls').on('click', '.custom-slider-preview-wrap .preview-controls', function(){
			//Current element position
			var elemIndex = $(this).closest('li').index();
			//Current slider image
			var slide = _this.closest('.custom-slider-body').find('.custom-slider-content-wrap .custom-slider-content:eq('+elemIndex+')').clone();
			if($(this).hasClass('fa-angle-up')){
				if($(this).closest('li').prev().length > 0){
					//Move preview element
					var prevElem = $(this).closest('li').prev().clone();
					$(this).closest('li').prev().remove();
					$(this).closest('li').after(prevElem);
					//Move slide
					_this.closest('.custom-slider-body').find('.custom-slider-content-wrap .custom-slider-content:eq('+elemIndex+')').remove();
					_this.closest('.custom-slider-body').find('.custom-slider-content-wrap .custom-slider-content:eq('+(elemIndex-1)+')').before(slide);
				}
			}else{
				if($(this).closest('li').next().length > 0){
					//Move preview element
					var nextElem = $(this).closest('li').next().clone();
					$(this).closest('li').next().remove();
					$(this).closest('li').before(nextElem);
					//Move slide
					_this.closest('.custom-slider-body').find('.custom-slider-content-wrap .custom-slider-content:eq('+elemIndex+')').remove();
					_this.closest('.custom-slider-body').find('.custom-slider-content-wrap .custom-slider-content:eq('+elemIndex+')').after(slide);
				}
			}
		});
	});
	//Close custom slider preview popup
	$('.custom-slider-preview-popup').on('click', '.close-popup', function(){
		$(this).closest('.custom-slider-preview-popup').hide(200);
	});
// /Custom slider
});