<?php
$reqAuth = false;
$module = 'login-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.login-nct.php";

extract($_REQUEST);

$winTitle = 'Forgot password ' . SITE_NM;

$headTitle = 'Forgot password ' . SITE_NM;
$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));

if ($sessUserId > 0) {
	$msgType = $_SESSION['msgType'] = disMessage(array(
		"type" => "suc",
		"var" => You_are_already_logged_in
	));
	redirectPage(SITE_URL);
}
else if (isset($_POST['submitForgetForm'])) {
	extract($_POST);

///////////////////
if(!isset($dataOnly) || !$dataOnly){
if(!checkFormToken($token)){
	$msgType = $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var' => Security_token_for_this_action_is_invalid_Please_refresh_and_try_again
        ));
        redirectPage(SITE_FORGOT);

}
}
///////////////////



	if (isset($email) && $email != NULL) {

		$selQuery = $db -> select("tbl_users", array(
			"userId",
			"firstName",
			"lastName",
			"userName",
			"email",
			"status",
			"isActive"
		), array(
			"email" => $email,
			"or userName=" => $email
		));
		if ($selQuery -> affectedRows() >= 1) {
			$result = $selQuery -> result();
			if ($result != false) {
				extract($result);

				if ($isActive == "n") {
					$msgType = $_SESSION["msgType"] = disMessage(array(
						'type' => 'err',
						'var' => Dear_user_it_seems_like_you_have_not_activated_your_account_yet
					));
					redirectPage(SITE_URL);
				}
				else if ($status == "n") {
					$msgType = $_SESSION["msgType"] = disMessage(array(
						'type' => 'err',
						'var' => deactivated_msg1." " . SITE_NM . ". ".deactivated_msg2." " . SITE_NM . " ".deactivated_msg3,
					));
					redirectPage(SITE_URL);
				}
				else {
					$new_pass = genrateRandom();					
					$db->update('tbl_users',array('password'=>md5($new_pass)),array('userId'=>$userId));
					
					$to = $email;
					$arrayCont = array(
						'greetings' => $firstName,
						'userName'=>$userName,
						'password' => $new_pass
					);

					$array = generateEmailTemplate('forgot_password', $arrayCont);
					//echo $array['message'];exit;
					sendEmailAddress($to, $array['subject'], $array['message']);
					$msgType = $_SESSION['msgType'] = disMessage(array(
						"type" => "suc",
						"var" => We_have_sent_you_password_Please_check_your_registered_email
					));
					redirectPage(SITE_URL);
				}
			}
			else {
				$msgType = $_SESSION["msgType"] = disMessage(array(
					'type' => 'err',
					'var' => Invalid_user_name_or_email
				));
				redirectPage(SITE_URL);
			}
		}
		else {
			$msgType = $_SESSION["msgType"] = disMessage(array(
				'type' => 'err',
				'var' => Invalid_user_name_or_email
			));
			redirectPage(SITE_URL);
		}
	}
	else {
		$msgType = $_SESSION["msgType"] = disMessage(array(
			'type' => 'err',
			'var' => err_Please_enter_your_user_name_or_email
		));
	}

}

$obj = new Login($module, 0, issetor($token));

$pageContent = $obj -> getForgetPage();

require_once DIR_TMPL . "parsing-nct.tpl.php";
?>