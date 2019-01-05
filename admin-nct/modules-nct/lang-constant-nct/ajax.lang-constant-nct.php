<?php
$content = '';
require_once "../../../includes-nct/config-nct.php";
if ($adminUserId == 0) {die('Invalid request');}
include "class.lang-constant-nct.php";

$module = 'lang-constant-nct';
$table  = 'tbl_lang_constants';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id     = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
$value  = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page   = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows   = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort   = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : null;
$order  = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : null;
$chr    = isset($_POST["sSearch"]) ? $_POST["sSearch"] : null;
$sEcho  = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
$langId = isset($_POST['langId']) ? $_POST['langId'] : 10;


extract($_GET);
$searchArray = array("page" => $page, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page, "chr" => $chr, 'sEcho' => $sEcho, 'langId' => $langId);
chkPermission($module);
$Permission = chkModulePermission($module);

$mainObject = new Constant($id, $searchArray, $action);

if ($action == "updateStatus" && in_array('status', $Permission)) {
    $setVal = array('status' => ($value == 'a' ? 'a' : 'd'));

    $mainObject->updateRecords($table, $setVal, array("id" => $id));

    echo json_encode(array('type' => 'success', 'Record ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);
    add_admin_activity($activity_array);
    exit;
} else if ($action == "delete" && in_array('delete', $Permission)) {

    $aWhere = array("id" => $id);

    $mainObject->deleteRecords($table, $aWhere);

    $aWhere1 = array("subId" => $id);

    $mainObject->deleteRecords($table, $aWhere1);

    echo json_encode(array('type' => 'success', 'message' => "Constant has been deleted successfully."));
    exit;

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
    add_admin_activity($activity_array);
} else if ($action == 'changeLanguage') {
    $action = "";
}

$mainObject = new Constant($id, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
