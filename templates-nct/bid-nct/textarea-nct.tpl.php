<form class="chat-form" id="bidMsgForm">
<input type="hidden" name="token" value="%tokenValue%">
	<div class="form-group">
		<textarea class="form-control" rows="4" placeholder="Describe your need for requesting changes in the bid."
                            id="description"
                            name="description"
                            data-validation="validateDescription"
                            data-validation-error-msg="{err_Please_check_description_carefully}"></textarea>
		<label><span id="pres-max-length">
				1200</span> {characters_left}</label>
	</div>
	<div class="form-group text-right">
		<input type="hidden" name="action" value="method"/>
		<input type="hidden" name="method" value="sendMessage"/>
		<button class="btn btn-link" type="submit" data-ele="submitBidMsg">
			{Send}
		</button>
	</div>
</form>