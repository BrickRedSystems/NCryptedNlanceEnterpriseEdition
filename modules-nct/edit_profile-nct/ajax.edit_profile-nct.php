<?php
$reqAuth = true;
$module  = 'edit_profile-nct';
require_once "../../includes-nct/config-nct.php";
include "class.edit_profile-nct.php";

$action = isset($_POST['action']) ? $_POST['action'] : null;

extract($_POST);
$obj = new EditProfile($module, 0, issetor($token));
if ($action == "method") {
    $html = null;

    $html               = $obj->{$method}(0, $val);
    $response['status'] = true;

    $response['html'] = $html;
    $response['msg']  = "undefined";
    echo json_encode($response);
    exit;
} elseif ($action == "submitEditProfile") {
    extract($_POST);

    if (!isset($dataOnly) || !$dataOnly) {
        if (!checkFormToken($token)) {
            $response['status']   = 0;
            $response['msg']      = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
            $response['newToken'] = setFormToken();
            echo json_encode($response);
            exit;
        }
    }

    if ($sessUserId != 0) {
        $isExist = $db->select('tbl_users', array('userId'), array('userId' => $sessUserId), ' LIMIT 1');
        if ($isExist->affectedRows() <= 0) {

            $msgType = $_SESSION["msgType"] = disMessage(array(
                'type' => 'err',
                'var'  => toastr_something_went_wrong,
            ));
            redirectPage(SITE_LOGIN);
        } else {
            $objPost->firstName     = filtering($_POST['firstName'], 'input');
            $objPost->lastName      = filtering($_POST['lastName'], 'input');
            $objPost->contactCode   = filtering($_POST['contactCode'], 'input');
            $objPost->contactNo     = filtering($_POST['contactNo'], 'input');
            $objPost->aboutMe       = filtering($_POST['aboutMe'], 'input');
            $objPost->catId         = filtering($_POST['catId'], 'input');
            $objPost->subcatId      = filtering($_POST['subcatId'], 'input');
            $objPost->paypalEmail   = filtering($_POST['paypalEmail'], 'input');
            $objPost->ipAddress     = get_ip_address();
            $objPost->lastLoginTime = date('Y-m-d H:i:s');
            $objPost->countryCode   = filtering($_POST['countryCode'], 'input');
            $objPost->state         = filtering($_POST['state'], 'input');
            $objPost->city          = filtering($_POST['city'], 'input');
            $objPost->langId        = filtering($_POST['langId'], 'input');
            $_SESSION["lId"]        = $langId;

            //for profile photo
            $profilePhoto = isset($profilePhoto) ? $profilePhoto : '';
            $old_image    = getTableValue('tbl_users', 'profilePhoto', array('userId' => $sessUserId));
            if ($old_image != $profilePhoto && $profilePhoto != null) {
                $objPost->profilePhoto = $profilePhoto;
                if (file_exists(DIR_IMG . 'profile/' . $old_image)) {
                    unlink(DIR_IMG . 'profile/' . $old_image);
                }
            }
            //for skill tags
            $db->delete('tbl_user_skills', array('userId' => $sessUserId));
            $skillsIds = (isset($_POST['hidden-skillsId']) && $_POST['hidden-skillsId'] != null) ? $_POST['hidden-skillsId'] : null;
            if ($skillsIds != null) {
                $ids_array = explode(',', $skillsIds);
                
                foreach ($ids_array as $k => $v) {
                    $query = getTableValue('tbl_skills', 'id', array('skillName_' . $_SESSION['lId'] => $v));
                    if ($query > 0) {
                        $db->insert('tbl_user_skills', array('userId' => $sessUserId, 'skillId' => $query));
                    } else {
                        /////////
                        $fetchRes   = $db->pdoQuery("SHOW COLUMNS FROM tbl_skills")->results();
                        $skillnmArr = array();
                        foreach ($fetchRes as $key => $value) {
                            if (startsWith($value["Field"], "skillName")) {
                                $skillnmArr[$value["Field"]] = $v;
                            }
                        }

                        $skillnmArr['skill_description'] = $v;
                        $skillnmArr['added_on']          = date('Y-m-d H:i:s');
                        //dump_exit($skillnmArr);
                        $last_id = $db->insert("tbl_skills", $skillnmArr)->lastInsertId();
                        /////////
                        $db->insert('tbl_user_skills', array('userId' => $sessUserId, 'skillId' => $last_id));
                    }
                }
            }
            $affectedRows = 0;
            $affectedRows = $db->update('tbl_users', (array) $objPost, array("userId" => $sessUserId))->affectedRows();
            if ($affectedRows) {
                $response['status'] = 1;
                $response['msg']    = toastr_Your_profile_has_been_updated_successfully;
            } else {
                $response['status'] = 0;
                $response['msg']    = toastr_Error_occured_while_updating_profile_Please_try_again_later;
            }

            $response['newToken'] = setFormToken();

        }
    }
    echo json_encode($response);

}
