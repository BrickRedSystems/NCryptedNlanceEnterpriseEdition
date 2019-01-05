// Add custom validation rule
$.formUtils.addValidator({
  name : 'multiple_of_number',
  validatorFunction : function(value, $el, config, language, $form) {
  	if (value == "") {
		$el.attr("data-validation-error-msg", lang.Please_enter_required_credits);
		$('.credit-box span').html(0.00);
		return false;
	}else if(parseInt(value)==0){
		$el.attr("data-validation-error-msg", lang.Entered_number_of_credits_is_invalid_Please_check);
		$('.credit-box span').html(0.00);
		return false;
	}else if(parseInt(value) % credit_bunch === 0){
		return true;
	}else{
		$el.attr("data-validation-error-msg", lang.You_can_avail_credits_only_in_the_multiples_of_X+' '+credit_bunch);
		$('.credit-box span').html(0.00);
		return false;
	}

  },
  errorMessage : lang.You_can_avail_credits_only_in_the_multiples_of_X+' '+credit_bunch,
  errorMessageKey: 'multiple_of_number'
});


$(document).on('click', '[data-ele="submitAdhocCrediForm"]', function(e) {
	e.preventDefault();
	submitFormHandler(ajaxUrl, 'adhocCrediForm', 'Processing..', function(data) {
		$('[data-ele="credits"]').val("0");		
	});
});
