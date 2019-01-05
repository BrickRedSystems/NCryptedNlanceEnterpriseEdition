<?php

function manageLanguageFields($langId = 0)
{
    global $db, $adminUserId;
    $id = $langId;

    //start:: for tbl_skills
    $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_skills LIKE 'skillName_" . $id . "'")->results();
    if (empty($fetchRes)) {
        $alterTablepage_title = $db->prepare("ALTER TABLE `tbl_skills`  ADD `skillName_" . $id . "` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `skillName`");
        $alterTablepage_title->execute();

        $updateTablepage_title=$db->prepare("UPDATE `tbl_skills` SET  skillName_".$id." = skillName");
        $updateTablepage_title->execute();
    }
    //end:: for tbl_skills

    //start:: for tbl_top_skills
    $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_top_skills LIKE 'skillName_" . $id . "'")->results();
    if (empty($fetchRes)) {
        $alterTablepage_title = $db->prepare("ALTER TABLE `tbl_top_skills`  ADD `skillName_" . $id . "` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `skillName`");
        $alterTablepage_title->execute();

        $updateTablepage_title=$db->prepare("UPDATE `tbl_top_skills` SET  skillName_".$id." = skillName");
        $updateTablepage_title->execute();
    }
    //end:: for tbl_top_skills

    //start:: for tbl_categories
    $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_categories LIKE 'cateName_" . $id . "'")->results();
    if (empty($fetchRes)) {
        $alterTablepage_title = $db->prepare("ALTER TABLE `tbl_categories`  ADD `cateName_" . $id . "` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `cateName`");
        $alterTablepage_title->execute();

        $updateTablepage_title=$db->prepare("UPDATE `tbl_categories` SET  cateName_".$id." = cateName");
        $updateTablepage_title->execute();
    }
    $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_categories LIKE 'description_" . $id . "'")->results();
    if (empty($fetchRes)) {
        $alterTablepage_desc = $db->prepare("ALTER TABLE `tbl_categories`  ADD `description_" . $id . "` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `description`");
        $alterTablepage_desc->execute();

        $updateTablepage_desc=$db->prepare("UPDATE `tbl_categories` SET  description_".$id." = description");
        $updateTablepage_desc->execute();
    }
    //end:: for tbl_categories

    //start:: for tbl_membership
    $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_memberships LIKE 'membership_" . $id . "'")->results();
    if (empty($fetchRes)) {
        $alterTablepage_title = $db->prepare("ALTER TABLE `tbl_memberships`  ADD `membership_" . $id . "` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `membership`");
        $alterTablepage_title->execute();

        $updateTablepage_title=$db->prepare("UPDATE `tbl_memberships` SET  membership_".$id." = membership");
        $updateTablepage_title->execute();
    }
    $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_memberships LIKE 'description_" . $id . "'")->results();
    if (empty($fetchRes)) {
        $alterTablepage_desc = $db->prepare("ALTER TABLE `tbl_memberships`  ADD `description_" . $id . "` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `description`");
        $alterTablepage_desc->execute();

        $updateTablepage_desc=$db->prepare("UPDATE `tbl_memberships` SET  description_".$id." = description");
        $updateTablepage_desc->execute();
    }
    //end:: for tbl_membership

    //start:: for tbl_content
    $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_content LIKE 'pageTitle_" . $id . "'")->results();
    if (empty($fetchRes)) {
        $alterTablepage_title = $db->prepare("ALTER TABLE `tbl_content`  ADD `pageTitle_" . $id . "` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `pageTitle`");
        $alterTablepage_title->execute();

        $updateTablepage_title=$db->prepare("UPDATE `tbl_content` SET  pageTitle_".$id." = pageTitle");
        $updateTablepage_title->execute();
    }
    $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_content LIKE 'pageDesc_" . $id . "'")->results();
    if (empty($fetchRes)) {
        $alterTablepage_desc = $db->prepare("ALTER TABLE `tbl_content`  ADD `pageDesc_" . $id . "` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `pageDesc`");
        $alterTablepage_desc->execute();

        $updateTablepage_desc=$db->prepare("UPDATE `tbl_content` SET  pageDesc_".$id." = pageDesc");
        $updateTablepage_desc->execute();
    }
    //end:: for tbl_content

    //start:: for tbl_email_templates
    $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_email_templates LIKE 'templates_" . $id . "'")->results();
    if (empty($fetchRes)) {
        $alterTablepage_title = $db->prepare("ALTER TABLE `tbl_email_templates`  ADD `templates_" . $id . "` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `templates`");
        $alterTablepage_title->execute();

        $updateTablepage_title=$db->prepare("UPDATE `tbl_email_templates` SET  templates_".$id." = templates");
        $updateTablepage_title->execute();
    }
    $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_email_templates LIKE 'subject_" . $id . "'")->results();
    if (empty($fetchRes)) {
        $alterTablepage_desc = $db->prepare("ALTER TABLE `tbl_email_templates`  ADD `subject_" . $id . "` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `subject`");
        $alterTablepage_desc->execute();

        $updateTablepage_desc=$db->prepare("UPDATE `tbl_email_templates` SET  subject_".$id." = subject");
        $updateTablepage_desc->execute();
    }
    //end:: for tbl_email_templates

    //start:: for tbl_newsletters
    $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_newsletters LIKE 'newsletter_content_" . $id . "'")->results();
    if (empty($fetchRes)) {
        $alterTablepage_title = $db->prepare("ALTER TABLE `tbl_newsletters`  ADD `newsletter_content_" . $id . "` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `newsletter_content`");
        $alterTablepage_title->execute();

        $updateTablepage_title=$db->prepare("UPDATE `tbl_newsletters` SET  newsletter_content_".$id." = newsletter_content");
        $updateTablepage_title->execute();
    }
    $fetchRes = $db->pdoQuery("SHOW COLUMNS FROM tbl_newsletters LIKE 'newsletter_subject_" . $id . "'")->results();
    if (empty($fetchRes)) {
        $alterTablepage_desc = $db->prepare("ALTER TABLE `tbl_newsletters`  ADD `newsletter_subject_" . $id . "` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `newsletter_subject`");
        $alterTablepage_desc->execute();

        $updateTablepage_desc=$db->prepare("UPDATE `tbl_newsletters` SET  newsletter_subject_".$id." = newsletter_subject");
        $updateTablepage_desc->execute();
    }
    //end:: for tbl_newsletters
}


function getLoggedinName()
{
    global $db, $adminUserId;
    $qrysel    = $db->select("uName", "id=" . $adminUserId . "");
    $fetchUser = mysql_fetch_object($qrysel);
    return trim(addslashes(ucwords($fetchUser->uName)));
}

//check Admin Permission
function chkPermission($module)
{
    global $db, $adminUserId;
    //"permissions",
    $admSl = $db->select("tbl_admin", array("adminType"), array("id =" => (int) $adminUserId))->result();
    if (!empty($admSl)) {
        $adm = $admSl;
        //echo $adm['adminType']; exit;
        if ($adm['adminType'] == 'g') {
            $moduleId     = $db->select("tbl_adminrole", array("id"), array("pagenm =" => (string) $module))->result();
            $chkPermssion = $db->select("tbl_admin_permission", array("permission"), array("admin_id" => (int) $adminUserId, "page_id" => $moduleId['id']))->result();
            if (empty($chkPermssion['permission'])) {

                    $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'You are not authorised to perform this action.'));
                    redirectPage(SITE_ADM_MOD . 'home-nct/');

            }else if($module == 'sitesetting-nct' || $module == 'users-nct'){
                    $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'You are not authorised to perform this action.'));
                    redirectPage(SITE_ADM_MOD . 'home-nct/');
                }
        }
    }
}

function add_admin_activity($activity_array = array())
{
    global $db, $adminUserId;
    $admSl = $db->select("tbl_admin", array("adminType"), array("id =" => (int) $adminUserId))->result();
    if ($admSl['adminType'] == 'g') {
        $module_name = $activity_array['module'];
        $activity    = $activity_array['activity'];

        $activity_array['id']           = (isset($activity_array['id'])) ? $activity_array['id'] : 0;
        $activity_array['module']       = (isset($activity_array['module'])) ? getTableValue('tbl_adminrole', 'id', array("pagenm" => $activity_array['module'])) : 0;
        $activity_array['activity']     = (isset($activity_array['activity'])) ? getTableValue('tbl_subadmin_action', 'id', array("constant" => $activity_array['activity'])) : 0;
        $activity_array['action']       = (isset($activity_array['action'])) ? $activity_array['action'] : '';
        $activity_array['created_date'] = date('Y-m-d H:i:s');
        $activity_array['updated_date'] = date('Y-m-d H:i:s');

        $val_array = array("activity_type" => $activity_array['activity'], "page_id" => $activity_array['module'], "admin_id" => $adminUserId, "entity_id" => $activity_array['id'], "entity_action" => $activity_array['action'], "created_date" => $activity_array['created_date'], "updated_date" => $activity_array['updated_date']);
        $db->insert('tbl_admin_activity', $val_array);
        /* START :: send mail on activity*/
        $arrayCont                = array();
        $arrayCont['greetings']   = "Admin";
        $arrayCont['subadmin']    = getTableValue("tbl_admin", "uName", array("id" => $adminUserId));
        $arrayCont['module_name'] = $module_name;
        $arrayCont['ipaddress']   = get_ip_address();
        $arrayCont['url']         = SITE_ADM_MOD . $module_name;

        $table_name          = getTableValue("tbl_adminrole", "table_name", array("id" => $activity_array['module']));
        $table_field         = getTableValue("tbl_adminrole", "table_field", array("id" => $activity_array['module']));
        $table_primary_field = getTableValue("tbl_adminrole", "table_primary_field", array("id" => $activity_array['module']));


        $entity_value = getTableValue($table_name, $table_field, array($table_primary_field => $activity_array['id']));
        $change="";
        if ("add" == $activity) {
            $change = '<p>Added a new record : ' .$table_field .' having value '. $entity_value . ' </p>';
        } else if ("edit" == $activity) {
            $change = '<p>Edited a record : ' .$table_field .' having value '. $entity_value . ' </p>';
        } else if ("delete" == $activity) {
            $change = '<p>Deleted a record : ' .$table_field .' having value '. $entity_value . ' </p>';
        } else if ("status" == $activity) {
            $change = '<p>Changed the status of : ' .$table_field .' having value '. $entity_value . ' </p>';
        }

        $arrayCont['original_content'] = SITE_ADM_MOD . $module_name;
        $arrayCont['change']           = $change;

        $array = generateEmailTemplate('subadmin_changed_content', $arrayCont);
        sendEmailAddress(ADMIN_EMAIL, $array['subject'], $array['message']);
        /* END :: send mail on activity*/

    }
}

/*
 * adminType : s- superadmin, g- general
 */
function chkModulePermission($module)
{
    global $db, $adminUserId;
    $permissions = array();
    $admSl       = $db->select("tbl_admin", array("adminType"), array("id =" => (int) $adminUserId))->result();

    if (!empty($admSl)) {
        $adm = $admSl;
        //echo $adm['adminType']; exit;
        if ($adm['adminType'] == 'g') {
            $moduleId     = $db->select("tbl_adminrole", array("id"), array("pagenm =" => (string) $module))->result();
            $chkPermssion = $db->select("tbl_admin_permission", array("permission"), array("admin_id" => (int) $adminUserId, "page_id" => $moduleId['id'], "and permission !=" => ""))->result();
            if (!empty($chkPermssion['permission'])) {
                $qryRes = $db->pdoQuery("select id,constant from tbl_subadmin_action where id in (" . $chkPermssion['permission'] . ")")->results();
                foreach ($qryRes as $fetchRes) {
                    $permissions[] = $fetchRes["constant"];
                }
            }
        } else {
            $qryRes = $db->select("tbl_subadmin_action", array("id,constant"), array())->results();
            foreach ($qryRes as $fetchRes) {
                $permissions[] = $fetchRes["constant"];
            }
        }
    }

    return $permissions;
}

// Get Section wise Role Array
function getSectionRoleArray($flag = false)
{
    global $db, $adminUserId;
    $arr[]      = array();
    $type       = '';
    $res1       = $db->select('tbl_admin', 'id,adminType,permissions', 'id=' . $adminUserId, null, null);
    $res1Fetch  = mysql_fetch_object($res1);
    $permission = $res1Fetch->permissions != '' ? $res1Fetch->permissions : 0;

    $res = $db->select('tbl_adminsection', 'id,type,section_name', null, null, '`order` ASC');
    if (mysql_num_rows($res) > 0) {
        $i = 0;
        while ($row = mysql_fetch_array($res)) {
            $per_wh_con = '';
            if ($res1Fetch->adminType == 'g') {
                $per_wh_con = ($permission != '0') ? (' AND id IN(' . str_replace('|', ',', $permission . ')')) : '';
            }

            $status_wh = ($res1Fetch->adminType == 's' && $flag == false) ? " status IN ('a','s')" : "status='a'";
            $qry_role  = "sectionid='" . $row['id'] . "' AND " . $status_wh . $per_wh_con;
            $res_role  = $db->select('tbl_adminrole', 'id,title,pagenm,image', $qry_role, null, '`seq` ASC', 0);
            if ($tot = mysql_num_rows($res_role) > 0) {
                $temp = $j = 0;
                while ($row_role = mysql_fetch_array($res_role)) {
                    $arr[$i]['id']     = $row_role['id'];
                    $arr[$i]['text']   = $row_role['title'];
                    $arr[$i]['pagenm'] = $row_role['pagenm'];
                    $arr[$i]['image']  = $row_role['image'];
                    if ($j == 0) {
                        $arr[$i]['optlbl'] = $row['section_name'];
                        $temp              = $row['id'];
                        $j++;
                    } else if ($j == ($tot - 1)) {
                        $j = 0;
                    }
                    $i++;
                }
            }
        }
    }
    return $arr;
}

function mysql_get_prim_key($table)
{
    global $db;
    $sql = "SHOW INDEX FROM $table WHERE Key_name = 'PRIMARY'";
    $gp  = mysql_query($sql);
    $cgp = mysql_num_rows($gp);
    $cgp = $db->pdoQuery($sql)->result();
    if (count($cgp) > 0) {
        $Column_name = $cgp['Column_name'];
//    extract($agp);
        return ($Column_name);
    } else {
        //return(false);
        return '';
    }
}
function searchInMultidimensionalArray($array, $key, $value)
{

    $response           = array();
    $response['status'] = false;

    foreach ($array as $main_key => $val) {
        if ($val[$key] == $value) {
            $response['status'] = true;
            $response['key']    = $main_key;

            return $response;
        }
    }

    return $response;
}
