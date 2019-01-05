//start:: open modal
$(document).on('click', '[data-ele="openPlaceBidModal"]', function(e) {
    callMethod = ($(this).data("info") == "edit") ? "edit_bid_modal" : "place_bid_modal";
    submitValueHandler(window.location.href, 'action=method&method=' + callMethod, 'Please wait..', function(data) {
        $('body').append(data.html);
        $("#placeBidModal").modal('show');
    });
});

$(document).on('shown.bs.modal', '#placeBidModal', function(e) {

    // Custom validation rule for validating description
    var errorMsg = "";
    $.formUtils.addValidator({
        name : 'validateDescription',
        validatorFunction : function(value, $el, config, language, $form) {

            if (value == "") {
                $el.attr("data-validation-error-msg", lang.err_Please_describe_your_project_details);
                return false;
            }

            
            var flag = false;
            $.each(["at", "connect", "payment", "customOffer"], function(i, val) {
                if (value.match(taCheckTypes[val].regEx)) {
                    flag = false;
                    errorMsg = taCheckTypes[val].message;
                    $el.attr("data-validation-error-msg", errorMsg);
                    //console.log(errorMsg);
                    return false;
                } else {
                    flag = true;
                }
            });

            //console.log(errorMsg);
            return flag;
        },
        errorMessage : errorMsg,
        errorMessageKey : 'badDescription'
    });

    $.validate({
	lang : langCode,
	
        modules : 'logic, date',
    });

});
//to remove popup from body once closed
$(document).on('hidden.bs.modal', '#placeBidModal', function(e) {
    $(this).remove();
});
$(document).on('click', '[data-ele="submitBid"]', function(e) {
    var formId = 'placeBidForm';
    if ($('#' + formId).isValid()) {
        e.preventDefault();
        submitFormHandler(window.location.href, formId, 'Please wait..', function(data) {
            $("#placeBidModal").modal('hide');
            submitValueHandler(window.location.href, 'action=method&method=panel_bid', 'Please wait..', function(data) {
                $('[data-ele="project_tabs"] a').removeClass('current');
                $('[data-method="panel_bid"]').addClass('current');
                $('[data-ele="tab_panel"]').empty().html(data.html);
                
            });
            submitValueHandler(window.location.href, 'action=method&method=place_bid_btn', 'Please wait..', function(data) {                
                $('[data-ele="placeABidBtn"]').empty().html(data.html);
            });
            setTimeout(function(){window.location.href=data.redirect;},2000);
            
        });
    }
});
//end:: open modal











$(document).on('click', '[data-ele="submitBidMsg"]', function(e) {
    e.preventDefault();
    if ($('#bidMsgForm').isValid()) {
        submitFormHandler(window.location.href, 'bidMsgForm', 'Please wait..', function(data){            
            $('.no_rec_section, .no_data_msg').remove();
            $('[data-ele="past"]').empty().html(data.html);
            $('.mCustomScrollbar').mCustomScrollbar("scrollTo","top");
            $('#description').val('');
        });
    }

});

// Custom validation rule for validating description
var errorMsg = "";
$.formUtils.addValidator({
    name : 'validateDescription',
    validatorFunction : function(value, $el, config, language, $form) {

        if (value == "") {
            $el.attr("data-validation-error-msg", lang.Please_describe_your_need_for_requesting_changes_in_the_bid);
            return false;
        }

        
        var flag = false;
        $.each(["at", "connect", "payment", "customOffer"], function(i, val) {
            if (value.match(taCheckTypes[val].regEx)) {
                flag = false;
                errorMsg = taCheckTypes[val].message;
                $el.attr("data-validation-error-msg", errorMsg);
                //console.log(errorMsg);
                return false;
            } else {
                flag = true;
            }
        });

        //console.log(errorMsg);
        return flag;
    },
    errorMessage : errorMsg,
    errorMessageKey : 'badDescription'
});

$.validate({
	lang : langCode,
	
    modules : 'logic',
});

// Restrict presentation length
$('#description').restrictLength($('#pres-max-length'));
