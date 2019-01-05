<div class="section-middle-right">
	<div class="section-wallet">
		<div class="sectionwallet-right-border"></div>
		<div class="clearfix"></div>
		<div class="section-middle-wallet">
			<div class="section-wallet-left">
				<h4>{Available_Balance}</h4>
				<p>
					{my_wallet_available_balance_text}
				</p>
			</div>
			<div class="section-wallet-right">
				<span class="round-wallet-available">
					{CURRENCY_SYMBOL}
				</span>
				%available%
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="section-middle-wallet">
			<div class="section-wallet-left">
				<h4>{Pending_Amount}</h4>
				<p>
					{my_wallet_pending_amount_text}
				</p>
			</div>
			<div class="section-wallet-right">
				<span class="round-wallet-available">
					{CURRENCY_SYMBOL}
				</span>
				%pending%
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="section-middle-wallet">
			<div class="section-wallet-left">
				<h4>{Requested_for_Redeem}</h4>
				<p>
					{my_wallet_requested_for_redeem_text}
				</p>
			</div>
			<div class="section-wallet-right">
				<span class="round-wallet-available">
					{CURRENCY_SYMBOL}
				</span>
				%requested%
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="section-middle-wallet %hideCreditSection%">
            <div class="section-wallet-left">
                <h4>{total_credits}</h4>
                <p>
                    {my_wallet_total_credits_text}
                </p>
            </div>
            <div class="section-wallet-right">
                <span class="round-wallet-available">
                    #
                </span>
                %totalCredits%
            </div>
        </div>
        <div class="clearfix"></div>
		<div class="section-wallet-button">
			<a href="javascript:void(0);" data-ele="openDepositFundModal" class="btn btn_blue_new btn_light_hover">{Deposit_fund}</a>
			<a href="javascript:void(0);" data-ele="openReedemModal" class="btn btn_dark_new btn_dark_hover %hideRequestToRedeem%">{Redeem_Request}</a>
		</div>
		<div class="clearfix"></div>
		<div class="citation-wrapper">
			<p class="citation">
	            *{my_wallet_deposit_commission_text1} {DEPOSIT_COMMISSION}% {my_wallet_deposit_commission_text2}
	        </p>        	
	        <p class="citation">
	        	*{my_wallet_redemption_commission_text1} {REDEEM_COMMISSION}% {my_wallet_redemption_commission_text2}
	        </p>
		</div>
		
	</div>
</div>
