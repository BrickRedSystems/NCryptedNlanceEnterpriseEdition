/*Start:: show more/less */
(function($) {
    $.fn.shorten = function(settings) {
        var config = {
            showChars: 100,
            ellipsesText: "...",
            moreText: "more",
            lessText: lang.less
        };
        if (settings) {
            $.extend(config, settings);
        }
        $(document).off("click", '.morelink');
        $(document).on({
            click: function() {
                var $this = $(this);
                if ($this.hasClass('less')) {
                    $this.removeClass('less');
                    $this.html(config.moreText);
                } else {
                    $this.addClass('less');
                    $this.html(config.lessText);
                }
                $this.parent().prev().toggle();
                $this.prev().toggle();
                return false;
            }
        }, '.morelink');
        return this.each(function() {
            var $this = $(this);
            if ($this.hasClass("shortened")) return;
            $this.addClass("shortened");
            var content = $this.html();
            if (content.length > config.showChars) {
                var c = content.substr(0, config.showChars);
                var h = content.substr(config.showChars, content.length - config.showChars);
                var html = c + '<span class="moreellipses">' + config.ellipsesText + ' </span><span class="morecontent"><span>' + h + '</span> <a href="#" class="morelink">' + config.moreText + '</a></span>';
                $this.html(html);
                $(".morecontent span").hide();
            }
        });
    };
})(jQuery);
$(function() {
    $(".job-cell .light-text").shorten({
        "showChars": 350,
        "moreText": lang.See_More,
        "lessText": lang.less,
    });
});
/*End:: show more/less */
/*START:: breadcrumb toggler*/
$(document).on('click', ".fa-navicon", function() {
    $('[data-ele="project_tabs"]').slideToggle();
});
/*END:: breadcrumb toggler*/
/*START:: module operations */
(function($) {
    'use strict';
    $.fn.moduleOperations = function(settings) {
        var config = $.extend({
            'url': window.location.href,
            'action': 'method',
            'method': $(this).data('do'),
            'origin': window.location.href
        }, settings);
        return this.each(function() {
            var $this = $(this);
            var params = {
                action: config.action,
                method: config.method,
                origin: config.origin
            };
            switch (config.method) {
                case 'payMil':
                    params.mid = $this.data('info');
                    break;
                default:
                    params.id = $this.data('info');
            }
            submitValueHandler(config.url, params, 'Please wait..', function(data) {
                if (typeof data.html != "undefined") {
                    $this.replaceWith(data.html);
                }
                if (typeof data.redirect != "undefined") {
                    setTimeout(function() {
                        window.location.href = data.redirect.split('#')[0];
                    }, 2000);
                }
            }, function(data) {
                if (typeof data.redirect != "undefined") {
                    setTimeout(function() {
                        window.location.href = data.redirect.split('#')[0];
                    }, 2000);
                }
            });
        });
    };
})(jQuery);
$(document).on('click', '[data-do]', function() {
    $(this).moduleOperations();
});
/*START:: module operations */
/*START:: submitDisputeMsg */
$(document).on('click', '[data-ele="submitDisputeMsg"]', function(e) {
    e.preventDefault();
    if ($('#disputeMsgForm').isValid()) {
        submitFormHandler(window.location.href, 'disputeMsgForm', 'Please wait..', function(data) {
            $('[data-ele="past"]').empty().html(data.html);
            $("#description").val('');
            $('.mCustomScrollbar').mCustomScrollbar('scrollTo', 'bottom');
        });
    }
});
/*END:: submitDisputeMsg */
/*START:: submitWorkroomMsg */
$(document).on('click', '[data-ele="submitWorkroomMsg"]', function(e) {
    e.preventDefault();
    if ($('#workroomMsgForm').isValid()) {
        submitFormHandler(window.location.href, 'workroomMsgForm', 'Please wait..', function(data) {
            $('[data-ele="workroomMsgs"]').empty().html(data.html);
            $('[name="description"]').val('');
            $('.mCustomScrollbar').mCustomScrollbar('scrollTo', 'bottom');
        });
    }
});
/*END:: submitWorkroomMsg */
$(document).on('click', '[data-ele="showBidsPanel"]', function(e) {
    $('[data-method="panel_bids"]').trigger('click');
});
$(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function(e) {
    var target = $(e.target).attr("href");
    if (target == '#attachments') {
        //START:: file upload script
        var saved_q = [];
        $(function() {
            'use strict';
            // Initialize the jQuery File Upload widget:
            $('#fileupload').fileupload({
                // Uncomment the following to send cross-domain cookies:
                //xhrFields: {withCredentials: true},
                url: window.location.href,
                autoUpload: true,
                formData: [{
                    name: 'action',
                    value: 'method'
                }, {
                    name: 'method',
                    value: 'uploadFile'
                }],
                maxNumberOfFiles: 10,
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png|zip|7z|pdf|doc|docx|txt|xls|xlsx)$/i,
            }).bind('fileuploaddone', function(e, data) {
                submitValueHandler(window.location.href, 'action=method&method=workroomAttachments', 'Please wait..', function(data) {
                    $('[data-ele="allAttachments"]').empty().html(data.html);
                });
            });
            // Enable iframe cross-domain access via redirect option:
            $('#fileupload').fileupload('option', 'redirect', window.location.href.replace(/\/[^\/]*$/, '/cors/result.html?%s'));
            // Load existing files:
            $('#fileupload').addClass('fileupload-processing');
            $.ajax({
                // Uncomment the following to send cross-domain cookies:
                //xhrFields: {withCredentials: true},
                url: $('#fileupload').fileupload('option', 'url'),
                dataType: 'json',
                context: $('#fileupload')[0]
            }).always(function() {
                $(this).removeClass('fileupload-processing');
            }).done(function(result) {
                $(this).fileupload('option', 'done').call(this, $.Event('done'), {
                    result: result
                });
            });
        });
        //END:: file upload script
    }
});
//start:: openReviewModal
$(document).on('click', '[data-ele="openReviewModal"]', function(e) {
    submitValueHandler(window.location.href, 'action=method&method=review_modal', 'Please wait..', function(data) {
        $('body').append(data.html);
        $("#ratings_modal").modal('show');
    });
});
$(document).on('shown.bs.modal', '#ratings_modal', function(e) {
    $('.rating').rating({
        step: 0.5
    });
    // Custom validation rule for validating description
    var errorMsg = "";
    $.formUtils.addValidator({
        name: 'validateDescription',
        validatorFunction: function(value, $el, config, language, $form) {
            if (value == "") {
                $el.attr("data-validation-error-msg", lang.Please_write_your_reviews);
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
        errorMessage: errorMsg,
        errorMessageKey: 'badDescription'
    });
    $.validate({
        lang: langCode,
        modules: 'logic',
    });
});
//to remove popup from body once closed
$(document).on('hidden.bs.modal', '#ratings_modal', function(e) {
    $(this).remove();
});
$(document).on('click', '[data-ele="submitReview"]', function(e) {
    var formId = 'reviewForm';
    if ($('#' + formId).isValid()) {
        e.preventDefault();
        submitFormHandler(window.location.href, formId, 'Please wait..', function(data) {
            $("#ratings_modal").modal('hide');
            submitValueHandler(window.location.href, 'action=method&method=panel_reviews', 'Please wait..', function(data) {
                $('[data-ele="project_tabs"] a').removeClass('current');
                $('[data-method="panel_reviews"]').addClass('current');
                $('[data-ele="tab_panel"]').empty().html(data.html);
            });
            submitValueHandler(window.location.href, 'action=method&method=place_bid_btn', 'Please wait..', function(data) {
                $('[data-ele="placeABidBtn"]').empty().html(data.html);
            });
        });
    }
});
//end:: openReviewModal
//start:: openCreateMilestones
$(document).on('click', '[data-ele="openCreateMilestones"]', function(e) {
    $('#MilModal').remove();
    callMethod = ($(this).data("info") == "edit") ? "edit_mil_modal" : "create_mil_modal";
    submitValueHandler(window.location.href, 'action=method&method=' + callMethod, 'Please wait..', function(data) {
        $('body').append(data.html);
        $("#MilModal").modal('show');
        ////////////
        $('.milestones-form').each(function(index) {
            if (index > 0) {
                $(this).prepend('<a href="#" class="fa fa-close" data-ele="removeMil"></a>');
            }
        });
        ////////////
    });
});
$(document).on('shown.bs.modal', '#MilModal', function(e) {
    // Custom validation rule for validating description
    var errorMsg = "";
    $.formUtils.addValidator({
        name: 'validateDescription',
        validatorFunction: function(value, $el, config, language, $form) {
            if (value == "") {
                $el.attr("data-validation-error-msg", "Please enter description.");
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
        errorMessage: errorMsg,
        errorMessageKey: 'badDescription'
    });
    $.validate({
        lang: langCode,
        modules: 'logic, date',
    });
});
//to remove popup from body once closed
$(document).on('hidden.bs.modal', '#MilModal', function(e) {
    $(this).remove();
    addMilIterator = 1;
    submitValueHandler(window.location.href, 'action=method&method=panel_milestones', 'Please wait..', function(data) {
        $('[data-ele="project_tabs"] a').removeClass('current');
        $('[data-method="panel_milestones"]').addClass('current');
        $('[data-ele="tab_panel"]').empty().html(data.html);
    });
    submitValueHandler(window.location.href, 'action=method&method=tab_ul', 'Please wait..', function(data) {
        $('[data-ele="project_tabs"]').empty().html(data.html);
        $('[data-ele="project_tabs"] a').removeClass('current');
        $('[data-method="panel_milestones"]').addClass('current');
    });
    submitValueHandler(window.location.href, 'action=method&method=place_bid_btn', 'Please wait..', function(data) {
        $('[data-ele="placeABidBtn"]').empty().html(data.html);
    });
});
$(document).on('click', '[data-ele="submitMilForm"]', function(e) {
    var formId = 'milForm';
    if ($('#' + formId).isValid()) {
        e.preventDefault();
        submitFormHandler(window.location.href, formId, 'Please wait..', function(data) {
            $("#MilModal").modal('hide');
            submitValueHandler(window.location.href, 'action=method&method=tab_ul', 'Please wait..', function(data) {
                $('[data-ele="project_tabs"]').empty().html(data.html);
                $('[data-ele="project_tabs"] a').removeClass('current');
                $('[data-method="panel_milestones"]').addClass('current');
            });
        });
    }
});
$(document).on('click', '[data-ele="submitEditMilForm"]', function(e) {
    var formId = 'milForm';
    if ($('#' + formId).isValid()) {
        e.preventDefault();
        submitFormHandler(window.location.href, formId, 'Please wait..', function(data) {
            $("#MilModal").modal('hide');
            submitValueHandler(window.location.href, 'action=method&method=panel_milestones', 'Please wait..', function(data) {
                $('[data-ele="tab_panel"]').empty().html(data.html);
                $('[data-ele="project_tabs"] a').removeClass('current');
                $('[data-method="panel_milestones"]').addClass('current');
            });
            submitValueHandler(window.location.href, 'action=method&method=tab_ul', 'Please wait..', function(data) {
                $('[data-ele="placeABidBtn"]').empty().html(data.html);
                $('[data-ele="project_tabs"] a').removeClass('current');
                $('[data-method="panel_milestones"]').addClass('current');
            });
        });
    }
});
$(document).on('click', '[data-ele="removeMil"]', function(e) {
    $(this).closest(".milestones-form").remove();
    var milTotal = 0;
    $('[name^="price"]').each(function() {
        milTotal += Number($(this).val());
    });
    $('[data-ele="milTotal"]').html(milTotal);
});
$(document).on('click', '[data-ele="addMil"]', function(e) {
    var clone = $('.milestones-form').first().clone(false);
    $(clone).prepend('<a href="#" class="fa fa-close" data-ele="removeMil"></a>');
    $(clone).find('[name]').each(function() {
        $(this).val("");
    });
    $('.milestones-form:visible').last().after(clone);
});
$(document).on('blur', '[name^="price"]', function(e) {
    var milTotal = 0;
    $('[name^="price"]').each(function() {
        milTotal += Number($(this).val());
    });
    $('[data-ele="milTotal"]').html(milTotal);
});
//end:: openCreateMilestones
//start:: openPlaceBidModal
$(document).on('click', '[data-ele="openPlaceBidModal"]', function(e) {
    var callMethod = ($(this).data("info") == "edit") ? "edit_bid_modal" : "place_bid_modal";
    var origin = window.location.href;
    submitValueHandler(window.location.href, 'action=method&method=' + callMethod + '&origin=' + origin, 'Please wait..', function(data) {
        $('body').append(data.html);
        $("#placeBidModal").modal('show');
    }, function(data) {
        if (typeof data.redirect != "undefined") {
            setTimeout(function() {
                window.location.href = data.redirect.split('#')[0];
            }, 2000);
        }
    });
});
$(document).on('shown.bs.modal', '#placeBidModal', function(e) {
    // Custom validation rule for validating description
    var errorMsg = "";
    $.formUtils.addValidator({
        name: 'validateDescription',
        validatorFunction: function(value, $el, config, language, $form) {
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
        errorMessage: errorMsg,
        errorMessageKey: 'badDescription'
    });
    $.validate({
        lang: langCode,
        modules: 'logic, date',
    });
    $('#description').restrictLength($('#pres-max-length'));
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
            submitValueHandler(window.location.href, 'action=method&method=tab_ul', 'Please wait..', function(data) {
                $('[data-ele="project_tabs"]').empty().html(data.html);
                $('[data-ele="project_tabs"] a').removeClass('current');
                $('[data-method="panel_bid"]').addClass('current');
            });
            submitValueHandler(window.location.href, 'action=method&method=place_bid_btn', 'Please wait..', function(data) {
                $('[data-ele="placeABidBtn"]').empty().html(data.html);
            });
        });
    }
});
//end:: openPlaceBidModal
$(document).on('click', '[data-ele="project_tabs"] a', function(e) {
    var ele = this;
    hasmoredata = true;
    submitValueHandler(window.location.href, 'action=method&method=' + $(this).data("method")+'&redirect='+window.location.href, 'Please wait..', function(data) {
        $('[data-ele="project_tabs"] a').removeClass('current');
        $(ele).addClass('current');
        $('[data-ele="tab_panel"]').empty().html(data.html);
        if ($(ele).data('method') == 'panel_dispute' || $(ele).data('method') == 'panel_workroom') {
            // Custom validation rule for validating description
            var errorMsg = "";
            $.formUtils.addValidator({
                name: 'validateDescription',
                validatorFunction: function(value, $el, config, language, $form) {
                    if (value == "") {
                        $el.attr("data-validation-error-msg", lang.Please_write_a_message);
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
                errorMessage: errorMsg,
                errorMessageKey: 'badDescription'
            });
            $.validate({
                lang: langCode,
                modules: 'logic',
            });
        }
        if ($(ele).data('method') == 'panel_workroom') {
            $('[data-ele="tab_panel"]').addClass('workroom-messages').removeClass('open-provider-about');
        }
        $(".job-cell .light-text").shorten({
            "showChars": 350,
            "moreText": lang.See_More,
            "lessText": lang.less,
        });
        $('.mCustomScrollbar').mCustomScrollbar();
        setTimeout($(".no_data_msg").centerNoRecMsg(), 500);
        pageIndex = 2;
    });
});
$(function() {
    var win = $(window);
    // Each time the user scrolls
    win.scroll(function() {
        // End of the document reached?
        var method = $('[data-ele="project_tabs"] a.current').data('method');
        if ($(document).height() - win.height() == win.scrollTop() && hasmoredata && method != "panel_about") {
            //$('#loading').show();
            var last_character = window.location.href.substr(window.location.href.length - 1);
            pageIndex++;
            if (last_character == "/") {
                var url_to_call = window.location.href + "?action=method&method=" + method + "&pageNo=" + pageIndex;
            } else {
                var url_to_call = window.location.href + "/?action=method&method=" + method + "&pageNo=" + pageIndex;
            }
            $.ajax({
                url: url_to_call,
                dataType: 'html',
                beforeSend: function() {
                    if ($('.cssload-square').length == 0) {
                        var loading_html = '<div class="cssload-square"><div class="cssload-square-part cssload-square-green"></div><div class="cssload-square-part cssload-square-pink"></div><div class="cssload-square-blend"></div></div>';
                        jQuery('[data-infinite="container"]').append(loading_html);
                    }
                },
                success: function(html) {
                    $newElems = $(html).filter('[data-infinite="row"]');
                    if ($newElems.length == 0) {
                        hasmoredata = false;
                    }
                    setTimeout(function() {
                        $('.cssload-square').remove();
                        jQuery('[data-infinite="container"]').append($newElems);
                    }, 1000);
                },
                complete: function() {
                    setTimeout(function() {
                        $('.cssload-square').remove();
                    }, 5000);
                }
            });
        }
    });
});
//start:: openInviteModal
$(document).on('click', '[data-ele="openInviteModal"]', function(e) {
    var userId = $(this).data('userId');
    submitValueHandler(window.location.href, 'action=method&method=invite_modal&userId=' + userId + '&origin=' + window.location.href, 'Please wait..', function(data) {
        $('body').append(data.html);
        $("#inviteModal").modal('show');
    }, function(data) {
        if (typeof data.redirect != "undefined") {
            setTimeout(function() {
                window.location.href = data.redirect;
            }, 2000);
        }
    });
});
$(document).on('shown.bs.modal', '#inviteModal', function(e) {
    $('[data-ele="multiselectProviders"]').selectpicker({
        noneSelectedText: lang.Nothing_selected,
        selectAllText: lang.Select_All,
        deselectAllText: lang.Deselect_All
    });
    $.validate();
});
//to remove popup from body once closed
$(document).on('hidden.bs.modal', '#inviteModal', function(e) {
    $(this).remove();
});
$(document).on('click', '[data-ele="submitInviteForm"]', function(e) {
    var formId = 'inviteForm';
    if ($('#' + formId).isValid()) {
        e.preventDefault();
        submitFormHandler(window.location.href, formId, 'Please wait..', function(data) {
            $("#inviteModal").modal('hide');
        });
    }
});
//end:: openInviteModal   data-do="startProject" // toast-bottom-center
$(document).on('click', '#pay_now_click', function(event) {
    toastr.options.tapToDismiss = false;
    toastr.options.timeOut = 50000;
    toastr.options.extendedTimeOut = 0;
    toastr.options.positionClass = "toast-top-right";
    submitValueHandler(window.location.href, 'action=method&method=getPaymentDetails', 'Please wait..', function(data) {
        var display_text = lang.Amount_to_start_project + ' ' + CURRENCY_SYMBOL + data.balanceToDeduct + '<br/>*' + lang.Includes_Admin_Commission + ' ' + CURRENCY_SYMBOL + data.adminCommission;
        toastr['info'](display_text + '<br /><br /><a href="javascript:void(0);" data-do="startProject" class="btn clear" style="float:right"><i class="fa fa-paypal"></i> ' + lang.Pay_Now + '</a> ');
    });
});