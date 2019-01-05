<h2>Messages</h2>
   <div>
        %top_links%
    </div>
    <div>
        <div role="tabpanel" class="menu active" id="inbox">
                %inbox%
        </div>
    </div>


<!-- Permanant Delete Message Popup Start -->
<div aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="permanant-deleted-items" class="modal fade">
    <div role="document" class="modal-dialog">
        <button aria-label="Close" data-dismiss="modal" class="close" type="button"><i class="fa fa-close"></i></button>
        <div class="modal-content clearfix">
            <h1 class="popup-title">Permanent Messages delete confirmation</h1>
                <div class="modal-body inner-popup clearfix">
                    <p class="delete-note">This message will be deleted permanently.</br>Are you sure you want it to be deleted?</p>
					<div class="clearfix"></div>
                </div>
                <div class="modal-body">
                <input type="hidden" name="item_id" id="item_id" value="">
                </div>
                <div class="popup-footer text-right">
                    <button class="btn btn-lg btn-light-green perDel_submit" id="perDel_submit" type="button">Delete</button>
                    <button class="btn btn-lg btn-light-red" id="perDel_cancel" type="button">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script type="text/javascript">

$('body').on("click", "#perDel_cancel",function(event) {
            event.preventDefault();
            $("#permanant-deleted-items").modal("hide");
});

</script>

<script type="text/javascript">

/*message content*/
    $(".conn_user").click(function(d){
        var user_id =  $(this).attr('id');
        var tab = 'inbox';
        var content_class = ".inbox_right_content_"+tab;

        $.ajax({
            url: "{SITE_URL}pms/get-conversation",
            type: "post",
            dataType: "json",
            data: "tab="+tab+"&user_id="+user_id,
            async:false,
            cache:false,
            beforeSend: function() {
                
            },
            success: function(data) {
                //toastr.message(data.msg);   
                $(content_class).html(data.result);
                $(".User_Name").html(data.uname);       
                $("#receiverId").val(data.uId);
                var elem = document.getElementById('msg-inbox-chat');
                elem.scrollTop = elem.scrollHeight;
            }
        });
    });
    
</script>
