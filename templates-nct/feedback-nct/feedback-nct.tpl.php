<div class="mainpart">
	<!-- /Section-heading start -->
	<section class="section-heading">
		<div class="container">
			<h1>{Feedback}</h1>
		</div>
	</section>
	<div class="contact-main">
		<div class="container">
			<div class="contact-form">
				<div class="white-box">
					<form id="feedbackForm">
						<h2><i class="fa fa-comment-o"></i> {contact_us_inner_title}</h2>
						<div class="form-group">
							<input class="form-control" placeholder="First Name" type="text"
							name="firstName"
							value="%firstName%"
							data-validation="length alphanumeric"
							data-validation-length="2-25"
							data-validation-error-msg="{err_First_name_has_to_be_an_alphanumeric_value}">
						</div>
						<div class="form-group">
							<input class="form-control" placeholder="Last Name" type="text"
							name="lastName"
							value="%lastName%"
							data-validation="length alphanumeric"
							data-validation-length="2-25"
							data-validation-error-msg="{err_Last_name_has_to_be_an_alphanumeric_value}">
						</div>
						<div class="form-group">
							<input class="form-control" placeholder="Email" type="email"
							value="%email%"
							name="email"
							data-validation="required email"
							data-validation-error-msg="{err_Please_check_your_email_address}">
						</div>
						<div class="form-group">
							<textarea class="form-control" name="message" rows="4" placeholder="Message"
							data-validation="required"                            
                            data-validation-error-msg="{err_Please_type_your_message}"></textarea>
						</div>
						<input type="hidden" name="action" value="method"/>
						<input type="hidden" name="method" value="submitFeedbackForm"/>
						<button type="submit" class="btn btn_blue btn-block" data-ele="submitFeedbackForm" name="submitFeedbackForm">
							<strong>Submit</strong>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
