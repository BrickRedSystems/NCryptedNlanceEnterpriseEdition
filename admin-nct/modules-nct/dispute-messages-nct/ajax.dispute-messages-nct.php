<?php
	$content = '';
	require_once("../../../includes-nct/config-nct.php");
	if($adminUserId == 0){die('Invalid request');}
	include("class.dispute-messages-nct.php");
	
	$module = 'dispute-messages-nct';
	$table = 'tbl_messages';
	$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
	
	//$image = array();
	$image= isset($_GET['image']) ? trim($_GET['image']) : '';
	$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
	$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
	$page = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
	$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
	$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
	$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;	
	$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;	
	$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
	$langId = isset($_POST['langId']) ? $_POST['langId'] : 1;
	$ctype = isset($_POST['ctype']) ? $_POST['ctype'] : 'f';
	

	extract($_GET);
	$searchArray = array("page"=>$page, "rows"=>$rows, "sort"=>$sort, "order"=>$order, "offset"=>$page, "chr"=>$chr, 'sEcho' =>$sEcho,'langId'=>$langId,'ctype'=>$ctype);
	chkPermission($module);
	$Permission=chkModulePermission($module);
	$mainObject = new DisputeMessages($module,$id,$searchArray,$action);
	if($action == "delete"  && in_array('delete',$Permission)) {
		$aWhere=array("id"=>$id);
		$db->delete($table,$aWhere);
		echo json_encode(array('type'=>'success','message'=>'Record has been deleted successfully'));
		exit;	
	}
	if(isset($_REQUEST['switch_action']) && $_REQUEST['switch_action'] == "featured" && in_array('status',$Permission)) {
		$setVal = array('isFeatured'=>($value == 'a' ? 'y' : 'n'));
		$db->update($table,$setVal,array("id"=>$id));
		echo json_encode(array('type'=>'success',$value == 'a' ? 'Project has been added to featured ' : 'Project has been removed from featured  '));
		exit;
	}
		
	
	extract($mainObject->data);
	echo ($content);
	exit;