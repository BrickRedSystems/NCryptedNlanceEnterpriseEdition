<?php
	$reqAuth=true;
	require_once("../../../includes-nct/config-nct.php");

	include("class.state-nct.php");
	$module = "state-nct";
	$table = "tbl_state";
	
	$styles = array(array("data-tables/DT_bootstrap.css",SITE_ADM_PLUGIN),
					array("bootstrap-switch/css/bootstrap-switch.min.css",SITE_ADM_PLUGIN));
	
	$scripts= array("core/datatable.js",
					array("data-tables/jquery.dataTables.js",SITE_ADM_PLUGIN),
					array("data-tables/DT_bootstrap.js",SITE_ADM_PLUGIN),
					array("bootstrap-switch/js/bootstrap-switch.min.js",SITE_ADM_PLUGIN));
	
	chkPermission($module);
	$Permission=chkModulePermission($module);
	
	$metaTag = getMetaTags(array("description"=>"Admin Panel",
			"keywords"=>'Admin Panel',
			"author"=>SITE_NM));
	
	$id = isset($_GET["id"]) ? (int)trim($_GET["id"]) : 0;	
	$postType = isset($_POST["type"])?trim($_POST["type"]):'';	
	$type = isset($_GET["type"])?trim($_GET["type"]):$postType;	
	
	$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage').' County';
	$winTitle = $headTitle.' - '.SITE_NM;
	$breadcrumb = array($headTitle);		
	if(isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
		extract($_POST);
		$objPost->stateName = isset($stateName) ? $stateName : '';
		$objPost->country_id = isset($section) ? $section : '';
		$objPost->status	= isset($isActive) && $isActive == 'y' ? 'y' : 'n';
		
		if($objPost->stateName != ""){

			if($type == 'edit' && $id > 0){
				if(in_array('edit',$Permission)){

					$exist = $db->pdoQuery("Select StateID from tbl_state where stateName = '".$objPost->stateName."' and StateID != ".$id."")->result();

					if($exist == 0){

						$db->update($table, array('stateName'=>$objPost->stateName,"CountryID"=>$objPost->country_id,'isActive'=>$objPost->status),array("StateID"=>$id));
						
						$activity_array = array("id"=>$id,"module"=>$module,"activity"=>'edit');
						add_admin_activity($activity_array);
						
						$_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>'recEdited'));

					}else{
						$_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>'recExist'));
					}
				}
				else{
					$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>'NoPermission'));		
				}
			} else {
				if(in_array('add',$Permission)){

					if(getTotalRows($table,"stateName='".$objPost->stateName."'",'StateID')==0){

						$valArray = array("stateName"=>$objPost->stateName,"CountryID"=>"".$objPost->country_id."","isActive"=>$objPost->status);
						$db->insert("tbl_state", $valArray);
						
						$activity_array = array("id"=>$id,"module"=>$module,"activity"=>'add');
						add_admin_activity($activity_array);
						
						$_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>'recAdded'));

					}else{
						$_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>'recExist'));
					}
				}
				else{
					$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>'NoPermission'));
				}
			}
			redirectPage(SITE_ADM_MOD.$module);
		}
		else {

			$msgType = array('type'=>'err','var'=>'fillAllvalues');
		}
	}	
	$searchArray = array();
	$objState = new State($id,$searchArray, $type);
	$pageContent = $objState->getPageContent();
	 
	require_once(DIR_ADMIN_TMPL."parsing-nct.tpl.php");	 