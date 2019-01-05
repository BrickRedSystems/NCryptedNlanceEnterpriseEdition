<div class="msg-tabs msg-tab-box">
    <div class="col-sm-4 col-md-3">
        <ul class="nav nav-tabs">
            <li class="active">
                <a data-do="updateMsgs" data-toggle="tab" href="#messages">
                    {Messages} (%unreadMessages%)
                </a>
            </li>
            <li>
                <a data-do="updateAttachments" data-toggle="tab" href="#attachments">
                    {Attachments}
                    <!-- (%unread{Attachments}%) -->
                </a>
            </li>
        </ul>
    </div>
    <div class="col-sm-8 col-md-9">
        <div class="tab-content">
            <div class="tab-pane fade in active" id="messages">
                <div class="msg-box-inbox mCustomScrollbar" id="content-2">
                    <div class="msg-chat-main">
                        <div class="msg-inbox-chat">
                            <ul class="chat-row" data-ele="workroomMsgs">
                                %messages%
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="clearfix">
                </div>
                <form class="chat-form" id="workroomMsgForm">
                    <input name="token" type="hidden" value="%tokenValue%">
                        <div class="form-group">
                            <textarea class="form-control" data-validation="validateDescription" data-validation-error-msg="{Please_check_your_message_carefully}" name="description" placeholder="{Write_your_message_here}" rows="4"></textarea>
                        </div>
                        <div class="form-group text-right">
                            <input name="action" type="hidden" value="method"/>
                            <input name="method" type="hidden" value="sendWorkroomMessage"/>
                            <button class="btn btn-link" data-ele="submitWorkroomMsg" type="submit">
                                {Send}
                            </button>
                        </div>
                    </input>
                </form>
            </div>
            <div class="tab-pane fade attachments-main" id="attachments">
                <div class="msg-box-inbox mCustomScrollbar" id="content-2">
                    <ul class="attachments-row" data-ele="allAttachments">
                        %attachments%
                    </ul>
                </div>
                <div class="clearfix">
                </div>
                <form class="attach-form" id="fileupload">
                    <div class="table-responsive">
                        <table class="table table-bordered" role="presentation">
                            <tbody class="files">
                            </tbody>
                        </table>
                    </div>
                    <div class="clearfix">
                    </div>
                    <div class="form-group">
                        <label class="sr-only" for="exampleInputAmount">
                            {Amount} ({in} {DEFAULT_CURRENY_CODE})
                        </label>
                        <div class="input-group">
                            <input class="form-control" id="exampleInputAmount" placeholder="" type="text">
                                <div class="input-group-addon">
                                    <span class="btn btn-link attach-link">
                                        <i class="fa fa-paperclip">
                                        </i>
                                        {Attach_New_File}
                                        <input class="btn-file upload-file" multiple="" name="files[]" type="file">
                                        </input>
                                    </span>
                                </div>
                            </input>
                        </div>
                    </div>
                    <!-- The table listing the files available for upload/download -->
                </form>
            </div>
        </div>
    </div>
</div>
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
    {% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">Processing...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td class="text-right">
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn_blue_new start" disabled>
                    <i class="fa fa-upload"></i>
                    <span>{Start}</span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn_blue_new cancel-btn cancel">
                    <i class="fa fa-close"></i>
                    <span>{Cancel}</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
</script>
