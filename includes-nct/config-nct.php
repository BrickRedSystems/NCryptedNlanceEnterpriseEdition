<?php

ob_start();
session_name('NCT');
session_start();
set_time_limit(0);

session_set_cookie_params(3600);

date_default_timezone_set('Asia/Kolkata');

global $db, $rand_numers, $helper, $fields, $module, $adminUserId, $sessUserId, $objHome, $main_temp, $breadcrumb, $Permission, $memberId;
global $head, $header, $left, $right, $footer, $content, $title, $resend_email_verification_popup;
global $css_array, $js_array, $js_variables;
global $dataOnly;

$include_sharing_js = false;

$header_panel = true;
$footer_panel = true;
$styles       = array();
$scripts      = array();

$reqAuth = isset($reqAuth) ? $reqAuth : false;

$allowedUserType = isset($allowedUserType) ? $allowedUserType : 'a';


$_SESSION['rand_numers'] = rand(4,999999999);
$rand_numers = (isset($_SESSION["rand_numers"]) ? $_SESSION["rand_numers"] : '');

$adminUserId = (isset($_SESSION["adminUserId"]) && $_SESSION["adminUserId"] > 0 ? (int) $_SESSION["adminUserId"] : 0);

/*$_SESSION["userId"] = 99;
$_SESSION["firstName"] = "customer";
$_SESSION["lastName"] = "Ashish";
$_SESSION["userType"] = "c";
$_SESSION["userName"] = "customerashish";
$_SESSION["profileLink"] = "customerashish";*/

$sessUserId      = (isset($_SESSION["userId"]) && (int) $_SESSION["userId"] > 0 ? (int) $_SESSION["userId"] : 0);
$sessFirstName   = (isset($_SESSION["firstName"]) && $_SESSION["firstName"] != '' ? $_SESSION["firstName"] : null);
$sessLastName    = (isset($_SESSION["lastName"]) && $_SESSION["lastName"] != '' ? $_SESSION["lastName"] : null);
$sessUserType    = (isset($_SESSION["userType"]) && $_SESSION["userType"] != '' ? $_SESSION["userType"] : null);
$sessUserName    = (isset($_SESSION["userName"]) && $_SESSION["userName"] != '' ? $_SESSION["userName"] : null);
$sessProfileLink = (isset($_SESSION["profileLink "]) && $_SESSION["profileLink "] != '' ? $_SESSION["profileLink "] : null);

$toastr_message = isset($_SESSION["toastr_message"]) ? $_SESSION["toastr_message"] : null;
unset($_SESSION['toastr_message']);

$memberId = isset($sessUserId) ? $sessUserId : 0;



    if (strpos($_SERVER["SERVER_NAME"], '192.168.100.128') !== false OR strpos($_SERVER["SERVER_NAME"], 'localhost') !== false)
    {
        require_once($_SERVER["DOCUMENT_ROOT"].'/nlance_web/install-nct/install_config.php');
    }else{
        require_once($_SERVER["DOCUMENT_ROOT"].'/install-nct/install_config.php');
    }


    define('SITENAME', $_SERVER['SERVER_NAME']);
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    if(INSTALL_TYPE == 'local'){

        $rootfile = $_SERVER["DOCUMENT_ROOT"] . '/nlance_web/demo.txt';
        if(!file_exists($rootfile)){
            header('Location: '.$protocol.SITENAME.'/nlance_web/install');
            exit;
        }
    }else{
        $rootfile = $_SERVER["DOCUMENT_ROOT"] . '/demo.txt';
        if(!file_exists($rootfile)){
            header('Location: '.$protocol.SITENAME.'/install');
            exit;
        }
    }

require_once('database-nct.php');
require_once('main_nct.php');


require_once 'functions-nct/class.pdohelper.php';
require_once 'functions-nct/class.pdowrapper.php';
require_once 'functions-nct/class.pdowrapper-child.php';
require_once 'mime_type_lib.php';

$dbConfig = array(
    "host"     => DB_HOST,
    "dbname"   => DB_NAME,
    "username" => DB_USER,
    "password" => DB_PASS,
);
$db     = new PdoWrapper($dbConfig);
$helper = new PDOHelper();
if (ENVIRONMENT == 'p') {
    $db->setErrorLog(false);
} else {
    $db->setErrorLog(true);
}
require_once 'constant-nct.php';
require_once 'functions-nct/functions-nct.php';
//register_shutdown_function("fatal_handler");

require_once DIR_FUN . 'validation.class.php';

require_once DIR_INC . 'FirePHPCore/FirePHP.class.php';
global $fb;
$fb = FirePHP::getInstance(true);

curPageURL();
curPageName();

checkIfIsActive();
Authentication($reqAuth, true, $allowedUserType);

require "class.main_template-nct.php";

$main    = new MainTemplater();
$msgType = isset($_SESSION["msgType"]) ? $_SESSION["msgType"] : null;
unset($_SESSION['msgType']);

if (domain_details('dir') == 'admin-nct') {
    $left_panel = true;
    require_once DIR_ADM_INC . 'functions-nct/admin-function-nct.php';
    require_once DIR_ADM_MOD . 'home-nct/class.home-nct.php';
    $objHome = new Home($module, 0);

} else {

//start:: for multi-language
    if (!isset($_REQUEST['dataOnly']) || $_REQUEST['dataOnly'] !== true) {
        setLang();
    }
//end:: for multi-language

    require_once DIR_MOD . 'home-nct/class.home-nct.php';
    require_once DIR_INC . "paypal-nct/paypal_class.php";
    $objHome = new Home("home-nct");
}

$objPost = new stdClass();

$description = SITE_NM;
$keywords    = "";
