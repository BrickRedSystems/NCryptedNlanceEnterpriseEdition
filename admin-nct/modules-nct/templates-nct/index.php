<?php

$reqAuth = true;
require_once ("../../../includes-nct/config-nct.php");
include ("class.templates-nct.php");
$module = "templates-nct";
$table = "tbl_email_templates";

$styles = array(
    array(
        "data-tables/DT_bootstrap.css",
        SITE_ADM_PLUGIN
    ),
    array(
        "bootstrap-switch/css/bootstrap-switch.min.css",
        SITE_ADM_PLUGIN
    )
);

$scripts = array(
    "core/datatable.js",
    array(
        "data-tables/jquery.dataTables.js",
        SITE_ADM_PLUGIN
    ),
    array(
        "data-tables/DT_bootstrap.js",
        SITE_ADM_PLUGIN
    ),
    array(
        "bootstrap-switch/js/bootstrap-switch.min.js",
        SITE_ADM_PLUGIN
    )
);

chkPermission($module);
$Permission = chkModulePermission($module);

$metaTag = getMetaTags(array(
    "description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    'author' => AUTHOR
));

$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Email Templates';
$winTitle = $headTitle . ' - ' . SITE_NM;
$breadcrumb = array($headTitle);

if (isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    
    extract($_POST);
    $subject = isset($subject) ? $subject : array();
    $templates = isset($templates) ? $templates: array();
    $objPost -> description = isset($description) ? filtering($description, 'input') : '';
    $objPost -> types = isset($types) ? filtering($types, 'input') : '';
    
    $objPost -> constant = isset($constant) ? filtering($constant, 'input') : '';

    if ($type == 'edit' && $id > 0) {
        if ($type == 'edit' && $id > 0) {
            $db -> update($table, array(                
                "description" => $objPost -> description,                
            ), array("id" => $id));


            //////////////
            $languages = $db->select("tbl_languages", array("id", "languageName"), array("status" => 'a'))->results();
            foreach ($languages as $key => $value) {
                $db->update($table, array('subject_'.$value["id"]=>trim($subject[$value["id"]]),'templates_'.$value["id"]=>trim($templates[$value['id']])), array("id" => $id));
            }
            //////////////

            $_SESSION["toastr_message"] = disMessage(array(
                'type' => 'suc',
                'var' => 'Record has been updated successfully.'
            ));
        }
        else {
            $toastr_message = $_SESSION["toastr_message"] = disMessage(array(
                'type' => 'err',
                'var' => 'You are not authorised to perform this action.'
            ));
        }
    }
    else {
        if (in_array('add', $Permission)) {
            $objPost -> updateDate = date('Y-m-d H:i:s');

            $valArray = array(
                
                "description" => $objPost -> description,
                
                "updateDate" => $objPost -> updateDate,
                "types" => ($objPost -> types != null)?$objPost -> types:$objPost -> constant,
                "constant" => $objPost -> constant
            );

            $id = $db -> insert("tbl_email_templates", $valArray)->getLastInsertId();;

            //////////////
            $languages = $db->select("tbl_languages", array("id", "languageName"), array("status" => 'a'))->results();
            foreach ($languages as $key => $value) {
                $db->update($table, array('subject_'.$value["id"]=>trim($subject[$value["id"]]),'templates_'.$value["id"]=>trim($templates[$value['id']])), array("id" => $id));
            }
            //////////////

            $_SESSION["toastr_message"] = disMessage(array(
                'type' => 'suc',
                'var' => 'Record has been added successfully.'
            ));
        }
        else {
            $toastr_message = $_SESSION["toastr_message"] = disMessage(array(
                'type' => 'err',
                'var' => 'You are not authorised to perform this action.'
            ));
        }
    }
    redirectPage(SITE_ADM_MOD . $module);
}

$objTemplate = new Templates($module);
$pageContent = $objTemplate -> getPageContent();
require_once (DIR_ADMIN_TMPL . "parsing-nct.tpl.php");
