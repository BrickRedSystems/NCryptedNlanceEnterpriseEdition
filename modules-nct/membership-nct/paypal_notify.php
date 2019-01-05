<?php
$content = '';
//print_r($_POST);
$content = "\n\n-----------------------------------------------------------------------------------" . date("Y-m-d H:i:s");
foreach ($_POST as $k => $v) {
    $content .= "\n\nkey=" . $k . "======>value=" . $v;
}
$h = fopen("notify.txt", "a");
$r = fwrite($h, $content);
fclose($h);

require_once '../../includes-nct/config-nct.php';
//error_reporting(E_ALL|E_STRICT);
sendEmailAddress('gaurav.chavda@ncrypted.com', 'ipn', '<pre>' . print_r($_REQUEST, true) . '</pre>', 'Content-type: text/html; charset=iso-8859-1');
$log_array = print_r($_REQUEST, true);

$txn_exist = getTableValue('tbl_payment_history', 'id', array('transactionId' => $_POST['txn_id']));

if (isset($txn_exist) && $txn_exist > 0) {
    echo '<br /><br /><center><h1> <font color="#009900">' . This_transaction_is_already_done . '</font></h1><center>';
    exit;
}

$objPost = new stdClass();
$myArray = explode(',', $_POST['custom']);
$arr     = array();
$new     = array();
$i       = 0;
foreach ($myArray as $k => $v) {
    $arr[$i]          = explode('=', $v);
    $new[$arr[$i][0]] = $arr[$i][1];
    $i++;
}

//note: for recurring change txn_type to subscr_payment
if ($_POST['payment_status'] == "Completed" && $_POST['txn_type'] == 'web_accept') {

    $date = date('Y-m-d H:i:s');
    $userId       = (int) $new['userId'];
    $membershipId = (int) $new['membershipId'];
    $paymentId = (int) $new['paymentId'];
    $tobeAdded    = getTableValue('tbl_memberships', 'credits', array('id' => $membershipId));
    $planName     = getTableValue('tbl_memberships', 'membership', array('id' => $membershipId));
    /* End Old Recurring */
   /* $objPost->userId        = $userId;
    $objPost->transactionId = $_POST['txn_id'];
    $objPost->paymentType   = "buy membership";
    $objPost->membershipId  = $membershipId;

    //$objPost->subscr_id             =  $_POST['subscr_id'];
    //$objPost->ipn_track_id         =  $_POST['ipn_track_id'];
    //$objPost->txn_type             =  $_POST['txn_type'];

    $objPost->mc_currency = $_POST['mc_currency'];
    //$objPost->receiver_id          =  $_POST['receiver_id'];
    $objPost->receiver_email = $_POST['receiver_email'];
    //$objPost->payer_id             =  $_POST['payer_id'];
    $objPost->payer_email = $_POST['payer_email'];
    //$objPost->payer_business_name  =  $_POST['payer_business_name'];
    //$objPost->payer_status         =  $_POST['payer_status'];
    //$objPost->payment_type         =  $_POST['payment_type'];
    //$objPost->verify_sign         =  $_POST['verify_sign'];
    //$objPost->residence_country  =  $_POST['residence_country'];
    $objPost->paypal_fees     = $_POST['mc_fee'];
    $objPost->creditBought    = $tobeAdded;
    $objPost->totalAmount     = (string) $_POST['mc_gross'];
    $objPost->paymentStatus   = strtolower($_POST['payment_status']);
    $objPost->isadmindelete   = 'n';
    $objPost->ipAddress       = get_ip_address();
    $objPost->createdDate     = date('Y-m-d H:i:s');
    $objPost->is_cancled_user = 'n';*/

   // $last_id = $db->insert('tbl_payment_history', (array) $objPost)->lastInsertId();

    $db->update('tbl_payment_history', array(
                'payer_email'     => $_POST['payer_email'],
                'receiver_email'  => $_POST['receiver_email'],
                'transactionId'   => $_POST['txn_id'],
                'jsonDetails'     => json_encode($log_array),
                'paymentStatus'   => 'completed',
                'totalAmount'     => $_POST['mc_gross'],
                'adminCommission' => (int) 0,
            ), array(
                'id'     => $paymentId,
                'userId' => $userId,
            ));

    $db->pdoQuery('UPDATE tbl_users SET totalCredits=totalCredits+? WHERE userId=?', array($tobeAdded, $userId));

    $user_details = $db->select('tbl_users', '*', array('userId' => $userId))->result();
    $planPrice     = getTableValue('tbl_memberships', 'membership', array('id' => $membershipId));

    //add log
    $db->insert('tbl_credit_log', array(
        'userId'          => $userId,
        'amount'          => (string) $tobeAdded,
        'transactionType' => 'membership',
        'createdDate'     => $date,
        'referenceId'     => $membershipId,
        'paidAmount'      => $_POST['mc_gross'],
        'description'     => $tobeAdded . ' ' . membership_credits_added . ' ' . $planName,
    ));

    if (isset($user_details['userId']) && $user_details['userId'] > 0) {
        $activationLink         = SITE_URL.$user_details['profileLink'];
        $mailarray              = array('greetings' => $user_details['firstName'], 'plan_name' => $planName,'tot_credits'=>$tobeAdded,'tot_paid'=>$_POST['mc_gross'],'date'=>$date,'activationLink'=>$activationLink);
        $_SESSION['sendMailTo'] = issetor($user_details['userId'], 0);
        $array                  = generateEmailTemplate('purchase_membership_plan', $mailarray);
        sendEmailAddress($user_details['email'], $array['subject'], $array['message']);
    }
} else if ($_POST['txn_type'] == 'subscr_cancel') {
    $userId       = (int) $new['userId'];
    $membershipId = (int) $new['membershipId'];

    $db->update('tbl_payment_history', array('is_cancled_user' => 'y'), array('subscr_id' => $subscr_id));
}

/* else if($_POST['payment_status'] == 'Pending') {
$user_details = $db->pdoQuery("SELECT u.fname,u.lname,u.email,u.profilecode FROM  tbl_users AS u WHERE u.id  = ? ORDER BY u.id DESC LIMIT 1",array((int)$userid));
if($user_details->affectedRows() > 0){
$fetchRes2 = $user_details->result();
$activationLink = SITE_URL;
$mailarray = array('greetings'=>$fetchRes2['fname'],'activationLink'=>$activationLink);
$array = generateEmailTemplatenew('purchase_membership_plan',$mailarray);
sendEmailAddress($fetchRes2['email'],$array['subject'],$array['message']);
}
} */
