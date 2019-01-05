<?php
$reqAuth=true;
require_once("../../../includes-nct/config-nct.php");
require_once("class.manage_images-nct.php");
	//error_reporting(1);
$module = "manage_images-nct";
$table = "tbl_home_images";
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
	'author'=>AUTHOR));
$id = isset($_GET["id"]) ? (int)trim($_GET["id"]) : 0;
$postType = isset($_POST["type"])?trim($_POST["type"]):'';
$type = isset($_GET["type"])?trim($_GET["type"]):$postType;
$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage').' Images';
$winTitle = $headTitle.' - '.SITE_NM;
$breadcrumb = array($headTitle);
if(isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
		//dump_exit($_POST);
		/*_print_r($_FILES);*/
		extract($_POST);
		extract($_FILES);
		$objPost->id = isset($id) ? $id : '';
		$objPost->name = isset($name) ? $name : '';
		$objPost->ip_address = get_ip_address();
		$objPost->status = isset($status) ? $status : 'd';
		$objPost->image_caption = isset($image_caption)?$image_caption:'';

		$totals = $db->select('tbl_home_images','*',array('status'=>'a'),' and id != '.$id)->affectedRows();
		if($totals == 1 && $objPost->status == 'a' ){
			$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>'Only image or video can be shown at one time.'));
			redirectPage(SITE_ADM_MOD.$module);
		}

	
		if(isset($image['name']) && $image['name']!=''){
			$time = time();
			$extenssion = explode('.', $image['name']);
			$file_name = $time.'.'.$extenssion[count($extenssion)-1];
			if(!file_exists(DIR_UPD_HOMEIMG )){ mkdir(DIR_UPD_HOMEIMG,0777); } 
			if (move_uploaded_file($image["tmp_name"], DIR_UPD_HOMEIMG.$file_name)) {
				$objPost->image_name = $file_name;
				if(file_exists(DIR_UPD_HOMEIMG.$txt_old_image))
					unlink(DIR_UPD_HOMEIMG.$txt_old_image);
			} else {
				$objPost->image_name = $txt_old_image;
			}
		}

		if(isset($video['name']) && $video['name']!=''){
			$time = time();
			$extenssion = explode('.', $video['name']);
			$file_name = $time.'.'.$extenssion[count($extenssion)-1];
			if(!file_exists(DIR_UPD_HOMEIMG )){ mkdir(DIR_UPD_HOMEIMG,0777); } 
			if (move_uploaded_file($video["tmp_name"], DIR_UPD_HOMEIMG.$file_name)) {
				$objPost->image_name = $file_name;
				if(file_exists(DIR_UPD_HOMEIMG.$txt_old_image))
					unlink(DIR_UPD_HOMEIMG.$txt_old_image);
			} else {
				$objPost->image_name = $txt_old_image;
			}
		}

		if($objPost->name != ""){
			if($type == 'edit' && $id > 0){
				if(in_array('edit',$Permission)){
					$objPost->txt_old_image = isset($txt_old_image) ? $txt_old_image : '';
					$objPost->image_name = (isset($objPost->image_name) && $objPost->image_name!='')?$objPost->image_name:$objPost->txt_old_image;
					
					$temp = array();
					$temp["name"] = $objPost->name;
					$temp["image_name"] = $objPost->image_name;
					$temp["status"] = $objPost->status;
					$temp["image_caption"] = $objPost->image_caption;

					$db->update($table, $temp, array("id"=>$id));
					$activity_array = array("id"=>$id,"module"=>$module,"activity"=>'edit');
					add_admin_activity($activity_array);
					$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>'Your changes are saved successfully.'));
				}else{
					$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>'You are not authorised to perform this action'));
				}
			}
			redirectPage(SITE_ADM_MOD.$module);
		}
		else {
			$toastr_message = array('type'=>'err','var'=>'Please fill all the values before submitting');
		}
	}
	$objImage = new Image($id, NULL, $type);
	$pageContent = $objImage->getPageContent();
	require_once(DIR_ADMIN_TMPL."parsing-nct.tpl.php");