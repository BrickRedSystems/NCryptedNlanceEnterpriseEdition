<?php
$reqAuth = true;
require_once ("../../../includes-nct/config-nct.php");
include ("class.redeem_request-nct.php");
$module = "redeem_request-nct";
$table = "tbl_redeem_requests";

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

$id = isset($_GET["id"]) ? (int)trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Redeem Request';
$winTitle = $headTitle . ' - ' . SITE_NM;
$breadcrumb = array($headTitle);

if (isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
	extract($_POST);

	/*$objPost->id = isset($id) ? $id : 0;*/
	$objPost -> genre_id = isset($genre_id) ? $genre_id : 0;
	$objPost -> lang_id = isset($lang_id) ? $lang_id : 0;
	$objPost -> profile_image = isset($profile_image) ? $profile_image : '';

	$insArray = array(
		'fname' => isset($firstName) ? $firstName : '',
		'lname' => isset($lastName) ? $lastName : '',
		'isactive' => isset($status) ? $status : 'n',
		'country_id' => isset($country_id) ? $country_id : '',
	);

	if (!empty($insArray['fname']) && !empty($insArray['lname']) && !empty($insArray['isactive']) && !empty($insArray['country_id']) && $objPost -> genre_id != '' && $objPost -> lang_id != '' && $objPost -> profile_image != '') {

		if ($type == 'edit' && $id > 0) {
			if (in_array('edit', $Permission)) {

				$old_image = getTableValue('tbl_user_profile_images', 'imagename', array(
					'userid' => $id,
					'isselected' => 'y'
				));

				if ($old_image != $objPost -> profile_image) {
					if (file_exists(DIR_PROFILEIMG . $id . '/' . $old_image)) {
						unlink(DIR_PROFILEIMG . $id . '/' . $old_image);
					}
				}
				$db -> update($table, $insArray, array("id" => $id));
				$db -> update('tbl_user_profile_images', array('imagename' => $objPost -> profile_image), array(
					'userid' => $id,
					'isselected' => 'y'
				));
				$db -> update('tbl_user_languages', array("language_id" => $lang_id), array("userid" => $id));
				$db -> update('tbl_user_genres', array("genre_id" => $genre_id), array("userid" => $id));

				$activity_array = array(
					"id" => $id,
					"module" => $module,
					"activity" => 'edit',
					"action" => 'edited',
					"notification_type" => "sub_edit"
				);
				add_admin_activity($activity_array);
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
		redirectPage(SITE_ADM_MOD . $module);
	}
	else {
		$msgType = array(
			'type' => 'err',
			'var' => 'Please fill all required fields carefully.'
		);
	}
}

$objRedeem = new Redeem($module);
$pageContent = $objRedeem -> getPageContent();

require_once (DIR_ADMIN_TMPL . "parsing-nct.tpl.php");
