
    <h2>Registration</h2>
         
             <div>
                 <form name="frmRegi" id="frmRegi" method="post">
                     <div>
                         <label>Email: </label>
                         <input type="text" value="" name="email" id="email">
                     </div>
                     <div>
                         <label>Password: </label>
                         <input type="password" value="" name="password" id="password">
                     </div>
                       
                     
                     <input type="submit" value="Register Now" name="sbtRegi" id="sbtRegi">

                    <br><div>OR</div><br>
                    <div>
                    <a href="{SITE_URL}social/facebook" class="loginWithSocialMedia"><img src="{SITE_IMG}fb.jpg"></a>&nbsp;
   
                    <a href="{SITE_URL}social/google" class="loginWithSocialMedia"><img src="{SITE_IMG}google.jpg"></a>   
                    </div><br><br><br>                   
                </form>
            
         </div>

<script type="text/javascript">
    
    $frmRegi="#frmRegi";
        {
    $($frmRegi).validate(
            {
                rules:
                {
                    email: {
                        required:true,
                        email:true
                    },
                    password:{required:true,minlength:6}
                },
                messages:
                {
                    email:
                    {
                        required:"Please enter email",
                        email:"Please enter valid email"
                    },
                    password:
                    {
                        required:"Please enter password",
                        minlength:"Minimum 6 character required"
                    }
                    
                }
            });
        }
</script>
<script type="text/javascript">
    $(document).on('click', ".loginWithSocialMedia", function(e) {
        e.preventDefault();
        
        var url = $(this).attr('href');
        
        var width = 626;
        var height = 436;
        var l = window.screenX + (window.outerWidth - width) / 2;
        var t = window.screenY + (window.outerHeight - height) / 2;
        var winProps = ['width=' + width, 'height=' + height, 'left=' + l, 'top=' + t, 'status=no', 'resizable=yes', 'toolbar=no', 'menubar=no', 'scrollbars=yes'].join(',');
        $.oauthpopup({
            path: url,
            windowOptions: winProps,
            callback: function() {
                window.location.href =  '<?php echo SITE_URL;?>';
            }
        });
        e.preventDefault();
    });

    $.oauthpopup = function(options) {
        options.windowName = options.windowName || 'ConnectWithOAuth';
        options.windowOptions = options.windowOptions || 'location=0,status=0,width=' + options.width + ',height=' + options.height + ',scrollbars=1';
        options.callback = options.callback || function() {
            window.location.reload();
        };
        var that = this;
        that._oauthWindow = window.open(options.path, options.windowName, options.windowOptions);
        that._oauthInterval = window.setInterval(function() {
            if (that._oauthWindow.closed) {
                window.clearInterval(that._oauthInterval);
                options.callback();
            }
        }, 1000);
    };



</script>