<?php
/*
	This code is only for web service call only
	for deposit or purchase membership plan
*/
require_once "../includes-nct/config-nct.php";
extract($_REQUEST);
if($payment_history_id <= 0 || !isset($payment_history_id)){
	$_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var'  => Something_went_wrong,
                    ));
	redirectPage(SITE_URL);
	exit;
}else{
 $getData = $db->pdoQuery("SELECT * FROM tbl_payment_history WHERE id = ?",array($payment_history_id));
 if($getData->affectedRows() <= 0){
 	$_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var'  => Something_went_wrong,
                    ));
	redirectPage(SITE_URL);
	exit;
 }else{
 	$paypalData = $getData->result();
 	$amount = $paypalData['totalAmount'];
 	if($action == 'deposit'){
 		$en_action        = base64_encode('deposit');
        $en_action_cancel = '/' . base64_encode($payment_history_id);
        $return_url = get_link('paypal_thankyou', $en_action);
        $notify_url = get_link('paypal_notify', $en_action);
        $cancel_url = get_link('paypal_failed', $en_action . $en_action_cancel);
        $custom_var = $payment_history_id . '__' . $paypalData['userId'];
 	}else if($action == 'membership'){
	 	$membershipId = $paypalData['membershipId'];
        $return_url = SITE_MEMBERSHIP_SUCCESS;
        $notify_url = SITE_MEMBERSHIP_NOTIFY;
        $cancel_url = SITE_MEMBERSHIP_CANCEL;
        $custom_var = "userId=".$paypalData['userId'].",finalPrice=".$amount.",membershipId=".$membershipId.",paymentId=".$payment_history_id;
 	}else{
 		$_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var'  => Something_went_wrong,
                    ));
		redirectPage(SITE_URL);
		exit;
 	}
?>

<div align="center" style="width:100%;margin-top:30px;">
	<h1><?php echo connecting_to_paypal; ?></h1>
</div>
<form name="frm_paypal_service" action="<?php echo PAYPAL_URL; ?>" method="post" id="frm_paypal_service">
    <input type="hidden" name="item_name" value="buy membership">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="business" value="<?php echo PAYPAL_EMAIL; ?>">
    <input type="hidden" name="currency_code" value="<?php echo PAYPAL_CURRENCY_CODE; ?>">
    <input type="hidden" name="no_shipping" value="1">
    <input type="image" src="http://www.paypal.com/en_GB/i/btn/x-click-but20.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" title="Make payments with PayPal - it's fast, free and secure!" style="display: none;">

    <input type="hidden" name="amount" value="<?php echo $amount; ?>">

    <input type="hidden" name="no_note" value="1">
    <input type="hidden" name="rm" value="2">
    <input type="hidden" name="notify_url" value="<?php echo  $notify_url; ?>">
    <input type="hidden" name="return" value="<?php echo  $return_url; ?>">
    <input type="hidden" name="cancel_return" value="<?php echo  $cancel_url; ?>">
    <input type="hidden" name="custom" value="<?php echo $custom_var; ?>">
    <input type="hidden" name="bn" value="NCryptedTechnologies_SP_EC" >
</form>

<script type="text/javascript">document.frm_paypal_service.submit();</script>

<?php }
 } ?>