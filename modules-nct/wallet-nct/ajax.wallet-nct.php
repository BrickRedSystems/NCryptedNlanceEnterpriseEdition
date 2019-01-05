<?php
$reqAuth = true;
$module = 'wallet-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.wallet-nct.php";

$winTitle = $headTitle = Wallet.' - ' . SITE_NM;
$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));
$obj = new Wallet($module, 0, $_REQUEST);

$response['status'] = '0';
$response['msg'] = toastr_something_went_wrong;
$action = (isset($_POST['action']) && $_POST['action']!=null)?$_POST['action']:null;
//Oops! something went wrong. Please try again later.
if ($action == 'submitRedeemRequest') {
	$obj->submitReedeemForm();
}elseif ($action == 'submitDepositFunds') {
	$obj->submitDepositFundsForm();
}
echo json_encode($response);
exit ;
?>

