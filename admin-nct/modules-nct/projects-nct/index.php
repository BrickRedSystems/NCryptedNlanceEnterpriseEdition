<?php
$reqAuth = true;
require_once ("../../../includes-nct/config-nct.php");
require_once ("class.projects-nct.php");

$module = "projects-nct";
$table = "tbl_projects";

chkPermission($module);
$Permission = chkModulePermission($module);

$styles = array(
	array(
		"data-tables/DT_bootstrap.css",
		SITE_ADM_PLUGIN
	),
	array(
		"bootstrap-switch/css/bootstrap-switch.min.css",
		SITE_ADM_PLUGIN
	)
);

$scripts = array(
	"core/datatable.js",
	array(
		"data-tables/jquery.dataTables.js",
		SITE_ADM_PLUGIN
	),
	array(
		"data-tables/DT_bootstrap.js",
		SITE_ADM_PLUGIN
	),
	array(
		"bootstrap-switch/js/bootstrap-switch.min.js",
		SITE_ADM_PLUGIN
	)
);

$metaTag = getMetaTags(array(
	"description" => "Admin Panel",
	"keywords" => 'Admin Panel',
	"author" => SITE_NM
));
$breadcrumb = array("Projects");

$id = isset($_GET['id']) ? $_GET['id'] : 0;

$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;
$ctypeTxt = isset($_REQUEST["ctype"]) ? trim($_REQUEST["ctype"]) : "f";
$ctype = $ctypeTxt == 'pages' ? 't' : ($ctypeTxt == 'messages' ? 'm' : 'f');
$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage ') . ' Projects';
$winTitle = $headTitle . ' - ' . SITE_NM;
$insArray = array();

if (isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
	extract($_POST);
	$insArray = array(
		'title' => $title,
		'description' => $description,		
		'categoryID' => $categoryId,
		'subcategoryID' => $subcategoryId,		
		'duration' => $duration,
		'jobStatus' => $jobStatus,		
		'isFeatured' => $isFeatured
	);

	if (trim($title) != "" && $categoryId>0 && $duration>0 && trim($jobStatus)!='' && trim($isFeatured)!='') {
		if ($type == 'edit' && $id > 0) {
			if (in_array('edit', $Permission)) {

				$up = $db -> update($table, $insArray, array("id" => $id));

				$_SESSION["toastr_message"] = disMessage(array(
					'type' => 'suc',
					'var' => 'Record has been updated successfully.'
				));
			}
			else {
				$msgType = $_SESSION["toastr_message"] = disMessage(array(
					'type' => 'err',
					'var' => 'You are not authorised to perform this action.'
				));
			}
		}
		else {
			if (in_array('add', $Permission)) {

				$db -> insert("tbl_projects", $insArray);

				$_SESSION["toastr_message"] = disMessage(array(
					'type' => 'suc',
					'var' => 'Record has been added successfully.'
				));

			}
			else {
				$msgType = $_SESSION["toastr_message"] = disMessage(array(
					'type' => 'err',
					'var' => 'You are not authorised to perform this action.'
				));
			}
		}
		redirectPage(SITE_ADM_MOD . $module);
	}
	else {
		$msgType = array(
			'type' => 'err',
			'var' => 'Please fill all required fields carefully.'
		);
	}
}

$constObj = new Projects($module, $id = 0, array(), $type = 'langArray');
$pageContent = $constObj -> getPageContent();
require_once (DIR_ADMIN_TMPL . "parsing-nct.tpl.php");
