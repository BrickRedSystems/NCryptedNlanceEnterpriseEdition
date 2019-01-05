<?php

$reqAuth = true;
require_once "../../../includes-nct/config-nct.php";
require_once "class.content-nct.php";
$module = "content-nct";
$table  = "tbl_content";

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
$breadcrumb = array("Content");

$id       = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type     = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Content';
$winTitle  = $headTitle . ' - ' . SITE_NM;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response           = array();
    $response['status'] = false;

    extract($_POST);

    $pageTitleArr = isset($pageTitle) ? $pageTitle : array();
    $pageDesc     = isset($pageDesc) ? $pageDesc : array();

    $objPost->metaKeyword = isset($metaKeyword) ? $metaKeyword : '';
    $objPost->metaDesc    = isset($metaDesc) ? $metaDesc : '';

    $objPost->isActive = isset($isActive) ? $isActive : 'n';

    if (empty($pageTitleArr)) {
        $response['error'] = "Please enter page title.";
        echo json_encode($response);
        exit;
    }
    //dump_exit($pageTitleArr[1]);
    $pageTitle = $pageTitleArr[1];

    $objPost->pageSlug = makeSlug($pageTitle, $table, 'pageId', 'pageSlug', 'url', $id);
    //$objPost->pageSlug = Slug($objPost->pageTitle);

    if ($type == 'edit' && $id > 0) {
        if (in_array('edit', $Permission)) {
            $objPostArray = (array) $objPost;
            $db->update($table, $objPostArray, array("pageId" => $id));

            //////////////
            $languages = $db->select("tbl_languages", array("id", "languageName"), array("status" => 'a'))->results();
            foreach ($languages as $key => $value) {

                $db->update($table, array('pageTitle_' . $value["id"] => $pageTitleArr[$value["id"]], 'pageDesc_' . $value["id"] => $pageDesc[$value['id']]), array("pageId" => $id));

            }
            //////////////

            $activity_array = array("id" => $id, "module" => $module, "activity" => 'edit');
            add_admin_activity($activity_array);

            $response['status']  = true;
            $response['success'] = "Content page has been updated successfully.";
            echo json_encode($response);
            exit;
        } else {
            $response['error'] = "You don't have permission.";
            echo json_encode($response);
            exit;
        }
    } else {
        if (in_array('add', $Permission)) {
            $objPost->createdDate = date("Y-m-d H:i:s");

            $objPostArray = (array) $objPost;
            $id           = $db->insert($table, $objPostArray)->getLastInsertId();

            //////////////
            $languages = $db->select("tbl_languages", array("id", "languageName"), array("status" => 'a'))->results();
            foreach ($languages as $key => $value) {

                $db->update($table, array('pageTitle_' . $value["id"] => $pageTitleArr[$value["id"]], 'pageDesc_' . $value["id"] => $pageDesc[$value['id']]), array("pageId" => $id));

            }
            //////////////

            $activity_array = array("id" => $id, "module" => $module, "activity" => 'add');
            add_admin_activity($activity_array);

            $response['status']  = true;
            $response['success'] = "Content page has been added successfully.";
            echo json_encode($response);
            exit;
        } else {
            $response['error'] = "You don't have permission.";
            echo json_encode($response);
            exit;
        }
    }

}
$objContent  = new Content($module);
$pageContent = $objContent->getPageContent();
require_once DIR_ADMIN_TMPL . "parsing-nct.tpl.php";
