$.validate({
	lang : langCode,
	
    modules : 'logic',
});

$(document).on('click', '[data-ele="submitContactForm"]', function(e) {

    e.preventDefault();
    if ($('#contactForm').isValid()) {
        submitFormHandler(window.location.href,'contactForm','Please wait..',function(){
            setTimeout(function(){window.location.href = siteUrl;},1000);
        }); 
    }
});
