<?php
$reqAuth = false;
$module  = 'registration-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.registration-nct.php";

extract($_REQUEST);
$winTitle = $headTitle = Signup . ' - ' . SITE_NM;

$metaTag = getMetaTags(array(
    "description" => $winTitle,
    "keywords"    => $headTitle,
    "author"      => AUTHOR,
));

if ($sessUserId > 0) {
    $msgType = $_SESSION['msgType'] = disMessage(array(
        "type" => "suc",
        "var"  => You_are_already_logged_in,
    ));
    redirectPage(SITE_URL);
} else if (isset($_POST['submitSignup'])) {
    //dump_exit($_POST);

    extract($_POST);
    ///////////////////
    if (!isset($dataOnly) || !$dataOnly) {
        if (!checkFormToken($token)) {
            $_SESSION["msgType"] = disMessage(array(
                'type' => 'err',
                'var'  => Security_token_for_this_action_is_invalid_Please_refresh_and_try_again,
            ));
            redirectPage(SITE_REGISTER);

        }
    }
///////////////////
    $email     = isset($email) ? trim($email) : null;
    $password  = isset($password) ? $password : null;
    $cpass     = isset($password_confirmation) ? $password_confirmation : null;
    $firstName = isset($firstName) ? trim($firstName) : null;
    $lastName  = isset($lastName) ? trim($lastName) : null;
    $userName  = isset($userName) ? trim($userName) : null;
    $userType  = (isset($userType) && in_array($userType, array('c', 'p'))) ? trim($userType) : null;

    //START :: PHP validations
    

    if ($email == null) {
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => err_It_sure_doesnt_seem_like_a_valid_email,
        ));
        redirectPage(SITE_REGISTER);
    } else if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => err_It_sure_doesnt_seem_like_a_valid_email,
        ));
        redirectPage(SITE_REGISTER);
    }

    if ($firstName == null || preg_match("/^[a-zA-Z0-9]{2-25}$/", $firstName)) {
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => err_First_name_has_to_be_an_alphanumeric_value,
        ));
        redirectPage(SITE_REGISTER);
    }

    if ($lastName == null || preg_match("/^[a-zA-Z0-9]{2-25}$/", $lastName)) {
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => err_Last_name_has_to_be_an_alphanumeric_value,
        ));
        redirectPage(SITE_REGISTER);
    }

    if ($userName == null || preg_match("/^[a-zA-Z0-9]{2-25}$/", $userName)) {
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => err_User_name_should_have_atleast,
        ));
        redirectPage(SITE_REGISTER);
    }

    if ($userType == null) {
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => err_Please_provide_a_valid_user_type,
        ));
        redirectPage(SITE_REGISTER);
    }

    if ($password == null) {
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => err_Please_enter_a_strong_password,
        ));
        redirectPage(SITE_REGISTER);
    } else if ($password != $cpass) {
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => err_Password_and_confirm_password_must_be_same,
        ));
        redirectPage(SITE_REGISTER);
    }
    //END :: PHP validations

    //START:: signup process
    $isExist = $db->select('tbl_users', array('userId'), array('email' => $email), ' LIMIT 1');

    if ($isExist->affectedRows() > 0) {
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => Email_you_have_entered_is_already_registered,
        ));
        redirectPage(SITE_REGISTER);
    } else {
        $insertarray = array(
            "email"       => $email,
            "password"    => md5($password),
            "userName"    => $userName,
            "profileLink" => $userName,
            "countryCode" => $countryCode,
            "contactCode" => $contactCode,
            "contactNo"   => $contactNo,
            "firstName"   => $firstName,
            "lastName"    => $lastName,
            "userType"    => $userType,
            "isActive"    => 'n',
            "ipAddress"   => get_ip_address(),
            "createdDate" => date('Y-m-d H:i:s'),
            "langId" => $langId
        );

        $activationCode = $insertarray['activationCode'] = md5(time());
        $activationLink = SITE_URL . 'active-account/' . $activationCode;

        $insert_id = $db->insert('tbl_users', $insertarray)->getLastInsertId();

        $to        = $email;
        $arrayCont = array(
            'greetings'      => $firstName,
            'activationLink' => $activationLink,
        );
        $_SESSION['sendMailTo'] = issetor($insert_id, 0);

        $array = generateEmailTemplate('user_register', $arrayCont);
        //echo $array['message'];exit;
        sendEmailAddress($to, $array['subject'], $array['message']);

        $msgType = $_SESSION["msgType"] = disMessage(array(
            'type' => 'suc',
            'var'  => successfully_registered_part1 . ' ' . SITE_NM . '. ' . successfully_registered_part2,
        ));
        redirectPage(SITE_LOGIN);
    }
    //END:: signup process

}

$obj = new Registration($module, $_REQUEST);

$pageContent = $obj->getPageContent();

require_once DIR_TMPL . "parsing-nct.tpl.php";
