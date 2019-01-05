<div class="section-redeem-header">
	<h3>*{Available_Balance}: %available%</h3>
	<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover %hideRequestToRedeem%" data-ele="openReedemModal">{Request_to_Redeem}</a>
</div>
<!-- section-middle-right start -->
<div class="section-redeem">
	<div class="redeem-history-table">
		<div class="hist-tables">
			<div class="thead">
				<div class="th">
					{Requested_Amount} ({CURRENCY_SYMBOL})
				</div>
				<div class="th">
					{Requested_Date}
				</div>
				<div class="th">
					{Description}
				</div>
				<div class="th">
					{Status}
				</div>
				<div class="th">
					{Redeemed_Amount} ({CURRENCY_SYMBOL})
				</div>
				<div class="th">
					{Redeemed_Date}
				</div>
			</div>
			%rows%
		</div>
		<div class="no_data_static %no_data_hide%">
			%text%
		</div>
	</div>
</div>
