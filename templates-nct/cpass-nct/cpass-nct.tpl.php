<h1>Change Password</h1>

<div>
    <form id="change-password" name="change-password" method="post" action="" autocomplete="off">
        <div>
                <div>
                    <label>Current Password: </label>
                    <input type="password" class="" name="cur_password" id="cur_password">
                </div>
                <div>
                    <label>New Password: </label>
                    <input type="password" class="" name="new_password" id="new_password" >
                </div>
                <div>
                    <label>Confirm Password: </label>
                    <input type="password" class="" name="conf_password" id="conf_password">
                </div>
            </div>
            <div>
                <div>
                    <button type="submit" name="sbtcPass" id="sbtcPass" value="Change Password" class="">Change Password</button>
                </div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function(e){
        $changePasswordId="change-password";
        $changePassword="#"+$changePasswordId;
        {
            $($changePassword).validate(
            {
                rules:
                {
                    cur_password: 
                    {
                        required:true,
                        minlength:6
                    },
                    new_password: 
                    {
                        required:true,
                        minlength:6
                    },
                    conf_password: 
                    {
                        required:true,
                        equalTo:"#new_password"
                    }
                },
                messages:
                {
                    cur_password: 
                    {
                        required:"Please enter current password",
                        minlength:"Minimum length of password is 8"
                    },
                    new_password: 
                    {
                        required:"Please enter new password",
                        minlength:"Minimum length of password is 8"
                    },
                    conf_password: 
                    {
                        required:"Please confirm new password",
                        equalTo:"Confirmed password not match with new password"
                    }
                }
            });
        }
    });
</script>