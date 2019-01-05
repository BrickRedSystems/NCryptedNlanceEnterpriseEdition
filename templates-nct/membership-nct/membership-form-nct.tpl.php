<div align="center" style="width:100%;margin-top:30px;">
	<h1>{connecting_to_paypal}</h1>
</div>
<form name="frm_membership_plan" action="{PAYPAL_URL}" method="post" id="frm_membership_plan">
    <input type="hidden" name="item_name" value="buy membership">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="business" value="{PAYPAL_EMAIL}">
    <input type="hidden" name="currency_code" value="{PAYPAL_CURRENCY_CODE}">
    <input type="hidden" name="no_shipping" value="1">
    <input type="image" src="http://www.paypal.com/en_GB/i/btn/x-click-but20.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" title="Make payments with PayPal - it's fast, free and secure!" style="display: none;">
    
    <input type="hidden" name="amount" value="%finalPrice%">
    
    <input type="hidden" name="no_note" value="1">
    <input type="hidden" name="notify_url" value="{SITE_MEMBERSHIP_NOTIFY}">
    <input type="hidden" name="return" value="{SITE_MEMBERSHIP_SUCCESS}">
    <input type="hidden" name="cancel_return" value="{SITE_MEMBERSHIP_CANCEL}">
    <input type="hidden" name="custom" value="userId=%userId%,finalPrice=%finalPrice%,membershipId=%membershipId%">
    <input type="hidden" name="bn" value="NCryptedTechnologies_SP_EC" >
</form>
<?php /* 
<form name="frm_membership_plan" action="{PAYPAL_URL}" method="post" id="frm_membership_plan">
	<input type="hidden" name="item_name" value="buy membership">
	<input type="hidden" name="cmd" value="_xclick-subscriptions">
	<input type="hidden" name="business" value="{PAYPAL_EMAIL}">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="no_shipping" value="1">
	<input type="image" src="http://www.paypal.com/en_GB/i/btn/x-click-but20.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" title="Make payments with PayPal - it's fast, free and secure!" style="display: none;">
	
	<input type="hidden" name="a3" value="%finalPrice%">
	<input type="hidden" name="p3" value="1">
	<input type="hidden" name="t3" value="M">
	<input type="hidden" name="src" value="1">
	<input type="hidden" name="sra" value="1">
	<!--<input type="hidden" name="srt" value="7">-->
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="notify_url" value="{SITE_MEMBERSHIP_NOTIFY}">
	<input type="hidden" name="return" value="{SITE_MEMBERSHIP_SUCCESS}">
	<input type="hidden" name="cancel_return" value="{SITE_MEMBERSHIP_CANCEL}">
	<input type="hidden" name="custom" value="userId=%userId%,finalPrice=%finalPrice%,membershipId=%membershipId%">
	<input type="hidden" name="bn" value="NCryptedTechnologies_SP_EC" >
</form>

*/ ?>
<script type="text/javascript">document.frm_membership_plan.submit();</script>