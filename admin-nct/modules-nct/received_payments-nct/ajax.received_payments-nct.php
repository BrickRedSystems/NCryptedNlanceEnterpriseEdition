<?php
	$content = '';
	require_once("../../../includes-nct/config-nct.php");
	if($adminUserId == 0){die('Invalid request');}
	include("class.received_payments-nct.php");

	$module = 'received_payments-nct';
	chkPermission($module);
	$Permission=chkModulePermission($module);
	$table = 'tbl_payment_history';
	$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
	$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
	$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
	$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
	$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
	$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
	$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
	$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
	$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
	$userType = ((isset($_POST['userType']) && $_POST['userType']!='') ? $_POST['userType'] : '');
	
	extract($_GET);
	$searchArray = array("page"=>$page_no, "rows"=>$rows, "sort"=>$sort, "order"=>$order, "offset"=>$page_no, "chr"=>$chr, 'sEcho' =>$sEcho,'userType'=>$userType);

	if(isset($_POST["ajaxvalidate"]) && $_POST["ajaxvalidate"]==true) {
		$genre_name = $_POST["genre_name"];
		$whr = '';
		if($id>0){
			//$whr = " AND id != $id";
			//$whr = array("id !="=> $id);
		}
		//$sqlCheck = $db->select($table,"page_name","page_name='".$page_name."' $whr");
		//echo mysql_num_rows($sqlCheck)>0 ? 'false' : 'true';
		$aWhere['genre_name'] = $genre_name;
		if($id > 0){
			$aWhere["id !="] = (int)$id;
		}
		$sqlCheck = $db->count($table,$aWhere);
		echo ($sqlCheck)>0 ? 'false' : 'true';
		exit;

	} else if($action == "updateStatus" && in_array('status',$Permission)) {
		$setVal = array('isactive'=>($value == 'y' ? 'y' : 'n'));
		$db->update($table,$setVal,array("id"=>$id));
		echo json_encode(array('type'=>'success','Record '.($value == 'y' ? 'activated ' : 'deactivated ').'successfully'));
		exit;
	} else if($action == "delete" && in_array('delete',$Permission)) {
		$aWhere=array("id"=>$id);
		$db->delete($table, $aWhere);
	}

	$mainObject = new ReceivedPayments($module, $id, NULL, $searchArray, $action);
	extract($mainObject->data);
	echo ($content);
	exit;