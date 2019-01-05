<?php

$content = '';
require_once("../../../includes-nct/config-nct.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include("class.users-nct.php");

$module = 'users-nct';
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
$user_type = ((isset($_POST['user_type']) && $_POST['user_type']!='') ? $_POST['user_type'] : '');

extract($_GET);
$searchArray = array("page" => $page, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page, "chr" => $chr, 'sEcho' => $sEcho, 'user_type'=>$user_type,);

if (isset($_GET['day']) && $_GET['day'] != '') {
    $searchArray['day'] = filtering($_GET['day'], 'input', 'int');
}
if (isset($_GET['month']) && $_GET['month'] != '') {
    $searchArray['month'] = filtering($_GET['month'], 'input', 'int');
}
if (isset($_GET['year']) && $_GET['year'] != '') {
    $searchArray['year'] = filtering($_GET['year'], 'input', 'int');
}


if (isset($_POST["ajaxvalidate"]) && $_POST["ajaxvalidate"] == true) {
    $page_name = $_POST["page_name"];
    $whr = '';  

    $aWhere['page_name'] = $page_name;
    if ($id > 0) {
        $aWhere["id !="] = (int) $id;
    }
    $sqlCheck = $db->count($table, $aWhere);
    echo ($sqlCheck) > 0 ? 'false' : 'true';
    exit;
} else if ($action == "updateStatus") {
    $setVal = array('status' => ($value == 'a' ? 'a' : 'd'));
    $db->update($table, $setVal, array("userId" => $id));
    echo json_encode(array('type' => 'success', 'User ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));
    $activity_array = array("userId" => $id, "module" => $module, "activity" => 'status', "action" => $value);
    add_admin_activity($activity_array);
    exit;
} else if ($action == "delete") {
    $aWhere = array("userId" => $id);
    //$db->delete($table, $aWhere);
    $affected_rows = $db->delete($table, $aWhere)->affectedRows();

    $activity_array = array("userId" => $id, "module" => $module, "activity" => 'delete');
    add_admin_activity($activity_array);

    if ($affected_rows && $affected_rows > 0) {
        echo json_encode(array('type' => 'success', 'message' => "User has been deleted successfully."));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting user."));
        exit;
    }
} else if ($action == "view" && in_array('view', $Permission)) {
    $activity_array = array("userId" => $id, "module" => $module, "activity" => 'view');
    add_admin_activity($activity_array);
	
} else if (isset($_POST['country']) && (isset($_POST['action']) && $_POST['action'] == 'getstate' )) {
    $content = '';
    $main_content = new Templater(DIR_ADMIN_TMPL . $module . "/ajax_select_state-nct.tpl.php");
    $content.= $main_content->parse();
    $search = array('%STATE_OPTION%');
    $mainObjectCity = new Users($module, $id, NULL, $searchArray, $action);
    //State dropdown
    $getSelectBoxOption = $mainObjectCity->getSelectBoxOption();
    $fields_search = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");
    $qrySelState = $db->pdoQuery("SELECT * FROM tbl_state where CountryID=" . $_POST['country'] . " AND isActive='y' ORDER BY stateName")->results();

    foreach ($qrySelState as $fetchRes) {
        $fields_replace = array($fetchRes['StateID'], $selected, filtering($fetchRes['stateName']));
        $state_option.=str_replace($fields_search, $fields_replace, $getSelectBoxOption);
    }
    $replace = array($state_option);
    $content = str_replace($search, $replace, $content);
    echo $content;
    exit;
}
if (isset($_POST['state']) && (isset($_POST['action']) && $_POST['action'] == 'getcity' )) {
    $country_id = $_POST['country'];
    $state_id = $_POST['state'];

    $content = '';
    $main_content = new Templater(DIR_ADMIN_TMPL . $module . "/ajax_select_city-nct.tpl.php");
    $content.= $main_content->parse();
    $search = array('%CITY_OPTION%');
    $mainObjectCity = new Users($module, $id, NULL, $searchArray, $action);
    //State dropdown
    $getSelectBoxOption = $mainObjectCity->getSelectBoxOption();
    $fields_search = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");
    $qrySelState = $db->pdoQuery("SELECT * FROM tbl_city where CountryID=" . $country_id . " AND StateID=" . $state_id . " AND isActive='y' ORDER BY cityName")->results();
    //echo "<pre>";print_r($qrySelState);exit;
    foreach ($qrySelState as $fetchRes) {
        $fields_replace = array($fetchRes['CityId'], $selected, filtering($fetchRes['cityName']));
        $state_option.=str_replace($fields_search, $fields_replace, $getSelectBoxOption);
    }
    $replace = array($state_option);
    $content = str_replace($search, $replace, $content);
    echo $content;
    exit;
}

$mainObject = new Users($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
