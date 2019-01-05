<?php

$reqAuth = true;
require_once ("../../../includes-nct/config-nct.php");
include ("class.users-nct.php");
$module = "users-nct";
$table = "tbl_users";

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

chkPermission($module);
$Permission = chkModulePermission($module);

$metaTag = getMetaTags(array(
	"description" => "Admin Panel",
	"keywords" => 'Admin Panel',
	'author' => AUTHOR
));

$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' User';
$winTitle = $headTitle . ' - ' . SITE_NM;
$breadcrumb = array($headTitle);

if (isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
	//echo "<pre>";print_r($_POST);exit;
	extract($_POST);
	$objPost -> id = isset($id) ? $id : '';
	$objPost -> firstName = isset($first_name) ? $first_name : '';
	$objPost -> lastName = isset($last_name) ? $last_name : '';
	$objPost -> email = isset($email_address) ? $email_address : '';
	
	$objPost -> status = isset($status) ? $status : 'a';

	if ($objPost -> firstName != "" && strlen($objPost -> firstName) > 0) {
		if ($type == 'edit' && $id > 0) {
			if (in_array('edit', $Permission)) {

				$db -> update($table, array(
					"firstName" => $objPost -> firstName,
					"lastName" => $objPost -> lastName,
					"status" => $objPost -> status
				), array("userId" => $id));

				$activity_array = array(
					"id" => $id,
					"module" => $module,
					"activity" => 'edit'
				);
				add_admin_activity($activity_array);
				$_SESSION["toastr_message"] = disMessage(array(
					'type' => 'suc',
					'var' => 'User has been updated successfully.'
				));
			}
			else {
				$toastr_message = $_SESSION["toastr_message"] = disMessage(array(
					'type' => 'err',
					'var' => 'You are not authorised to perform this action.'
				));
			}
		}
		else {
			if (in_array('add', $Permission)) {
				if (getTotalRows($table, "firstName='" . $objPost -> fname . "'", 'uId') == 0) {
					$objPost -> created_date = date('Y-m-d H:i:s');

					$valArray = array(
						"firstName" => $objPost -> firstName,
						"lastName" => $objPost -> lastName,
						"email" => $objPost -> email,
						"status" => $objPost -> status
					);

					$id = $db -> insert("tbl_users", $valArray) -> getLastInsertId();
					$activity_array = array(
						"id" => $id,
						"module" => $module,
						"activity" => 'add'
					);
					add_admin_activity($activity_array);

					$_SESSION["toastr_message"] = disMessage(array(
						'type' => 'suc',
						'var' => 'Record has been added successfully.'
					));
				}
				else {
					$_SESSION["toastr_message"] = disMessage(array(
						'type' => 'err',
						'var' => 'Record already exist. Please check carefully.'
					));
				}
			}
			else {
				$toastr_message = $_SESSION["toastr_message"] = disMessage(array(
					'type' => 'err',
					'var' => 'You are not authorised to perform this action.'
				));
			}
		}
		redirectPage(SITE_ADM_MOD . $module);
	}
	else {
		$toastr_message = array(
			'type' => 'err',
			'var' => 'Please fill all required fields carefully.'
		);
	}
}

$objUsers = new Users($module);
$pageContent = $objUsers -> getPageContent();
require_once (DIR_ADMIN_TMPL . "parsing-nct.tpl.php");
