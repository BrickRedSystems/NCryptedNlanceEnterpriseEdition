<?php

$content = '';
require_once("../../../includes-nct/config-nct.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include("class.notifications-nct.php");

$module = 'notifications-nct';

chkPermission($module);
$Permission = chkModulePermission($module);

$table = 'tbl_admin_notifications';

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

if (isset($_REQUEST["operational_status"]) && $_REQUEST["operational_status"] != '') {
    $operational_status = $_REQUEST["operational_status"];

    $where_cond = '';

    $where_cond = " operational_status = '" . $operational_status . "' ";
    if ($id > 0) {
        $where_cond.= " AND id != '" . (int) $id . "' ";
    }
    $sqlCheck = $db->pdoQuery("SELECT * FROM " . $table . " WHERE " . $where_cond)->results();
    $count = count($sqlCheck);
    echo ($count) > 0 ? 'false' : 'true';
    exit;
} else if ($action == "updateStatus") {
    $setVal = array('status' => ($value == 'y' ? 'a' : 'd'));
    $db->update($table, $setVal, array("id" => $id));

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);
    add_admin_activity($activity_array);
    echo json_encode(array('type' => 'success', 'Operational Status has been ' . ($value == 'y' ? 'activated ' : 'deactivated ') . 'successfully'));
    exit;
} else if ($action == "delete") {
    $check_if_used = $db->select("tbl_ewaste_items", "*", array("os_id" => $id))->result();
    if ($check_if_used) {
        echo json_encode(array('type' => 'error', 'message' => "This Operational Status has been selected in one of the wastes. So this can't be deleted."));
        exit;
    } else {
        $aWhere = array("id" => $id);
        $db->delete($table, $aWhere);

        $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);
        add_admin_activity($activity_array);
        echo json_encode(array('type' => 'success', 'message' => 'Operational Status has been deleted successfully'));
        exit;
    }
} else if (isset($_REQUEST['action']) && 'getRests' == $_REQUEST['action']) {

    $objNotifications = new Notifications($module, $id, NULL, $searchArray, $action);
    $limit = filtering($_REQUEST['limit'], 'input', 'int');
    $offset = filtering($_REQUEST['offset'], 'input', 'int');
    $listing_type = 'ajax';

    $final_result = array();
    $final_result['list'] = $objNotifications->getNotifications($limit, $offset, $listing_type);

    echo json_encode($final_result);
    exit;
} else if ($action == "mark_read") {
    $response = array();
    
    $adminUserId = $_SESSION['adminUserId'];
    $affectedRows = $db->update("tbl_admin_notifications", array(
        "is_notified" => "y",
        "is_read" => "y"), array("admin_id" => $adminUserId))->affectedRows();
    
    if($affectedRows > 0) {
        $response['operation_status'] = 'success';
    } else {
        $response['operation_status'] = 'fail';
    }
    echo json_encode($response);
    exit;
    
} else if (isset($_REQUEST['action']) && 'get_notifications' == $_REQUEST['action']) {
    $objNotifications = new Notifications();
    $total_notifications_count = $objNotifications->getNotificationsCount();
    $new_notifications_array = $objNotifications->getNewNotifications();
    
    if (!empty($new_notifications_array)) {
        $message['operation_status'] = "success";
        $message['notifications'] = filtering($new_notifications_array['notification_html'], 'output', 'text');
        $message['notifications_count'] = filtering($total_notifications_count, 'output', 'int');
        echo json_encode($message);
    }
}

$mainObject = new Notifications($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
