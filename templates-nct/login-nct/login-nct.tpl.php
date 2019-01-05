
	<div class="sign_up">
		<div class="container wow slideInUp">
			<div class="row">
				<div class="col-md-6 col-md-offset-3 col-sm-10 col-sm-offset-1">
					<h1 id="login_tit" class="font_bold text-center marginbtm20">{Login}</h1>
					<div class="sign_up_form">
						<form id="loginForm" method="post">
						<input type="hidden" name="token" value="%tokenValue%">
							<div class="row">
								<div class="col-sm-12">
									<div class="form-group">
										<input type="text" class="form-control" placeholder="{User_Name}" 
										value="%userName%"
										tabindex="1"
										name="email"
										data-validation="required"
										data-validation-error-msg="{err_Please_enter_your_user_name_or_email}">
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<input type="password" class="form-control" placeholder="{Password}"
										value="%password%"
										tabindex="2"
										name="password"
										data-validation="required"
										data-validation-error-msg="{err_Please_enter_your_password}">
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<input type="submit" name="submitLoginForm" class="btn btn_blue btn-block btn_light_hover" value="{Submit}" tabindex="3">
									</div>
								</div>
								<div class="col-sm-12 marginbtm20">
									<div class="row">
										<div class="col-sm-7">
											<label class="control control--checkbox">{Remember_Me_Next_Time}
												<input type="checkbox" name="remember_me" id="remember_me" value="y" %remember_me% tabindex="4"/>
												<span class="control__indicator"></span> </label>
										</div>
										<div class="col-sm-5">
											<div class="form-group text-right">
												<a href="{SITE_FORGOT}">{Forgot_Password}?</a>
											</div>
										</div>
									</div>
								</div>
								<div class="col-sm-12 marginbtm25">
									<div class="relative">
										<hr class="hr">
										<div class="or">
											<span>{OR}</span>
										</div>
									</div>
								</div>
								<div class="col-sm-12">
									<div class="with_social text-center">
										<a href="javascript:void(0)" onclick="login('facebook')"><i class="fa fa-facebook" aria-hidden="true" title="{Login_with} Facebook"></i></a>
										<a href="javascript:void(0)" onclick="login('linkedin')"><i class="fa fa-linkedin" aria-hidden="true" title="{Login_with} Linkedin"></i></a>
										<a href="javascript:void(0)" onclick="login('google')"><i class="fa fa-google-plus" aria-hidden="true" title="{Login_with} Google"></i></a>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

