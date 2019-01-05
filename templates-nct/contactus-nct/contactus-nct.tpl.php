<div class="mainpart">
    <!-- /Section-heading start -->
    <section class="section-heading">
        <div class="container">
            <h1>
                {Contact_us}
            </h1>
        </div>
    </section>
    <section class="contact-main">
        <div class="container">
            <div class="contact-form">
                <div class="white-box">
                    <form id="contactForm" name="contactForm">
                        <input name="token" type="hidden" value="%tokenValue%">
                            <h2>
                                <i class="fa fa-comment-o">
                                </i>
                                {contact_us_inner_title}
                            </h2>
                            <div class="form-group">
                                <input class="form-control" data-validation="length alphanumeric" data-validation-error-msg="{err_First_name_has_to_be_an_alphanumeric_value}" data-validation-length="2-25" name="firstName" placeholder="{First_Name}" type="text" value="%firstName%">
                                
                            </div>
                            <div class="form-group">
                                <input class="form-control" data-validation="length alphanumeric" data-validation-error-msg="{err_Last_name_has_to_be_an_alphanumeric_value}" data-validation-length="2-25" name="lastName" placeholder="{Last_Name}" type="text" value="%lastName%">
                                
                            </div>
                            <div class="form-group">
                                <input class="form-control" data-validation="required email" name="email" data-validation-error-msg="{err_Please_check_your_email_address}" placeholder="{Email}" type="email" value="%email%">
                               
                            </div>
                            <div class="form-group">
                                <input class="form-control" data-validation="number" name="contactNo" data-validation-error-msg="{err_Contact_number_has_to_be_a_numeric_value}" placeholder="{Contact_No}" tabindex="4" type="text" value="">
                               
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" data-validation="required" data-validation-error-msg="{err_Please_type_your_message}" name="message" placeholder="{Message}" rows="4"></textarea>
                            </div>
                            <input name="action" type="hidden" value="method"/>
                            <input name="method" type="hidden" value="submitContactForm"/>
                            <button class="btn btn_blue btn-block" data-ele="submitContactForm" name="submitContactForm" type="submit">
                                <strong>
                                    {Submit}
                                </strong>
                            </button>
                       
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
