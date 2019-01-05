<!-- /Section-middle start -->
<div class="membership-plan">
	<div class="container wow fadeInUp">
		<div class="row">
			%plans%
			<div class="col-sm-12">
				<div class="credit-section clearfix wow fadeInLeft">
					<p class="credit-disc">
						{adhoc_credit_text} <strong><a href="javascript:void(0)" class="credit-link">{Click_Here}</a></strong>
					</p>
					<div class="clearfix"></div>
					<form id="adhocCrediForm">
					<input type="hidden" name="token" value="%tokenValue%">
						<ul class="credit-row clearfix">
							<li class="credit-cell">
								<h3>{Unit_Price}</h3>
								<div class="credit-box">
									{CURRENCY_SYMBOL}%BUNCH_PRICE%
								</div>
							</li>
							<li class="credit-cell credit-blank">
								<div class="credit-box">
									<i class="fa fa-times"></i>
								</div>
							</li>
							<li class="credit-cell">
								<h3>{No_of_Credits}</h3>
								<input type="number" class="form-control"
								onkeyup="$('.credit-box span').html(($(this).val()/%CREDIT_BUNCH%)*%BUNCH_PRICE%);"
								name="credits" data-ele="credits"								
								data-validation="multiple_of_number"
								data-validation-error-msg="{Please_enter_required_credits}">
							</li>
							<li class="credit-cell credit-blank">
								<div class="credit-box">
									=
								</div>
							</li>
							<li class="credit-cell">
								<h3>{Total}</h3>
								<div class="credit-box">
									{CURRENCY_SYMBOL}<span>0.00</span>
								</div>
							</li>
							<li class="credit-cell credit-blank">
								<div class="credit-box">
									<input type="hidden" name="action" value="adHocCredits" />
									<button class="btn btn_blue_new" type="submit" name="submitAdhocCrediForm" data-ele="submitAdhocCrediForm">{Purchase}</button>
									
								</div>
							</li>
						</ul>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /Section-middle over -->
