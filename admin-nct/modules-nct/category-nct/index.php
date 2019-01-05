<?php
$reqAuth = true;
$module  = "category-nct";
require_once "../../../includes-nct/config-nct.php";
require_once "class.category-nct.php";

$module = "category-nct";
$table  = "tbl_categories";

chkPermission($module);
$Permission = chkModulePermission($module);
$styles     = array(array("data-tables/DT_bootstrap.css", SITE_ADM_PLUGIN),
    array("bootstrap-switch/css/bootstrap-switch.min.css", SITE_ADM_PLUGIN));

$scripts = array("core/datatable.js",
    array("data-tables/jquery.dataTables.js", SITE_ADM_PLUGIN),
    array("data-tables/DT_bootstrap.js", SITE_ADM_PLUGIN),
    array("bootstrap-switch/js/bootstrap-switch.min.js", SITE_ADM_PLUGIN));

$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords"                                 => 'Admin Panel',
    "author"                                   => SITE_NM));
$breadcrumb = array("Manage categories");

$id        = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType  = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type      = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;
$ctypeTxt  = isset($_REQUEST["ctype"]) ? trim($_REQUEST["ctype"]) : "f";
$ctype     = $ctypeTxt == 'pages' ? 't' : ($ctypeTxt == 'messages' ? 'm' : 'f');
$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage ') . ' Categories';
$winTitle  = $headTitle . ' - ' . SITE_NM;
if (isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $insArr = array();
    extract($_POST);

    $cateNameArr    = isset($cateName) ? $cateName : array();
    $descriptionArr = isset($description) ? $description : array();

    if ($type == 'edit' && $id > 0) {
        if (in_array('edit', $Permission)) {

            //get no of projects in that category
            $no_of_projects = $db->count('tbl_projects', array('categoryId' => $id, 'isActive' => 'y'));
            if ($no_of_projects > 0 && $status == 'n') {
                $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'This category contains active projects and hence you can not deactivate it.'));

            } else {
                //get eng lang id & generate slug from that
                $engLangId = getTableValue('tbl_languages', 'id', array('langCode' => 'en'));

                $insArr['isActive'] = $status;
                $insArr['slug']     = makeSlug($cateNameArr[$engLangId], $table, $field = 'id', $whereCol = 'slug', $extra = 'url', $id);
            $insArr['cateName'] =  reset($cateNameArr);
                $db->update('tbl_categories', $insArr, array('id' => $id));
                //////////////
                $languages = $db->select("tbl_languages", array("id", "languageName"), array("status" => 'a'))->results();
                foreach ($languages as $key => $value) {
                    $db->update($table, array('cateName_' . $value["id"] => $cateNameArr[$value["id"]], 'description_' . $value["id"] => $description[$value['id']]), array("id" => $id));

                }
                //////////////

                $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Record has been updated successfully.'));
            }

        } else {
            $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'You are not authorised to perform this action.'));
        }
    } else {
        if (in_array('add', $Permission)) {

            //get eng lang id & generate slug from that
            $engLangId             = getTableValue('tbl_languages', 'id', array('langCode' => 'en'));
            $insArr['slug']        = makeSlug($cateNameArr[$engLangId], $table, $field = 'id', $whereCol = 'slug', $extra = 'url', $id);
            $insArr['createdDate'] = date('Y-m-d H:i:s');
            $insArr['cateName'] =  reset($cateNameArr);
            $insertedId            = $db->insert('tbl_categories', $insArr)->getLastInsertId();

            //////////////
            $languages = $db->select("tbl_languages", array("id", "languageName"), array("status" => 'a'))->results();
            foreach ($languages as $key => $value) {
                $db->update($table, array('cateName_' . $value["id"] => $cateNameArr[$value["id"]], 'description_' . $value["id"] => $description[$value['id']]), array("id" => $insertedId));

            }
            //////////////
            $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Record has been added successfully.'));

        } else {
            $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'You are not authorised to perform this action.'));
        }
    }
    redirectPage($_SERVER['REQUEST_URI']);
}
$CatObj = new Category($module);

$pageContent = $CatObj->getPageContent();
require_once DIR_ADMIN_TMPL . "parsing-nct.tpl.php";
