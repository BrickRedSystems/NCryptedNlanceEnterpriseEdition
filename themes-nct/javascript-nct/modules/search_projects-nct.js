var slider;

function redirect() {
	var level = $('[data-ele="levels"] input:checked').map(function() {
		return this.value;
	}).get().join(',');
	var exp_level = (level != "") ? '&level=' + level : '';

	var f = $('[data-ele="isFeatured"]:checked').length ? '&isFeatured=y' : '&isFeatured=n';

	var keyword = $('[data-ele="searchBox"]').val();
	keyword = (keyword != "") ? '&keyword=' + keyword : '';

	var sort_by = '&sort_by=' + $('[data-ele="sort_by"]').val();

	var category = ($('[data-ele="cat"]').val()!=0 && $('[data-ele="cat"]').val()!='') ? '?category=' + $('[data-ele="cat"]').val() : '?';

	var subcategory = ($('[data-ele="multiselectsubcat"]').val()!=0 && $('[data-ele="multiselectsubcat"]').val()!=null) ? '&subcategory=' + $('[data-ele="multiselectsubcat"]').val():'';

	var skills = ($('[data-ele="multiselectskills"]').val()!=0 && $('[data-ele="multiselectskills"]').val()!=null) ? '&skills=' + $('[data-ele="multiselectskills"]').val():'';
	
	var url = site_search_projects + category + subcategory + skills + exp_level + '&budget_from=' + slider.result.from + '&budget_to=' + slider.result.to + f + keyword + sort_by;
	
	m(url, true, true, '.search-row', 'getPageContent');
}


$(document).on('click', '[data-ele="submitSearch"]', redirect);
$(document).on('keyup', '[data-ele="searchBox"]', function(e) {
	if (e.which == 13) {
		redirect();
	}
});

$(document).on('change', '[data-ele="isFeatured"], [data-ele="levels"] input', function() {
	redirect();
});
$(document).on('changed.bs.select', '[data-ele="cat"]', function(e) {
	submitValueHandler(window.location.href, "action=method&method=projectSubCategories&category="+$(this).val(), "please wait..", function(data){
		$('[data-ele="multiselectsubcat"]').empty().html(data.html);
		$('[data-ele="multiselectsubcat"]').selectpicker('refresh');
	});
	redirect();	
});


$(document).on('changed.bs.select', '[data-ele="sort_by"], [data-ele="multiselectsubcat"], [data-ele="multiselectskills"]', function(e) {
	if($(this).data('ele') == "sort_by"){
        pageIndex = 1;
    }
	redirect();	
});


$(document).ready(function() {

	$('[data-ele="cat"]').selectpicker();
	$('[data-ele="multiselectsubcat"]').selectpicker({ noneSelectedText: lang.Nothing_selected,selectAllText:lang.Select_All,deselectAllText:lang.Deselect_All });
	$('[data-ele="multiselectskills"]').selectpicker({ noneSelectedText: lang.Nothing_selected,selectAllText:lang.Select_All,deselectAllText:lang.Deselect_All });
	$('[data-ele="sort_by"]').selectpicker();	

	

	$('[data-ele="priceRange"]').ionRangeSlider({
		type : "double",
		grid : false,
		hide_min_max : true,
		hide_from_to : true,
		onStart : function(data) {			
			$('[data-ele="sliderMin"]').html(data.from);
			$('[data-ele="sliderMax"]').html(data.to);
		},
		onChange : function(data) {
			$('[data-ele="sliderMin"]').html(data.from);
			$('[data-ele="sliderMax"]').html(data.to);
		},
		onFinish : function(data) {
			redirect();
		},
	});
	slider = $('[data-ele="priceRange"]').data("ionRangeSlider");

});
//ends:: document ready

/*
 *  to display html, title of the page we called
 *  here we replace html into the div
 */

 function displayContent(state, container) {
	// change the page title
	document.title = state.title;

	// replace the current content
	if ( typeof (container) != "undefined" && container != null) {
		jQuery(container).html('');
		jQuery(container).hide().html(state.content).show();
	} else {
		jQuery('#content').html('');
		jQuery('#content').html(state.content);
	}

}

// create a state object from html
function createState($content, title, container, url, extra) {
	var state = {
		content : $content,
		container : container,
		url : url,
		title : title,
		extra : extra
	};
	return state;
}

function m($ele, flag, replacestate, container, extra, callback) {
	hasmoredata = true;
	if ( typeof (flag) != "undefined" && flag == true) {
		var clickedHref = $ele;
		var container = (container) ? container : "#content";
		var loading_text = "Loading...";
		var extra = (extra) ? extra : "";
	} else {
		var clickedHref = $ele.attr("href");
		var container = $ele.attr('data-container');
		var loading_text = $ele.attr('data-loading_text');
		var extra = $ele.attr('data-extra');
	}
	if ( typeof (req) != 'undefined') {
		req.abort();
	}

	req = $.ajax({
		url : clickedHref,
		cache : false,
		beforeSend : function() {
		},
		method : 'post',
		dataType : 'json',
		async : true,
		data : {
			"rel" : "true",
			"extra" : extra
		},
		success : function(data) {
			if (data.code == 200) {

				// create state object
				var state = createState(data.content, data.title, container, clickedHref, extra);

				displayContent(state, container);

				if ( typeof (replacestate) == "undefined" || replacestate == false) {
					history.pushState(state, state.title, clickedHref);
				} else {
					history.replaceState(state, state.title, clickedHref);
				}

			} else {
				console.log("data.msg is coming");
			}
		},
		complete : function(data) {
			//console.log(data);
		}
	});

}

// handle back buttons of browser
window.onpopstate = function(evt) {

	if (evt.state) {
		//m($ele, flag, replacestate, container, extra, callback)
		m(evt.state.url, true, true, evt.state.container, evt.state.extra);
	}

};

$(function() {
	var win = $(window);
	var isWorking = 0;
	// Each time the user scrolls
	var deductHeight = (isMobile)?1000:0;

	win.scroll(function() {
		// End of the document reached?
		if ($(window).scrollTop() >= $(document).height() - $(window).height() - deductHeight && hasmoredata) {
			
			if ( isWorking==0) {			
				
				pageIndex++;
				isWorking=1;
				$.ajax({
					url : window.location.href,
					dataType : 'html',
					data:{
						'action':'method',
						'method':'project_rows',
						'pageNo': pageIndex
					},
					beforeSend : function() {
						if($('.cssload-square').length == 0){
							var loading_html = '<div class="cssload-square"><div class="cssload-square-part cssload-square-green"></div><div class="cssload-square-part cssload-square-pink"></div><div class="cssload-square-blend"></div></div>';
							jQuery('[data-infinite="container"]').append(loading_html);

							setTimeout(function(){
								$('.cssload-square').remove();
							},5000);
						}
					},
					success : function(html) {
						$newElems = $(html).filter('[data-infinite="row"]');
						if ($newElems.length == 0) {
							hasmoredata = false;
							pageIndex = 1;
						}
						setTimeout(function() {
							$('.cssload-square').remove();
							jQuery('[data-infinite="container"]').append($newElems);
							isWorking=0;
						}, 1000);
					},
					complete: function(){
						setTimeout(function(){
							$('.cssload-square').remove();
						},5000);
					}  
					
				});
			}
		}

	});

});

