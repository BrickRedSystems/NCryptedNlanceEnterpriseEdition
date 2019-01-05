<div class="modal fade place-bid-popup" id="placeBidModal" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					&times;
				</button>
				<h3>{Place_a_Bid}</h3>
			</div>
			<div class="modal-body bid-modal-body">
				<form id="placeBidForm">
				<input type="hidden" name="token" value="%tokenValue%">
					<div class="row">
						<div class="col-sm-8">
							<div class="form-group">
								<input type="number" class="form-control" placeholder="Amount ({CURRENCY_SYMBOL})"
								value="%price%"
								name="price"
								data-validation="required number"
								data-validation-allowing="range[1.00;99999999999],float"
								data-validation-error-msg="{Please_enter_right_price_for_the_project}"/>
							</div>
						</div>
						<div class="col-sm-4">
							<span class="credit-note">
								{CREDITS_PER_BID} {Credits_Required}
							</span>
						</div>
						<div class="col-sm-12">
							<div class="form-group">
								<div class="input-group">
									<input type="number" class="form-control" placeholder="{Estimated_Delivery_Time}"
									value="%duration%"
									name="duration"
									data-validation="required number"
									data-validation-allowing="range[1;99999999999]"
									data-validation-error-msg="{err_Please_enter_days_for_the_project}">
									<div class="input-group-addon">
										<i class="fa fa-calendar"></i>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="form-group">
								<label class="inline-radio">{Escrow_Required}?</label>
								<div class="custom_radio">
									<label class="control control--radio">{Yes}
										<input %escrowY% type="radio"
										name="escrow"
										value="y"
										data-validation="required">
										<span class="control__indicator">
										</span> </label>
									<label class="control control--radio">{No}
										<input type="radio"
										%escrowN%
										name="escrow"
										value="n">
										<span class="control__indicator">
										</span> </label>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="form-group">
								<textarea class="form-control" id="bidDetail" rows="5" tabindex="4"
								name="bidDetail" 
								data-validation="validateDescription"
								data-validation-error-msg="{err_Please_check_description_carefully}">%bidDetail%</textarea>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="form-group">
								<input type="hidden" name="action" value="method"/>
								<input type="hidden" name="method" value="editBid"/>
								<button type="submit" class="btn btn_blue btn-block" data-ele="submitBid">
									<strong>{Submit}</strong>
								</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
