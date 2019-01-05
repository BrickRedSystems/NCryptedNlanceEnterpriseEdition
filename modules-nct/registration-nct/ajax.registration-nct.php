<?php
$reqAuth = false;
$module  = 'registration-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.registration-nct.php";

$winTitle = 'Registration ' . SITE_NM;

$headTitle = 'Registration' . SITE_NM;
$metaTag   = getMetaTags(array(
    "description" => $winTitle,
    "keywords"    => $headTitle,
    "author"      => AUTHOR,
));
$obj = new Registration($module, 0, issetor($token));

$response['status'] = '0';
$response['msg']    = "undefined";
//Oops! something went wrong. Please try again later.
if (isset($_POST['action']) && $_POST['action'] == 'chk_uname') {

    $response = array(
        'valid'   => false,
        'message' => Post_argument_user_is_missing,
    );

    if (isset($_POST['userName'])) {
        $user = getTableValue('tbl_users', 'userId', array('userName' => $_POST['userName']));

        if ($user) {
            // User name is registered on another account
            $response = array(
                'valid'   => false,
                'message' => This_user_name_is_already_registered,
            );
        } elseif (strlen($_POST['userName']) < 3) {
            $response = array(
                'valid'   => false,
                'message' => err_User_name_should_have_atleast,
            );
        } else {
            $response = array('valid' => true);
        }

    }
    echo json_encode($response);
    exit;

} elseif (isset($_POST['action']) && $_POST['action'] == 'chk_email') {

    $response = array(
        'valid'   => false,
        'message' => 'Post argument "email" is missing.',
    );

    if (isset($_POST['email'])) {
        $email = getTableValue('tbl_users', 'userId', array('email' => $_POST['email']));

        if ($email) {
            // email is registered on another account
            $response = array(
                'valid'   => false,
                'message' => This_email_is_already_registered,
            );
        } else {
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
                $response = array('valid' => true);
            } else {
                $response = array(
                    'valid'   => false,
                    'message' => err_It_sure_doesnt_seem_like_a_valid_email,
                );
            }
        }

    }
    echo json_encode($response);
    exit;

} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'socialLogin') {

    extract($_REQUEST);

    if ($provider == 'google') {
        $img     = $_REQUEST['picture'] . '?width=500&height=500';
        $imgNm   = md5(time() . rand());
        $image   = $imgNm . '.png';
        $content = file_get_contents($img);
        file_put_contents(DIR_UPD . 'profile/' . $image, $content);
    } else if ($provider == 'facebook') {
        $img     = $_REQUEST['picture'] . '?width=500&height=500';
        $imgNm   = md5(time() . rand());
        $image   = $imgNm . '.png';
        $content = file_get_contents($img);
        file_put_contents(DIR_UPD . 'profile/' . $image, $content);
    } else if ($provider == 'linkedin') {
        $img     = $_REQUEST['pictureUrl'] . '?width=500&height=500';
        $imgNm   = md5(time() . rand());
        $image   = $imgNm . '.png';
        $content = file_get_contents($img);
        file_put_contents(DIR_UPD . 'profile/' . $image, $content);
    } else {
        //do nothing.
    }

    if ($sessUserId > 0) {
        $response['status']  = 1;
        $response['msg']     = Welcome_back . " " . $_SESSION['userName'];
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'suc',
            'var'  => $response['msg'],
        ));
    }

    $firstName  = $first_name;
    $lastName   = $last_name;
    $userName   = makeSlug($first_name . $last_name, 'tbl_users', 'userId', 'userName', 'name');
    $email      = (isset($email) ? $email : $emails['value']);
    $identifier = $id;
    $password   = generatePassword();
    $data       = $db->select('tbl_users', '*', array('email' => $email));
    $affrows    = $data->affectedRows();

    if ($affrows > 0) {
        $res = $data->result();
        if ($res['isActive'] == 'y' && $res['status'] == 'a') {
            $_SESSION['userId']      = $res['userId'];
            $_SESSION['userName']    = $res['userName'];
            $_SESSION['profileLink'] = $userName;
            $_SESSION['userType']    = $res['userType'];
            $_SESSION['firstName']   = ucfirst(strtolower($res['firstName']));
            $_SESSION['lId']         = $res['langId'];
            //update social verification accordingly
            $db->update('tbl_users',array($provider.'_verify'=>1), array('userId'=>$res['userId']));

            $response['status'] = 1;
            $response['redirect'] = ($res['userType'] == 't') ? SITE_USERTYPE : SITE_DASHBOARD;

            $response['msg']     = Welcome_back . " " . $_SESSION['userName'];
            $_SESSION["msgType"] = disMessage(array(
                'type' => 'suc',
                'var'  => $response['msg'],
            ));

        } else {
            $response['status']   = 0;
            $response['msg']      = Your_account_is_no_longer_active;
            $response['redirect'] = SITE_URL;
            $_SESSION["msgType"]  = disMessage(array(
                'type' => 'err',
                'var'  => $response['msg'],
            ));
        }

    } else {
        $pass   = generatePassword();
        $insArr = array(
            'firstName'       => $firstName,
            'lastName'        => $lastName,
            'userName'        => $userName,
            'profilePhoto'    => $image,
            'email'           => $email,
            'isActive'        => 'y',
            'status'          => 'a',
            'loginType'       => ($provider == 'facebook') ? 'f' : ($provider == 'google' ? 'g' : 'l'),
            'activationCode'  => md5(time()),
            'createdDate'     => date("Y-m-d h:i:s"),
            'lastLoginTime'   => date("Y-m-d h:i:s"),
            'profileLink'     => $userName,
            'password'        => md5($pass),
            'ipAddress'       => get_ip_address(),
            'facebook_verify' => ($provider == 'facebook') ? 1 : 0,
            'google_verify'   => ($provider == 'google') ? 1 : 0,
            'linkedin_verify' => ($provider == 'linkedin') ? 1 : 0,
            'langId'          => $_SESSION['lId'],
        );
        $data = $db->insert('tbl_users', $insArr)->getLastInsertId();

        if ($data > 0) {
            $email_address = $email;

            $to        = $email;
            $arrayCont = array(
                'greetings' => $firstName,
                'USERNAME'  => $userName,
                'PASSWORD'  => $pass,
            );
            $_SESSION['sendMailTo'] = issetor($data, 0);

            $array = generateEmailTemplate('social_signup', $arrayCont);
            sendEmailAddress($email_address, $array['subject'], $array['message']);

            $qrySelNL                        = $db->select("tbl_newsletters", "*", array("id" => 1))->result();
            $arrayCont                       = array();
            $arrayCont['subject']            = $qrySelNL['newsletter_subject'];
            $arrayCont['newsletter_content'] = $qrySelNL['newsletter_content'];
            $arrayCont['greetings']          = $firstName;
            $array                           = generateEmailTemplate('newsletter', $arrayCont);
            sendEmailAddress($email_address, $arrayCont['subject'], $array['message']);

            $_SESSION['userId']      = $data;
            $_SESSION['userName']    = $userName;
            $_SESSION['profileLink'] = $userName;
            $_SESSION['firstName']   = ucfirst(strtolower($firstName));

            $response['status']   = 1;
            $response['redirect'] = SITE_USERTYPE;
            $response['msg']      = Welcome_to . " " . SITE_NM . " " . ucfirst(strtolower($userName));

            $_SESSION["msgType"] = disMessage(array(
                'type' => 'suc',
                'var'  => $response['msg'],
            ));

        } else {

        }
    }
} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'activation') {
    extract($_REQUEST);
    $activationCode = isset($activationcode) ? $activationcode : null;

    if ($activationCode != null) {
        $selUser = $db->pdoQuery('SELECT userId,status,isActive,userType FROM tbl_users WHERE activationCode = ? LIMIT 1', array($activationCode));

        if ($selUser->affectedRows() > 0) {
            $fetchUser = $selUser->result();
            if ($fetchUser['isActive'] == 'y') {

                if ($fetchUser['status'] == 'd') {
                    $_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var'  => Your_account_has_been_deactivated_by_admin,
                    ));
                } else {
                    $_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var'  => You_have_already_activated_your_account,
                    ));
                }

                redirectPage(SITE_URL . 'sign-up/');
            } else {

                $id = $fetchUser['userId'];

                $db->update('tbl_users', array(
                    'isActive' => 'y',
                    'status'   => 'a',
                ), array("userId" => $id));
                $_SESSION["msgType"] = disMessage(array(
                    'type' => 'suc',
                    'var'  => email_Verification_completed,
                ));
                redirectPage(SITE_URL . 'login/');

            }

        } else {
            $_SESSION["msgType"] = disMessage(array(
                'type' => 'err',
                'var'  => Verification_failed,
            ));
            redirectPage(SITE_URL);

        }
    }
    $_SESSION["msgType"] = disMessage(array(
        'type' => 'err',
        'var'  => Something_went_wrong,
    ));
    redirectPage(SITE_URL);
} else if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'fetchContactCode') {

    $response['status'] = 1;
    $response['msg']    = 'undefined';
    $response['html']   = $obj->contactCodeOptions(null, $_REQUEST['countryId']);

}

echo json_encode($response);
exit;
