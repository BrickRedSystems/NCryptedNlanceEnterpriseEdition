<?php
$reqAuth = false;
$module  = 'login-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.login-nct.php";

extract($_REQUEST);
$winTitle = $headTitle = 'Login ' . SITE_NM;

$metaTag = getMetaTags(array(
    "description" => $winTitle,
    "keywords"    => $headTitle,
    "author"      => AUTHOR,
));

if ($sessUserId > 0) {
    redirectPage(SITE_URL);
} else if (isset($_POST['submitLoginForm'])) {
    extract($_POST);
    ///////////////////
    if (!isset($dataOnly) || !$dataOnly) {
        if (!checkFormToken($token)) {
            $msgType = $_SESSION["msgType"] = disMessage(array(
                'type' => 'err',
                'var'  => Security_token_for_this_action_is_invalid_Please_refresh_and_try_again,
            ));
            redirectPage(SITE_LOGIN);
        }
    }
    ///////////////////

    if (isset($email) && isset($password)) {

        $entered_pass = $password;
        $selQuery     = $db->pdoQuery('select * from tbl_users where (email = ? or userName = ?) AND password =?', array(
            strtolower($email),
            $email,
            md5($password),
        ));
        $countUsr = $selQuery->affectedRows();
        $result   = $selQuery->result();

        // retrieve from DB
       /* $first_failed_login = getTableValue('tbl_login_attempts', 'first_failed_login', array(
            'ipAddress' => get_ip_address(),
        ));
        $failed_login_count = getTableValue('tbl_login_attempts', 'failed_login_count', array(
            'ipAddress' => get_ip_address(),
        ));*/
        //dump_exit(array($first_failed_login,$failed_login_count,get_ip_address()));

        //dump_exit(array(strtotime(date('Y-m-d H:i:s')),$first_failed_login,strtotime(date('Y-m-d H:i:s'))-strtotime($first_failed_login),(int)LOCKOUT_TIME));
        /*if (($failed_login_count >= BAD_LOGIN_LIMIT) && (strtotime(date('Y-m-d H:i:s')) - strtotime($first_failed_login) < (int) LOCKOUT_TIME)) {
            $msgType = $_SESSION["msgType"] = disMessage(array(
                'type' => 'err',
                'var'  => You_are_currently_locked_out_Please_try_again_after_some_time,
            ));

        } else */if ($countUsr >= 1) {

            if ($result != false) {
                extract($result);
                $pureSiteNm = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', SITE_NM)));
                if (isset($remember_me) && $remember_me == 'y') {
                    setcookie($pureSiteNm . 'userName', $userName, time() + 3600 * 24 * 30, '/', null, isset($_SERVER["HTTPS"]), true);
                    setcookie($pureSiteNm . 'password', $entered_pass, time() + 3600 * 24 * 30, '/', null, isset($_SERVER["HTTPS"]), true);
                    setcookie($pureSiteNm . 'rememberme', 'y', time() + 3600 * 24 * 30, '/', null, isset($_SERVER["HTTPS"]), true);
                } else {
                    setcookie($pureSiteNm . 'userName', '', time() - 3600, '/', null, isset($_SERVER["HTTPS"]), true);
                    setcookie($pureSiteNm . 'password', '', time() - 3600, '/', null, isset($_SERVER["HTTPS"]), true);
                    setcookie($pureSiteNm . 'rememberme', '', time() - 3600, '/', null, isset($_SERVER["HTTPS"]), true);
                }

                if ($isActive == "n") {
                    $msgType = $_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var'  => Dear_user_it_seems_like_you_have_not_activated_your_account_yet."<br/><br/><a href='".SITE_REACTIVATE."'>Click here to resend activation email</a>",
                    ));
                    //redirectPage(SITE_URL);
                } else if ($status == "d") {
                    $msgType = $_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var'  => deactivated_msg1 . " " . SITE_NM . ". " . deactivated_msg2 . " " . SITE_NM . " " . deactivated_msg3,
                    ));
                    redirectPage(SITE_URL);
                } else {

                    regenerateSession();
                    $_SESSION["userId"]      = $userId;
                    $_SESSION["firstName"]   = ucfirst(strtolower($firstName));
                    $_SESSION["lastName"]    = ucfirst(strtolower($lastName));
                    $_SESSION['userName']    = $userName;
                    $_SESSION['profileLink'] = $profileLink;
                    $_SESSION['userType']    = $userType;
                    $_SESSION['lId']         = $langId;
                    $msgType                 = $_SESSION["msgType"]                 = disMessage(array(
                        'type' => 'suc',
                        'var'  => Welcome_back . " " . ucfirst(strtolower($firstName)),
                    ));
                    /*$db->delete('tbl_login_attempts', array(
                        'ipAddress' => get_ip_address(),
                    ));*/
                    if ($userType == 't') {
                        redirectPage(SITE_USERTYPE);
                    }else if (isset($path) && $path != null) {
                        redirectPage($path);
                    }

                    redirectPage(SITE_DASHBOARD);
                }
            } else {
                if (strtotime(date('Y-m-d H:i:s')) - strtotime($first_failed_login) > LOCKOUT_TIME) {
                    // first unsuccessful login since LOCKOUT_TIME on the last one expired
                   /* $first_failed_login = date('Y-m-d H:i:s'); // commit to DB
                    $failed_login_count = 1; // commit to db

                    $db->insert('tbl_login_attempts', array(
                        'first_failed_login' => $first_failed_login,
                        'failed_login_count' => $failed_login_count,
                        'ipAddress'          => get_ip_address(),
                    ));*/
                } else {
                    /*$failed_login_count++; // commit to db.
                    $db->update('tbl_login_attempts', array(
                        'failed_login_count' => $failed_login_count,
                    ), array(
                        'ipAddress' => get_ip_address(),
                    ));*/
                }

                $msgType = $_SESSION["msgType"] = disMessage(array(
                    'type' => 'err',
                    'var'  => toastr_Invalid_login_please_verify_your_credentials,
                ));
                //redirectPage(SITE_URL);
            }
        } else {
            if (strtotime(date('Y-m-d H:i:s')) - strtotime($first_failed_login) > LOCKOUT_TIME) {
                // first unsuccessful login since LOCKOUT_TIME on the last one expired
                /*$first_failed_login = date('Y-m-d H:i:s'); // commit to DB

                if ($failed_login_count == 3) {
                    $db->update('tbl_login_attempts', array(
                        'first_failed_login' => $first_failed_login,
                    ), array(
                        'ipAddress' => get_ip_address(),
                    ));
                } else {
                    $failed_login_count = 1; // commit to db

                    $db->insert('tbl_login_attempts', array(
                        'first_failed_login' => $first_failed_login,
                        'failed_login_count' => $failed_login_count,
                        'ipAddress'          => get_ip_address(),
                    ));
                }*/

            } else {
                /*$failed_login_count++; // commit to db.
                $db->update('tbl_login_attempts', array(
                    'failed_login_count' => $failed_login_count,
                ), array(
                    'ipAddress' => get_ip_address(),
                ));*/
            }

            $msgType = $_SESSION["msgType"] = disMessage(array(
                'type' => 'err',
                'var'  => toastr_Invalid_login_please_verify_your_credentials,
            ));
            //redirectPage(SITE_URL);
        }
    } else {
        $msgType = $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => Please_fill_all_value_before_login_into . ' ' . SITE_NM,
        ));
    }

}

$obj = new Login($module, 0, issetor($token));

$pageContent = $obj->getPageContent();

require_once DIR_TMPL . "parsing-nct.tpl.php";
