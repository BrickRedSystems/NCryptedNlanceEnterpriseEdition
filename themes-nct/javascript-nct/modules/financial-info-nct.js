$(document).on('click', '[data-ele="tabs"] li', function(e) {
    var ele = this;
    submitValueHandler(window.location.href, 'action=method&method=' + $(this).data("method"), 'Please wait..', function(data) {
        $('[data-ele="tabs"] li').removeClass('active');
        $(ele).addClass('active');
        $('[data-ele="tab_panel"]').empty().html(data.html);
    });
}); 