$.validate({
	lang : langCode,
	
    modules : 'logic',
});

$(document).on('click', '[data-ele="submitFeedbackForm"]', function(e) {
    e.preventDefault();
    if ($('#feedbackForm').isValid()) {
        submitFormHandler(window.location.href,'feedbackForm','Please wait..',function(){
            setTimeout(function(){window.location.href = siteUrl;},1000);
        }); 
    }
});
