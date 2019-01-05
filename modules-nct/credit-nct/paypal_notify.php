<?php
	/*$content='';
	print_r($_POST);
	$content="\n\n-----------------------------------------------------------------------------------".date("Y-m-d H:i:s");
	foreach($_POST as $k=>$v) {
	 $content.="\n\nkey=".$k."======>value=".$v;
	}
	$h=fopen("notify.txt","a");
	$r=fwrite($h,$content);
	fclose($h);
  */
  	require_once '../../includes-nct/config-nct.php';
/*	mail('ashish.joshi@ncrypted.com','ipn','<pre>'.print_r($_REQUEST,true).'</pre>','Content-type: text/html; charset=iso-8859-1');*/

  	$txn_exist = getTableValue('tbl_payment_history', 'id',array('transactionId'=>$_POST['txn_id']));

	if(isset($txn_exist) && $txn_exist>0){
		echo '<br /><br /><center><h1> <font color="#009900">This transaction is already done.</font></h1><center>';
		exit;
	}


  	$objPost = new stdClass();
  	$myArray = explode(',', $_POST['custom']);
	$arr = array();
	$new = array();
	$i=0;
	foreach ($myArray as $k => $v) {
		$arr[$i] = explode('=', $v);
		$new[$arr[$i][0]]=$arr[$i][1];
		$i++;
	}



    if($_POST['payment_status']=="Completed" && $_POST['txn_type'] == 'web_accept') {

	  	$userId       = (int)$new['userId'];
	  	$creditId = (int)$new['creditId'];
		$creditTobeAdded = getTableValue('tbl_credit_plans', 'credits',array('id'=>$creditId));
		$transaction_subject = $new['transaction_subject'];
        /* Breack old Recurring*/

        /*$checkAnyPurchasePlan = $q = $db->pdoQuery("SELECT COUNT(pm.id) as totalRec,pm.subscr_id FROM tbl_users as u
                            LEFT JOIN tbl_membership_payment as pm ON (pm.id = u.planid )
                            WHERE u.id = ? AND  pm.id = u.planid  ORDER BY pm.id DESC LIMIT 1", array((int) $userid))->result();


        if($checkAnyPurchasePlan['totalRec'] > 0){

            $profile_id = $checkAnyPurchasePlan['subscr_id'];
            $action = 'Cancel';  //'Suspend   // Reactivate';
            define("USER","kuldip.bhatt01_api1.ncrypted.com");
            define("PASS","PXFLQP2LCFWH72LF");
            define("SIGN","AFcWxV21C7fd0v3bYYYRCpSSRl31Ad0N5FUlL8B4Ub1Kg5D8aDpFE5ed");
            // 'PAYPAL_API_APP_ID'  'APP-80W284485P519543T'


                $api_request = 'USER=' . urlencode(USER)
                            .  '&PWD=' . urlencode(PASS)
                            .  '&SIGNATURE=' . urlencode(SIGN)
                            .  '&VERSION=76.0'
                            .  '&METHOD=ManageRecurringPaymentsProfileStatus'
                            .  '&PROFILEID=' . urlencode( $profile_id )
                            .  '&ACTION=' . urlencode( $action )
                            .  '&NOTE=' . urlencode( 'Old cycle was canceled.-Kuldip Bhatt' );

                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, 'https://api-3t.sandbox.paypal.com/nvp' ); // For live transactions, change to 'https://api-3t.paypal.com/nvp'
                curl_setopt( $ch, CURLOPT_VERBOSE, 1 );

                // Uncomment these to turn off server and peer verification
                // curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
                // curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_POST, 1 );

                // Set the API parameters for this transaction
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $api_request );

                // Request response from PayPal
                $response = curl_exec( $ch );

                // If no response was received from PayPal there is no point parsing the response
                if( ! $response )
                    die( 'Calling PayPal to change_subscription_status failed: ' . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')' );
                curl_close( $ch );
                // An associative array is more usable than a parameter string
                parse_str( $response, $parsed_response );

        } */

        /* End Old Recurring*/

        $objPost->userId    		   =  $userId;
        $objPost->transactionId        =  $_POST['txn_id'];
        $objPost->paymentType  		   =  $transaction_subject;
		$objPost->membershipId         =  $creditId;

        //$objPost->ipn_track_id  	   =  $_POST['ipn_track_id'];
        //$objPost->txn_type  		   =  $_POST['txn_type'];


        $objPost->mc_currency		   =  $_POST['mc_currency'];
        //$objPost->receiver_id   	   =  $_POST['receiver_id'];
        $objPost->receiver_email 	   =  $_POST['receiver_email'];
        //$objPost->payer_id  		   =  $_POST['payer_id'];
        $objPost->payer_email          =  $_POST['payer_email'];
        //$objPost->payer_business_name  =  $_POST['payer_business_name'];
        //$objPost->payer_status  	   =  $_POST['payer_status'];
        //$objPost->payment_type  	   =  $_POST['payment_type'];
        //$objPost->verify_sign  	   =  $_POST['verify_sign'];
        //$objPost->residence_country  =  $_POST['residence_country'];
        $objPost->paypal_fees  		   =  $_POST['mc_fee'];
        $objPost->balanceAdded 		   =  $creditTobeAdded;
        $objPost->creditBought  	   =  $_POST['mc_gross'];
        $objPost->paymentStatus	   	   =  strtolower($_POST['payment_status']);
        $objPost->isadmindelete 	   =  'n';
        $objPost->ipAddress   	       =  get_ip_address();
        $objPost->createdDate 		   =  date('Y-m-d H:i:s');
        $objPost->is_cancled_user      =  'n';

        $last_id = $db->insert('tbl_payment_history',(array)$objPost)->getLastInsertId();
        $db->pdoQuery('UPDATE tbl_users SET totalCredits=totalCredits+? WHERE userId=?', array($creditTobeAdded, $userId));

        $sql = $db->select('tbl_users','*',array('userId'=>$userId))->result();
        if(isset($sql['userId']) && $sql['userId']> 0){
            $link = SITE_URL.$sql['profileLink'];
            $mailarray = array('greetings'=>$sql['firstName'],'total_credits'=>$creditTobeAdded,'link'=>$link);
            $_SESSION['sendMailTo'] = issetor($sql['userId'],0);
            $array = generateEmailTemplate('purchase_credit',$mailarray);
            sendEmailAddress($sql['email'],$array['subject'],$array['message']);
        }


    }

    else if($_POST['txn_type'] == 'subscr_cancel'){
        $userId       = (int)$new['userId'];
	  	$creditId = (int)$new['creditId'];

        $db->update('tbl_payment_history',array('is_cancled_user'=>'y'),array('subscr_id'=>$subscr_id));


    }

   /* else if($_POST['payment_status'] == 'Pending') {
        $sql = $db->pdoQuery("SELECT u.fname,u.lname,u.email,u.profilecode FROM  tbl_users AS u WHERE u.id  = ? ORDER BY u.id DESC LIMIT 1",array((int)$userid));
        if($sql->affectedRows() > 0){
            $fetchRes2 = $sql->result();
            $activationLink = SITE_URL;
            $mailarray = array('greetings'=>$fetchRes2['fname'],'activationLink'=>$activationLink);
            $array = generateEmailTemplatenew('purchase_membership_plan',$mailarray);
            sendEmailAddress($fetchRes2['email'],$array['subject'],$array['message']);
        }
    } */

?>