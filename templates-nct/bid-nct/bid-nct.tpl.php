<div class="mainpart">
	<!-- Section-heading start -->
	<section class="section-heading">
		<div class="container">
			<ol class="breadcrumb %customerBreadcrums%">
				<li>
					<h1><a href="%projectLink%">%title%</a></h1>
				</li>
				<li>
					{Bids}
				</li>
				<li class="active">
					{Bid_Details}
				</li>
			</ol>
			<ol class="breadcrumb %providerBreadcrums%">
                <li>
                    <h1><a href="%projectLink%">%title%</a></h1>
                </li>
                <li class="active">
                    Your {Bid_Details}
                </li>
            </ol>
		</div>
	</section>
	<div class="bid-detail-main">
		<div class="container">
			<div class="row">
				<div class="col-sm-8 col-md-9">
					<div class="white-box marginbtm20">
						<div class="bid-detail-top">
							<p>
								%bidDetail%
							</p>
							<ul class="price-date">
								<li>
									<h5>{CURRENCY_SYMBOL}%price%</h5>
								</li>
								<li class="text12 gray-color %escrow%">
									<i class="fa fa-bank"></i> {Escrow_Required}
								</li>
							</ul>
							<ul class="price-date">
								<li>
									{Duration} <strong>%duration% days</strong>
								</li>
								<li>
									{Posted_on} <strong>%postedOn%</strong>
								</li>
							</ul>
							<div class="bid-right">
								%accept_or_modify_bid%
							</div>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="clearfix"></div>
					<div id="message" class="msg-box-inbox mCustomScrollbar">
						
							%past%
						
					</div>
					<div class="clearfix"></div>
					%textarea%
				</div>
				<div class="col-sm-4 col-md-3">
					<div class="white-box">
						<div class="about-client">
							<h2>{About_Provider}</h2>
							<div class="about-top">
								<a href="%profileLink%" class="client-img"> <img src="%profilePhoto%" alt="%fullName%"> </a>
								<h3>%fullName%</h3>
								<p>
									<i class="fa fa-map-marker"></i> %location%
								</p>
							</div>
							<ul class="about-row">
								<li class="about-cell clearfix">
									<div class="left-part">
										{Jobs_Completed}
									</div>
									<div class="right-part">
										<strong>%completed%</strong>
									</div>
								</li>
								<li class="about-cell clearfix">
									<div class="left-part">
										{Jobs_In_Progress}
									</div>
									<div class="right-part">
										<strong>%ongoing%</strong>
									</div>
								</li>
								<li class="about-cell clearfix">
									<div class="left-part">
										{Member_Since}
									</div>
									<div class="right-part">
										<strong>%providerCreatedDate%</strong>
									</div>
								</li>
								<li class="hide">
									<a href="javascript:void(0);" class="btn btn_blue_new dark-blue-btn">{Message}</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
