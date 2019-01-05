<?php
class Wallet
{
    public function __construct($module = "", $id = 0, $reqData = array())
    {
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module  = $module;
        $this->id      = $id;
        $this->reqData = $reqData;

        //for web service
        $this->dataOnly   = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly'] == true) ? true : false;
        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] > 0) ? $reqData['userId'] : $this->sessUserId;

        $this->walletAmount = getTableValue('tbl_users', 'walletamount', array('userId' => $this->sessUserId));
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;
            setLang();
        }
    }

    public function submitDepositFundsForm()
    {
        extract($this->reqData);
        if ($amount < 1) {
            $response['status'] = 0;
            $response['msg']    = err_Please_enter_valid_amount;
            echo json_encode($response);
            exit;
        }


        /*$paypalId = getTableValue('tbl_users', 'paypalEmail', array('userId' => $this->sessUserId));
        if ($paypalId == null) {
        $response['status'] = 0;
        $response['msg']    = Please_share_your_Paypal_id_to_proceed . ' <a href="' . SITE_EDIT_PROFILE . '">' . Click_to_edit_it_now . '</a>';
        echo json_encode($response);
        exit;
        }*/
        //make entry for pending status
        $payment_history_id = $this->db->insert('tbl_payment_history', array(
            'userId'       => $this->sessUserId,
            'paymentType'  => 'deposit to wallet',
            'membershipId' => 0,
            'totalAmount'  => $amount,
            'ipAddress'    => get_ip_address(),
            'balanceAdded' => $amount,
            'createdDate'  => date('Y-m-d H:i:s'),
        ))->lastInsertId();

        // Paypal
        $en_action        = base64_encode('deposit');
        $en_action_cancel = '/' . base64_encode($payment_history_id);
        $url_paypal       = PAYPAL_URL;
        $url_paypal       .= "?business=" . urlencode(PAYPAL_EMAIL);
        $url_paypal       .= "&cmd=" . urlencode('_xclick');
        $url_paypal       .= "&item_name=Deposit to wallet - " . urlencode(SITE_NM);
        $url_paypal       .= "&item_number=" . urlencode($payment_history_id);
        $url_paypal       .= "&custom=" . urlencode($payment_history_id . '__' . $this->sessUserId);
        $url_paypal       .= "&amount=" . urlencode($amount);
        $url_paypal       .= "&currency_code=" . urlencode(PAYPAL_CURRENCY_CODE);
        $url_paypal       .= "&rm=2" ;
        $url_paypal       .= "&handling=" . urlencode('0');
        $url_paypal       .= "&bn=" . urlencode('NCryptedTechnologies_SP_EC');
        $url_paypal       .= "&return=" . urlencode(get_link('paypal_thankyou', $en_action));
        $url_paypal       .= "&cancel_return=" . urlencode(get_link('paypal_failed', $en_action . $en_action_cancel));
        $url_paypal       .= "&notify_url=" . urlencode(get_link('paypal_notify', $en_action));

        if (!isset($this->dataOnly) || !$this->dataOnly) {
            redirectPage($url_paypal);
            die();
        }else{
            $response['status']     = 0;
            $response['msg']        = 'success';
            $response['success']    = 'thankyou';
            $response['fail']       = 'failed';
            $response['paypal_url'] = SITE_URL.'paypal-service/'.$payment_history_id.'/deposit/';;
            return $response;
            exit;
        }
    }

    public function depositFundModal()
    {

            return get_view(DIR_TMPL . $this->module . "/depositFundModal-nct.tpl.php");


    }

    public function submitReedeemForm()
    {
///////////////////
        if (!isset($this->dataOnly) || !$this->dataOnly) {
            if (!checkFormToken($this->reqData['token'])) {
                $response['status']   = 0;
                $response['msg']      = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
                $response['newToken'] = setFormToken();
                echo json_encode($response);
                exit;
            }
        }
///////////////////

        $walletamount = getTableValue('tbl_users', 'walletamount', array('userId' => $this->sessUserId));

        if ($walletamount > 0) {
            extract($this->reqData);
            if ($amount < 1 || $amount > $walletamount) {
                $response['status'] = 0;
                $response['msg']    = Please_enter_valid_redemption_amount;
                if ($this->dataOnly) {
                    return $response;
                    exit;
                } else {
                    echo json_encode($response);
                    exit;
                }
            }
            if (trim($description) == null) {
                $response['status'] = 0;
                $response['msg']    = Please_enter_reason_for_this_redemption;
                if ($this->dataOnly) {
                    return $response;
                    exit;
                } else {
                    echo json_encode($response);
                    exit;
                }
            }
            $this->db->delete('tbl_redeem_requests', array('userId' => $this->sessUserId, 'paymentStatus' => 'pending'));
            $insArr = array(
                'userId'         => $this->sessUserId,
                'amount'         => (string) $amount,
                'description'    => $description,
                'paymentStatus'  => 'pending',
                'createdDate'    => date('Y-m-d H:i:s'),
                'updatedDate'    => date('Y-m-d H:i:s'),
                'redeemedAmount' => 0,
                'paypalId'       => $paypalId,
            );

            $lastid = $this->db->insert('tbl_redeem_requests', $insArr)->getLastInsertId();
            if ($lastid > 0) {
                $response['status'] = 1;
                $response['msg']    = Your_request_has_been_submitted_successfully;
            } else {
                $response['status'] = 0;
                $response['msg']    = toastr_something_went_wrong;
            }
            if ($this->dataOnly) {
                return $response;
                exit;
            } else {
                echo json_encode($response);
                exit;
            }
        } else {
            $response['status'] = 0;
            $response['msg']    = You_do_not_have_enough_wallet_amount_to_redeem;
            if ($this->dataOnly) {
                return $response;
                exit;
            } else {
                echo json_encode($response);
                exit;
            }

        }
    }

    public function balance_tab()
    {
        $totalCredits = getTableValue('tbl_users', 'totalCredits', array('userId' => $this->sessUserId));

        if ($this->sessUserType == 'c') {
            $result = $this->db->pdoQuery("SELECT a.available, p.pending, r.requested FROM (SELECT IFNULL(u.`walletamount`, 0) AS available FROM tbl_users AS u WHERE u.userId = ?) AS a, (SELECT IFNULL(SUM(pnd.price), 0) AS pending FROM (SELECT DISTINCT m.id,m.`price` AS price ,m.projectId,m.milestone_date FROM tbl_milestone AS m INNER JOIN tbl_projects AS tp ON m.`projectId` = tp.`id` INNER JOIN tbl_bids AS b on b.projectId = m.projectId INNER JOIN tbl_payment_history AS ph ON ph.milestoneId = m.id WHERE tp.userId = ? AND b.escrow = 'y' AND tp.jobStatus = 'progress' AND m.status != 'paid' AND m.isNulled = 'n' AND m.isFinal = 'y') AS pnd ) AS p, (SELECT IFNULL(SUM(rr.`amount`), 0) AS requested FROM tbl_redeem_requests AS rr WHERE rr.userId = ? AND (rr.`paymentStatus`='pending' OR rr.`paymentStatus`='initiated')) AS r ", array(
                $this->sessUserId,
                $this->sessUserId,
                $this->sessUserId,
            ));
        } else {
            $result = $this->db->pdoQuery("SELECT a.available, p.pending, r.requested FROM (SELECT IFNULL(u.`walletamount`, 0) AS available FROM tbl_users AS u WHERE u.userId = ?) AS a, (SELECT IFNULL(SUM(m.`price`), 0) AS pending FROM tbl_milestone AS m LEFT JOIN tbl_projects AS tp ON m.`projectId` = tp.`id` WHERE tp.providerId = ? AND m.`status`='remain' AND (tp.jobStatus = 'progress' or tp.jobStatus = 'dispute')) AS p, (SELECT IFNULL(SUM(rr.`amount`), 0) AS requested FROM tbl_redeem_requests AS rr WHERE rr.userId = ? AND (rr.`paymentStatus`='pending' OR rr.`paymentStatus`='initiated')) AS r ", array(
                $this->sessUserId,
                $this->sessUserId,
                $this->sessUserId,
            ));
        }

        if ($this->dataOnly) {
            return $result->result();
        } else {
            $result = $result->result();
        }

        $replace = array(
            '%available%'           => $result['available'],
            '%pending%'             => $result['pending'],
            '%requested%'           => $result['requested'],
            '%totalCredits%'        => ($this->sessUserType == 'p') ? $totalCredits : null,
            '%hideCreditSection%'   => ($this->sessUserType == 'c') ? 'hide' : null,
            '%hideRequestToRedeem%' => ($this->walletAmount <= 0) ? 'hide' : null,
        );
        return get_view(DIR_TMPL . $this->module . "/balance-nct.tpl.php", $replace);
    }

    public function redeem_rows($count = false)
    {
        $html   = null;
        $result = $this->db->select('tbl_redeem_requests', '*', array('userId' => $this->sessUserId), 'ORDER BY id DESC')->results();
        if ($count) {
            return count($result);
        }
        if (!empty($result)) {

            if ($this->dataOnly) {
                foreach ($result as $k => $v) {
                    $result[$k]['paymentStatus'] = constant($result[$k]['paymentStatus']);
                }
                return $result;
            }

            foreach ($result as $k => $v) {
                extract($v);
                $replace = array(
                    '%amount%'         => $amount,
                    '%createdDate%'    => ($createdDate > 0) ? date(PHP_DATE_FORMAT, strtotime($createdDate)) : Not_Available,
                    '%description%'    => filtering($description),
                    '%paymentStatus%'  => constant($paymentStatus),
                    '%redeemedAmount%' => $redeemedAmount,
                    '%redeemedDate%'   => ($redeemedDate > 0) ? date(PHP_DATE_FORMAT, strtotime($redeemedDate)) : Not_Available,
                );
                $html .= get_view(DIR_TMPL . $this->module . "/redeem-row-nct.tpl.php", $replace);
            }
        } else {

        }

        return $html;
    }

    public function get_redeemModal()
    {
        $walletamount = getTableValue('tbl_users', 'walletamount', array('userId' => $this->sessUserId));
        if ($walletamount > 0) {
            $replace = array(
                '%available%'  => $this->walletAmount,
                '%email%'      => getTableValue('tbl_users', 'paypalEmail', array('userId' => $this->sessUserId)),
                '%maxAmount%'  => $this->walletAmount,
                '%tokenValue%' => setFormToken(),
            );
            return get_view(DIR_TMPL . $this->module . "/redeemModal-nct.tpl.php", $replace);
        } else {
            $response['status'] = 0;
            $response['msg']    = You_do_not_have_enough_wallet_amount_to_redeem;
            echo json_encode($response);
            exit;
        }

    }

    public function redeem_tab()
    {

        $replace = array(
            '%available%'           => CURRENCY_SYMBOL . $this->walletAmount,
            '%rows%'                => $this->redeem_rows(),
            '%text%'                => There_are_no_redemption_requests_yet,
            '%no_data_hide%'        => ($this->redeem_rows(true) == 0) ? null : 'hide',
            '%hideRequestToRedeem%' => ($this->walletAmount <= 0) ? 'hide' : null,
        );

        return get_view(DIR_TMPL . $this->module . "/redeem-nct.tpl.php", $replace);
    }

    public function credit_rows($count = false)
    {
        $html   = null;
        $result = $this->db->select('tbl_credit_log', '*', array('userId' => $this->sessUserId), 'ORDER BY id DESC')->results();

        if ($this->dataOnly) {
            foreach ($result as $k => $v) {

                switch ($v['transactionType']) {
                    case 'bid':
                        //get projId from refId
                        $projTitle = getTableValue('tbl_projects','title',array('id'=>$v['referenceId']));
                        $result[$k]['description'] = $v['amount'] . ' ' . credits_debited_in_reference_to_your_bid_on . ' ' . $projTitle . '.';
                        break;
                    case 'adhoc':
                        //get paidAmount
                        $result[$k]['description'] = adhoc_credits_added_PART1 . ' ' . $v['amount'] . ' ' . adhoc_credits_added_PART2 . ' ' . $v['paidAmount'] . ' ' . adhoc_credits_added_PART3;
                        break;
                    case 'membership':
                        //get memId from refId
                        $planName = getTableValue('tbl_memberships','membership_'.$_SESSION['lId'],array('id'=>$v['referenceId']));
                        $result[$k]['description'] = $v['amount'] . ' ' . membership_credits_added . ' ' . $planName;
                        break;
                    default:
                        # code...
                        break;
                }
            }
            return $result;
            exit;
        }
        if ($count) {
            return count($result);
        }
        if (!empty($result)) {
            foreach ($result as $k => $v) {

                switch ($v['transactionType']) {
                    case 'bid':
                        //get projId from refId
                        $projTitle = getTableValue('tbl_projects','title',array('id'=>$v['referenceId']));
                        $description = $v['amount'] . ' ' . credits_debited_in_reference_to_your_bid_on . ' ' . $projTitle . '.';
                        break;
                    case 'adhoc':
                        //get paidAmount
                        $description = adhoc_credits_added_PART1 . ' ' . $v['amount'] . ' ' . adhoc_credits_added_PART2 . ' ' . $v['paidAmount'] . ' ' . adhoc_credits_added_PART3;
                        break;
                    case 'membership':
                        //get memId from refId
                        $planName = getTableValue('tbl_memberships','membership_'.$_SESSION['lId'],array('id'=>$v['referenceId']));
                        $description = $v['amount'] . ' ' . membership_credits_added . ' ' . $planName;
                        break;
                    default:
                        # code...
                        break;
                }
                $replace = array(
                    '%amount%'      => (isset($v['amount']) && $v['amount'] > 0) ? $v['amount'] : Not_Available,
                    '%createdDate%' => (isset($v['createdDate']) && $v['createdDate'] != null) ? date(PHP_DATE_FORMAT, strtotime($v['createdDate'])) : Not_Available,
                    '%description%' => ucfirst($description),
                );
                $html .= get_view(DIR_TMPL . $this->module . "/credits-row-nct.tpl.php", $replace);
            }
        }
        return $html;
    }

    public function credits_tab()
    {
        $availableCredits = getTableValue('tbl_users', 'totalCredits', array('userId' => $this->sessUserId));
        $replace          = array(
            '%available%'    => $availableCredits,
            '%rows%'         => $this->credit_rows(),
            '%text%'         => 'There is no data in your credit history.',
            '%no_data_hide%' => ($this->credit_rows(true) == 0) ? null : 'hide',
        );

        return get_view(DIR_TMPL . $this->module . "/credits-nct.tpl.php", $replace);
    }

    public function getPageContent()
    {
        $replace = array('%tab_panel%' => $this->balance_tab(), '%hideCreditSection%' => ($this->sessUserType == 'c') ? 'hide' : null);
        return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", $replace);
    }

}
