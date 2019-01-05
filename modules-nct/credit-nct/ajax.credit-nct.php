<?php
$reqAuth = false;
$module = 'credit-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.credit-nct.php";

$obj = new Credit();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : NULL;
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : NULL;
$table = 'tbl_credit_plans';
if ($action == 'buy' && $id > 0) {
	extract($_REQUEST);

	$date = date('Y-m-d H:i:s');
	$ip = get_ip_address();

	$planData = $db -> select($table, '*', array('id' => $id)) -> result();

	if ($planData['price'] > 0) {
		$replace = array(
			'%creditId%' => $id,
			'%userId%' => $sessUserId,
			'%finalPrice%' => $planData['price']
		);
		echo get_view(DIR_TMPL . $module . "/credit-form-nct.tpl.php", $replace);
	}
	else {
		$_SESSION["msgType"] = disMessage(array(
			'type' => 'err',
			'var' => invalid_price_for_plan_purchase
		));
		redirectPage(SITE_MEM_PLANS);
	}

}
?>