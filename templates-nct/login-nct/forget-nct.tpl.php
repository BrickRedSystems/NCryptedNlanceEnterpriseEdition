
	<div class="sign_up">
		<div class="container wow slideInUp">
			<div class="row">
				<div class="col-md-6 col-md-offset-3 col-sm-10 col-sm-offset-1">
					<h1 id="login_tit" class="font_bold text-center marginbtm20">{Forgot_your_password}</h1>
					<div class="sign_up_form">
						<form id="forgetForm" method="post">
						<input type="hidden" name="token" value="%tokenValue%">
							<div class="row">
								<div class="col-sm-12">
									<div class="form-group">
										<input type="text" class="form-control" placeholder="{Please_enter_your_user_name_or_email}"
										name="email"
										tabindex="1"										
										data-validation="required"
										data-validation-error-msg="{Please_enter_your_user_name_or_email}">
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<input class="form-control" data-validation="recaptcha">					
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<input type="submit" name="submitForgetForm" class="btn btn_blue btn-block btn_light_hover" value="{Submit}" tabindex="2">
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
