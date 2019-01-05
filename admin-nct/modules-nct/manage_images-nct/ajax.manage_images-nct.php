<?php
	$content = '';
	require_once("../../../includes-nct/config-nct.php");
	if($adminUserId == 0){die('Invalid request');}
	require_once("class.manage_images-nct.php");
	$module = 'manage_images-nct';
	$table = 'tbl_home_images';
	chkPermission($module);
	$Permission=chkModulePermission($module);
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
		//_print_r($_REQUEST);
		$setVal = array('status'=>($value == 'y' ? 'a' : 'd'));
		$db->update($table,$setVal,array("id"=>$id));
		echo json_encode(array('type'=>'success','Record '.($value == 'y' ? 'activated ' : 'deactivated ').'successfully'));
		$activity_array = array("id"=>$id,"module"=>$module,"activity"=>'status',"action"=>$value);
		add_admin_activity($activity_array);
		exit;
	} else if($action == 'get_properties') {
		$q=str_replace(array('_', '%'), array('\_', '\%'),$_GET['q']);
		$qData= $db->pdoQuery("select id,listing_title as name FROM `tbl_listing` where visible='v' and status='a' and listing_title LIKE '%$q%' ORDER BY listing_title ")->results();
		$arr = array();
		$i = 0;
		foreach ($qData as $value) {
			$arr[$i] = $value;
			$i++;
		}
		print json_encode($arr);
		exit;
	} else if($action == "delete" && in_array('delete',$Permission)) {
		//$aWhere=array("id"=>$id);
		//$db->delete($table,$aWhere);
		$db->update($table,array("status"=>"t"),array("id"=>$id));
		$activity_array = array("id"=>$id,"module"=>$module,"activity"=>'delete');
		add_admin_activity($activity_array);
	}
	$mainObject = new Image($id, $searchArray, $action);
	extract($mainObject->data);
	echo ($content);
	exit;