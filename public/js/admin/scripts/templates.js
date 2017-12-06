function insetrAtCaret(text){
	var selStart = $('textarea[name=html_content]').prop('selectionStart');
	var selEnd = $('textarea[name=html_content]').prop('selectionEnd');
	if(selStart != selEnd){
		var replaceText = $('textarea[name=html_content]').val().substr(selStart, selEnd-selStart);
		$('textarea[name=html_content]').val($('textarea[name=html_content]').val().replace(replaceText, text));
	}else{
		var start = $('textarea[name=html_content]').val().substr(0, selStart);
		var end = $('textarea[name=html_content]').val().substr(selStart, $('textarea[name=html_content]').val().length - selStart);
		$('textarea[name=html_content]').val(start+text+end);
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

	$(document).off('click','.custom-slider-wrap .add-button');
	$(document).off('click', '.custom-slider-wrap .view-button');
	$(document).off('click','button[name=uploadSliderImages]');
	$(document).off('click','button[name=galleryOverview]');
	$(document).off('click', 'button[name=fakeSingleImageLoad]');

	//Inset fieldset
	$('button[name=fieldset]').click(function(){
		var tag =	'<fieldset>'+"\n"+'<legend></legend>'+"\n"+"\n"+'</fieldset>'+"\n";
		insetrAtCaret(tag);
	});
	//Insert Row Div
	$('button[name=row]').click(function(){
		var tag = '<div class="row-wrap">'+"\n"+"\n"+'</div>'+"\n";
		insetrAtCaret(tag);
	});
	//Insert input type text
	$('button[name=inputText]').click(function(){
		var tag =
		'<label>'+"\n"+
			'<input type="text" name="" class="input-text" placeholder="&hellip;">'+"\n"+
			'<span></span>'+"\n"+
		'</label>'+"\n";
		insetrAtCaret(tag);
	});
	//Insert input type number
	$('button[name=inputNumber]').click(function(){
		var tag =
		'<label>'+"\n"+
			'<input type="number" name="" class="input-text" placeholder="&hellip;">'+"\n"+
			'<span></span>'+"\n"+
		'</label>'+"\n";
		insetrAtCaret(tag);
	});
	//Insert input type checkbox
	$('button[name=inputCheckbox]').click(function(){
		var tag =
		'<label>'+"\n"+
			'<input type="checkbox" name="" class="chbox-input">'+"\n"+
			'<span></span>'+"\n"+
		'</label>'+"\n";
		insetrAtCaret(tag);
	});
	//Insert input type radio
	$('button[name=inputRadio]').click(function(){
		var tag = '<input type="radio" name="" class="chbox-input" value="">'+"\n"+'<span></span>'+"\n";
		insetrAtCaret(tag);
	});
	//Insert select
	$('button[name=select]').click(function(){
		var tag =
		'<label>'+"\n"+
			'<select name="">'+"\n"+
				'<option value=""></option>'+"\n"+
			'</select>'+"\n"+
			'<span></span>'+"\n"+
		'</label>'+"\n";
		insetrAtCaret(tag);
	});
	//Insert Wysiwyg
	$('button[name=wysiwyg]').click(function(){
		insetrAtCaret('<textarea name="" class="text-area needCKE" data-name=""></textarea>'+"\n");
	});
	//Insert textarea
	$('button[name=textarea]').click(function(){
		insetrAtCaret('<textarea name="" class="text-area"></textarea>'+"\n");
	});
	//Insert single image select
	$('button[name=singleImage]').click(function(){
		var tag =
		'<div class="preview-image">'+"\n"+
			'<div class="preview-image-wrap">'+"\n"+
			'</div>'+"\n"+
			'<div class="preview-image-controls">'+"\n"+
				'<input name="image" style="display: none" type="file">'+"\n"+
				'<button name="fakeSingleImageLoad" class="button" type="button">Browse&hellip;</button>'+"\n"+
				'<button name="galleryOverview" class="button" type="button">Gallery&hellip;</button>'+"\n"+
				'<button name="clear" class="button" type="button">Clear</button>'+"\n"+
			'</div>'+"\n"+
		'</div>'+"\n";
		insetrAtCaret(tag);
	});
	//Insert single image slider
	$('button[name=slider]').click(function(){
		var tag =
		'<div class="slider-wrap">'+"\n"+
			'<div class="slider-controls-wrap" style="display: block;">'+"\n"+
				'<div class="slider-images">'+"\n"+
					'<div class="slider-control-elem"><span class="fa fa-angle-left"></span></div>'+"\n"+
					'<div class="slider-content-wrap"></div>'+"\n"+
					'<div class="slider-control-elem"><span class="fa fa-angle-right"></span></div>'+"\n"+
				'</div>'+"\n"+
				'<div class="slider-buttons">'+"\n"+
					'<input name="upload" style="display: none" multiple="multiple" type="file">'+"\n"+
					'<button name="uploadSliderImages" type="button" class="button">Browse…</button>'+"\n"+
					'<button name="galleryOverview" type="button" class="button">Gallery…</button>'+"\n"+
				'</div>'+"\n"+
			'</div>'+"\n"+
			'<div class="slider-previews-wrap" style="display: block;">'+"\n"+
				'<ul>'+"\n"+
				'</ul>'+"\n"+
			'</div>'+"\n"+
		'</div>'+"\n";
		insetrAtCaret(tag);
	});
	$('button[name=customSlider]').click(function(){
		var tag =
		'<div class="row-wrap col_1">'+"\n"+
			'<div class="custom-slider-wrap">'+"\n"+
				'<div class="custom-slider-controls"><span class="fa fa-angle-left"></span></div>'+"\n"+
				'<div class="custom-slider-body">'+"\n"+
					'<div class="custom-slider-preview-controlls">'+"\n"+
						'<div class="add-button" title="Add Slide"><span class="fa fa-plus-circle"></span></div>'+"\n"+
						'<div class="view-button" title="View Slider"><span class="fa fa-eye"></span></div>'+"\n"+
					'</div>'+"\n"+
					'<div class="custom-slider-content-wrap">'+"\n"+
						'<div class="custom-slider-content active">'+"\n"+"\n"+'</div>'+"\n"+
					'</div>'+"\n"+
				'</div>'+"\n"+
				'<div class="custom-slider-controls"><span class="fa fa-angle-right"></span></div>'+"\n"+
			'</div>'+"\n"+
		'</div>';
		insetrAtCaret(tag);
	});

//Save template
	$('button[name=save]').click(function(e){
		e.preventDefault();
		var validation = validate('form[name=templates]');
		if(validation){
			var title = $('input[name=title]').val();
			var id = $('input[name=id]').val().trim();
			var id = (id.length > 0)? '/'+id: '';
			var type = (id.length > 0)? 'PUT': 'POST';

			$.ajax({
				url:	'/admin/pages/templates'+id,
				type:	type,
				headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
				data:	{
					title:		title,
					enabled:	($('input[name=enabled]').prop('checked') == true)? 1: 0,
					html_content:$('textarea[name=html_content]').val(),
					ajax:		1
				},
				error:	function(jqXHR){
					showError(jqXHR.responseText, type+'::/admin/pages/templates'+id);
				},
				success: function(data){
					try{
						data = JSON.parse(data);
						if(data.message == 'success'){
							statusBarAddMessage(true, 'Template "'+title+'" was successfully saved');
							showStatus(true);

							var confirmMessage = (id.length > 0)
								? 'Do you want to continue edit template?'
								: 'Do you want to add next template?';
							showConfirm(confirmMessage);

							$(document).on('customEvent', function(e){
								if(e.message === true){
									if(id.length == 0){
										location.reload(true);
									}
								}else if(e.message === false){
									location = '/admin/pages/templates';
								}
							});
						}else if(data.message == 'error'){
							for(var i in data.errors){
								statusBarAddMessage(false, data.errors[i]);
								showStatus(true);
							}
						}
					}catch(e){
						showError(e+data, type+'::/admin/pages/templates'+id);
					}
				}
			});
		}
	});

//Drop template
	$('.items-list a.drop').click(function(e){
		e.preventDefault();
		var _this = $(this);
		showConfirm('Do you really want to delete template "'+$(this).attr('data-title')+'"?');
		$(document).on('customEvent', function(e){
			if(e.message === true){
				var id = _this.attr('data-id');
				$.ajax({
					url:	'/admin/pages/templates/'+id,
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/pages/templates/'+id);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								location.reload(true);
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/pages/templates/'+id);
						}
					}
				})
			}
		});
	});
});