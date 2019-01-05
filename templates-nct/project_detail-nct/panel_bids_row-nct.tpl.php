<li class="customer-bids-cell" data-infinite="row">
	<div class="customer-bids-left">
		<a href="%profileLink%" class="customer-bids-user">
		<span class="customer-user-photo">
			<img src="%profilePhoto%" alt="">
		</span> %fullName% </a>
		<div class="clearfix"></div>
		<ul class="retings">
			<li>
				<a href="javascript:void(0);"><i class="fa fa-star"></i></a>
			</li>
			<li>
				<strong>%averageRating%</strong>
			</li>
			<li>
				(%totalReviews%)
			</li>
		</ul>
	</div>
	<div class="customer-bids-right-content">
		<p class="search-disc">
			%bidDetail% <a href="{SITE_BID}%bidId%/" class="underline">{View_Details}</a>
		</p>
		<div class="clearfix"></div>
		<div class="center-block">
			<h3 class="bid-price">{CURRENCY_SYMBOL}%price%</h3>
			<p class="req-text %escrow%">
				<i class="fa fa-bank"></i> {Escrow_Required}
			</p>
		</div>
		<div class="clearfix"></div>
		<ul class="date-time">
			<li>
				{Duration} <strong>%duration% {days}</strong>
			</li>
			<li>
				{Posted_on} <strong>%createdTime%</strong>
			</li>
		</ul>
		%bidsBtns%
		
	</div>
</li>