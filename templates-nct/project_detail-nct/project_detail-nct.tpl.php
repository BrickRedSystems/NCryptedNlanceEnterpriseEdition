<div class="mainpart">
	<!-- /Section-heading start -->
	<section class="section-heading">
		<div class="container">
			<h2>{Project_Details}</h2>
		</div>
	</section>
	<div class="project-detail-main">
		<div class="container">
			<div class="project-detail-top">
				<div class="row">
					<div class="col-sm-8 col-md-9">
						<div class="project-detail">
							<h1>%title%</h1>
							<div class="price-right">
								<h2>{CURRENCY_SYMBOL}%budget%</h2>
								<span class="open-label %jobStatusClass%">%jobStatus%</span>
							</div>
							<div class="clearfix"></div>
							<ul class="detail-row">
								<li class="%isFeatured%">
									<a href="{SITE_SEARCH}?isFeatured=y" class="feature-label">{Featured}</a>
								</li>
								<li>
									%cat%
								</li>
								<li>
									{Experience_Level} <strong>%experienceWanted%</strong>
								</li>
								<li class="%hidefeaturedExpiryDate%">{Last_featured_date}: <strong>%featuredExpiryDate%</strong></li>
							</ul>
						</div>
						<ul class="project-detail-bottom">
							<li>
								<p>
									{Estimated_Time}
								</p>
								<h4>%duration% {days}</h4>
							</li>
							<li>
								<p>
									{Bids}
								</p>
								
								<div class="tooltip-top">
									<h4><strong class="blue-color"> %bids%</strong></h4>
									<div class="tooltip_box">
										<ul class="tooltip-row">
											<li class="left-cell">
												{Average_Value}
											</li>
											<li class="right-cell">
												<strong>{CURRENCY_SYMBOL}%avgBid%</strong>
											</li>
											<li class="left-cell">
												{Average_ETA}
											</li>
											<li class="right-cell">
												<strong>%avgETA% {days}</strong>
											</li>
										</ul>
									</div> 
								</div> 
								
							</li>
							<li>
								<p>
									{Invited_Provider}
								</p>
								<h4>%invited%</h4>
							</li>
							<li>
								<p>
									{Posted_on}
								</p>
								<h4>%createdDate%</h4>
							</li>
							<li>
								<p>
									{Bidding_ends_on}
								</p>
								<h4>%biddingDeadline%</h4>
							</li>
						</ul>
					</div>
					<div class="col-sm-4 col-md-3">
						<ul class="project-buttons">
							<li data-ele="placeABidBtn">%place_bid_btn%</li>

							
							<li class="%hide_escalate%">%escalate_text%</li>


							<li class="%hide_fav%">%fav_text%</li>

							
							<li class="%hide_report%">%report_text%</li>

							
							<li class="%hide_reopen%">%reopenlink%</li>


							<li class="%hide_invite%">%invitelink%</li>


						</ul>
					</div>
				</div>
			</div>
			<div class="project-bottom-part">
				<div class="row">
					<div class="col-sm-8 col-md-9">
						<div class="project-bottom-part">
							<div class="white-box">
								<span class="status-menu">&nbsp;<i class="fa fa-navicon"></i> </span>
								<ul class="project-tab" data-ele="project_tabs">
									%tab_ul%
								</ul>
								<div class="clearfix"></div>
								<div class="open-provider-about clearfix" data-ele="tab_panel" data-infinite="container">
									%tab_panel%
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-4 col-md-3">
						%about_client%
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
