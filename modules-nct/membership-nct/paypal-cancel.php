<?php

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
	$reqAuth = true;
	if($sessUserId == 0){
		redirectPage(SITE_URL);
	}
	else{
		$log_array = print_r($_REQUEST, true);

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
	    $paymentId = (int) $new['paymentId'];
	    $userId       = (int) $new['userId'];

		$getData = $db->pdoQuery("SELECT * FROM tbl_payment_history WHERE id = ".$paymentId)->result();
		$db -> update('tbl_payment_history', array(
			'payer_email' => $_POST['payer_email'],
			'receiver_email' => $_POST['receiver_email'],
			'transactionId' => $_POST['txn_id'],
			'jsonDetails' => json_encode($log_array),
			'paymentStatus' => 'failed',
		), array(
			'id' => $paymentId,
			'userId' => $userId
		));
		if(isset($_SESSION['random_number']) && $_SESSION['random_number']!=''){?>
        	<br /><br /><center><h1> <font color="#FF0000">Beginning to cancel transaction......</font></h1><center>
			<?php   unset($_SESSION['random_number']);
			 $_SESSION["msgType"] = disMessage(array(
                    'type' => 'suc',
                    'var' => "Successfully cancel transaction."
        	));
        	redirectPage(SITE_URL.'membership-plans/');
        }
		else{
			 $_SESSION["msgType"] = disMessage(array(
                    'type' => 'suc',
                    'var' => "Successfully cancel transaction."
        	));
			redirectPage(SITE_URL);
		}
	}
?>