<div class="mainpart">
	<!-- /Section-heading start -->
	<section class="section-heading">
		<div class="container">
			<h1>{Account_Settings}</h1>
		</div>
	</section>
	<!-- /Section-heading over -->
	<!-- /Section-middle start -->
	<div class="account-settings-main">
		<div class="container">
			<div class="row">
				<div class="col-sm-3 section-middle clearfix">
					<div class="section-middle-left">
						<ul>
							<li class="active" data-ele="settings_tabs" data-target="tab_noti">
								{Manage_Notifications}
								<span class="count-figure">
									%noti_type_count%
								</span>
							</li>
							<li data-ele="settings_tabs" data-target="tab_change_pass">
								{Change_Password}
							</li>
						</ul>
					</div>
				</div>
				<div class="col-sm-9">
					<div class="white-box">
						<div class="row">
							<div class="col-sm-8" data-panel="settings_panels" data-ele="tab_noti">
								<ul class="note-main">
									%noti_settings%
								</ul>
							</div>
							<div class="col-sm-8 col-md-7 col-lg-7 hide" data-panel="settings_panels" data-ele="tab_change_pass">
								<form id="changePasswordForm">
								<input type="hidden" name="token" value="%tokenValue%">
									<div class="form-group">
										<input class="form-control" type="password" placeholder="{Old_Password}"
										name="oldpassword"
										data-validation="length"
										data-validation-length="min6"
										data-validation-error-msg="{err_input_value_is_shorter_than_6_characters}">
									</div>
									<div class="form-group">
										<input class="form-control" type="password" placeholder="{New_Password}"
										name="password_confirmation"
										data-validation="length strength"
										data-validation-length="min6"
										data-validation-strength="2"
										data-validation-error-msg="{err_input_value_is_shorter_than_6_characters}">
									</div>
									<div class="form-group">
										<input class="form-control" type="password" placeholder="{Confirm_Password}"
										name="password" 
										data-validation="confirmation"
										data-validation-error-msg="{err_Password_and_confirm_password_must_be_same}">
									</div>
									<div class="form-group">
										<input type="hidden" value="submitChangePasswordForm" name="action"/>
										<button type="submit" data-ele="submitChangePasswordForm" class="btn btn_blue_new">
											{Submit}
										</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Section-middle over -->
</div>
