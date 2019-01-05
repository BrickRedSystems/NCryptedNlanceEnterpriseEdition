<?php
$content = '';
require_once "../../../includes-nct/config-nct.php";
if ($adminUserId == 0) {die('Invalid request');}
include "class.language-nct.php";

$module = 'language-nct';

chkPermission($module);
$Permission = chkModulePermission($module);

$table  = 'tbl_languages';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id     = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
$value  = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page   = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows   = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort   = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : null;
$order  = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : null;
$chr    = isset($_POST["sSearch"]) ? $_POST["sSearch"] : null;
$sEcho  = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;

extract($_GET);
$searchArray = array("page" => $page, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page, "chr" => $chr, 'sEcho' => $sEcho);

$mainObject = new Language($id, $searchArray, $action);
if ($action == "updateStatus" && in_array('status', $Permission)) {
    $setVal = array('status' => ($value == 'a' ? 'a' : 'd'));

    $mainObject->updateRecords($table, $setVal, array("id" => $id));
    if ($value == 'a') {
        manageLanguageFields($id);
    }

    echo json_encode(array('type' => 'success', 'Language ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));
    exit;
} else if ($action == "delete" && in_array('delete', $Permission)) {

    $isDefault = $mainObject->checkIfLangIsDefault($table, array("id" => $id));

    if ($isDefault != 'y') {
        //start:: for tbl_categories
        $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_categories")->results();
        foreach ($fetchRes as $k => $v) {
            if (endsWith($v["Field"],"_" . $id)) {
                $alterTable3 = $db->prepare("ALTER TABLE  tbl_categories DROP COLUMN ".$v["Field"]);
                $alterTable3->execute();
            }            
        }
        //end:: for tbl_categories
        
        //start:: for tbl_memberships
        $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_memberships")->results();
        foreach ($fetchRes as $k => $v) {
            if (endsWith($v["Field"],"_" . $id)) {
                $alterTable3 = $db->prepare("ALTER TABLE  tbl_memberships DROP COLUMN ".$v["Field"]);
                $alterTable3->execute();
            }            
        }
        //end:: for tbl_memberships

        //start:: for tbl_content
        $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_content")->results();
        foreach ($fetchRes as $k => $v) {
            if (endsWith($v["Field"],"_" . $id)) {
                $alterTable3 = $db->prepare("ALTER TABLE  tbl_content DROP COLUMN ".$v["Field"]);
                $alterTable3->execute();
            }            
        }
        //end:: for tbl_content

        //start:: for tbl_email_templates
        $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_email_templates")->results();        
        foreach ($fetchRes as $k => $v) {
            if (endsWith($v["Field"],"_" . $id)) {
                $alterTable3 = $db->prepare("ALTER TABLE  tbl_email_templates DROP COLUMN ".$v["Field"]);
                $alterTable3->execute();
            }            
        }
        //end:: for tbl_email_templates

        //start:: for tbl_newsletters
        $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_newsletters")->results();        
        foreach ($fetchRes as $k => $v) {
            if (endsWith($v["Field"],"_" . $id)) {
                $alterTable3 = $db->prepare("ALTER TABLE  tbl_newsletters DROP COLUMN ".$v["Field"]);
                $alterTable3->execute();
            }            
        }
        //end:: for tbl_newsletters

        $aWhere = array("id" => $id);
        $mainObject->deleteRecords($table, $aWhere);

        echo json_encode(array('type' => 'success', 'message' => "Language has been deleted successfully."));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "You can not delete the language which has been set as default."));
        exit;
    }
}
$mainObject = new Language($id, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
