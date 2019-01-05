<?php
	$content = '';
	require_once("../../../includes-nct/config-nct.php");
	if($adminUserId == 0){die('Invalid request');}
	include("class.category-nct.php");
	
	$module = 'category-nct';
	$table = 'tbl_categories';
	$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
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
	 
	if($action == "updateStatus"  && in_array('status',$Permission)) {

		//get no of projects in that category
		$no_of_projects = $db->count('tbl_projects',array('categoryId'=>$id,'isActive'=>'y'));
		if($no_of_projects>0 && $value == 'd' ){
			echo json_encode(array('type'=>'error','This category contains active projects and hence you can not deactivate it.'));exit;
		}

		$setVal = array('isActive'=>($value == 'a' ? 'y' : 'n'));
		$db->update($table,$setVal,array("id"=>$id));
		echo json_encode(array('type'=>'success','Record '.($value == 'a' ? 'activated ' : 'deactivated ').'successfully'));exit;
	}else if($action == "delete"  && in_array('delete',$Permission)) {
		//get no of projects in that category
		$no_of_projects = $db->count('tbl_projects',array('categoryId'=>$id,'isActive'=>'y'));
		if($no_of_projects>0){
			echo json_encode(array('type'=>'error','message' => 'This category contains active projects and hence you can not delete it.'));exit;
		}
		$aWhere=array("id"=>$id);
		$db->delete($table,$aWhere);
		echo json_encode(array('type' => 'success', 'message' => "Record has been deleted successfully."));
        exit;
	}
	
	$mainObject = new Category($module,$id,$searchArray,$action);
	extract($mainObject->data);
	echo ($content);
	exit;