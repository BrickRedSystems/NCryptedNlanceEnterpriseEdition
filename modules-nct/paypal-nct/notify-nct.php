<?php
$reqAuth = false;
$content = '';
//print_r($_REQUEST);
$content = "\n\n-----------------------------------------------------------------------------------" . date("Y-m-d H:i:s");
foreach ($_REQUEST as $k => $v) {
    $content .= "\n\nkey=" . $k . "======>value=" . $v;
}
$h = fopen("notify.txt", "a");
$r = fwrite($h, $content);
fclose($h);
//error_reporting(E_ALL | E_STRICT);

require_once '../../includes-nct/config-nct.php';
$type = (!empty($_REQUEST['f']) ? base64_decode(trim($_REQUEST['f'])) : '');
$type = trim($type);

$message   = "<pre>" . print_r($_REQUEST, true);
$log_array = print_r($_REQUEST, true);

/*sendEmailAddress('ashish.joshi@ncrypted.com', 'Notify - ' . trim($type) . ' - ' . SITE_NM, $message);*/

if ($_POST['payment_status'] == 'Completed') {
    //user deposits funds in his wallet
    if (trim($type) == 'deposit') {

        list($payment_history_id, $userId) = preg_split('/__/', $_POST["custom"]);

        if (!empty($payment_history_id) && !empty($userId)) {

            /*  calculate wallet amount
            give wallet amount by subtracting admin commission from paid amount
            walletAmount = mc_gross - (mc_gross * admin percent for deposit/ 100)
             */
            $calculatedAdminCommission = $_POST['mc_gross'] * DEPOSIT_COMMISSION / 100;
            $CalculatedWalletAmount    = $_POST['mc_gross'] - $calculatedAdminCommission;

            $db->update('tbl_payment_history', array(
                'payer_email'     => $_POST['payer_email'],
                'receiver_email'  => $_POST['receiver_email'],
                'transactionId'   => $_POST['txn_id'],
                'jsonDetails'     => json_encode($log_array),
                'paymentStatus'   => 'completed',
                'totalAmount'     => $_POST['mc_gross'],
                'adminCommission' => (string) $calculatedAdminCommission,
            ), array(
                'id'     => $payment_history_id,
                'userId' => $userId,
            ));

            //  update total wallet amount in user table
            $db->pdoQuery('UPDATE tbl_users SET walletAmount = walletAmount + ? WHERE userId=?', array(
                (string) $CalculatedWalletAmount,
                $userId,
            ));

            $user_details = $db->select('tbl_users', array(
                'firstName',
                'email',
            ), array('userId' => $userId))->result();

            // check for notification

            $arrayCont = array(
                'greetings'      => $user_details['firstName'],
                'USER_EAMIL'     => $user_details['email'],
                'PAYPAL_ACCOUNT' => $_POST['payer_email'],
                'TRANSACTION_ID' => $_POST['txn_id'],
                'AMOUNT'         => CURRENCY_SYMBOL . $_POST['mc_gross'],
                'DATE'           => date('d-m-Y'),
                'STATUS'         => Completed,
            );

            $array = generateEmailTemplate('deposit_to_wallet', $arrayCont);
            //echo $array['message'];exit;
            sendEmailAddress($user_details['email'], $array['subject'], $array['message']);

        }
    } else if ($type == 'redeem') {
        //admin has paid in response to redeem request of user
        list($payment_history_id, $user_id, $redeem_id, $calculatedAdminCommission) = preg_split('/__/', $_POST["custom"]);

        if (!empty($payment_history_id) && !empty($user_id) && !empty($redeem_id)) {

            $user_details = $db->select('tbl_users', array(
                'firstName',
                'email',
            ), array('userId' => $user_id))->result();

            $db->update('tbl_payment_history', array(
                'receiver_email'  => $_POST['receiver_email'],
                'transactionId'   => $_POST['txn_id'],
                'jsonDetails'     => json_encode($log_array),
                'paymentStatus'   => 'completed',
                'totalAmount'     => $_POST['mc_gross'],
                'adminCommission' => (string) $calculatedAdminCommission,
            ), array('id' => $payment_history_id));

            $db->update('tbl_redeem_requests', array(
                'paymentStatus'  => 'deposited',
                'redeemedAmount' => $_POST['mc_gross'],
                'redeemedDate'   => date('Y-m-d H:i:s'),
                'updatedDate'    => date('Y-m-d H:i:s')), array('id' => $redeem_id));

            $walletamounttodeduct = getTableValue('tbl_redeem_requests', 'amount', array('id' => $redeem_id));
            // update total wallet amount in user table
            $db->pdoQuery('UPDATE tbl_users SET walletamount=walletamount-? WHERE userId=?', array(
                (string) $walletamounttodeduct,
                $user_id,
            ));

            $mailarray = array(
                'greetings'      => $user_details['firstName'],
                'USER_EAMIL'     => $user_details['email'],
                'PAYPAL_ACCOUNT' => $_POST['receiver_email'],
                'TRANSACTION_ID' => $_POST['txn_id'],
                'AMOUNT'         => CURRENCY_SYMBOL . $_POST['mc_gross'],
                'DATE'           => date('d-m-Y'),
                'STATUS'         => Completed,
            );
            $array = generateEmailTemplate('redeem_succ', $mailarray);
            sendEmailAddress($user_details['email'], $array['subject'], $array['message']);

        }
    } else if ($type == 'featured') {
        list($payment_history_id, $user_id, $project_id, $featuredDays) = preg_split('/__/', $_POST["custom"]);

        if (!empty($payment_history_id) && !empty($user_id) && !empty($project_id)) {

            /*START:: To check whether the gross is equal to amount set for that featured project or not*/
            $featuredCost = getTableValue('tbl_payment_history', 'totalAmount', array('id' => $payment_history_id));
            if ($featuredCost != $_POST['mc_gross'] || strtolower($_POST['mc_currency']) != 'usd') {
                //add log
                $db->update('tbl_payment_history', array(
                    'payer_email'   => $_POST['payer_email'],
                    'transactionId' => $_POST['txn_id'],
                    'jsonDetails'   => json_encode($log_array),
                    'paymentStatus' => 'failed',
                    'totalAmount'   => $_POST['mc_gross'],
                ), array('id' => $payment_history_id));
                exit;
            }
            /*END:: To check whether the gross is equal to amount set for that featured project or not*/

            $user_details = $db->select('tbl_users', array(
                'firstName',
                'email',
            ), array('userId' => $user_id))->result();

            $projectDetails = $db->select('tbl_projects', '*', array('id' => $project_id))->result();

            $db->update('tbl_projects', array('isFeatured' => 'y', 'featuredDays' => $featuredDays,'featuredExpiryDate'=>date('Y-m-d H:i:s',strtotime("+ $featuredDays day"))), array('id' => $project_id));


            $db->update('tbl_payment_history', array(
                'payer_email'   => $_POST['payer_email'],
                'transactionId' => $_POST['txn_id'],
                'jsonDetails'   => json_encode($log_array),
                'paymentStatus' => 'completed',
                'totalAmount'   => $_POST['mc_gross'],
            ), array('id' => $payment_history_id));

            $mailarray = array(
                'greetings'      => ucwords($user_details['firstName']),
                'EVENT_NM'       => ucfirst($projectDetails['title']),
                'featuredDays'   => $featuredDays,
                'PAYPAL_ACCOUNT' => $_POST['buyer_email'],
                'TRANSACTION_ID' => $_POST['txn_id'],
                'AMOUNT'         => CURRENCY_SYMBOL . $_POST['mc_gross'],
                'DATE'           => date('d-m-Y'),
                'STATUS'         => Completed,
            );
            $array = generateEmailTemplate('featured_succ', $mailarray);
            sendEmailAddress($user_details['email'], $array['subject'], $array['message']);

        }
    } else {

    }
} else if ($_POST['payment_status'] == 'Pending') {
    if ($type == 'redeem_admin_pay') {

    } else if ($type == 'user_admin_pay') {

    }
}

?>