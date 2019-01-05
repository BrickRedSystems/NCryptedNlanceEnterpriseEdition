
	<div class="sign_up">
		<div class="container wow slideInUp">
			<div class="row">
				<div class="col-md-6 col-md-offset-3 col-sm-10 col-sm-offset-1">
					<h5 class="font_bold marginbtm20">{Sign_Up}</h5>
					<form id="signUp" method="post">
					<input type="hidden" name="token" value="%tokenValue%">
						<div class="custom_radio">
							<label class="control control--radio">{Customer}
								<input type="radio" name="userType" value="c" %selected_customer% tabindex="1"/>
								<span class="control__indicator"></span> </label>
							<label class="control control--radio">{Provider}
								<input type="radio" name="userType" value="p" %selected_provider% tabindex="2"/>
								<span class="control__indicator"></span> </label>
						</div>
						<div class="sign_up_form">
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<input class="form-control" tabindex="3"
										name="firstName"
										placeholder="{First_Name}"
										data-validation="length alphanumeric"
										data-validation-length="2-25"
										data-validation-error-msg="{err_First_name_has_to_be_an_alphanumeric_value}">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<input type="text" class="form-control" placeholder="{Last_Name}" tabindex="4"
										name="lastName"
										data-validation="length alphanumeric"
										data-validation-length="2-25"
										data-validation-error-msg="{err_Last_name_has_to_be_an_alphanumeric_value}">
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<input type="email" class="form-control" placeholder="{Email}" tabindex="5"
										name="email"
										data-validation="server" 
										data-validation-req-params='{ "action":"chk_email"}'
										data-validation-param-name="email"        
										data-validation-url="{SITE_URL}ajax-registration-nct/">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<select class="form-control" tabindex="6"
										name="langId"
										data-validation="required" 
		 								data-validation-error-msg="{err_Please_select_your_language}">
											<option value="">{Select_your_language}</option>
											%language_options%
										</select>
									</div>
								</div>
								
								<div class="col-sm-6">
									<div class="form-group">
										<select class="form-control" tabindex="7"
										name="countryCode"
										data-validation="required" 
		 								data-validation-error-msg="{err_Please_select_your_country}">
											<option value="">{Select_your_country}</option>
											%country_options%
										</select>
									</div>
								</div>

								<div class="col-sm-12">
									<div class="form-group">
										<div class="row">
											<div class="col-xs-5 col-sm-4 paddingright0">
											    <select class="form-control" tabindex="8" 
                                                name="contactCode"
                                                data-validation="required" 
		 										data-validation-error-msg="{Please_select_dialing_code}">
                                                	<option value="">{Select_code}</option>
                                                    %contactCode_options%
                                                </select>
																								
											</div>
											<div class="col-xs-7 col-sm-8">
												<input type="text" class="form-control" placeholder="{Contact_No}" tabindex="9" 
												name="contactNo" 
												data-validation-optional="true"
												data-validation="number"
												data-validation-error-msg="{err_Contact_number_has_to_be_a_numeric_value}">
											</div>
										</div>
									</div>
								</div>

								
								<div class="col-sm-12">
									<div class="form-group">
										<input type="text" class="form-control" placeholder="{User_Name}" tabindex="10"	
										name="userName"								
										data-validation="server alphanumeric" 
										data-validation-req-params='{ "action":"chk_uname"}'
										data-validation-param-name="userName"        
										data-validation-url="{SITE_URL}ajax-registration-nct/">
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<input type="password" class="form-control" placeholder="{Password}" tabindex="11"
										name="password_confirmation"
										data-validation="length strength" 
										data-validation-length="min6"
		 								data-validation-strength="2"
		 								data-validation-error-msg="{err_input_value_is_shorter_than_6_characters}">
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<input type="password" class="form-control" placeholder="{Confirm_Password}" tabindex="12"
										name="password" 
										data-validation="confirmation"
										data-validation-error-msg="{err_Password_and_confirm_password_must_be_same}">
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<input class="form-control" data-validation="recaptcha">					
									</div>
								</div>
								<div class="col-sm-12 marginbtm15">
									<label class="control control--checkbox margintop15">{By_creating_an_account_you_agree_to_our}
										<br/>
										<a target="_blank" href="{SITE_CONTENT}%terms_url%">{SITE_NM} {Marketplace_User_Agreement}</a> {and} <a target="_blank" href="{SITE_CONTENT}%privacy_url%">{Privacy_policy}</a>
										<input type="checkbox" tabindex="13"
										data-validation="required" 
		 								data-validation-error-msg="{You_must_agree_to_our_terms_to_register_yourself}"/>
										<span class="control__indicator"></span> </label>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<input class="btn btn_blue btn-block btn_light_hover" name="submitSignup" id="submitSignup" type="submit" value="{get_started}" tabindex="14">
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
										<a href="javascript:void(0)" onclick="login('facebook')" tabindex="15"><i class="fa fa-facebook" aria-hidden="true" title="Sign up with Facebook"></i></a>
										<a href="javascript:void(0)" onclick="login('linkedin')" tabindex="16"><i class="fa fa-linkedin" aria-hidden="true" title="Sign up with Linkedin"></i></a>
										<a href="javascript:void(0)" onclick="login('google')" tabindex="17"><i class="fa fa-google-plus" aria-hidden="true" title="Sign up with Google"></i></a>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>


