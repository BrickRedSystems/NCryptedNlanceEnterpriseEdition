<!-- Redeem Popup start -->
<div class="modal fade" id="openReedemModal" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header redeem-modal-header">
				<button type="button" class="close" data-dismiss="modal">
					&times;
				</button>
				<h3>*{Available_Balance}: {CURRENCY_SYMBOL}%available%</h3>
			</div>
			<div class="modal-body redeerm-modal-body">
				<div class="row">
					<div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
						<div class="sign_up_form">
							<div class="row">
								<form id="redeemRequestForm">
								<input type="hidden" name="token" value="%tokenValue%">
									<div class="col-sm-12">
										<div class="form-group">
											<input type="email" class="form-control" placeholder="{Paypal_Email}" value="%email%"
											name="paypalId"
											data-validation="required email"
											data-validation-error-msg="{Please_enter_your_valid_paypal_id}">
										</div>
									</div>
									<div class="col-sm-12">
										<div class="form-group">
											<input type="text" class="form-control" placeholder="{Amount} ({CURRENCY_SYMBOL})"
											name="amount"
											data-validation="required number"
											data-validation-allowing="range[1.00;%available%],float"
											data-validation-error-msg="{Please_enter_valid_redemption_amount_between_1_and} %available%">
											<span class="commision">
												{REDEEM_COMMISSION}% {commission_is_applicable_on_every_redemption}
											</span>
										</div>
									</div>
									<div class="col-sm-12">
										<div class="form-group">
											<textarea class="form-control" rows="5" id="description" name="description" placeholder="{Description}"
											data-validation="required"
											data-validation-error-msg="{Please_describe_your_reason_for_this_redemption}"></textarea>
											<span class="commision" id="pres-max-length">
												500
											</span>
											{characters_left}
										</div>
									</div>
									<div class="col-sm-12">
										<div class="form-group">
											<input type="hidden" name="action" value="submitRedeemRequest"/>
											<input data-ele="submitRedeemRequest" type="submit" name="submitRedeemRequest" class="btn btn_blue btn-block btn_light_hover" value="{Submit}">
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					{Close}
				</button>
			</div>
		</div>
	</div>
</div>
<!-- Redeem Popup End -->