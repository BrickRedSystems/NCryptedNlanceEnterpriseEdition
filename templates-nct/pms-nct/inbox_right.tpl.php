<div role="tabpanel" class="tab-pane active" id="{{id}}">
            <h1>Conversation with <span class="text-blue">{{username}}</span> <a href="#" class="btn btn-sm" data-target="#messages-deleted-items" data-toggle="modal">Delete conversation</a> </h1>
            <a href="#" class="load-msg">Load earlier messages..</a>
                <div id="content-2" class="msg-box-inbox inbox_right_content mCustomScrollbar">
                    <div class="msg-chat-main">
                        <div class="msg-inbox-chat">
                        {{conversation}}
                        </div>
                    </div>
                </div>
                <form class="chat-form">
                    <div class="form-group">
                        <textarea class="form-control" rows="2" placeholder="Your message here"></textarea>
                    </div>
                    <div class="form-group text-right">
                        <button class="btn btn-sm" type="submit">Reply</button>
                    </div>
                </form> 
        </div>  
                
                            
                        
            