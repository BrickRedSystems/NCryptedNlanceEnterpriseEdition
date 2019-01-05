<form class="chat-form" id="disputeMsgForm">
<input type="hidden" name="token" value="%tokenValue%">
	<div class="form-group">
		<textarea class="form-control" rows="4" placeholder="{Write_your_message_here}"
        id="description"
        name="description"
        data-validation="validateDescription"
        data-validation-error-msg="{Please_check_your_message_carefully}"></textarea>
	</div>
	<div class="form-group text-right">
		<input type="hidden" name="action" value="method"/>
		<input type="hidden" name="method" value="sendDisputeMessage"/>
		<button class="btn btn-link" type="submit" data-ele="submitDisputeMsg">
			{Send}
		</button>
	</div>
</form>