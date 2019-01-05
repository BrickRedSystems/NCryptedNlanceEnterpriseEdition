$(document).on('click', '[data-ele="wallet_tab"] li', function(e) {
    var ele = this;
    submitValueHandler(window.location.href, 'action=method&method=' + $(this).data("method"), 'Please wait..', function(data) {
        $('[data-ele="wallet_tab"] li').removeClass('active');
        $(ele).addClass('active');
        $('[data-ele="tab_panel"]').empty().html(data.html);
    });
});
//start:: redeem request modal
$(document).on('click', '[data-ele="openReedemModal"]', function(e) {
    submitValueHandler(window.location.href, 'action=method&method=get_redeemModal', 'Please wait..', function(data) {
        $('body').append(data.html);
        $("#openReedemModal").modal('show');
    });
});

$(document).on('shown.bs.modal', '#openReedemModal', function(e) {
    $.validate();
    $('#description').restrictLength($('#pres-max-length'));
});
//to remove popup from body once closed
$(document).on('hidden.bs.modal', '#openReedemModal', function(e) {
    $(this).remove();
});
$(document).on('click', '[data-ele="submitRedeemRequest"]', function(e) {
    //e.preventDefault();

    if ($('#redeemRequestForm').isValid()) {
        e.preventDefault();
        submitFormHandler(ajaxUrl, 'redeemRequestForm', 'Please wait..', function(data) {
            $("#openReedemModal").modal('hide');
            submitValueHandler(window.location.href, 'action=method&method=redeem_tab', 'Please wait..', function(data) {
                $('[data-ele="wallet_tab"] li').removeClass('active');
                $('[data-method="redeem_tab"]').addClass('active');
                $('[data-ele="tab_panel"]').empty().html(data.html);
            });
        });
    }

});
//end:: redeem request modal

//start:: deposit funds modal

$(document).on('click', '[data-ele="openDepositFundModal"]', function(e) {
    submitValueHandler(window.location.href, 'action=method&method=depositFundModal', 'Please wait..', function(data) {
        $('body').append(data.html);
        $("#DepositFundModal").modal('show');
    });
});

$(document).on('shown.bs.modal', '#DepositFundModal', function(e) {
    $.validate();
});
//to remove popup from body once closed
$(document).on('hidden.bs.modal', '#DepositFundModal', function(e) {
    $(this).remove();
});
$(document).on('click', '[data-ele="submitDepositFunds"]', function(e) {
    

});
//end:: deposit funds modal

