<?php
	$reqAuth=true;
	require_once("../../../includes-nct/config-nct.php");
	require_once("class.experience-nct.php");		
	$module = "experience-nct";
	$table = "tbl_experience";
	
	$styles = array(array("data-tables/DT_bootstrap.css",SITE_ADM_PLUGIN),
					array("bootstrap-switch/css/bootstrap-switch.min.css",SITE_ADM_PLUGIN));
	
	$scripts= array("core/datatable.js",
					array("data-tables/jquery.dataTables.js",SITE_ADM_PLUGIN),
					array("data-tables/DT_bootstrap.js",SITE_ADM_PLUGIN),
					array("bootstrap-switch/js/bootstrap-switch.min.js",SITE_ADM_PLUGIN));
	
	chkPermission($module);
	$Permission=chkModulePermission($module);
	global $fb;
	$metaTag = getMetaTags(array("description"=>"Admin Panel",
			"keywords"=>'Admin Panel',
			'author'=>AUTHOR));
	$breadcrumb = array("Experience");
	$page_name = "Experience";
	
	$id = isset($_GET["id"]) ? (int)trim($_GET["id"]) : 0;	
	$postType = isset($_POST["type"])?trim($_POST["type"]):'';	
	$type = isset($_GET["type"])?trim($_GET["type"]):$postType;	
	
	$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage').' Experience';
	$winTitle = $headTitle.' - '.SITE_NM;
	
	if(isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
		extract($_POST);

		$insArray = array(
			'hours'=>isset($hours) ? $hours : '',			
			'isActive'=>isset($isactive) ? $isactive : 'y'			
		);
		
		if($insArray['hours']>0 ){				
			if($type == 'edit' && $id > 0){				
				if(in_array('edit',$Permission)){					
					if($id > 0 ){						
						$db->update($table, $insArray, array("id"=>$id));
						$providers = $db->select('tbl_users',array('userId'),array('userType'=>'p'))->results();
						foreach ($providers as $key => $value) {
							//get completed projects

							$completed = $db->pdoQuery('select COUNT(id) AS tot_completed FROM tbl_projects WHERE providerId = ? AND jobStatus = "completed" ',array($value['userId']))->result();
							$completed = $completed['tot_completed'];

							$db->pdoQuery('UPDATE tbl_users SET experience = CASE WHEN ? <= (SELECT hours FROM tbl_experience WHERE NAME = "Entry Level") THEN "entry level" WHEN (? < (SELECT hours FROM tbl_experience WHERE NAME = "Expert") AND ? > (SELECT hours FROM tbl_experience WHERE NAME = "Entry Level")) THEN "moderate" ELSE "expert" END WHERE userId = ?',array($completed,$completed,$completed,$value['userId']));
						}
						
						$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>'Record has been updated successfully'));
					}
				}else{
					$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>'You are not authorised to perform this action.'));
				}
			} else {
				if(in_array('add',$Permission)){
					if($id > 0){
						$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>'Record already exist. Please check carefully.'));
					}else{
						$insArray['createddate'] = date("Y-m-d H:i:s");
						$id=$db->insert($table, $insArray)->getLastInsertId();
						$activity_array = array("id"=>$id,"module"=>$module,"activity"=>'add');
						add_admin_activity($activity_array);
						$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>'New experience level has been added'));
					}
				}else{
					$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>'You are not authorised to perform this action.'));
				}
			}
			redirectPage(SITE_ADM_MOD.$module);
		}
		else {			
			$toastr_message  = $_SESSION["toastr_message"] = array('type'=>'err','var'=>'Please fill all required fields carefully.');
		}
	}	
	$objContent = new Experience($module);
	$pageContent = $objContent->getPageContent();
	require_once(DIR_ADMIN_TMPL."parsing-nct.tpl.php");	
	//require_once(DIR_ADMIN_THEME."default.nct");