<?php

$reqAuth = true;
require_once("../../../includes-nct/config-nct.php");
require_once("class.city-nct.php");
$module = "city-nct";
$table = "tbl_city";

$styles = array(
    array(
        "data-tables/DT_bootstrap.css",
        SITE_ADM_PLUGIN),
    array(
        "bootstrap-switch/css/bootstrap-switch.min.css",
        SITE_ADM_PLUGIN));

$scripts = array(
    "core/datatable.js",
    array(
        "data-tables/jquery.dataTables.js",
        SITE_ADM_PLUGIN),
    array(
        "data-tables/DT_bootstrap.js",
        SITE_ADM_PLUGIN),
    array(
        "bootstrap-switch/js/bootstrap-switch.min.js",
        SITE_ADM_PLUGIN));

chkPermission($module);
$Permission = chkModulePermission($module);

$metaTag = getMetaTags(array(
    "description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    "author" => SITE_NM));

$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Cities';
$winTitle = $headTitle . ' - ' . SITE_NM;
$breadcrumb = array(
    $headTitle);

if (isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {

    extract($_POST);
    $objPost->cityName = isset($cityName) ? $cityName : '';
    $objPost->stateId = isset($state) ? $state : '';
    $objPost->countryId = isset($country) ? $country : '';
    $objPost->TimeZone = isset($timeZone) ? $timeZone : '';
    $objPost->status = isset($status) && $status == 'y' ? 'y' : 'n';

    if ($objPost->cityName != "") {

        if ($type == 'edit' && $id > 0) {
            if (in_array('edit', $Permission)) {

                $exist = $db->pdoQuery("Select CityId from tbl_city where cityName = '" . $objPost->cityName . "' and CityId != " . $id . "")->result();

                if ($exist == 0) {

                    $db->update($table, array(
                        'cityName' => $objPost->cityName,
                        'stateId' => $objPost->stateId,
                        'countryId' => $objPost->countryId,
                        'TimeZone' => $objPost->TimeZone,
                        'isActive' => $objPost->status), array(
                        "CityId" => $id));

                    $activity_array = array(
                        "id" => $id,
                        "module" => $module,
                        "activity" => 'edit');
                    add_admin_activity($activity_array);

                    $msgType = $_SESSION["msgType"] = disMessage(array(
                        'type' => 'suc',
                        'var' => 'recEdited'));
                } else {
                    $_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var' => 'recExist'));
                }
            } else {
                $msgType = $_SESSION["msgType"] = disMessage(array(
                    'type' => 'err',
                    'var' => 'NoPermission'));
            }
        } else {
            if (in_array('add', $Permission)) {

                $doesExist = getTableValue($table,'CityId',array("cityName" => $objPost->cityName));
                if (!$doesExist) {

                    $valArray = array(
                        "cityName" => $objPost->cityName,
                        "stateId" => $objPost->stateId,
                        "countryId" => $objPost->countryId,
                        'TimeZone' => $objPost->TimeZone,
                        "isActive" => $objPost->status);
                    $db->insert("tbl_city", $valArray);

                    $activity_array = array(
                        "id" => $id,
                        "module" => $module,
                        "activity" => 'add');
                    add_admin_activity($activity_array);

                    $msgType = $_SESSION["msgType"] = disMessage(array(
                        'type' => 'suc',
                        'var' => 'recAdded'));
                } else {
                    $_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var' => 'recExist'));
                }
            } else {
                $msgType = $_SESSION["msgType"] = disMessage(array(
                    'type' => 'err',
                    'var' => 'NoPermission'));
            }
        }
        redirectPage(SITE_ADM_MOD . $module);
    } else {
        $msgType = array(
            'type' => 'err',
            'var' => 'fillAllvalues');
    }
}
$objCity = new City($id, NULL, $type);
$pageContent = $objCity->getPageContent();
require_once(DIR_ADMIN_TMPL . "parsing-nct.tpl.php");
