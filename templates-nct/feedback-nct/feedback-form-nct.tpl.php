
    
    <h2>Leave Your Feed Back</h2>
         <div>
             <form name="frmFeed" id="frmFeed" method="post" enctype="multipart/form-data">
                     <div>
                         <label>First Name: </label>
                         <input type="text" value="%FIRSTNAME%" name="firstName" id="firstName">
                     </div>
                     <div>
                         <label>Last Name: </label>
                         <input type="text" value="%LASTNAME%" name="lastName" id="lastName">
                     </div>
                     <div>
                         <label>Email: </label>
                         <input type="text" value="%EMAIL%" name="email" id="email">
                     </div>
                     <div>
                         <label>Upload your photo: </label>
                         <input type="file" name="user_img" id="user_img">
                     </div>
                     <div>
                         <label>Enter Feed Back: </label>
                         <textarea name="message" value="" id="message"></textarea> 
                     </div>
                       
                     
                     <input type="submit" value="Submit" name="sbtfeedback" id="sbtfeedback">

                </form>
           </div>

<script type="text/javascript">
    
    $frmFeed="#frmFeed";
        {
    $($frmFeed).validate(
            {
                rules:
                {
                    email: {required:true,email:true},
                    message:{required:true},
                    firstName:{required:true},
                    lastName:{required:true},
                    
                },
                messages:
                {
                    email:{required:"Please enter email"},
                    message:{required:"Please enter your feedback."},
                    firstName:{required:"Please enter your first name."},
                    lastName:{required:"Please enter your last name."},
                    
                }
            });
        }
</script>
