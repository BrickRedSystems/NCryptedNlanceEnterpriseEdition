<div class="mainpart">
	<!-- /Section-heading start -->
	<section class="section-heading">
		<div class="container">
			<h1>{Edit_Profile}</h1>
		</div>
	</section>
	<!-- /Section-heading over -->

	<!-- /Section-middle start -->
	<div class="edit-profile-main">
		<div class="container">
			<div class="white-box">
				<div class="left-bg">
					<div class="profile-img">

						<div class="profile-pic">
							<img data-ele="profile_pic" src="%show_main_user_image%" alt="{Change_Profile_Picture}" style="width: 100%;" title="{Your_profile_picture}">
						</div>
						<form id="frmProfilePic" name="frmProfilePic">
							<div data-ele="cropProfPic" class="profile-link" title="{Change_Profile_Picture}"> {Change_Profile_Picture}
							<input type="file" id="profilePhoto" name="profilePhoto" class="upload" accept="image/*" />
							<input type="hidden" name="crop_action" value="edit_profile" />
							<input type="hidden" name="height" id="height" value="500" />
							<input type="hidden" name="width" id="width" value="500" />
							<input type="hidden" name="dest_site_folder" id="dest_site_folder" value="{SITE_UPD}profile/" />
							<input type="hidden" name="dest_dir_folder" id="dest_dir_folder" value="{DIR_UPD}profile/" />
							</div>
						</form>
						<div class="user-email">
							<p class="mail-label" title="{Your_email_address}"><i class="fa fa-envelope"></i> %email%</p>
						</div>
					</div>
				</div>

				<div class="edit-form">
					<div class="row">
						<form id="editProfileForm">
						<input type="hidden" name="token" value="%tokenValue%">
							<div class="col-sm-6">
								<div class="form-group">
									<label>{First_Name}</label>
									<input type="text" class="form-control" placeholder="Enter first name" tabindex="1"
									name="firstName" value="%firstName%"
									data-validation="length alphanumeric"
									data-validation-length="2-25"
									data-validation-error-msg="{err_First_name_has_to_be_an_alphanumeric_value}">
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group">
									<label>{Last_Name}</label>
									<input type="text" class="form-control" placeholder="Enter last name" tabindex="2"
									name="lastName" value="%lastName%"
									data-validation="length alphanumeric"
									data-validation-length="2-25"
									data-validation-error-msg="{err_Last_name_has_to_be_an_alphanumeric_value}">
								</div>
							</div>

							<div class="col-sm-12">
								<div class="form-group">
									<label>{About_Me} (
										<span id="pres-max-length">
											800
										</span> {characters_left})</label>
									<textarea class="form-control" name="aboutMe" id="aboutMe" tabindex="5" rows="5" >%aboutMe%</textarea>
								</div>
							</div>
							<div class="col-sm-12">
								<div class="row">
									<div class="col-sm-12">
										<div class="form-group">
											<label>{Your_Language}</label>
											<select class="form-control" tabindex="8"
										name="langId"
										data-validation="required" 
		 								data-validation-error-msg="{err_Please_select_your_language}">
											<option value="">{Select_your_language}</option>
											%language_options%
										</select>
										</div>
									</div>
								</div>
							</div>

							<div class="col-sm-6  %hideSkillsForCustomer%">
								<div class="form-group">
									<label>Category</label>
									<select class="form-control" tabindex="4"
									data-ele="catId"
									name="catId" id="catId-input" 
									data-validation="required"
									data-validation-error-msg="{Please_select_your_category}">
										<option value="">{Select_your_category}</option>
										%cats%
									</select>
								</div>
							</div>
							<div class="col-sm-6  %hideSkillsForCustomer%">
								<div class="form-group">
									<label>{Sub_Category}</label>
									<select class="form-control" tabindex="5"
									data-ele="subcatId"
									name="subcatId"									
									data-validation-depends-on="catId">
										<option value="">{Select_your_sub_category}</option>
										%subcats%
									</select>
								</div>
							</div>
							<div class="col-sm-12 %hideSkillsForCustomer%">
								<div class="form-group">
									<label>{Skills}</label>
									<div class="tagscont" data-ele="skillsContainer"></div>
									<input type="text" class="tagsinput form-control" placeholder="{select_skills_examples_text}"
									name="skillsId" data-ele="skillsId" tabindex="9"	/>

								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label>{Country}</label>
									<select class="form-control" tabindex="10"
									data-ele="countryCode"
									name="countryCode" id="countryCode-input"
									data-validation="required"
									data-validation-error-msg="{err_Please_select_your_country}">
										<option value="">{Select_your_country}</option>
										%country_options%
									</select>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label>{State}</label>
									<select class="form-control" tabindex="11"
									data-ele="state"
									name="state" id="state-input" 
									data-validation="required"
									data-validation-depends-on="countryCode"
									data-validation-error-msg="{err_Please_select_your_state}">
										<option value="">{Select_your_state}</option>
										%state_options%
									</select>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label>{City}</label>
									<select class="form-control" tabindex="12"
									data-ele="city"
									name="city"   
									data-validation="required"
									data-validation-depends-on="countryCode, state"
									data-validation-error-msg="{err_Please_select_your_city}">
										<option value="">{Select_your_city}</option>
										%city_options%
									</select>
								</div>
							</div>
							<div class="clearfix"></div>
							<div class="col-sm-6">
								<div class="form-group">
									<label><img src="{SITE_IMG}paypal-logo.png" alt=""></label>
									<input type="email" class="form-control" tabindex="13"
									value="%paypalEmail%"
									name="paypalEmail"
									data-validation="email"
									data-validation-optional="true"
									data-validation-error-msg="{Please_check_your_playpal_ID}">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group phone-set">
									<label>{Contact_No}</label>
                                    <div class="country-code">
									<select class="form-control" tabindex="14" data-ele="select_code"
									name="contactCode"
									data-validation="required"
									data-validation-error-msg="{Please_select_your_dialing_code}">
										%contactCode_options%
									</select>
                                    </div>
									<input type="number" class="form-control" placeholder="{Contact_No}" tabindex="12"
									value="%contactNo%"
									name="contactNo"
									data-validation="number"
									data-validation-error-msg="{err_Contact_number_has_to_be_a_numeric_value}">
								</div>
							</div>
							<div class="col-sm-12">
								<div class="center-block text-center margintop20">
									<input type="hidden" name="action" value="submitEditProfile"/>
									<input type="submit" data-ele="submitEditProfile" name="submitEditProfile" class="btn btn_blue_new" value="{Update}" tabindex="17"/>
									<input type="button" data-ele="cancelEditProfile" class="btn btn-link" value="{Cancel}" tabindex="17"/>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Section-middle over -->
</div>
%cropmodal%
