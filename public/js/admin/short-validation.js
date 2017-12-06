function displayErrorMessage(selector, position, title, text){
	var top = position.top - 30;
	var elIndex = $(document).find('.validate-wrap').length;
	var errorBlock = '<div class="validate-wrap" style="top:'+top+'px; left: '+position.left+'px" data-index="'+elIndex+'">' +
		'<div class="error-title">'+title+'</div>'+
		'<div class="error-text">'+text+'</div>'+
		'<div class="error-close">X</div>'+
		'</div>';
	$(selector).before(errorBlock);
	var topShift = top - $(document).find('.validate-wrap[data-index='+elIndex+']').height();
	$(document).find('.validate-wrap[data-index='+elIndex+']').css({'top': topShift});
}

function checkForm(selector){
	var error = false;
	$(selector).find('input[required], textarea[required], select[required]').each(function(){
		var displayErrBorder = false;
		var displayErrMsg = false;

		if(typeof $(this).attr('name') == 'undefined'){
			displayErrBorder = true;
			displayErrMsg = true;
			var obj = {
				title: 'This field hasn\'t attribute name.',
				text: 'Ask site administrator to solve this problem.'
			};
		}

		switch($(this)[0].localName){
			case 'input':
				var type = (typeof $(this).attr('type') != 'undefined')? $(this).attr('type'): '';
				switch(type){
					case 'button':
					case 'hidden':
					case 'image':
					case 'reset':
					case 'submit':break;

					case 'checkbox':
						if($(this).prop('checked') == false){
							displayErrMsg = true;
							var obj = {
								title: 'This field must be checked.',
								text: 'You must check it to continue.'
							};
						}
					break;

					case 'color':
						if($(this).val().length < 1){
							displayErrBorder = true;
							displayErrMsg = true;
							var obj = {
								title: 'Color picker is empty.',
								text: 'Click this element and pick the color.'
							};
						}
					break;

					case 'date':
					case 'datetime':
					case 'datetime-local':
						if($(this).val().length < 1){
							displayErrBorder = true;
							displayErrMsg = true;
							var obj = {
								title: 'This field is empty.',
								text: 'Enter the date in this field.'
							};
						}
					break;

					case 'email':
						var showError = false;
						if($(this).val().length < 1){
							showError = true;
							var obj = {
								title: 'This field is empty.',
								text: 'This element must contain email.'
							};
						}else{
							var regExp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
							if(!regExp.test($(this).val())){
								showError = true;
								var obj = {
									title: 'Email is invalid.',
									text: 'The email address does not match a valid address template.'
								};
							}
						}
						if(showError){
							displayErrBorder = true;
							displayErrMsg = true;
						}
					break;

					case 'file':
						if($(this).prop('files').length < 1){
							displayErrBorder = true;
							displayErrMsg = true;
							var obj = {
								title: 'File is missing.',
								text: 'Please, attach a file.'
							};
						}
					break;

					case 'number':
						var showError = false;
						if($(this).val().length < 1){
							showError = true;
							var obj = {
								title: 'This field is empty.',
								text: 'This element must contain some text.'
							};
						}else{
							if(typeof $(this).attr('min') != 'undefined'){
								var val = parseFloat($(this).val());
								var compared = parseFloat($(this).attr('min'));
								if(val < compared){
									showError = true;
									var obj = {
										title: 'Invalid field value.',
										text: 'This field value must be greater than '+$(this).attr('min')+'.'
									};
								}
							}
							if(typeof $(this).attr('max') != 'undefined'){
								var val = parseFloat($(this).val());
								var compared = parseFloat($(this).attr('max'));
								if(val > compared){
									showError = true;
									var obj = {
										title: 'Invalid field value.',
										text: 'This field value must be lower than '+$(this).attr('max')+'.'
									};
								}
							}
						}
						if(showError){
							displayErrBorder = true;
							displayErrMsg = true;
						}
					break;

					case 'radio':
						if(typeof $(this).attr('name') != 'undefined'){
							if($(this).closest('form').find('input[name='+$(this).attr('name')+']:checked').length < 1){
								displayErrMsg = true;
								var obj = {
									title: 'One of this radio-buttons must be checked.',
									text: 'Please, check one of them.'
								};
							}
						}
					break;

					case 'range':
						var showError = false;
						if(typeof $(this).attr('min') != 'undefined'){
							var val = parseFloat($(this).val());
							var compared = parseFloat($(this).attr('min'));
							if(val < compared){
								showError = true;
								var obj = {
									title: 'Invalid field value.',
									text: 'This field value must be greater than '+$(this).attr('min')+'.'
								};
							}
						}
						if(typeof $(this).attr('max') != 'undefined'){
							var val = parseFloat($(this).val());
							var compared = parseFloat($(this).attr('max'));
							if(val > compared){
								showError = true;
								var obj = {
									title: 'Invalid field value.',
									text: 'This field value must be lower than '+$(this).attr('min')+'.'
								};
							}
						}
						if(showError){
							displayErrBorder = true;
							displayErrMsg = true;
						}
					break;

					case 'tel':
						if($(this).val().length < 1){
							displayErrBorder = true;
							displayErrMsg = true;
							var obj = {
								title: 'This field is empty.',
								text: 'Enter the phone number in this field.'
							};
						}
					break;

					case 'time':
						if($(this).val().length < 1){
							displayErrBorder = true;
							displayErrMsg = true;
							var obj = {
								title: 'This field is empty.',
								text: 'Enter the time in this field.'
							};
						}
					break;

					case 'url':
						if($(this).val().length < 1){
							displayErrBorder = true;
							displayErrMsg = true;
							var obj = {
								title: 'This field is empty.',
								text: 'Enter the url in this field.'
							};
						}else{
							var regExp = /(http|ftp|ftps|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?/;
							if(!regExp.test($(this).val())){
								showError = true;
								var obj = {
									title: 'URL is invalid.',
									text: 'URL address does not match a valid url template.'
								};
							}
						}
						if(showError){
							displayErrBorder = true;
							displayErrMsg = true;
						}
					break;

					default:
						if($(this).val().length < 1){
							displayErrBorder = true;
							displayErrMsg = true;
							var obj = {
								title: 'This field is empty.',
								text: 'This element must contain some text.'
							};
						}
				}
			break;
			case 'select':
				if($(this).val().length < 1){
					displayErrBorder = true;
					displayErrMsg = true;
					var obj = {
						title: 'Option is not selected.',
						text: 'You must select some item to continue.'
					};
				}
			break;
			case 'textarea':
				if($(this).val().length < 1){
					displayErrBorder = true;
					displayErrMsg = true;
					var obj = {
						title: 'Textarea is empty.',
						text: 'This element must contain some text.'
					};
				}
			break;
		}

		if(displayErrBorder) $(this).addClass('validate-error');
		if(displayErrMsg){
			var element = $(this)[0].localName + '[name='+$(this).attr('name')+']';
			displayErrorMessage(element, $(this).position(), obj.title, obj.text);
			error = true;
		}
	});
	return error;
}

function validate(selector){
	$(document).find('.validate-wrap').remove();
	var token = ($(selector).find('input[name=_token]').length > 0)
		? $(selector).find('input[name=_token]').val()
		: ($('meta[name=csrf-token]').length > 0)
			? $('meta[name=csrf-token]').attr('content')
			: '';
	var error = checkForm(selector);

	if(!error){
		return true;
	}else{
		return false;
	}
}

$(document).ready(function(){
	$(document).on('click','.error-close',function(){
		$(this).closest('.validate-wrap').remove();
	});
	$(document).on('click','.validate-error',function(){
		$(this).removeClass('validate-error');
	});
});