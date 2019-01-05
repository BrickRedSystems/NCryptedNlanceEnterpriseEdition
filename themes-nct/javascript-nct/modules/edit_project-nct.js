//START:: file upload script
var saved_q = [];
$(function() {
	'use strict';

	// Initialize the jQuery File Upload widget:
	$('#fileupload').fileupload({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url : window.location.href,
		formData : [{
			name : 'action',
			value : 'method'
		}, {
			name : 'method',
			value : 'uploadFile'
		}],
		maxNumberOfFiles : 10,
		acceptFileTypes : /(\.|\/)(gif|jpe?g|png|zip|7z|pdf|doc|docx|txt|xls|xlsx)$/i,

	});

	// Enable iframe cross-domain access via redirect option:
	$('#fileupload').fileupload('option', 'redirect', window.location.href.replace(/\/[^\/]*$/, '/cors/result.html?%s'));

	// Load existing files:
	$('#fileupload').addClass('fileupload-processing');
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url : $('#fileupload').fileupload('option', 'url')+'&action=method&method=getFile',
		
		dataType : 'json',
		context : $('#fileupload')[0]
	}).always(function() {		
		$(this).removeClass('fileupload-processing');		
	}).done(function(result) {		
		$(this).fileupload('option', 'done').call(this, $.Event('done'), {
			result : result
		});
	});

});

//END:: file upload script


//START:: Country, State, City

$(document).on('change', '[data-ele="countryId"]', function(e) {
	submitValueHandler(window.location.href, 'action=method&method=stateOptions&val=' + $(this).val(), 'Please wait..', function(data) {
		$('[data-ele="stateId"]').find('option:not(:first)').remove();
		$('[data-ele="stateId"]').append(data.html);
		$('[data-ele="cityId"]').find('option:not(:first)').remove();
		//$('[data-ele="stateId"]').selectpicker('refresh');
	});
});

$(document).on('change', '[data-ele="stateId"]', function(e) {
	submitValueHandler(window.location.href, 'action=method&method=cityOptions&val=' + $(this).val(), 'Please wait..', function(data) {
		$('[data-ele="cityId"]').find('option:not(:first)').remove();
		$('[data-ele="cityId"]').append(data.html);
		//$('[data-ele="cityId"]').selectpicker('refresh');
	});
});
//END:: Country, State, City


$(document).on('click', '[data-ele="next"]', function() {
	if ($('#editProjectForm').isValid()) {
		var current_step = $(this).closest('[data-ele="steps"]').data('step');
		$('[data-ele="steps"]').addClass('hide');
		var next_step = current_step + 1;
		var prev_step = current_step - 1;
		$('[data-step="' + next_step + '"]').removeClass('hide');
		
		
		//for right panel
		$('[data-ele="right-filled"]').addClass('hide');
		$('[data-ele="right-empty"]').removeClass('hide');
		
		$('[data-ele="right-filled"][data-right-step="'+next_step+'"]').removeClass('hide');
		$('[data-ele="right-empty"][data-right-step="'+next_step+'"]').addClass('hide');		
		window.scrollTo(0,0);
	}

});
$(document).on('click', '[data-ele="finish"]', function(e) {
	e.preventDefault();
	if ($('#editProjectForm').isValid()) {
		var activeUploads = $('#fileupload').fileupload('active');
		if (activeUploads > 0) {
			toastr["error"](lang.uploads_are_in_progress_message);
		} else {			
			submitFormHandlerWithUpload(window.location.href, 'editProjectForm', 'Please wait..', function(data){
			    if(typeof data.redirect != "undefined" || data.redirect != ""){
			        setTimeout(function(){
			            window.location.href = data.redirect;
			        },500);
			    }
			});
		}

	}

});

// Custom validation rule for skill tags
$.formUtils.addValidator({
	name : 'validateSkillsId',
	validatorFunction : function(value, $el, config, language, $form) {
		if ($('[data-ele="skillsContainer"]').children().length) {
			return true;
		} else {
			return false;
		}
	},
	errorMessageKey : 'noSkillsRequired'
});

// Custom validation rule for validating description
var errorMsg = "";
$.formUtils.addValidator({
	name : 'validateDescription',
	validatorFunction : function(value, $el, config, language, $form) {
		
		if(value == ""){
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

// Restrict presentation length
$('#description').restrictLength($('#pres-max-length'));

$(document).on('change', '[data-ele="categoryId"]', function(e) {
	submitValueHandler(window.location.href, 'action=method&method=get_sub_cats&parentId=' + $(this).val(), 'Please wait..', function(data) {
		$('[data-ele="subcategoryId"]').find('option:not(:first)').remove();
		$('[data-ele="subcategoryId"]').append(data.html);
	});
});

//START :: for skills tags
var substringMatcher = function(strs) {
	return function findMatches(q, cb) {
		var matches,
		    substringRegex;

		// an array that will be populated with substring matches
		matches = [];

		// regex used to determine if a string contains the substring `q`
		substrRegex = new RegExp(q, 'i');

		// iterate through the pool of strings and for any string that
		// contains the substring `q`, add it to the `matches` array
		$.each(strs, function(i, str) {
			if (substrRegex.test(str)) {
				matches.push(str);
			}
		});

		cb(matches);
	};
};

var tagApi = jQuery("[data-ele=skillsId]").tagsManager({
	prefilled : prefilledValues,
	tagsContainer : '[data-ele="skillsContainer"]',
	CapitalizeFirstLetter : true,
	tagList : allSkills,
	backspace: [],
}),
    typeahead = $("[data-ele=skillsId]").typeahead({
	hint : true,
	highlight : true,
	minLength : 1
}, {
	name : 'skills',
	source : substringMatcher(allSkills)
}).on("typeahead:cursorchanged", function(e, d) {
	//tagApi.tagsManager("pushTag", d);
}).on("typeahead:selected", function(e, d) {
	tagApi.tagsManager("pushTag", d);
	typeahead.typeahead('val','');
});
//END :: for skills tags

$(function(){
	
	$(".date").datepicker().on('change', function() {
		$(this).isValid();
    });
    $("[name=biddingDeadline]").datepicker({
    	format: BOOTSTRAP_DATEPICKER_FORMAT,
  		autoclose: true,
  		startDate:'+1d'
    }).on('changeDate', function (selected) {
	    var startDate = new Date(selected.date.valueOf());
	    $('[name="startDate"]').datepicker('clearDates');
	    $('[name="startDate"]').datepicker('setStartDate', startDate);
	}).on('clearDate', function (selected) {
		$('[name="startDate"]').datepicker('clearDates');
	    $('[name="startDate"]').datepicker('setStartDate', null);
	});

	$('[name="startDate"]').datepicker({
		format: BOOTSTRAP_DATEPICKER_FORMAT,
  		autoclose: true,
	});

});