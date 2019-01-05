<?php
$reqAuth = false;
require_once '../../includes-nct/config-nct.php';
$type = (!empty($_REQUEST['f']) ? base64_decode(trim($_REQUEST['f'])) : '');
$type = trim($type);
$postData = '';
foreach ($_POST as $id => $val) {
    $postData .= $id . '-' . $val . ' | ';
}
//sendEmailAddress('ashish.joshi@ncrypted.com', 'Thankyou - ' . trim($type) . ' - ' . SITE_NM, $postData);

if ($type == 'deposit') {
    list($payment_history_id, $userId) = preg_split('/__/', $_POST["custom"]);
    if (!empty($payment_history_id) && !empty($userId)) {
       
        $user_details = $db -> select('tbl_users', array(
            'firstName',
            'email'
        ), array('userId' => $userId)) -> result();

        //send acknowledge mail
        $arrayCont = array(
            'greetings' => $user_details['firstName'],
            'USER_EAMIL' => $user_details['email'],
            'PAYPAL_ACCOUNT' => $_POST['payer_email'],
            'TRANSACTION_ID' => $_POST['txn_id'],
            'AMOUNT' => CURRENCY_SYMBOL . $_POST['mc_gross'],
            'DATE' => date('d-m-Y'),
            'STATUS' => In_Progress
        );
        $_SESSION['sendMailTo'] = issetor($user_details['userId'], 0);
        $array = generateEmailTemplate('deposit_to_wallet', $arrayCont);
        //echo $array['message'];exit;
        sendEmailAddress($user_details['email'], $array['subject'], $array['message']);
    }
    $_SESSION["msgType"] = disMessage(array(
        'type' => 'suc',
        'var' => Payment_status_for_this_transaction_is_pending
    ));

    redirectPage(SITE_WALLET);
}
else if ($type == 'redeem') {
    list($payment_history_id, $user_id, $redeem_id) = preg_split('/__/', $_POST["custom"]);
    if (!empty($payment_history_id) && !empty($user_id) && !empty($redeem_id)) {
         $status = getTableValue('tbl_payment_history', 'paymentStatus', array('id' => $$payment_history_id ));
        if($status == 'pending'){
            $db -> update('tbl_payment_history', array(
                'receiver_email' => $_POST['receiver_email'],
                'transactionId' => $_POST['txn_id'],
                'jsonDetails' => json_encode($postData),
                'paymentStatus' => 'initiated',
                'totalAmount' => $_POST['mc_gross']
            ), array('id' => $payment_history_id));
            $db -> update('tbl_redeem_requests', array('paymentStatus' => 'initiated'), array('id' => $redeem_id));
        }
        $user_details = $db -> select('tbl_users', array(
            'firstName',
            'paypalEmail',
            'email'
        ), array('userId' => $user_id)) -> result();



        //send acknowledge mail
        $mailarray = array(
            'greetings' => $user_details['firstName'],
            'USER_EAMIL' => $user_details['email'],
            'PAYPAL_ACCOUNT' => $_POST['paypalEmail'],
            'TRANSACTION_ID' => $_POST['txn_id'],
            'AMOUNT' => CURRENCY_SYMBOL . $_POST['mc_gross'],
            'DATE' => date('d-m-Y'),
            'STATUS' => In_Progress
        );
        $_SESSION['sendMailTo'] = issetor($user_details['userId'], 0);
        $array = generateEmailTemplate('redeem_succ', $mailarray);
        sendEmailAddress($user_details['email'], $array['subject'], $array['message']);

    }
    $_SESSION["msgType"] = disMessage(array(
        'type' => 'suc',
        'var' => (strtolower($_POST['payment_status']) == 'pending') ? wallet_update_is_pending : amount_transferred_successfully
    ));

    redirectPage(SITE_ADM_MOD . 'redeem_request-nct/');
}
else if ($type == 'user_admin_pay') {

}
else {

}
?>