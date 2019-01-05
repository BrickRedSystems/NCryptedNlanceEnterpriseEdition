<?php

$reqAuth = true;
require_once("../../../includes-nct/config-nct.php");
include("class.skills-nct.php");
$module = "skills-nct";
$table = "tbl_skills";

$styles = array(array("data-tables/DT_bootstrap.css", SITE_ADM_PLUGIN),
    array("bootstrap-switch/css/bootstrap-switch.min.css", SITE_ADM_PLUGIN));

$scripts = array("core/datatable.js",
    array("data-tables/jquery.dataTables.js", SITE_ADM_PLUGIN),
    array("data-tables/DT_bootstrap.js", SITE_ADM_PLUGIN),
    array("bootstrap-switch/js/bootstrap-switch.min.js", SITE_ADM_PLUGIN));

chkPermission($module);
$Permission = chkModulePermission($module);
$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    "author" => SITE_NM));

$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Skills';
$winTitle = $headTitle . ' - ' . SITE_NM;
$breadcrumb = array($headTitle);

if (isset($_POST["skill_description"]) && $_SERVER["REQUEST_METHOD"] == "POST") {

    $response = array();
    $response['status'] = false;

    extract($_POST);
    $skillNameArr  = isset($skillName) ? $skillName : array();
    
    $objPost->skill_description = filtering($_POST['skill_description'], 'input');
   
    $objPost->status = isset($status) && $status == 'a' ? 'a' : 'd';

    if (!empty($skillNameArr) && $objPost->skill_description != "") {
        
        
        if ($type == 'edit' && $id > 0) {

            if (in_array('edit', $Permission)) {
            	

                $objPostArray = (array) $objPost;
                $db->update($table, $objPostArray, array("id" => $id));
                //////////////
                $languages = $db->select("tbl_languages", array("id", "languageName"), array("status" => 'a'))->results();
                foreach ($languages as $key => $value) {
                    $db->update($table, array('skillName_' . $value["id"] => $skillNameArr[$value["id"]]), array("id" => $id));

                }
                //////////////

                $activity_array = array("id" => $id, "module" => $module, "activity" => 'edit');
                add_admin_activity($activity_array);

                $response['status'] = true;
                $response['success'] = "Skill has been updated successfully.";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission.";
                echo json_encode($response);
                exit;
            }
        } else {
            if (in_array('add', $Permission)) {


                $objPost->added_on = date("Y-m-d H:i:s");

                $objPostArray = (array) $objPost;
                $id = $db->insert($table, $objPostArray)->getLastInsertId();
                //////////////
                $languages = $db->select("tbl_languages", array("id", "languageName"), array("status" => 'a'))->results();
                foreach ($languages as $key => $value) {
                    $db->update($table, array('skillName_' . $value["id"] => $skillNameArr[$value["id"]]), array("id" => $id));

                }
                //////////////
                $activity_array = array("id" => $id, "module" => $module, "activity" => 'add');
                add_admin_activity($activity_array);

                $response['status'] = true;
                $response['success'] = "Skill has been added successfully.";
                echo json_encode($response);
                exit;
            } else {
                $response['error'] = "You don't have permission.";
                echo json_encode($response);
                exit;
            }
        }
    } else {
        $response['error'] = "Please enter all the details.";
        echo json_encode($response);
        exit;
    }
}

$objSkills = new Skills($module, $id, NULL);
$pageContent = $objSkills->getPageContent();
require_once(DIR_ADMIN_TMPL . "parsing-nct.tpl.php");
