<?php
$reqAuth = true;
require_once "../../../includes-nct/config-nct.php";
require "class.language-nct.php";

$module = "language-nct";
$table  = "tbl_languages";
error_reporting(-1);
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
    "author"                                   => SITE_NM));

$id       = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type     = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle  = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Languages';
$winTitle   = $headTitle . ' - ' . SITE_NM;
$breadcrumb = array($headTitle);
$objLanguage = new Language();
if (isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    global $fb;

    extract($_POST);
    $objPost->languageName = isset($languageName) ? $languageName : '';
    $objPost->status       = isset($status) ? $status : '';

    $objPost->isDefault = isset($isDefault) ? $isDefault : 'n';

    if ($objPost->languageName != "") {
        if ($type == 'edit' && $id > 0) {
            if (in_array('edit', $Permission)) {
                if($objPost->isDefault == 'y'){
                    $db->pdoQuery('update tbl_languages set isDefault = "n"');
                    $objPost->status = 'a';
                }

                $objLanguage->updateRecords('tbl_languages', array('languageName' => $objPost->languageName, 'status' => $objPost->status, 'isDefault' => $objPost->isDefault), array("id" => $id));

                $activity_array = array("id" => $id, "module" => $module, "activity" => 'edit');

                manageLanguageFields($id);


                add_admin_activity($activity_array);
                $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Record edited successfully.'));
            } else {
                $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => 'You are not authorized to perform this activity.'));
                $msgType = $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'You are not authorized to perform this activity.'));
            }
        } else {
            if (in_array('add', $Permission)) {
                if (getTotalRows($table, "languageName='" . $objPost->languageName . "'", 'id') == 0) {

                    $objPost->createdDate = date('Y-m-d H:i:s');
                    /*$slug = makeSlug($objPost->languageName, $table, $field = 'id', $whereCol = 'slug', $extra = 'url');*/
                    $valArray = array("languageName" => $objPost->languageName, "status" => $objPost->status, "created_date" => $objPost->createdDate, 'isDefault' => $objPost->isDefault,"langCode"=>$objPost->languageName,'urlConst'=>$objPost->languageName,'showOnHome'=>'n');

                    $ins_id = $objLanguage->insertRecords($table,$valArray);

                    $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Record added successfully.'));

                    //start:: for tbl_content
                    $alterTablepage_title=$db->prepare("ALTER TABLE `tbl_content`  ADD `pageTitle_".$ins_id."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `pageTitle`");
                    $alterTablepage_title->execute();

                    $updateTablepage_title=$db->prepare("UPDATE `tbl_content` SET  pageTitle_".$ins_id." = pageTitle");
                    $updateTablepage_title->execute();

                    $alterTablepage_desc=$db->prepare("ALTER TABLE `tbl_content`  ADD `pageDesc_".$ins_id."` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `pageDesc`");
                    $alterTablepage_desc->execute();

                    $updateTablepage_desc=$db->prepare("UPDATE `tbl_content` SET  pageDesc_".$ins_id." = pageDesc");
                    $updateTablepage_desc->execute();
                    //end:: for tbl_content

                    //start:: for tbl_email_templates
                    $alterTablepage_title=$db->prepare("ALTER TABLE `tbl_email_templates`  ADD `templates_".$ins_id."` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `templates`");
                    $alterTablepage_title->execute();

                    $updateTablepage_title=$db->prepare("UPDATE `tbl_email_templates` SET  templates_".$ins_id." = templates");
                    $updateTablepage_title->execute();

                    $alterTablepage_desc=$db->prepare("ALTER TABLE `tbl_email_templates`  ADD `subject_".$ins_id."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `subject`");
                    $alterTablepage_desc->execute();

                    $updateTablepage_desc=$db->prepare("UPDATE `tbl_email_templates` SET  subject_".$ins_id." = subject");
                    $updateTablepage_desc->execute();
                    //end:: for tbl_email_templates

                    //start:: for tbl_newsletters
                    $alterTablepage_title=$db->prepare("ALTER TABLE `tbl_newsletters`  ADD `newsletter_content_".$ins_id."` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `newsletter_content`");
                    $alterTablepage_title->execute();

                    $updateTablepage_title=$db->prepare("UPDATE `tbl_newsletters` SET  newsletter_content_".$ins_id." = newsletter_content");
                    $updateTablepage_title->execute();

                    $alterTablepage_desc=$db->prepare("ALTER TABLE `tbl_newsletters`  ADD `newsletter_subject_".$ins_id."` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `newsletter_subject`");
                    $alterTablepage_desc->execute();

                    $updateTablepage_desc=$db->prepare("UPDATE `tbl_newsletters` SET  newsletter_subject_".$ins_id." = newsletter_subject");
                    $updateTablepage_desc->execute();
                    //end:: for tbl_newsletters

                    manageLanguageFields($ins_id);

                    makeConstantFile();
                } else {
                    $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'Record already exists.'));
                }
            } else {
                $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => 'You are not authorized to perform this activity.'));
            }
        }
        header('Location:' . SITE_ADM_MOD . $module);
        exit;
    } else {
        $msgType = array('type' => 'err', 'var' => 'fillAllvalues');
    }
}
$objLanguage = new Language();
$pageContent = $objLanguage->getPageContent();
require_once DIR_ADMIN_TMPL . "parsing-nct.tpl.php";
