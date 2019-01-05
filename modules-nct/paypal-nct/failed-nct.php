<?php
$reqAuth = false;
require_once '../../includes-nct/config-nct.php';
$type = (!empty($_REQUEST['f']) ? base64_decode(trim($_REQUEST['f'])) : '');
$type = trim($type);
$postData = '';
foreach ($_POST as $id => $val) {
	$postData .= $id . '-' . $val . ' | ';
}
/*sendEmailAddress('ashish.joshi@ncrypted.com', 'Failed - ' . trim($type) . ' - ' . SITE_NM, $postData);*/


if ($type == 'deposit') {
	/*list($payment_history_id, $userId) = preg_split('/__/', $_POST["custom"]);*/
	$payment_history_id = base64_decode(trim($_REQUEST['payment_history_id']));

	if (!empty($payment_history_id) ) {
		$getData = $db->pdoQuery("SELECT * FROM tbl_payment_history WHERE id = ".$payment_history_id)->result();
		$db -> update('tbl_payment_history', array(
			'payer_email' => $_POST['payer_email'],
			'receiver_email' => $_POST['receiver_email'],
			'transactionId' => $_POST['txn_id'],
			'jsonDetails' => json_encode($postData),
			'paymentStatus' => 'failed',
		), array(
			'id' => $payment_history_id,
			'userId' => $getData['userId']
		));

		$user_details = $db -> select('tbl_users', array(
			'firstName',
			'email'
		), array('userId' => $getData['userId'])) -> result();
		$arrayCont = array(
			'greetings' => $user_details['firstName'],
			'USER_EAMIL' => $user_details['email'],
			'PAYPAL_ACCOUNT' => issetor($_POST['payer_email'],'-'),
			'TRANSACTION_ID' => issetor($_POST['txn_id'],'-'),
			'AMOUNT' => CURRENCY_SYMBOL . $getData['totalAmount'],
			'DATE' => date('d-m-Y'),
			'STATUS' => 'Failed'
		);

		$array = generateEmailTemplate('deposit_to_wallet', $arrayCont);
		//echo $array['message'];exit;
		sendEmailAddress($user_details['email'], $array['subject'], $array['message']);
	}
	$_SESSION["msgType"] = disMessage(array(
		'type' => 'error',
		'var' => Transaction_to_deposit_balance_to_wallet_has_been_failed
	));

	redirectPage(SITE_WALLET);
}
else if ($type == 'redeem') {
	/*list($payment_history_id, $user_id) = preg_split('/__/', $_POST["custom"]);*/
	$payment_history_id = base64_decode(trim($_REQUEST['payment_history_id']));

	if (!empty($payment_history_id) ) {
		/*$db -> update('tbl_payment_history', array(
			'paypal_email' => $_POST['payer_email'],
			'txn_id' => $_POST['txn_id'],
			'payment_details' => json_encode($postData),
			'status' => 'f',
		), array('id' => $payment_history_id));*/
		$db -> update('tbl_payment_history', array(
			'payer_email' => $_POST['payer_email'],
			'receiver_email' => $_POST['receiver_email'],
			'transactionId' => $_POST['txn_id'],
			'jsonDetails' => json_encode($postData),
			'paymentStatus' => 'failed',
		), array(
			'id' => $payment_history_id
		));
	}
	$msgType = $_SESSION["msgType"] = disMessage(array(
		'type' => 'error',
		'var' => Transaction_failed_for_redeem_amount
	));
	redirectPage(SITE_ADM_MOD . 'redeem_request-nct/');
}
else if ($type == 'user_admin_pay') {

}
else {

}
?>