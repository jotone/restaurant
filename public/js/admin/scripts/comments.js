$(document).ready(function(){
	if($('.main-wrap>.items-list').length > 0){
		var getParams = getRequest();
		if(typeof getParams.sort_by == 'undefined'){
			var getParams = {
				sort_by: 'created_at',
				dir: 'desc'
			}
		}
		$('.main-wrap>.items-list #'+getParams.sort_by+' .'+getParams.dir).addClass('active');
	}

	buildFixedNavMenu();

//Save comment
	$('button[name=save]').click(function(e){
		e.preventDefault();
		var validation = validate('form[name=news]');
		if(validation){
			var id = $('input[name=id]').val();
			$.ajax({
				url:	'/admin/comments/'+id,
				type:	'PUT',
				headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
				data:	{
					text: $('textarea[name=text]').val(),
					ajax: 1
				},
				error:	function(jqXHR){
					showError(jqXHR.responseText, 'PUT::/admin/comments/'+id);
				},
				success: function(data){
					try{
						data = JSON.parse(data);
						if(data.message == 'success'){
							statusBarAddMessage(true, 'Comment  was successfully saved');
							showStatus(true);

							var confirmMessage =  'Do you want to continue edit comment?';
							showConfirm(confirmMessage);

							$(document).on('customEvent', function(e){
								if(e.message === true){
									if(id.length == 0){
										location.reload(true);
									}
								}else if(e.message === false){
									location = '/admin/comments';
								}
							});
						}else if(data.message == 'error'){
							for(var i in data.errors){
								statusBarAddMessage(false, data.errors[i]);
								showStatus(true);
							}
						}
					}catch(e){
						showError(e+data, 'PUT::/admin/comments/'+id);
					}
				}
			});
		}else{
			goTo('.validate-wrap');
		}
	});

//Drop comment
	$('.items-list a.drop').click(function(e){
		e.preventDefault();
		var _this = $(this);
		showConfirm('Do you really want to delete this comment?');
		$(document).on('customEvent', function(e){
			if(e.message === true){
				var id = _this.attr('data-id');
				$.ajax({
					url:	'/admin/comments/'+id,
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
					error:	function(jqXHR){
						showError(jqXHR.responseText, 'DELETE::/admin/comments/'+id);
					},
					success:function(data){
						try{
							data = JSON.parse(data);
							if(data.message == 'success'){
								location.reload(true);
							}
						}catch(e){
							showError(e + data, 'DELETE::/admin/comments/'+id);
						}
					}
				})
			}
		});
	});
});