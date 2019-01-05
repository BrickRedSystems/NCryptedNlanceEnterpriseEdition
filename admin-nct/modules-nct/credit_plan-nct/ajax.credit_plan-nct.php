<?php
	$content = '';
	require_once("../../../includes-nct/config-nct.php");
	if($adminUserId == 0){die('Invalid request');}
	include("class.credit_plan-nct.php");
	
	$module = 'credit_plan-nct';
	chkPermission($module);
	$Permission=chkModulePermission($module);
	$table = 'tbl_credit_plans';
	$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
	$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
	$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
	$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
	$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
	$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
	$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;	
	$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;	
	$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;

	extract($_GET);
	$searchArray = array("page"=>$page_no, "rows"=>$rows, "sort"=>$sort, "order"=>$order, "offset"=>$page_no, "chr"=>$chr, 'sEcho' =>$sEcho);

	 if($action == "updateStatus") {
		$setVal = array('isActive'=>($value == 'a' ? 'y' : 'n'));
		$db->update($table,$setVal,array("id"=>$id));	
		echo json_encode(array('type'=>'success','Plan '.($value == 'a' ? 'activated ' : 'deactivated ').'successfully'));
		exit;
	} /*else if($action == "delete") {
		$aWhere=array("id"=>$id);
		$db->delete($table,$aWhere);
	}*/
	
	$mainObject = new Credit_plan($module, $id, NULL, $searchArray, $action);
	extract($mainObject->data);
	echo ($content);
	exit;