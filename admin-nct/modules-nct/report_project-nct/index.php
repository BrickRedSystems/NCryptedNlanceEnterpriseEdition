<?php
$reqAuth = true;
require_once ("../../../includes-nct/config-nct.php");
require_once ("class.report_project-nct.php");

$module = "report_project-nct";
$table = "tbl_report_abuse";

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
$breadcrumb = array("Reported Projects");

$id = isset($_GET['id']) ? $_GET['id'] : 0;

$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;
$ctypeTxt = isset($_REQUEST["ctype"]) ? trim($_REQUEST["ctype"]) : "f";
$ctype = $ctypeTxt == 'pages' ? 't' : ($ctypeTxt == 'messages' ? 'm' : 'f');
$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage ') . ' Reported Project';
$winTitle = $headTitle . ' - ' . SITE_NM;
$updateArr = array();

if (isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
	extract($_POST);
	
	$updateArr = array(
		'reply' => $reply,
		'emailStatus'=>'y'
		);
	
	if (trim($reply) != "" && trim($reply)!='' ) {
		//reply to user about report
		if ($type == 'edit' && $id > 0) {
			if (in_array('edit', $Permission)) {

				$db -> update($table, $updateArr, array("id" => $id));

				$details = $db->pdoQuery("SELECT 
					p.title, p.slug,
					CONCAT_WS(' ',u.`firstName`,u.`lastName`) AS userName,u.email,	
					u.userType, r.*	FROM `tbl_report_abuse` AS r 
					LEFT JOIN `tbl_projects` AS p 
					ON r.`projectId` = p.`id` 
					LEFT JOIN tbl_users AS u 
					ON r.`userId`=u.`userId`
					WHERE r.id = ? 
					",array($id))->result();
				
                //send to customer
				$array = generateEmailTemplate('report_project_reply', array(
					'greetings' => ucfirst($details['userName']),
					'projectTitle' => $details['title'],
					'reply' => $details['reply'],
					'date'=> $details['createdDate']
					));
                //echo $array['message'];exit;
				sendEmailAddress($details['email'], $array['subject'], $array['message']);

				$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Your reply has been sent to reporter.'));
                    redirectPage(SITE_ADM_MOD . 'report_project-nct/');

			}
			else {

				$msgType = $_SESSION["toastr_message"] = disMessage(array(
					'type' => 'err',
					'var' => 'You are not authorised to perform this action.'
					));
				redirectPage(SITE_ADM_MOD . 'report_project-nct/');
			}
		}
	}
	else {
		$msgType = array(
			'type' => 'err',
			'var' => 'Please fill all required fields carefully.'
			);
		redirectPage(SITE_ADM_MOD . 'report_project-nct/');
	}
}

$constObj = new ReportProject($module, $id = 0, array(), $type = 'langArray');
$pageContent = $constObj -> getPageContent();
require_once (DIR_ADMIN_TMPL . "parsing-nct.tpl.php");
