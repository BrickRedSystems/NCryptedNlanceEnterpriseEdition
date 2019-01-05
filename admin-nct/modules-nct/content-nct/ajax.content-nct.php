<?php

$content = '';
require_once("../../../includes-nct/config-nct.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include("class.content-nct.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'content-nct';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_content';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;

extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho);

if (isset($_REQUEST['ajaxvalidate']) && $_REQUEST['ajaxvalidate']) {
    $pageTitle = filtering($_REQUEST['pageTitle'], 'input');
    $pageId = filtering($_REQUEST['id'], 'input', 'int');
    if ($pageTitle != '' && $pageId != '') {
        if (getTotalRows("tbl_content", "pageTitle='" . $pageTitle . "' AND pageId != '" . $pageId . "' ", 'pageId') >= 1) {
            echo 'false';
            exit;
        } else {
            echo 'true';
            exit;
        }
    } else {
        echo 'false';
        exit;
    }
} else if ($action == "updateStatus" && in_array('status', $Permission)) {
    $setVal = array('isActive' => ($value == 'a' ? 'y' : 'n'));

    $db->update($table, $setVal, array("pageId" => $id));

    echo json_encode(array('type' => 'success', 'Content page has ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);

    add_admin_activity($activity_array);

    exit;
} else if ($action == "delete" && in_array('delete', $Permission)) {
    $aWhere = array("pageId" => $id);
    $affected_rows = $db->delete($table, $aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);        
        echo json_encode(array('type' => 'success', 'message' => "Content page has been deleted successfully."));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting content page."));
        exit;
    }
} else if ($action == "view" && in_array('view', $Permission)) {
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
    add_admin_activity($activity_array);
}

$mainObject = new Content($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
