<div class="modal fade create-milestone-data" id="MilModal" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					&times;
				</button>
				<h3>{Create_Milestone}</h3>
			</div>
			<div class="modal-body bid-modal-body">
				<form id="milForm">
				<input type="hidden" name="token" value="%tokenValue%">
					<div class="clearfix"></div>
					<div class="milestones-form">
						
						<div class="form-group">
							<label>{Amount} ({PAYPAL_CURRENCY_CODE})</label>
							<input type="number" class="form-control" placeholder="{Amount_for_milestone}"
							name="price[]"
							data-validation="required number"
							data-validation-allowing="range[1.00;99999999999],float"
							data-validation-error-msg="{Please_enter_price_for_the_milestone}">
						</div>
						<div class="form-group">
							<label>{Delivery_Date}</label>
							<div class="input-group date" data-provide="datepicker" data-date-start-date="+1d" data-date-format="yyyy/mm/dd">
								<input type="text" class="form-control" placeholder="{Delivery_Date}"
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
                            data-validation-error-msg="{err_Please_check_description_carefully}"></textarea>
						</div>
					</div>
					
					<div class="clearfix"></div>
					<div class="center-block bottom-part">
						<a href="javascript:void(0);" data-ele="addMil"><i class="fa fa-plus-square-o"></i> {Add_a_Milestone}</a>
						<h3>{Milestone_Total}
						<span>
							{CURRENCY_SYMBOL}<span data-ele="milTotal">0.00</span> / %project_price%
						</span></h3>
					</div>
					<div class="form-group">
					    <input type="hidden" name="action" value="method"/>
					    <input type="hidden" name="method" value="createMilestones"/>
						<button type="submit" class="btn btn_blue btn-block" data-ele="submitMilForm">
							<strong>{Submit}</strong>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
