<?php
	$content = '';
	require_once("../../../includes-nct/config-nct.php");
	if($adminUserId == 0){die('Invalid request');}
	include("class.redeem_request-nct.php");

	$module = 'redeem_request-nct';
	chkPermission($module);
	$Permission=chkModulePermission($module);
	$table = 'tbl_redeem_requests';
	$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
	$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
	$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
	$page = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
	$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
	$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
	$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
	$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
	$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
	$user_type = ((isset($_POST['user_type']) && $_POST['user_type']>0) ? $_POST['user_type'] : 0);
	$status_filter = isset($_POST['status_filter']) ? $_POST['status_filter'] :'';

	extract($_GET);
	$searchArray = array("page"=>$page, "rows"=>$rows, "sort"=>$sort, "order"=>$order, "offset"=>$page, "chr"=>$chr, 'sEcho' =>$sEcho,'user_type'=>$user_type,'status_filter'=>$status_filter);

	$mainObject = new Redeem($module, $id, NULL, $searchArray, $action);

	if($action == "updateStatus") {
		$db->update($table,array('isactive'=>($value=='y'?'y':'n')),array("id"=>$id));
		echo json_encode(array('type'=>'success','Record '.($value == 'y' ? 'activated ' : 'deactivated ').'successfully'));
		exit;
	}else if($action == "delete") {
		$db->delete($table, array('id'=>$id));
	} else if($action=="ApproveRedeem" && !empty($id)) {
		$a = $mainObject->getPayPalRedeemLink($id);
		echo json_encode($a);
		exit;
	}
	extract($mainObject->data);
	echo ($content);
	exit;