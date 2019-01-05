<div class="">
    <div class="message-left"> <!--<a class="btn btn-sm btn-sm-green compose_btn" href="#" data-toggle="modal" data-target="#compose-message">Compose new message</a>-->
    <h3>People List</h3>
        <div>
            <ul class="nav nav-tabs message-sub-tab" role="tablist" style="width:100%">
                %connection_list%
            </ul>
        </div>
    </div>
    <div class="message-deleted">
        
            <h2>Conversation with <span class="text-blue User_Name">%userName%</span></h2>
                <div class="msg-chat-main">
                        <div class="msg-inbox-chat inbox_right_content_inbox" id="msg-inbox-chat">
                            %message%
                         </div>
                    </div>
                
                <form class="chat-form messageReplyForm" id="messageReplyForm"  name="message_replay" method="post" action="" data-url="{SITE_URL}pms/message-replay">
                    <div class="form-group">
                        <textarea class="form-control" name="replay_message" rows="2" placeholder="Your message here"></textarea>
                    </div>
                    <input type="hidden" name="action" value="message-replay">
                    <input type="hidden" name="receiverId" id="receiverId" value="%receiverId%">
                    <div class="form-group text-right">
                        <button class="btn btn-sm" name="replay_submit" id="replay_submit" type="submit">Send Message</button>
                    </div>
                </form> 
     </div>                   
        
</div>
<script type="text/javascript">
        $("#messageReplyForm").validate({
            rules:{
                replay_message:{required:true}
            },
            messages:{
                replay_message:{required:"Please enter your message."}
            },
            submitHandler:function(form){
                var form_id = 'messageReplyForm';
                var formElement = document.all(form_id),
                    formObj = jQuery("#" + form_id),
                    formURL = formObj.attr("data-url"),
                    formData = new FormData(formElement);

                $.ajax({
                    url: formURL,
                    type: "post",
                    dataType: "json",
                    data: formData,
                    async:false,
                    cache:false,
                    processData: !1,
                    contentType: !1,
                    enctype: "multipart/form-data",
                    mimeType: "multipart/form-data",
                    beforeSend: function() {
                        
                    },
                    success: function(data) {
                        //toastr.message(data.msg);   
                        $(form)[0].reset();
                        $(".inbox_right_content_inbox").html(data.result);
                        var elem = document.getElementById('msg-inbox-chat');
                        elem.scrollTop = elem.scrollHeight;
                    }
                });
                
            }
        });
                                
</script>
