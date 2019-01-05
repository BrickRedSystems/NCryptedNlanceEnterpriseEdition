$(document).on('click', '[data-ele="tab"]', function(e) {
	var info = $(this).data('info');
	submitValueHandler(window.location.href, 'action=method&method=markRead&info=' + info, 'Please wait..', function(data) {
		$('[data-info="' + info + '"]').find('.badge').html("0");
	});
	submitValueHandler(window.location.href, 'action=method&method=right&info=' + $(this).data('info'), 'Please wait..', function(data) {
		$('[data-ele="tab_panel"]').empty().html(data.html);
		$('.mCustomScrollbar').mCustomScrollbar();
	});
	$('[data-ele="tab"]').parent().removeClass('active');
	$(this).parent().addClass('active');
});

