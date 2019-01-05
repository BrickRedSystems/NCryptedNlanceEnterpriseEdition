(function ($) {
    $.fn.scrollPagination = function (options) {

        var settings = {
            nop: 8, // The number of posts per scroll to be loaded
            offset: 0, // Initial offset, begins at 0 in this case
            initMsg: 'Loading content .....',
            error: 'No More Posts!', // When the user reaches the end this is the message that is
            // displayed. You can change this if you want.
            delay: 500, // When you scroll down the posts will load after a delayed amount of time.
            // This is mainly for usability concerns. You can alter this as you see fit
            scroll: true, // The main bit, if set to false posts will not load as the user scrolls. 
            // but will still load if the user clicks.
            ajaxFile: '',
            addDiv: true,
            afterLoad: null,
            extraData: new Array()
        }
        // Extend the options so they work with the plugin
        if (options) {
            $.extend(settings, options);
        }
        // For each so that we keep chainability.
        return this.each(function () {
            // Some variables 
            $this = $(this);
            $settings = settings;
            var ajaxFile = $settings.ajaxFile;
            var offset = $settings.offset;
            var loadImg = $settings.initMsg;
            var busy = false; // Checks if the scroll action is happening 
            // so we don't run it multiple times

            // Custom messages based on settings
            if ($settings.scroll == true)
                $initmessage = loadImg;
            else
                $initmessage = '<a href="javascript:void(0);" title="Click for more content">Click for more content</a>';

            // Append custom messages and extra UI
            if ($settings.addDiv) {
                $this.append('<li class="loading-bar flclear clearfix">' + $initmessage + '</li>');
            } else {
                $this.append('<tr class="loading-bar"><td colspan="8">' + $initmessage + '</td></tr>');
            }
            
            
            
            function getData() {
                busy = true;

                // Post data to ajax.php
                $.post($settings.ajaxFile, {
                    action: $settings.action,
                    limit: $settings.nop,
                    offset: offset,
                    extraData: $settings.extraData


                }, function (data) {

                    // Change loading bar content (it may have been altered)


                    /*if ($settings.addDiv)
                    {
                        $this.find('.loading-bar').html($initmessage);
                    }
                    else
                    {
                        $this.find('.loading-bar').remove();
                        $this.append($initmessage);
                    }*/

                    // If there is no data returned, there are no more posts to be shown. Show error

                    if (data.list == "") {
                        if ($settings.addDiv) {
                            $this.find('.loading-bar').html($settings.error);
                        } else {
                            $this.find('.loading-bar').remove();
                            $this.append($settings.error);
                        }
                    }
                    else {
                        if(data.available_records == 0) {
                            $this.find('.loading-bar').remove();
                            $this.append($settings.error);
                        }
                        
                        // Offset increases
                        offset = offset + $settings.nop;

                        // Append the data to the content div
                        if ($settings.addDiv) {
                            console.log($this);
                            //$this.append(data.list);
                            $this.find('li:last').before(data.list);
                        }
                        else
                        {
                            //$this.append(data.list);
                            $this.find('tr:last').before(data.list);
                        }
                        // No longer busy!	
                        busy = false;
                    }

                    if ($settings.afterLoad != null) {
                        //console.log($settings.afterLoad);
                        $settings.afterLoad(data);
                    }

                }, 'json');
                $.ajaxSetup({async: false});
            }

            getData(); // Run function initially

            // If scrolling is enabled
            if ($settings.scroll == true) {
                // .. and the user is scrolling
                $(window).scroll(function () {

                    // Check the user is at the bottom of the element
                    if ((parseFloat($(window).scrollTop()) + parseFloat($(window).height())) > $this.height() && !busy) {
                        // Now we are working, so busy is true
                        busy = true;

                        // Tell the user we're loading posts
                        $this.find('.loading-bar').html($settings.initMsg);

                        // Run the function to fetch the data inside a delay
                        // This is useful if you have content in a footer you
                        // want the user to see.
                        setTimeout(function () {

                            getData();

                        }, $settings.delay);

                    }
                });
            }

            // Also content can be loaded by clicking the loading bar/
            $this.find('.loading-bar').click(function () {

                if (busy == false) {
                    busy = true;
                    getData();
                }

            });

        });
    }

})(jQuery);
