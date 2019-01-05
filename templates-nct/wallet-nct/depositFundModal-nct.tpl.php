<!-- Redeem Popup start -->
<div class="modal fade" id="DepositFundModal" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header redeem-modal-header">
				<button type="button" class="close" data-dismiss="modal">
					&times;
				</button>
				<h3>{Deposit_Funds_To_Your_Wallet}</h3>
			</div>
			<div class="modal-body redeerm-modal-body">
				<div class="row">
					<div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
						<div class="sign_up_form">
							<div class="row">
								<form id="depositFundsForm" method="post" action="{SITE_URL}ajax-wallet-nct/" autocomplete="off">									
									<div class="col-sm-12">
										<div class="form-group">
											<input type="text" class="form-control" placeholder="{Amount}({DEFAULT_CURRENCY_CODE})"
											name="amount" tabindex="1"
											data-validation="required number"
											data-validation-allowing="range[1.00;99999999999],float"
											data-validation-error-msg="{err_Please_enter_valid_amount}">	
											<span class="commision">
												*{my_wallet_deposit_commission_text1} {DEPOSIT_COMMISSION}% {my_wallet_deposit_commission_text2}
											</span>									
										</div>
									</div>
									
									<div class="col-sm-12">
										<div class="form-group">
											<input type="hidden" name="action" value="submitDepositFunds"/>
											<input data-ele="submitDepositFunds" type="submit" name="submitDepositFunds" class="btn btn_blue btn-block btn_light_hover" value="{Deposit}" tabindex="2">
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