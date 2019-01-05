<?php
$reqAuth = false;
$module  = 'forget-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.login-nct.php";

extract($_REQUEST);

$winTitle = $headTitle = 'Reactivate your account ' . SITE_NM;

$metaTag = getMetaTags(array(
    "description" => $winTitle,
    "keywords"    => $headTitle,
    "author"      => AUTHOR,
));

if ($sessUserId > 0) {
    $msgType = $_SESSION['msgType'] = disMessage(array(
        "type" => "suc",
        "var"  => "You are already loggedin.",
    ));
    redirectPage(SITE_URL);
} else if (isset($_POST['submitReactivateForm'])) {
    extract($_POST);

    if (!isset($dataOnly) || !$dataOnly) {
        if (!checkFormToken($token)) {
            $msgType = $_SESSION["msgType"] = disMessage(array(
                'type' => 'err',
                'var'  => 'Invalid request.',
            ));
            redirectPage(SITE_FORGOT);

        }
    }

    if (isset($email) && $email != null) {

        $selQuery = $db->select("tbl_users", array(
            "userId",
            "firstName",
            "lastName",
            "userName",
            "email",
            "status",
            "isActive",
        ), array(
            "email"        => $email,
            "or userName=" => $email,
        ));
        if ($selQuery->affectedRows() >= 1) {
            $result = $selQuery->result();
            if ($result != false) {
                extract($result);

                if ($status == "n") {
                    $msgType = $_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var'  => "Dear user, Your account is deactivated by " . SITE_NM . ". Please contact admin of " . SITE_NM . " to activate your account.",
                    ));
                    redirectPage(SITE_URL);

                } else if ($isActive == "n") {
                    $activationCode = $updatearray['activationCode'] = md5(time());
                    $activationLink = SITE_URL . 'active-account/' . $activationCode;

                    $db->update('tbl_users',$updatearray, array('userId'=>$result['userId']));

                    $to        = $email;
                    $arrayCont = array(
                        'greetings'      => $firstName,
                        'activationLink' => $activationLink,
                    );

                    $array = generateEmailTemplate('resend_activation_link', $arrayCont);
                    //echo $array['message'];exit;
                    sendEmailAddress($to, $array['subject'], $array['message']);

                    $msgType = $_SESSION['msgType'] = disMessage(array(
                        "type" => "suc",
                        "var"  => "We have sent new activation link to your email address, please check your inbox.",
                    ));
                    redirectPage(SITE_LOGIN);

                } else {

                    $msgType = $_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var'  => 'Dear user, your account is already active.',
                    ));
                    redirectPage(SITE_URL);
                }
            } else {
                $msgType = $_SESSION["msgType"] = disMessage(array(
                    'type' => 'err',
                    'var'  => "Invalid user name or email.",
                ));
                redirectPage(SITE_URL);
            }
        } else {
            $msgType = $_SESSION["msgType"] = disMessage(array(
                'type' => 'err',
                'var'  => "Invalid user name or email.",
            ));
            redirectPage(SITE_URL);
        }
    } else {
        $msgType = $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => 'Please enter your user name or email.',
        ));
    }

}

$obj = new Login($module, 0, issetor($token));

$pageContent = $obj->getReactivatePage();

require_once DIR_TMPL . "parsing-nct.tpl.php";
