<?php
$reqAuth = true;
$module = 'account_settings-nct';
require_once ("../../includes-nct/config-nct.php");
include ("class.account_settings-nct.php");

$action = isset($_POST['action']) ? $_POST['action'] : NULL;

extract($_POST);
$obj = new Accountsettings($module, 0, issetor($token));

if ($action == "change_noti") {
	extract($_POST);

	if ($value == 1) {
		$db->delete('tbl_notification_settings', array(
			'userId' => $sessUserId,
			'typeId' => $noti_type
		));
	}
	else {
		$db->insert('tbl_notification_settings', array(
			'userId' => $sessUserId,
			'typeId' => $noti_type
		));
	}
	$response['status'] = 1;
	$response['msg'] = toastr_Your_changes_have_been_successfully_saved;

    echo json_encode($response);
    exit;
} elseif ($action == "submitChangePasswordForm") {
    extract($_POST);
    ///////////////////
    if(!isset($dataOnly) || !$dataOnly){
    	
if(!checkFormToken($token)){
	    	$response['status'] = 0;
	        $response['msg']    = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
	        $response['newToken']    = setFormToken();
	        echo json_encode($response);
	        exit;
	    }
    }    
    ///////////////////
	if ($oldpassword == null || $password == null || $password_confirmation == null) {
		$response['status'] = 0;
		$response['msg'] = toastr_Please_fill_all_the_field_before_submitting;
		echo json_encode($response);
		exit ;
	}

	$old_pass_check = getTableValue('tbl_users', 'userId', array(
		'isActive' => 'y',
		'userId' => $sessUserId,
		'password' => md5($oldpassword)
	));
	if (isset($old_pass_check) && $old_pass_check > 0) {
		if ($oldpassword == $password) {
			$response['status'] = 0;
			$response['msg'] = toastr_Your_new_password_must_be_different_than_old_one;
			echo json_encode($response);
			exit ;
		}
		if($password !== $password_confirmation){
			$response['status'] = 0;
			$response['msg'] = toastr_Your_new_password_and_confirm_password_must_be_same;
			echo json_encode($response);
			exit ;
		}


		$db->update('tbl_users', array('password' => md5($password)), array('userId' => $sessUserId));

		$to = getTableValue('tbl_users', 'email', array('userId' => $sessUserId));
		
		$arrayCont = array(
			'greetings' => $sessFirstName,
			'link' => SITE_LOGIN
		);
		$_SESSION['sendMailTo'] = $sessUserId;
		$array = generateEmailTemplate('change_pass', $arrayCont);
		//echo $array['message'];exit;
		sendEmailAddress($to, $array['subject'], $array['message']);
		$response['status'] = 1;
		$response['msg'] = toastr_password_changed_successfully;
		
	}
	else {
		$response['status'] = 0;
		$response['msg'] = toastr_incorrect_current_password;
	}

	echo json_encode($response);
	exit ;
}
else {
	$response['status'] = 0;
	$response['msg'] = toastr_something_went_wrong;

	echo json_encode($response);
	exit ;
}
?>