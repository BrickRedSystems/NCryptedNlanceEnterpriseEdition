<div class="mainpart">
	<!-- /Section-heading start -->
	<div class="section-head-bg">
		<div class="container wow fadeInRight">
			<div class="row">
				<div class="col-sm-6">
					<div class="customer-profile-detail provider-invi-btn">
						<img class="img-rounded" src="%profilePhoto%" alt="%fullName%">
						<span class="image-pattern %level_hide%">%level%</span>
						<h3>%fullName%<a href="{SITE_EDIT_PROFILE}" class="%edit_icon%" title="{Edit_Profile}"><i class="fa fa-pencil" title="{Edit_Profile}"></i></a><a href="javascript:void(0);" class="%heart_hide%" data-operation="favoriteUser" data-info="%userId%"><i class="fa %heart_icon%"></i></a></h3>						
						<p>
							%userType%
						</p>
						%review%
						%inviteProviderBtn%
					</div>
				</div>
				<div class="col-sm-6">
					<div class="customer-profile-list">
						<ul>
							<li>
								<span class="list-amount">
									%earned_or_spent_number%</span><span class="list-amount-tit">
									{Total} %earned_or_spent_text%</span>
							</li>
							<li>
								<span class="list-amount">
									%comp_proj%</span><span class="list-amount-tit">
									{COMPLETED_PROJECTS}</span>
							</li>
							<li>
								<span class="list-amount">
									%ongoing_proj%</span><span class="list-amount-tit">
									{ONGOING_PROJECTS}</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Section-heading over -->
	<!-- /Section-middle start -->
	<div class="section-middle">
		<div class="container">
			<div class="row">
				<div class="col-sm-4">
					<!-- customer-profile-left start -->
					<div class="customer-profile-left">
						<!-- contact-details-section start -->
						%contact%
						<!-- contact-details-section end -->
						<!-- verification-section start -->
						<div class="verification-section wow fadeInUp">
							<div class="verfication-section-tit">
								{Verifications}
							</div>
							<div class="verification-section-desc">
								<div class="verifiication-section-desc-part">
									<i class="fa fa-facebook-f" title="Facebook Verifiication"></i>
									<div class="fb">
										<i class="fa %f_check%"></i><span class="fb-connect">
											Facebook %f_connected%</span>%f_link%
									</div>
								</div>
								<div class="verifiication-section-desc-part">
									<i class="fa fa-google-plus" title="Google Plus Verifiication"></i>
									<div class="gplus">
										<i class="fa %g_check%"></i><span class="fb-connect">
											Google %g_connected%</span>%g_link%
									</div>
								</div>
								<div class="verifiication-section-desc-part">
									<i class="fa fa-linkedin" title="Linkedin Verifiication"></i>
									<div class="linkedin">
										<i class="fa %l_check%"></i><span class="fb-connect">
											linkedin %l_connected%</span>%l_link%
									</div>
								</div>
							</div>
						</div>
						<!-- verification-section end -->
					</div>
					<!-- customer-profile-left end -->
				</div>
				<div class="col-sm-8">
					<!-- contact-details-section start -->
					<div class="about-me-section wow fadeInUp">
						<div class="about-me-tit">
							{About_Me}
						</div>
						<div class="about-me-section-desc">
							<p>%about%</p>
						</div>
					</div>
					<!-- contact-details-section end -->
					<!-- job-detail-section start -->
					%projects_or_reviews%					
					<!-- job-detail-section end -->
				</div>
				<nav id="page-nav">
				  <a href="page/1/"></a>
				</nav>
			</div>
		</div>
	</div>
	<!-- /Section-middle over -->
</div>
