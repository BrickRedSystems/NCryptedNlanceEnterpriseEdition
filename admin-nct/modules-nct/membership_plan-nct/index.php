<?php
$reqAuth = true;
require_once "../../../includes-nct/config-nct.php";
require_once "class.membership_plan-nct.php";
$module = "membership_plan-nct";
$table  = "tbl_memberships";

$styles = array(array("data-tables/DT_bootstrap.css", SITE_ADM_PLUGIN),
    array("bootstrap-switch/css/bootstrap-switch.min.css", SITE_ADM_PLUGIN));

$scripts = array("core/datatable.js",
    array("data-tables/jquery.dataTables.js", SITE_ADM_PLUGIN),
    array("data-tables/DT_bootstrap.js", SITE_ADM_PLUGIN),
    array("bootstrap-switch/js/bootstrap-switch.min.js", SITE_ADM_PLUGIN));

chkPermission($module);
$Permission = chkModulePermission($module);

$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords"                                 => 'Admin Panel',
    'author'                                   => AUTHOR));
$breadcrumb = array("Membership Plan");
$page_name  = "Membership Plan";

$id       = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type     = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Membership Plan';
$winTitle  = $headTitle . ' - ' . SITE_NM;

if (isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    extract($_POST);

    $membershipArr  = isset($membership) ? $membership : array();
    $descriptionArr = isset($description) ? $description : array();

    $insArray = array(
        'price'    => isset($price) ? $price : '',
        'credits'  => isset($credits) ? $credits : 0,
        'isactive' => isset($isactive) ? $isactive : 'y',
    );

    if (!empty($membershipArr) && $insArray['price'] > 0 && $insArray['credits'] > 0) {

        if ($type == 'edit' && $id > 0) {
            if (in_array('edit', $Permission)) {

                $db->update($table, $insArray, array("id" => $id));
                //////////////
                $languages = $db->select("tbl_languages", array("id", "languageName"), array("status" => 'a'))->results();
                foreach ($languages as $key => $value) {
                    $db->update($table, array('membership_' . $value["id"] => $membershipArr[$value["id"]], 'description_' . $value["id"] => $description[$value['id']]), array("id" => $id));

                }
                //////////////

                $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Record has been updated successfully'));

            } else {
                $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'You are not authorised to perform this action.'));
            }
        } else {
            if (in_array('add', $Permission)) {

                $insArray['createddate'] = date("Y-m-d H:i:s");
                $id                      = $db->insert($table, $insArray)->getLastInsertId();
                //////////////
                $languages = $db->select("tbl_languages", array("id", "languageName"), array("status" => 'a'))->results();
                foreach ($languages as $key => $value) {
                    $db->update($table, array('membership_' . $value["id"] => $membershipArr[$value["id"]], 'description_' . $value["id"] => $description[$value['id']]), array("id" => $id));

                }
                //////////////
                $activity_array = array("id" => $id, "module" => $module, "activity" => 'add');
                add_admin_activity($activity_array);
                $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'New membership has been added'));

            } else {
                $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'You are not authorised to perform this action.'));
            }
        }
        redirectPage(SITE_ADM_MOD . $module);
    } else {
        $toastr_message = array('type' => 'err', 'var' => 'Please fill all required fields carefully.');
    }
}
$objContent  = new Membership_plan($module);
$pageContent = $objContent->getPageContent();
require_once DIR_ADMIN_TMPL . "parsing-nct.tpl.php";
//require_once(DIR_ADMIN_THEME."default.nct");
