<section>
	<div class="chose_user_type">
		<div class="container">
			<h2 class="font_light text-center">{Choose_User_Type}</h2>
			<div class="row">
				<form id="formUserType" method="post">
				<input type="hidden" name="token" value="%tokenValue%">
					<div class="col-sm-5">
						<div class="want_to center">
							<img src="{SITE_IMG}customer.png" alt="{Customer}">
							<h4 class="font_light marginbtm20 margintop20">{I_want_to_hire_a_freelancer}</h4>
							<p class="font-size16 marginbtm0">
								{Find_collaborate_with}
							</p>
							<p class="font-size16">
								{and_pay_an_expert}
							</p>
							<button type="submit" name="userType" class="btn btn_blue margintop30 btn_light_hover" value="customer">{Customer}</button>
							
						</div>
					</div>
					<div  class="col-sm-2 want_to center hidden-xs">
					<div class="or-txt"><span>{OR}</span></div>						
					</div>
					<div class="col-sm-5">
						<div class="want_to center">
							<img src="{SITE_IMG}provider.png" alt="{Provider}">
							<h4 class="font_light marginbtm20 margintop20">{I_want_to_work_online}</h4>
							<p class="font-size16 marginbtm0">
								{Find_collaborate_with}
							</p>
							<p class="font-size16">
								{and_pay_an_expert}
							</p>
							<button type="submit" name="userType" class="btn btn_blue btn_dark margintop30 btn_dark_hover" value="provider">{Provider}</button>							
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>