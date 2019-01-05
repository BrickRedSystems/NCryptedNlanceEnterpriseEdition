<footer>
	<div class="nct-common-footer">
		<div class="container">
			<div class="nct-common-footer-top">

				<div class="row">
					<div class="nct-footer-navigation col-sm-8">
						<ul>							
							%MENU_ITEMS%
						</ul>

					</div>
					<div class="col-sm-4">
						<div class="form-group">							
							<input type="text" class="form-control nct-footer-emailtext" data-ele="subscriptionmail" placeholder="{Enter_your_email}">
							<button type="button" class="btn btn-primary nct-footer-messagebtn" data-ele="subscribe">
								<i class="fa fa-envelope" title="{Message}"></i>
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="nct-common-footer-bottom">
				<div class="row">
					<div class="col-sm-6">
						<div class="footer-text">
							© {Copyright} {CURRENT_YEAR} <a href="https://www.ncrypted.net/nlance" target="_blank" title="NCrypted Freelancer Script">NCrypted Freelancer Script</a>. {All_Rights_Reserved}.
							{Contact_us_today_to_buy_or_enquire_for_NCrypted} <a href="https://www.ncrypted.net/upwork-clone" target="_blank" title="Upwork Clone by NCrypted">Upwork Clone.</a>
						</div>
						<div class="ncrypted-logo-footer center-block">
							<a href="https://www.ncrypted.net/nlance" title="Web development company" target="_blank"> <img src="{SITE_IMG}nctlogo.png" alt="Web development company"> </a>
						</div>
					</div>
					<div class="col-sm-2">
						
					</div>
					<div class="col-sm-4">
						<div class="ncrypted-footer-social-media">
							<a href="{FB_LINK}" title="{Find} {SITE_NM} {on} Facebook" target="_blank" class="footer_social facebook"></a>
							<a href="{TWIITER_LINK}" title="{Follow} {SITE_NM} {on} Twitter" target="_blank" class="footer_social twitter"></a>
							<a href="{GPLUS_LINK}" title="{Follow} {SITE_NM} {on} Google Plus" target="_blank" class="footer_social google"></a>
							<a href="{LINKEDIN_LINK}" title="{Connect_with} {SITE_NM} {on} LinkedIn" target="_blank" class="footer_social linkedin"></a>
							<a href="http://ncrypted.net" title="NCrypted Technologies Pvt. Ltd." target="_blank" class="footer_social ncrypted"></a>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</footer>


<script>
	var siteNm = '{SITE_NM}',
        siteUrl = '{SITE_URL}',
        ajaxUrl = '<?php echo SITE_URL."ajax-".$this->module."/"; ?>',
        sitePlugin = '{SITE_PLUGIN}',        
        FACEBOOK_CLIENT_ID = '{FB_APP_ID}',
        GOOGLE_CLIENT_ID = '{GOOGLE_APP_ID}',
        LINKEDIN_CLIENT_ID = '{LINKEDIN_APP_ID}',        
        reCaptchaSiteKey = '{GOOGLE_RECAPTCHA_SITE_KEY}',
        CURRENCY_SYMBOL = '{CURRENCY_SYMBOL}',
        featuredProjPrice = {FEATURED_PROJ_PRICE},
        langCode = '<?php echo $_SESSION["langCode"]; ?>',
        BOOTSTRAP_DATEPICKER_FORMAT = '{BOOTSTRAP_DATEPICKER_FORMAT}',
    	BOOTSTRAP_DATETIMEPICKER_FORMAT = '{BOOTSTRAP_DATETIMEPICKER_FORMAT}',
    	pageIndex = 1, /*used for infinite scrolling*/ 
        hasmoredata = true; /*used for infinite scrolling*/ 
</script>




	<script src="{SITE_JS}jquery.min.js"></script>
	<script src="{SITE_JS}bootstrap.min.js"></script>
	<script src="{SITE_PLUGIN}hello/demos/client_ids.js"></script>
	<script src="{SITE_PLUGIN}hello/src/hello.js"></script>
	<script src="{SITE_PLUGIN}hello/src/modules/facebook.js"></script>
	<script src="{SITE_PLUGIN}hello/src/modules/google.js"></script>
	<script src="{SITE_PLUGIN}hello/src/modules/linkedin.js"></script>
	<script src="{SITE_PLUGIN}bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="{SITE_JS}placeholder.js"></script>
	<script src="<?php echo SITE_LNG.$_SESSION["lId"]; ?>.js"></script>
	<script src="{SITE_JS}custom.js"></script>
	
	<script src="{SITE_PLUGIN}jQuery-Form-Validator-master/form-validator/jquery.form-validator.min.js"></script>
	<script src="{SITE_JS}wow.min.js"></script>

<?php
	global $css_array,$js_array,$js_variables;
	if (!empty($css_array))
	{
		foreach ($css_array as $k=>$v)
		{
			echo '<link href="' . $v . '" rel="stylesheet" type="text/css"/>';
		}
	}

	if($js_variables!=NULL){
		echo '<script type="text/javascript">'.$js_variables.'</script>';
	}

	if (!empty($js_array))
	{
		foreach ($js_array as $k=>$v)
		{
			echo '<script src="' . $v . '" type="text/javascript"></script>';
		}
	}
?>


















