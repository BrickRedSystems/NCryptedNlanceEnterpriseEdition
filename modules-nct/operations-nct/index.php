<?php
$reqAuth = false;
$module = 'operations-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.operations-nct.php";

$obj = new Operations($module, 0, $_REQUEST);

extract($_REQUEST);
if (isset($action) && isset($method) && $action == "method" && method_exists($obj, $method)) {
	$obj->{$method}();
	
}else{
	$response['status'] = 0;
	$response['msg'] = Something_went_wrong;
	echo json_encode($response);
	exit ;
}
?>