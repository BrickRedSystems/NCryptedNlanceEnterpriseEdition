/*
 * START :: code for cropping profile image
 */

$(document).on('change', '#profilePhoto', function(e) {
	var _this = $(this);
	var value = _this.val();

	var allowedFiles = [".jpg", ".jpeg", ".png"];
	var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:()])+(" + allowedFiles.join('|') + ")$");

	if (value && value != '') {
		if (!regex.test(value.toLowerCase())) {
			toastr['error'](lang.Please_select_valid_image);
		} else if (this.files[0].size > 4194304) {
			toastr['error'](lang.Image_size_must_be_less_then_set_value);
		} else {

			$('#avatar-modal').modal('show');

			setTimeout(function() {
				var url = URL.createObjectURL(e.target.files[0]);
				var img = $('<img src="' + url + '">');
				$('.avatar-wrapper').empty().html('<img src="' + url + '">');
				$('.avatar-wrapper img').cropper({
					aspectRatio : 1,
					strict : true,
					crop : function(e) {
						var json = ['{"x":' + e.x, '"y":' + e.y, '"height":' + e.height, '"width":' + e.width, '"rotate":' + e.rotate + '}'].join();
						$('.avatar-data').val(json);
					}
				});

			}, 500);
		}
	} else {
		e.preventDefault();
		toastr['error'](lang.Please_select_image);
	}

});

$('#avatar-modal').on('hidden.bs.modal', function() {
	$('.avatar-wrapper img').cropper('destroy');
	$('.avatar-wrapper').empty();
});


$(document).on('click', '#btnCrop', function() {
	var avatarForm = $('.avatar-form');
	var frmCont = $('#frmProfilePic');
	var url = avatarForm.attr('action');

	var data = new FormData(frmCont[0]);
	data.append('avatar_src', $('#avatar_src').val());
	data.append('avatar_data', $('#avatar_data').val());

	$.ajax(url, {
		type : 'post',
		data : data,
		dataType : 'json',
		processData : false,
		contentType : false,
		beforeSend : function() {
		},
		success : function(data) {
			if (data.state == 200) {
				$('[data-ele="profile_pic"], .img-rounded.profileimage').attr('src', data.source);
				$('#avatar-modal').modal('hide');
			} else {
				toastr['error'](data.message);
			}
		},
		complete : function() {
		}
	});
});

/*
 * END :: code for cropping profile image
 */

$.validate({
	lang : langCode,	
	modules : 'logic',
});


$(function(){
	$('[data-ele="select_code"], [data-ele="countryCode"], [data-ele="state"], [data-ele="city"]').selectpicker();
});

// Restrict presentation length
$('#aboutMe').restrictLength($('#pres-max-length'));

$(document).on('click', '[data-ele="submitEditProfile"]', function(e) {
	e.preventDefault();
	if ($('#editProfileForm').isValid()) {
    	submitFormHandler(ajaxUrl,'editProfileForm','Please wait..',function(){
    	    setTimeout(function(){window.location.reload();},500);
    	});	
	}
});

$(document).on('click', '[data-ele="cancelEditProfile"]', function(e) {
    window.history.back();
});

$(document).on('change', '[data-ele="catId"]', function(e) {
	submitValueHandler(ajaxUrl, 'action=method&method=get_sub_cats&val=' + $(this).val(), 'Please wait..', function(data) {
		$('[data-ele="subcatId"]').find('option:not(:first)').remove();
		$('[data-ele="subcatId"]').append(data.html);
	});
});

$(document).on('change', '[data-ele="countryCode"]', function(e) {
	submitValueHandler(ajaxUrl, 'action=method&method=stateOptions&val=' + $(this).val(), 'Please wait..', function(data) {
		$('[data-ele="state"]').find('option:not(:first)').remove();
		$('[data-ele="state"]').append(data.html);
		$('[data-ele="state"]').selectpicker('refresh');
	});
});

$(document).on('change', '[data-ele="state"]', function(e) {
	submitValueHandler(ajaxUrl, 'action=method&method=cityOptions&val=' + $(this).val(), 'Please wait..', function(data) {
		$('[data-ele="city"]').find('option:not(:first)').remove();
		$('[data-ele="city"]').append(data.html);
		$('[data-ele="city"]').selectpicker('refresh');
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
	minLength : 1,
	
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
