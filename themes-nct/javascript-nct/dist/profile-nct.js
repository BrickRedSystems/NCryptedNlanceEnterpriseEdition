

//------------------------------------------------------
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
	
	win.scroll(function() {
		// End of the document reached?
		
		if ($(document).height() - win.height() == win.scrollTop() && hasmoredata) {
			//$('#loading').show();
			var last_character = window.location.href.substr(window.location.href.length - 1);			
			var status = $('[data-ele="project_status"] li.active a').data('status');
			if (last_character == "/") {
				var url_to_call = window.location.href + "?action=method&method=" + method + "&status=" + status + "&pageNo=" + pageIndex;
			} else if (last_character == "=") {
                var url_to_call = window.location.href + "&action=method&method=" + method + "&status=" + status + "&pageNo=" + pageIndex;
            }else {
				var url_to_call = window.location.href + "/?action=method&method=" + method + "&status=" + status + "&pageNo=" + pageIndex;
			}

			pageIndex++;
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


/*
function infiniteScroll() {
	//scroll more
	var pageIndex = 2;

	jQuery('[data-infinite="container"]').infinitescroll({
		loading: {            
            finishedMsg: "<em>That's it. No more data now.</em>",            
        },
		navSelector : '#page-nav', // selector for the paged navigation
		nextSelector : '#page-nav a', // selector for the NEXT link (to page 2)
		itemSelector : '[data-infinite="row"]', // selector for all items you'll retrieve
		donetext : 'success',		
		debug : false,
		path : function() {
			var last_character = window.location.href.substr(window.location.href.length - 1);
			var method = "project_rows";
			var status = $('[data-ele="project_status"] li.active a').data('status');
			if (last_character == "/") {
				var url_to_call = window.location.href + "?action=method&method=" + method + "&status=" + status + "&pageNo=" + pageIndex;
			} else {
				var url_to_call = window.location.href + "&action=method&method=" + method + "&status=" + status + "&pageNo=" + pageIndex;
			}

			pageIndex++;
			return url_to_call;
		},
		errorCallback : function(e) {
			console.log(e);			
		}
	}, function(newElements) {
		var $newElems = $(newElements);		
		setTimeout(function() {
			console.log($newElems);
			jQuery('[data-infinite="container"]').append($newElems);
		}, 10);

	});

}*/

