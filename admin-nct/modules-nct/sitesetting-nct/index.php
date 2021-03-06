<?php

$reqAuth = true;
require_once "../../../includes-nct/config-nct.php";
require_once "class.sitesetting-nct.php";

$objPost = new stdClass();

$winTitle  = 'Site Settings - ' . SITE_NM;
$headTitle = 'Site Settings';

$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords"                                 => 'Admin Panel',
    "author"                                   => SITE_NM));

$module     = 'sitesetting-nct';
$breadcrumb = array("Site Settings");


if (isset($_FILES) && !empty($_FILES)) {
    //echo "<pre>";print_r($_FILES);exit;
    foreach ($_FILES as $a => $b) {
        
        $selField = array('type');
        $selWhere = array('id' => $a);

        $type1Sql = $db->select("tbl_site_settings", $selField, $selWhere)->results();

        foreach ($type1Sql as $c => $b) {
            $type1    = $b["type"];
            $constant = $b["constant"];
        }

        if ($type1 == "filebox") {
            //dump_exit($_FILES);
            $type     = $_FILES[$a]["type"];
            $fileName = $_FILES[$a]["name"];
            $TmpName  = $_FILES[$a]["tmp_name"];
            if ($type == "image/jpeg" || $type == "image/png" || $type == "image/gif" || $type == "image/x-png" || $type == "image/jpg" || $type == "image/x-png" || $type == "image/x-jpeg" || $type == "image/pjpeg" || $type == "image/x-icon" || $type == "image/vnd.microsoft.icon") {
                $fileName  = uploadFile($_FILES[$a], 'tbl_site_settings', 'value', 'id', $a, DIR_IMG, SITE_IMG);
                $dataArr   = array("value" => $fileName['file_name']);
                $dataWhere = array("id" => $a);
                $db->update('tbl_site_settings', $dataArr, $dataWhere);
            }
        }
    }
}
if (isset($_POST["submitSetForm"])) {
    extract($_POST);
    foreach ($_POST as $k => $v) {
        if ((int) $k) {
            $v      = closetags($v);
            $sData  = array("value" => filtering($v, 'input'));
            $sWhere = array("id" => $k);
            $affectedRows = $db->update("tbl_site_settings", $sData, $sWhere)->affectedRows();
            if ($k == 2) {
                $data  = array("uEmail" => $v);
                $where = array("id" => "1", "adminType" => "s");
                $db->update("tbl_admin", $data, $where);
            }
            if($affectedRows){
                $activity_array = array(
                    "id" => $k,
                    "module" => 'sitesetting-nct',
                    "activity" => 'edit'
                );
                add_admin_activity($activity_array);    
            }
            

        }
    }

    $_SESSION["toastr_message"] = disMessage(array(
        'type' => 'suc',
        'var'  => 'Site settings has been updated successfully.',
    ));
    redirectPage(SITE_ADM_MOD . $module);
}

chkPermission($module);

$objSiteSetting = new SiteSetting();

$pageContent = $objSiteSetting->getPageContent();
require_once DIR_ADMIN_TMPL . "parsing-nct.tpl.php";
