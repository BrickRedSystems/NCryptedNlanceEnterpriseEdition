<?php
$reqAuth = false;
$module = 'unsubscribe-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.unsubscribe-nct.php";

$obj = new Home();

$response['status'] = '0';
$response['msg'] = "undefined";
//Oops! something went wrong. Please try again later.

echo json_encode($response);
exit ;
?>