$(document).on('click', '[data-ele="submitSearch"]', function(){
    searchByKeyword();
});

$(document).on('keyup', '[data-ele="searchBox"]', function(e){
    if(e.which == 13) {
        searchByKeyword();
    }
});

function searchByKeyword(){
    var keyword = $('[data-ele="searchBox"]').val();
    keyword = keyword ? keyword : '';
    var url = $('.active a[rel="tab"]').attr('href')+$('[data-ele="sort_by"]').val()+'/'+keyword;    
    m(url,true,true,'.search-row',$('.active a[rel="tab"]').data('extra'));
}

$(document).on('click', 'a[rel="tab"]', function(evt) {
    // prevent normal navigation
    evt.preventDefault();
    $('[data-ele="tabs"]').removeClass('active');
    $(this).parent().addClass('active');
    m(jQuery(this));
});

$(document).on('change', '[data-ele="sort_by"]', function() {  
    var keyword = $('[data-ele="searchBox"]').val();
    keyword = keyword ? keyword : '';  
    var url = $('.active a[rel="tab"]').attr('href')+$(this).val()+'/'+keyword;    
    m(url,true,true,'.search-row',$('.active a[rel="tab"]').data('extra'));
});

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
    //add_track_player();

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
            console.log(data);
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

    // Each time the user scrolls
    
    win.scroll(function() {
        // End of the document reached?
        
        if ($(document).height() - win.height() == win.scrollTop()) {
            //$('#loading').show();
            var last_character = window.location.href.substr(window.location.href.length - 1);          
            pageIndex++;
            if (last_character == "/") {
                var url_to_call = window.location.href + "?action=method&method=project_rows&pageNo=" + pageIndex;
            } else if (last_character == "=") {
                var url_to_call = window.location.href + "&action=method&method=project_rows&pageNo=" + pageIndex;
            }else {
                var url_to_call = window.location.href + "/?action=method&method=project_rows&pageNo=" + pageIndex;
            }

            
            $.ajax({
                url : url_to_call,
                dataType : 'html',
                success : function(html) {
                    $newElems = $(html).filter('[data-infinite="row"]');
                    if($newElems.length == 0){
                        hasmoredata = false;
                    }  
                    setTimeout(function() {
                        jQuery('[data-infinite="container"]').append($newElems);
                    }, 10);
                }
            });
        }
    });

}); 
