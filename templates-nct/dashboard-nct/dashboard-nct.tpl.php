<div class="mainpart">
	<!-- /Section-heading start -->
	<section class="section-head-bg ">
		<div class="container wow fadeInRight">
			<div class="row">
				<div class="col-sm-6">
					<div class="customer-profile-detail">
						<img class="img-rounded" src="%profilePhoto%" alt="%fullName%">
						<span class="image-pattern %level_hide%"> %level%</span>
						<h3>%fullName%<a href="{SITE_EDIT_PROFILE}" class="%edit_icon%" title="{Edit_Profile}"><i class="fa fa-pencil"></i></a></h3>

						<p>
							%userType%
						</p>
						%review%
					</div>
				</div>
				<div class="col-sm-6">
					<div class="customer-profile-list">
						<ul>
							<li class="%hide_earned_only%">
								<span class="list-amount"> %earned_or_spent_number% </span>
								<span class="list-amount-tit"> {Total} %earned_or_spent_text% </span>
							</li>
							<li>
								<span class="list-amount"> %comp_proj% </span>
								<span class="list-amount-tit"> {COMPLETED_PROJECTS} </span>
							</li>
							<li>
								<span class="list-amount"> %ongoing_proj% </span>
								<span class="list-amount-tit"> {ONGOING_PROJECTS} </span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- /Section-heading over -->
	<!-- /Section-middle start -->
	<div class="section-middle">
		<div class="container">
			<div class="row">
				<div class="col-sm-4">
					<!-- customer-profile-left start -->
					<div class="customer-profile-left">
						<!-- contact-details-section start -->
						%contact% <!-- contact-details-section end -->
						<!-- verification-section start -->
						<div class="verification-section wow fadeInUp">
							<div class="verfication-section-tit">
								{Verifications}
							</div>
							<div class="verification-section-desc">
								<div class="verifiication-section-desc-part">
									<i class="fa fa-facebook-f" title="Facebook {Verifiication}"></i>
									<div class="fb">
										<i class="fa %f_check%"></i>
										<span class="fb-connect"> Facebook %f_connected% </span>
										%f_link%
									</div>
								</div>
								<div class="verifiication-section-desc-part">
									<i class="fa fa-google-plus" title="Google Plus {Verifiication}"></i>
									<div class="gplus">
										<i class="fa %g_check%"></i>
										<span class="fb-connect"> Google %g_connected% </span>
										%g_link%
									</div>
								</div>
								<div class="verifiication-section-desc-part">
									<i class="fa fa-linkedin" title="Linkedin {Verifiication}"></i>
									<div class="linkedin">
										<i class="fa %l_check%"></i>
										<span class="fb-connect"> linkedin %l_connected% </span>
										%l_link%
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
					<div class="project-section wow fadeInUp">
						<div class="project-section-tit">
							%proj_section_headline%? <a href="%redirect_link%" class="btn btn_blue_new btn_light_hover">{Click_Here}</a>
						</div>
					</div>
					<!-- contact-details-section end -->
					<!-- notification-section start -->
					<div class="notification-section wow fadeInUp" id="notifications">
						<div class="notification-tit">
							{Notifications} (%totalUnreadNoti%)
						</div>
						<div class="notification-desc ">
							<div class="noti-height mCustomScrollbar">
								<ul>
									%notifications%
								</ul>
							</div>

						</div>
					</div>
					<!-- notification-section end -->
				</div>
			</div>
		</div>
	</div>
	<!-- /Section-middle over -->
</div>
