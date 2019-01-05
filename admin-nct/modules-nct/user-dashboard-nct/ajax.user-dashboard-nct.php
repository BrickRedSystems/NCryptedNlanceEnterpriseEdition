<?php

$content = '';
require_once("../../../includes-nct/config-nct.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include("class.user-dashboard-nct.php");

$module = 'user-dashboard-nct';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_users';

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
$searchArray = array("page" => $page, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page, "chr" => $chr, 'sEcho' => $sEcho);

//echo "<pre>";print_r($_REQUEST);exit;
if(isset($_REQUEST['id']) && $_REQUEST['id'] > 0 && isset($_REQUEST['action']) && $_REQUEST['action'] != "" ) {
    $user_id = filtering($_REQUEST['id'], 'input', 'int');
    $action = filtering($_REQUEST['action']);
    
    $response = array();
    $response['status'] = false;
    
    $objUserDashboard = new User_dashboard($module);
    if($action == "experience") {
        $response['status'] = true;
        $response['html'] = $objUserDashboard->getExperience($user_id);
    } else if($action == "education") {
        $response['status'] = true;
        $response['html'] = $objUserDashboard->getEducation($user_id);
    } else if($action == "languages") {
        $response['status'] = true;
        $response['html'] = $objUserDashboard->getLanguages($user_id);
    } else if($action == "skills") {
        $response['status'] = true;
        $response['html'] = $objUserDashboard->getSkills($user_id);
    } else if($action == "my_pages") {
        $response['status'] = true;
        $response['html'] = $objUserDashboard->getMyPages($user_id);
    } else if($action == "following") {
        $response['status'] = true;
        $response['html'] = $objUserDashboard->getFollowing($user_id);
    } else if($action == "my_jobs") {
        $response['status'] = true;
        $response['html'] = $objUserDashboard->getMyJobs($user_id);
    } else if($action == "applied_jobs") {
        $response['status'] = true;
        $response['html'] = $objUserDashboard->getAppliedJobs($user_id);
    } else if($action == "my_groups") {
        $response['status'] = true;
        $response['html'] = $objUserDashboard->getMyGroups($user_id);
    } else if($action == "joined_groups") {
        $response['status'] = true;
        $response['html'] = $objUserDashboard->getJoinedGroups($user_id);
    } else if($action == "connections") {
        $response['status'] = true;
        $response['html'] = $objUserDashboard->getConnections($user_id);
    } else if($action == "membership_plans") {
        $response['status'] = true;
        $response['html'] = $objUserDashboard->getMembershipPlans($user_id);
    } else {
        $response['error'] = "The requested content can not be fetched";
    }
     
    echo json_encode($response);
    exit;
}

$mainObject = new User_dashboard($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
