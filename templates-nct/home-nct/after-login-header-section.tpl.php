<ul class="nav navbar-nav navbar-right navbar-inner %navbar_Type% relative">
	<li class="nav-right-menulist">
		<div class="dropdown dropdown-inner profilename drpdwn">
			<a id="dLabel-3" href="javascript:void(0);" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{Messages}"> <i class="fa fa-envelope-o"></i><sup class="inner-sup %hideUnreadMessageCount%" data-ele="msgCounter">%unreadMessageCount%</sup> </a>
			<div class="dropdown-menu" aria-labelledby="dLabel-3">
				<div class="dropdown-menu-header">
					<h6>{Messages}</h6>
				</div>
				<div class="dropdown-menu-body">
					<ul class="msg-list">
						%messages%
					</ul>
				</div>
				<div class="dropdown-menu-footer">
					<a href="{SITE_MESSAGES}">{view_all}</a>
				</div>
			</div>
		</div>
	</li>
	<li class="nav-right-menulist">
		<div class="dropdown dropdown-inner profilename drpdwn">
			<a id="dLabel-1" href="javascript:void(0);" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{Notifications}"> <i class="fa fa-bell-o"></i><sup class="inner-sup %hideUnreadNotiCount%" data-ele="notiCounter">%unreadNotiCount%</sup> </a>
			<div class="dropdown-menu" aria-labelledby="dLabel-1">
				<div class="dropdown-menu-header">
                    <h6>{Notifications}</h6>
                </div>
				<div class="dropdown-menu-body">
					<ul>
						%notifications%
					</ul>
				</div>
				<div class="dropdown-menu-footer">
					<a href="{SITE_DASHBOARD}#notifications">{view_all}</a>
				</div>
			</div>
		</div>
	</li>
	<li class="user-profile">
		<div class="dropdown dropdown-inner profilename drpdwn">
			<a id="dLabel-2" href="%profileLink%" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <img class="img-rounded profileimage" src="%profilePhoto%" alt="">%userName% </a>
			<div class="dropdown-menu" aria-labelledby="dLabel-2">
				<div class="dropdown-menu-body">
					<ul>
						<li>
							<a href="{SITE_URL}%profileLink%" title="{My_Profile}"> <h5>{My_Profile}</h5> </a>
						</li>
						<li>
							<a href="{SITE_DASHBOARD}" title="{My_Dashboard}"> <h5>{My_Dashboard}</h5> </a>
						</li>
						<li>
							<a href="{SITE_EDIT_PROFILE}" title="{Edit_Profile}"> <h5>{Edit_Profile}</h5> </a>
						</li>
						<li>
							<a href="{SITE_ACC_SETTINGS}" title="{Account_Settings}"> <h5>{Account_Settings}</h5> </a>
						</li>
						<li>
							<a href="{SITE_WALLET}" title="{My_Wallet}"> <h5>{My_Wallet}</h5> </a>
						</li>
						<li>
							<a href="{SITE_FINANCIAL_INFO}" title="{Financial_Information}"> <h5>{Financial_Information}</h5> </a>
						</li>
						<?php global $sessUserType; if($sessUserType == 'p'){?>
						<li>
							<a href="{SITE_MEM_PLANS}" title="{Membership_Plans}"> <h5>{Membership_Plans}</h5> </a>
						</li>
						<li>
							<a href="{SITE_PROJECTS}" title="{My_Projects}"> <h5>{My_Projects}</h5> </a>
						</li>
						<?php }else{ ?>
						<li>
							<a href="{SITE_PROVIDERS}" title="{My_Providers}"> <h5>{My_Providers}</h5> </a>
						</li>
						<li>
							<a href="{SITE_PROJECTS}" title="{My_Projects}"> <h5>{My_Projects}</h5> </a>
						</li>
						<li>
							<a href="{SITE_PROJECT_POST}" title="{Post_a_Project}"> <h5>{Post_Project}</h5> </a>
						</li>
						<?php } ?>		
						<li>
                            <a href="{SITE_FAVORITES}" title="{My_Favorites}"> <h5>{My_Favorites}</h5> </a>
                        </li>				
						<li>
                            <a href="{SITE_REVIEWS}" title="{My_Reviews}"> <h5>{My_Reviews}</h5> </a>
                        </li>
						<li>
							<a href="{SITE_LOGOUT}" title="{Logout}"> <h5>{Logout}</h5> </a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</li>
	<li class="dropdown drpdwn langdrpdwn">
				<select class="form-control"
				data-ele="userLanguage"							
				name="userLanguage">								
					%lang_options%
				</select>
			</li>
</ul>