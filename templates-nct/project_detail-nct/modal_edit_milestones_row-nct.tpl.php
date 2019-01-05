<div class="milestones-form">
	<div class="form-group">
		<label>{Amount} ({PAYPAL_CURRENCY_CODE})</label>
		<input type="number" class="form-control" placeholder="{Amount_for_milestone}"
		value="%price%"
		name="price[]"
		data-validation="required number"
		data-validation-allowing="range[1.00;99999999999],float"
		data-validation-error-msg="{Please_enter_price_for_the_milestone}">
	</div>
	<div class="form-group">
		<label>{Delivery_Date}</label>
		<div class="input-group date" data-provide="datepicker" data-date-start-date="+1d" data-date-format="yyyy/mm/dd">
			<input type="text" class="form-control" placeholder="{Delivery_Date}"
			value="%milestone_date%"
			name="milestone_date[]"
			data-validation="date required"
			data-validation-format="yyyy/mm/dd"
			data-validation-error-msg="{Please_select_deadline_for_this_milestone}">
			<div class="input-group-addon">
				<span class="fa fa-calendar">
				</span>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label>{Description}</label>
		<textarea class="form-control" rows="2" placeholder="{Describe_what_will_you_complete_in_this_milestone}"
        name="description[]" 
        data-validation="validateDescription"
        data-validation-error-msg="{err_Please_check_description_carefully}">%description%</textarea>
	</div>
</div>