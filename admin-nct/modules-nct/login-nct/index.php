<?php

$reqAuth = false;

require_once("../../../includes-nct/config-nct.php");
require_once("class.login-nct.php");
if ($adminUserId > 0) {
    redirectPage(SITE_ADM_MOD . 'home-nct');
}
$header_panel = false;
$left_panel = false;
$footer_panel = false;

$winTitle = 'Login - ' . SITE_NM;
$headTitle = 'Login';
$styles = array("pages/login.css");
$scripts = array("custom/login.js");
$module = 'login-nct';
$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    'author' => AUTHOR));
//print_r($_POST);
//exit;
$objUser = new Login();

if (isset($_POST["uEmail"])) {

    extract($_POST);
    $objPost->uEmail = isset($uEmail) ? filtering($uEmail, 'input') : '';
    if ($objPost->uEmail != "") {
        $loginReturn1 = $objUser->forgotProdedure();
        switch ($loginReturn1) {
            case 'succForgotPass' : {
                    $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'You have successfully requested for forgot password, Please check your email.'));
                    redirectPage(SITE_ADM_MOD . 'login-nct/');
                    break;
                }
            case 'wrongUsername' : {
                    $toastr_message = $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'You have entered wrong email.'));
                    redirectPage(SITE_ADM_MOD . 'login-nct/');
                    break;
                }
        }
    } else {
        //$_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>'Please fill all required fields carefully.'));
    }
}

if (isset($_POST["submitLogin"])) {

    extract($_POST);
    $objPost->uName = isset($uName) ? filtering($uName, 'input') : '';
    $objPost->uPass = isset($uPass) ? filtering($uPass, 'input') : '';
    $objPost->isRemember = isset($remember) ? $remember : '';

    if ($objPost->isRemember == 'y') {
        setcookie('remember', 'y', time() + 3600);
        setcookie('uName', $objPost->uName, time() + 3600);
        setcookie('uPass', base64_encode($objPost->uPass), time() + 3600);
    } else {
        setcookie('remember', '');
        setcookie('uName', '');
        setcookie('uPass', '');
    }

    if ($objPost->uName != "" && $objPost->uPass != "") {
        $objUser = new Login();
        $loginReturn = $objUser->loginSubmit();

        switch ($loginReturn) {
            case 'invaildUsers' : $toastr_message = disMessage(array('type' => 'err', 'var' => 'Please enter valid user name or password.'));
                break;
            case 'inactivatedUser' : $toastr_message = disMessage(array('type' => 'err', 'var' => 'This user is not active.'));
                break;
            case 'invaildUsersAd' : $toastr_message = disMessage(array('type' => 'err', 'var' => 'This user is not valid any more.'));
                break;
        }
    }
}
if ($toastr_message == '' && isset($_SESSION['req_uri_adm']) && $_SESSION['req_uri_adm'] != '') {
    if (!isset($_SESSION['loginDisplayed_adm'])) {
        $toastr_message = array('type' => 'err', 'var' => 'Please login to continue.');
        $_SESSION['loginDisplayed_adm'] = 1;
    }
}
if (isset($_COOKIE["remember"]) && $_COOKIE["remember"] == 'y') {
    $objPost->uName = isset($_COOKIE["uName"]) ? $_COOKIE["uName"] : '';
    $objPost->uPass = isset($_COOKIE["uPass"]) ? base64_decode($_COOKIE["uPass"]) : '';
    $objPost->isRemember = 'y';
}

$pageContent = $objUser->getPageContent();
require_once(DIR_ADMIN_TMPL . "parsing-nct.tpl.php");
