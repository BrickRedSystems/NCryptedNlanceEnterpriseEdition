<?php

$content = '';
require_once("../../../includes-nct/config-nct.php");

if ($adminUserId == 0) {
    die('Invalid request');
}
include("class.newsletters-nct.php");

$module = "newsletters-nct";
$table = "tbl_newsletters";

chkPermission($module);
$Permission = chkModulePermission($module);

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

if ($action == "getSubscribedUsers" ) {
    $mainObject = new newsletter();
    echo $mainObject->getSubscribedUsers(filtering($_REQUEST['user_type']));
    exit;
} elseif ($action == "updateStatus") {

    $setVal = array('status' => $value);
    $db->update($table, $setVal, array("id" => $id));

    echo json_encode(array('type' => 'success', 'Newsletter has been ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);
    add_admin_activity($activity_array);
    exit;
} else if ($action == "delete") {

    $aWhere = array("id" => $id);
    $affected_rows = $db->delete($table, $aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) {
        echo json_encode(array('type' => 'success', 'message' => "Newsletter has been deleted successfully."));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting Newsletter."));
        exit;
    }
}
$mainObject = new newsletter($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
