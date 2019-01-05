<?php
	$content = '';
	require_once("../../../includes-nct/config-nct.php");
	if($adminUserId == 0){die('Invalid request');}
	include("class.country-nct.php");
	
	$module = 'country-nct';
	chkPermission($module);
	$Permission=chkModulePermission($module);
	
	$table = 'tbl_country';
	$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
	$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
	$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
	$page = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
	$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
	$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
	$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;	
	$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;	
	$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;

	extract($_GET);
	$searchArray = array("page"=>$page, "rows"=>$rows, "sort"=>$sort, "order"=>$order, "offset"=>$page, "chr"=>$chr, 'sEcho' =>$sEcho);
	if($action == "updateStatus" && in_array('status',$Permission)) {

	
		$setVal = array('isActive'=>($value == 'a' ? 'y' : 'n'));
		
		$db->update($table,$setVal,array("CountryId"=>$id));
		echo json_encode(array('type'=>'success','Record '.($value == 'a' ? 'activated ' : 'deactivated ').'successfully'));
		$activity_array = array("id"=>$id,"module"=>$module,"activity"=>'status',"action"=>$value);
		add_admin_activity($activity_array);
		exit;
	} else if($action == "delete" && in_array('delete',$Permission)) {
		/*$aWhere=array("id"=>$id);
		$db->delete($table,$aWhere);*/
		$db->delete($table,array("CountryId"=>$id));
		//$db->delete("tbl_state",array("status"=>'t'),array("country_id"=>$id));
		//db->delete("tbl_city",array("status"=>'t'),array("country_id"=>$id));
		
		$activity_array = array("id"=>$id,"module"=>$module,"activity"=>'delete');
		add_admin_activity($activity_array);
		echo json_encode(array('type'=>'success','message'=>'Record deleted successfully'));
		exit;
	}

	$mainObject = new Country($id, $searchArray, $action);
	extract($mainObject->data);
	echo ($content);
	exit;