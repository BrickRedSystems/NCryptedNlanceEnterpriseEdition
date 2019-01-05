
$.validate({
	lang : langCode,
	
	modules : 'logic',
});


$(document).on('click', '[data-ele="submitChangePasswordForm"]', function(e) {
	e.preventDefault();
	if ($('#changePasswordForm').isValid()) {
		submitFormHandler(ajaxUrl,'changePasswordForm','Please wait..',function(){
			setTimeout(function(){
				window.location.reload();
			},500);
		});	
	}
});

$(document).on('change', '[data-ele="notification"]', function(e) {		
	submitValueHandler(ajaxUrl,'action=change_noti&noti_type='+$(this).val()+'&value='+($(this).is(':checked')?1:0),'Please wait..');	
});

$(document).on('click', '[data-ele="settings_tabs"]', function(e) {		
	$('[data-ele="settings_tabs"]').removeClass('active');
	$(this).addClass('active');
	
	$('[data-panel="settings_panels"]').addClass('hide');	
	$('[data-ele="'+$(this).data('target')+'"]').removeClass('hide');
});
