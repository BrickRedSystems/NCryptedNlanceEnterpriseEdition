
	<div class="sign_up">
		<div class="container wow slideInUp">
			<div class="row">
				<div class="col-md-6 col-md-offset-3 col-sm-10 col-sm-offset-1">
					<h1 id="login_tit" class="font_bold text-center marginbtm20">Resend activation email</h1>
					<div class="sign_up_form">
						<form id="reactivateForm" method="post">
						<input type="hidden" name="token" value="%tokenValue%">
							<div class="row">
								<div class="col-sm-12">
									<div class="form-group">
										<input type="text" class="form-control" placeholder="User Name or Email"
										name="email"
										tabindex="1"										
										data-validation="required"
										data-validation-error-msg="Please enter your user name or email">
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<input class="form-control" data-validation="recaptcha">					
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<input type="submit" name="submitReactivateForm" class="btn btn_blue btn-block btn_light_hover" value="Submit" tabindex="2">
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

