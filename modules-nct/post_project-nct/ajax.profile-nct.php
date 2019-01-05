<?php
$reqAuth = false;
$module = 'profile-nct';
require_once "../../includes-nct/config-nct.php";
//require_once "class.profile-nct.php";
//$obj = new Profile($module, 0, $_POST);

$response['status'] = '0';
$response['msg'] = "undefined";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'socialVerify') {
	if ($sessUserId <= 0) {
		$response['status'] = '0';
		$response['msg'] = "Please login to perform this action.";

		echo json_encode($response);
		exit ;
	}
	extract($_REQUEST);
	$firstName = $first_name;
	$lastName = $last_name;

	$data = $db -> select('tbl_users', '*', array('userId' => $sessUserId));
	$affrows = $data -> affectedRows();

	if ($affrows > 0) {
		$res = $data -> result();
		if ($res['isActive'] == 'y') {
			$updArr = array(
				'lastLoginTime' => date("Y-m-d h:i:s"),
				'ipAddress' => get_ip_address(),
				'facebook_verify' => ($provider == 'facebook') ? 1 : $res['facebook_verify'],
				'google_verify' => ($provider == 'google') ? 1 : $res['google_verify'],
				'linkedin_verify' => ($provider == 'linkedin') ? 1 : $res['linkedin_verify']
			);
			$data = $db -> update('tbl_users', $updArr, array('userId' => $sessUserId));
			$response['status'] = '1';
		}
	}
}

echo json_encode($response);
exit ;
?>