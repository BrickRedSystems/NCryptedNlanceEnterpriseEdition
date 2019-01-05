<?php
$reqAuth = false;
$module  = 'membership-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.membership-nct.php";

$obj = new Membership();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
$id     = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
$table  = 'tbl_memberships';
if ($action == 'buy' && $id > 0) {
    extract($_REQUEST);

    $date = date('Y-m-d H:i:s');
    $ip   = get_ip_address();

    $planData = $db->select($table, '*', array('id' => $id))->result();
    if ($planData['price'] > 0) {
            $amount = $planData['price'];
            $payment_history_id = $db->insert('tbl_payment_history', array(
                'userId'       => $sessUserId,
                'paymentType'  => 'buy membership',
                'membershipId' => $id,
                'totalAmount'  => $amount,
                'ipAddress'    => get_ip_address(),
                'balanceAdded' => $amount,
                'createdDate'  => date('Y-m-d H:i:s'),
            ))->lastInsertId();

            //$payment_history_id = 15;
            // Paypal
            $url_paypal       = PAYPAL_URL;
            $url_paypal .= "?business=" . urlencode(PAYPAL_EMAIL);
            $url_paypal .= "&cmd=" . urlencode('_xclick');
            $url_paypal .= "&item_name=Buy Membership - " . urlencode(SITE_NM);
            $url_paypal .= "&item_number=" . urlencode($payment_history_id);
            $url_paypal .= "&custom=" . urlencode("userId=".$sessUserId.",finalPrice=".$amount.",membershipId=".$id.",paymentId=".$payment_history_id);
            $url_paypal .= "&amount=" . urlencode($amount);
            $url_paypal .= "&rm=2";
            $url_paypal .= "&currency_code=" . urlencode(PAYPAL_CURRENCY_CODE);
            $url_paypal .= "&handling=" . urlencode('0');
            $url_paypal .= "&bn=" . urlencode('NCryptedTechnologies_SP_EC');
            $url_paypal .= "&return=" . urlencode(SITE_MEMBERSHIP_SUCCESS);
            $url_paypal .= "&cancel_return=" . urlencode(SITE_MEMBERSHIP_CANCEL);
            $url_paypal .= "&notify_url=" . urlencode(SITE_MEMBERSHIP_NOTIFY);


            if (!isset($dataOnly) || !$dataOnly) {
                redirectPage($url_paypal);
            }else{
                $response['status']     = 0;
                $response['msg']        = 'success';
                $response['success']    = 'success-membership';
                $response['fail']       = 'cancel-membership';
                $response['paypal_url'] = SITE_URL.'paypal-service/'.$payment_history_id;
                return $response;
                exit;
            }


            //echo get_view(DIR_TMPL . $module . "/membership-form-nct.tpl.php", $replace);

    } else {
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => invalid_price_for_plan_purchase,
        ));
        redirectPage(SITE_MEM_PLANS);
    }

} elseif ($action == "adHocCredits") {

    extract($_POST);
    ///////////////////
    if (!isset($dataOnly) || !$dataOnly) {
        if (!checkFormToken($token)) {
            $response['status']   = 0;
            $response['msg']      = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
            $response['newToken'] = setFormToken();
            echo json_encode($response);
            exit;
        }
    }
    ///////////////////

    if ($credits > 0) {

        //get credits required by admin
        $credits_bunch = $obj->getAdminCreditPlan(true);
        if ($credits % $credits_bunch !== 0) {
            $response['status'] = 0;
            $response['msg']    = You_can_avail_credits_only_in_the_multiples_of_X . ' ' . $credits_bunch;
            echo json_encode($response);
            exit;
        }

        //check if sufficient balance?
        $requiredBalance = ($credits / $obj->getAdminCreditPlan(true)) * $obj->getAdminCreditPlan(false, true);
        $check           = $db->select('tbl_users', 'userId', array(
            'walletamount >=' => (string) $requiredBalance,
            'userId'          => $sessUserId,
            'userType'        => 'p',
        ))->result();

        //if yes then prpceed
        if (isset($check) && $check > 0) {
            //update walletamount
            $updwalletresults = $db->pdoQuery("UPDATE tbl_users set walletamount = walletamount-$requiredBalance where userId=? AND userType='p'", array($sessUserId));

            //update totalCredits
            $updcreditresults = $db->pdoQuery("UPDATE tbl_users set totalCredits = totalCredits+$credits where userId=? AND userType='p'", array($sessUserId));

            //add log
            $db->insert('tbl_credit_log', array(
                'userId'          => $sessUserId,
                'amount'          => $credits,
                'transactionType' => 'adhoc',
                'referenceId'     => '',
                'paidAmount'      => $requiredBalance,
                'createdDate'     => date('Y-m-d H:i:s'),
                'description'     => adhoc_credits_added_PART1 . ' ' . $credits . ' ' . adhoc_credits_added_PART2 . ' ' . $requiredBalance . ' ' . adhoc_credits_added_PART3,
            ));

            $userDetails = $db->select('tbl_users', '*', array('userId' => $sessUserId))->result();

            $array = generateEmailTemplate('credit_added', array(
                'greetings'   => ucfirst($userDetails['firstName']),
                'tot_credits' => $credits,
                'tot_paid'    => $requiredBalance,
                'date'        => date('d M Y'),

            ));
            //echo '<br/>'.$array['message'];exit;
            sendEmailAddress($userDetails['email'], $array['subject'], $array['message']);

            if ($updwalletresults->affectedRows() > 0 && $updcreditresults->affectedRows() > 0) {
                $response['status'] = 1;
                $response['msg']    = adhoc_added_and_wallet_deducted;
            } else {
                $response['status'] = 0;
                $response['msg']    = err_update_credit_balance;
            }
        } else {
            $response['status'] = 0;
            $response['msg']    = You_do_not_have_sufficient_wallet_balance_to_purchase_credits;
        }

    } else {
        $response['status'] = 0;
        $response['msg']    = Incorrect_number_of_credits_Please_check_again;
    }
    echo json_encode($response);
    exit;
}
