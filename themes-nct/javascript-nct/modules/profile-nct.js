

//start:: openInviteModal
$(document).on('click', '[data-ele="openInviteModal"]', function(e) {
 
    var userId = $(this).data('userid');

    submitValueHandler(window.location.href, 'action=method&method=invite_modal&suserId='+userId+'&origin='+window.location.href, 'Please wait..', function(data) {        
        $('body').append(data.html);
        $("#inviteModal").modal('show');
    },function(data){
        if ( typeof data.redirect != "undefined") {
            setTimeout(function() {
                window.location.href = data.redirect;
            }, 2000);
        }
    });
});

$(document).on('shown.bs.modal', '#inviteModal', function(e) {    
    $('[data-ele="multiselectProjects"]').selectpicker({ noneSelectedText: lang.Nothing_selected,selectAllText:lang.Select_All,deselectAllText:lang.Deselect_All });
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
//end:: openInviteModal


$(document).on('click', '[data-ele="project_status"] a', function(e) {
	var ele = this;
	submitValueHandler(window.location.href, 'action=method&method=project_rows&status=' + $(this).data("status"), 'Please wait..', function(data) {
		hasmoredata = true;
		$('[data-ele="project_status"] li').removeClass('active');
		$(ele).parent('li').addClass('active');
		$('[data-ele="project_list"]').empty().html(data.html);
		setTimeout($(".no_data_msg").centerNoRecMsg(), 500);
		pageIndex = 2;
	});
});


$(function() {	
	var win = $(window);

	// Each time the user scrolls
	//$(window).scrollTop($('#review-section').offset().top);
	win.scroll(function() {
		// End of the document reached?
		
		if ($(document).height() - win.height() == win.scrollTop() && hasmoredata) {
			//$('#loading').show();
			var last_character = window.location.href.substr(window.location.href.length - 1);			
			var status = $('[data-ele="project_status"] li.active a').data('status');
			pageIndex++;
			if (last_character == "/") {
				var url_to_call = window.location.href + "?action=method&method=" + method + "&status=" + status + "&pageNo=" + pageIndex;
			} else if (last_character == "=") {
                var url_to_call = window.location.href + "&action=method&method=" + method + "&status=" + status + "&pageNo=" + pageIndex;
            }else {
				var url_to_call = window.location.href + "/?action=method&method=" + method + "&status=" + status + "&pageNo=" + pageIndex;
			}

			
			$.ajax({
				url : url_to_call,
				dataType : 'html',
				beforeSend : function() {
                	if($('.cssload-square').length == 0){
						var loading_html = '<div class="cssload-square"><div class="cssload-square-part cssload-square-green"></div><div class="cssload-square-part cssload-square-pink"></div><div class="cssload-square-blend"></div></div>';
						jQuery('[data-infinite="container"]').append(loading_html);
					}
	            },
				success : function(html) {
					$newElems = $(html).filter('[data-infinite="row"]');
					if ($newElems.length == 0) {
						hasmoredata = false;
					}
					setTimeout(function() {
						$('.cssload-square').remove();
						jQuery('[data-infinite="container"]').append($newElems);
					}, 1000);
				},
				complete: function(){
                	setTimeout(function(){
                		$('.cssload-square').remove();
                	},5000);
                }
			});
		}
	});

}); 


