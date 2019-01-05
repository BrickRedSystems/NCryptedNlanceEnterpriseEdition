/* START :: functions */

function submitFormHandler(url, form_id, loading_text, suc_callback, error_callback, before_callback) {

    try {

        $.ajax({
            url : url,
            beforeSend : function() {
                $("form#" + form_id).find('[type=submit]').prop('disabled', true);
                if ( typeof (eval(before_callback)) === 'function') {
                    window[(before_callback)]();
                }
            },
            method : 'post',
            dataType : 'json',
            data : jQuery("form#" + form_id).serialize(),
            success : function(data) {
                if (data.status) {
                    if ( typeof (eval(suc_callback)) === 'function') {
                        if ( typeof window[(suc_callback)] === 'function') {
                            window[(suc_callback)](data);
                        } else {
                            suc_callback(data);
                        }
                    }
                    if (data.msg != "undefined") {
                        toastr['success'](data.msg);
                    }
                    
                    //$("form#" + form_id).trigger("reset");

                } else {
                    if ( typeof (eval(error_callback)) === 'function') {
                        if ( typeof window[(error_callback)] === 'function') {
                            window[(error_callback)](data);
                        } else {
                            error_callback(data);
                        }
                    }
                    if (data.msg != "undefined") {
                        toastr['error'](data.msg);
                    }

                }
                $("form#" + form_id).find('[name=token]').val(data.newToken);

            },
            complete : function(e) {
                $("form#" + form_id).find('[type=submit]').prop('disabled', false);
            }
        });

    } catch(e) {

        console.log("Error in redirection - " + e);
    }
}

function submitValueHandler(url, data_string, loading_text, suc_callback, error_callback, async, before_callback) {

    if ( typeof (async) === "undefined") {
        async = true;
    }
    try {

        if ( typeof (req) != 'undefined') {
            req_val.abort();
        }

        req_val = $.ajax({

            url : url,
            beforeSend : function() {
                if ( typeof (eval(before_callback)) === 'function') {
                    window[(before_callback)]();
                }
            },
            method : 'post',
            dataType : 'json',
            data : data_string,
            async : async,
            success : function(data) {

                if (data.status) {
                    if ( typeof (eval(suc_callback)) === 'function') {
                        if ( typeof window[(suc_callback)] === 'function') {
                            window[(suc_callback)](data);
                        } else {
                            suc_callback(data);
                        }
                    }
                    if (data.msg != "undefined") {
                        toastr['success'](data.msg);
                    }

                } else {
                    if ( typeof (eval(error_callback)) === 'function') {
                        if ( typeof window[(error_callback)] === 'function') {
                            window[(error_callback)](data);
                        } else {
                            error_callback(data);
                        }
                    }
                    if (data.msg != "undefined") {
                        toastr['error'](data.msg);
                    }

                }
                //$("form#" + form_id).find('[name=token]').val(data.newToken);

            }
        });
        return req_val;
    } catch(e) {
        console.log("Error in redirection - " + e);
    }
}

function submitFormHandlerWithUpload(url, form_id, loading_text, suc_callback, error_callback) {
    var formElement = document.getElementById(form_id);
    var formObj = jQuery("#" + form_id);
    var formURL = formObj.attr("action");

    var formData = new FormData(formElement);
    jQuery.ajax({
        url : url,
        type : 'post',
        dataType : 'json',
        data : formData,
        processData : false, // tell jQuery not to process the data
        contentType : false, // tell jQuery not to set contentType
        enctype : 'multipart/form-data',
        mimeType : 'multipart/form-data',
        cache : false,
        beforeSend : function() {
            $("form#" + form_id).find('[type=submit]').prop('disabled', true);
        },
        success : function(data, textStatus, jqXHR) {
            if (data.status) {
                if ( typeof (eval(suc_callback)) === 'function') {
                    if ( typeof window[(suc_callback)] === 'function') {
                        window[(suc_callback)](data);
                    } else {
                        suc_callback(data);
                    }
                }
                if (data.msg != "undefined") {
                    toastr['success'](data.msg);
                }

            } else {
                if ( typeof (eval(error_callback)) === 'function') {
                    if ( typeof window[(error_callback)] === 'function') {
                        window[(error_callback)](data);
                    } else {
                        error_callback(data);
                    }
                }
                if (data.msg != "undefined") {
                    toastr['error'](data.msg);
                }

            }
            $("form#" + form_id).find('[name=token]').val(data.newToken);

        },
        complete : function(e) {
            $("form#" + form_id).find('[type=submit]').prop('disabled', false);
        },
        error : function(jqXHR, textStatus, errorThrown) {

        }
    });
    return false;

}
/* END :: functions */

/* START :: javascripts */
var isMobile = false;
if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
    isMobile = true; 
}

/*
 * variable 'taCheckTypes' used for validating user description or message 
 * in post/edit/repost project as well in messages.
 * if required, can be declared or override locally in 'validateDescription' rule
 */
var taCheckTypes = {
    at : {
        regEx : /@|\[at\]|\(at\)|\{at\}|\-at\-|\+at\+|\[dot\]|\(dot\)|\{dot\}|\-dot\-|\+dot\+/,
        message : lang.illegal_use_of_communication_email+" "+siteNm
    },
    connect : {
        regEx : lang.illegal_use_of_communication_email_regex,
        message : lang.illegal_use_of_communication_email+" "+siteNm
    },
    payment : {
        regEx : lang.illegal_use_of_communication_payment_regex,
        message : lang.illegal_use_of_communication_payment
    },
    customOffer : {
        regEx : lang.illegal_use_of_communication_offer_regex,
        message : lang.illegal_use_of_communication_payment
    }
};

$(document).on('click', '[data-ele="subscribe"]', function() {
    submitValueHandler(siteUrl + 'ajax-home-nct/', 'action=subscribe&email=' + $('[data-ele="subscriptionmail"]').val(), "please wait..",function(data){
        $('[data-ele="subscriptionmail"]').val("");
    },function(data){
        $('[data-ele="subscriptionmail"]').val("");
    });
});

$(document).on('click', '[data-ele="headerSearchBtn"]', function(e) {
    window.location.href = siteUrl + 'search/' + $('[data-ele="multiselectsearch"]').val() + '/?keyword=' + $('[data-ele="headerSearchBox"]').val();
    
});
$(document).on('keyup', '[data-ele="headerSearchBox"]', function(e) {
    if (e.which == 13) {
        window.location.href = siteUrl + 'search/' + $('[data-ele="multiselectsearch"]').val() + '/?keyword=' + $(this).val();
    }
});
$(document).on('changed.bs.select', '[data-ele="multiselectsearch"]', function(e) {
    $('[data-ele="headerSearchBox"]').attr('placeholder',($(this).val() == 'projects')? lang.find_a_project:lang.find_a_provider);
    
});



$(document).ready(function() {    

    $(".social_img").mouseover(function() {
        $(".social_img img").attr("src", "images/n_hover.png");
    });
    $(".social_img").mouseout(function() {
        $(".social_img img").attr("src", "images/n.png");
    });
    $.validate({
        modules : 'location, date, security, file',
        reCaptchaSiteKey: reCaptchaSiteKey,
        reCaptchaTheme: 'light'
    });

    /* ----------------------------------------------------------- */
    /*  WOW SMOOTH ANIMATIN
    /* ----------------------------------------------------------- */

    wow = new WOW({
        animateClass : 'animated',
        offset : 100
    });
    wow.init();

    $('[data-ele="multiselectsearch"], [data-ele="userLanguage"]').selectpicker();
    
    $('input,textarea').placeholder();

});

$(document).on('changed.bs.select', '[data-ele="userLanguage"]', function(e) {
    submitValueHandler(siteUrl, "action=method&method=updateUserLang&userLanguage="+$(this).val(), "please wait..",function(){
        window.location.reload();
    });    
});

$(document).on('click',".filter-toggle",function(){	
    $(".search-form").slideToggle();    
});
$(document).on('click',"#li_job",function(){
    $("#txt_search").attr("placeholder", "Find Jobs");
});
$(document).on('click',"#li_free",function(){
    $("#txt_search").attr("placeholder", lang.find_a_provider);
});
$(document).on('click',".credit-link",function(){
    $(".credit-row").slideToggle();
});
$(document).keyup(function(e) {
    if (e.keyCode == 27) {
        $('.modal').modal('hide');
    }
});

/* START :: verify social accounts */
function verify(network) {
    var my_network = hello(network);
    my_network.login({
        scope : 'email',
        force : true
    }).then(function() {
        return my_network.api('me');
    }).then(function(p) {
        console.log(p);
        submitValueHandler(ajaxUrl + '?action=socialVerify&provider=' + network, p, 'Please wait..', function(data) {
            window.location.reload();
        }, function(data) {
            window.location.reload();
        });
    }, function(e) {
        console.log('Signin error: ' + e.error.message);
    });
}
/* END :: verify social accounts */

/*STARTS:: hello login*/
function login(network) {
    /*

     for google
     {
     "kind": "plus#person",
     "etag": "\"FT7X6cYw9BSnPtIywEFNNGVVdio/W9lUcBIlQ0YoW8VY-mH2BX7F7jM\"",
     "gender": "female",
     emails: [
     {
     value: "forever5820@gmail.com",
     type: "account"
     }
     ],
     "objectType": "person",
     "id": "114375668200944097943",
     "displayName": "Testing Hiral",
     "name": "Testing Hiral",
     "url": "https://plus.google.com/114375668200944097943",
     "image": {
     "url": "https://lh3.googleusercontent.com/-XdUIqdMkCWA/AAAAAAAAAAI/AAAAAAAAAAA/4252rscbv5M/photo.jpg?sz=50",
     "isDefault": true
     },
     "isPlusUser": true,
     "language": "en",
     "circledByCount": 1,
     "verified": false,
     "last_name": "Hiral",
     "first_name": "Testing",
     "picture": "https://lh3.googleusercontent.com/-XdUIqdMkCWA/AAAAAAAAAAI/AAAAAAAAAAA/4252rscbv5M/photo.jpg?sz=50",
     "thumbnail": "https://lh3.googleusercontent.com/-XdUIqdMkCWA/AAAAAAAAAAI/AAAAAAAAAAA/4252rscbv5M/photo.jpg?sz=50"
     }

     for linkedin
     {
     emailAddress: "tester333.testing@gmail.com",
     firstName: "jalpa",
     formattedName: "jalpa linked",
     id: "U-MgggfYDO",
     lastName: "linked",
     pictureUrl: "https://media.licdn.com/mpr/mprx/0_izp7YYGYzNhNl3P3_typYjt3BzcnACP33BHrYjtTe9iQa6kT712_rg67MgBWt5tDG9OllshO1mb-",
     first_name: "jalpa",
     last_name: "linked",
     name: "jalpa linked",
     thumbnail: "https://media.licdn.com/mpr/mprx/0_izp7YYGYzNhNl3P3_typYjt3BzcnACP33BHrYjtTe9iQa6kT712_rg67MgBWt5tDG9OllshO1mb-",
     email: "tester333.testing@gmail.com"
     }

     for fb
     {
     email: "forever5820@gmail.com",
     first_name: "Hiral",
     last_name: "Patel",
     name: "Hiral Patel",
     timezone: 5.5,
     verified: true,
     id: "604496203052584",
     picture: "https://graph.facebook.com/604496203052584/picture",
     thumbnail: "https://graph.facebook.com/604496203052584/picture"
     }

     */

    var my_network = hello(network);
    my_network.login({
        scope : 'email',
        force : true
    }).then(function() {
        return my_network.api('me');
    }).then(function(p) {
        //console.log(p);
        submitValueHandler(siteUrl + 'ajax-registration-nct/?action=socialLogin&provider=' + network, p, 'Please wait..', function(data) {
            window.location.href = data.redirect;
        }, function(data) {
            window.location.href = data.redirect;
        });
    }, function(e) {
        console.log('Signin error: ' + e.error.message);
    });

}

hello.init({
    facebook : CLIENT_IDS_ALL.facebook,
    google : CLIENT_IDS_ALL.google,
    linkedin : CLIENT_IDS_ALL.linkedin
}, {
    redirect_uri : sitePlugin + 'hello/redirect.html'
});
/*ENDS:: hello login*/

/*START:: operations */
(function($) {
    'use strict';
    $.fn.operations = function(settings) {
        var config = $.extend({
            'url' : siteUrl + 'operations/',
            'action' : 'method',
            'method' : $(this).data('operation'),
            'origin' : window.location.href
        }, settings);

        return this.each(function() {
            var $this = $(this);

            var params = {
                action : config.action,
                method : config.method,
                origin : config.origin
            };

            switch(config.method) {
            case 'reportProject':
                params.projectId = $this.data('info');
                break;
            case 'favoriteProject':
                params.projectId = $this.data('info');
                break;
            case 'acceptBid':
                params.bidId = $this.data('info');
                break;
            default:
                params.id = $this.data('info');
            }
            submitValueHandler(config.url, params, 'Please wait..', function(data) {
                if (config.method == 'readNotification') {
                    var notiClone = $this.clone();
                    notiClone.removeClass('unread');
                    $this.replaceWith(notiClone);

                    var notiCounter = $('[data-ele="notiCounter"]').html();
                    if (notiCounter > 0) {
                        notiCounter--;
                        $('[data-ele="notiCounter"]').html(notiCounter);
                    }
                }
                if (config.method == 'readMessage') {
                    var msgClone = $this.clone();
                    msgClone.removeClass('unread');
                    $this.replaceWith(msgClone);

                    var msgCounter = $('[data-ele="msgCounter"]').html();
                    if (msgCounter > 0) {
                        msgCounter--;
                        $('[data-ele="msgCounter"]').html(msgCounter);
                    }
                }

                if ( typeof data.html != "undefined") {
                    $this.replaceWith(data.html);
                }
                if ( typeof data.redirect != "undefined") {
                    setTimeout(function() {
                        window.location.href = data.redirect;
                    }, 500);
                }
            }, function(data) {
                if ( typeof data.redirect != "undefined") {
                    setTimeout(function() {
                        window.location.href = data.redirect;
                    }, 1000);
                }
            });

        });
    };
})(jQuery);

$(document).on('click', '[data-operation]', function() {
    $(this).operations();
});
/*END:: operations */





/* START :: module: signup - on change country, select dialing code */
$(document).on('change','[name="countryCode"]',function(){
    submitValueHandler(siteUrl + 'ajax-registration-nct', 'action=fetchContactCode&countryId=' + $(this).val(), 'Please wait..', function(data) {
        $('[name="contactCode"]').find('option:not(:first)').remove();
        $('[name="contactCode"]').append(data.html);
    });    
});
/* END :: module: signup - on change country, select dialing code */

/* END :: javascripts */